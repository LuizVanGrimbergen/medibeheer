<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Notifications\Auth\ResetPasswordNotification;
use App\Notifications\Auth\VerifyEmailNotification;
use App\Observers\UserObserver;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use NotificationChannels\WebPush\HasPushSubscriptions;

#[ObservedBy(UserObserver::class)]
class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory;
    use HasPushSubscriptions;
    use Notifiable;

    /**************************************/
    /*             Attributes */
    /**************************************/

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'name_encrypted',
        'email_encrypted',
        'email_hash',
    ];

    protected $attributes = [
        'role' => null,
    ];

    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if ($user->role === null) {
                $user->role = UserRole::PATIENT;
            }
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**************************************/
    /*           Relationships */
    /**************************************/

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class);
    }

    public function family(): HasOne
    {
        return $this->hasOne(Family::class);
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    public function consents(): HasMany
    {
        return $this->hasMany(UserConsent::class);
    }

    /**************************************/
    /*       Accessors / Mutators */
    /**************************************/

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->name_encrypted === null
                ? ''
                : Crypt::decryptString($this->name_encrypted),
            set: fn (string $value): array => [
                'name_encrypted' => Crypt::encryptString(static::normalizeName($value)),
            ],
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->email_encrypted === null
                ? ''
                : Crypt::decryptString($this->email_encrypted),
            set: fn (string $value): array => [
                'email_encrypted' => Crypt::encryptString(static::normalizeEmail($value)),
                'email_hash' => static::hashEmail($value),
            ],
        );
    }

    /**************************************/
    /*              Scopes */
    /**************************************/

    /**************************************/
    /*              Helpers */
    /**************************************/

    public static function normalizeName(string $name): string
    {
        return trim($name);
    }

    public static function normalizeEmail(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    public static function hashEmail(string $email): string
    {
        return static::hashEmailWithKey($email, static::resolveEmailHashKey());
    }

    public static function emailHashCandidates(string $email): array
    {
        $candidates = [static::hashEmail($email)];

        foreach (config('app.email_hash_previous_keys', []) as $previousEmailHashKey) {
            if (! is_string($previousEmailHashKey) || $previousEmailHashKey === '') {
                continue;
            }

            $candidates[] = static::hashEmailWithKey($email, $previousEmailHashKey);
        }

        return array_values(array_unique($candidates));
    }

    public static function findByEmail(string $email): ?self
    {
        return static::query()
            ->whereIn('email_hash', static::emailHashCandidates($email), 'and', false)
            ->first();
    }

    protected static function hashEmailWithKey(string $email, string $key): string
    {
        return hash_hmac('sha256', static::normalizeEmail($email), $key);
    }

    protected static function resolveEmailHashKey(): string
    {
        $emailHashKey = config('app.email_hash_key');

        if (is_string($emailHashKey) && $emailHashKey !== '') {
            return $emailHashKey;
        }

        return (string) config('app.key');
    }

    /** The password broker stores the hashed email identifier in its email column. */
    public function getEmailForPasswordReset(): string
    {
        return $this->email_hash;
    }

    public function isPatient(): bool
    {
        return $this->role === UserRole::PATIENT;
    }

    public function isDoctor(): bool
    {
        return $this->role === UserRole::DOCTOR;
    }

    public function isFamilyMember(): bool
    {
        return $this->role === UserRole::FAMILY_MEMBER;
    }

    public function familyOrCreate(): Family
    {
        return $this->family ?? $this->family()->firstOrCreate([
            'user_id' => $this->id,
        ]);
    }

    public function isFamilyLinkedToPatient(Patient $patient): bool
    {
        if (! $this->isFamilyMember()) {
            return false;
        }

        $family = $this->family;

        if ($family === null) {
            return false;
        }

        return $family->patients()->whereKey($patient->getKey())->exists();
    }

    public function defaultAuthenticatedHomeRoute(): string
    {
        if ($this->isPatient()) {
            return 'patient.dashboard';
        }

        if ($this->isFamilyMember()) {
            return 'family.overview';
        }

        if ($this->isDoctor()) {
            return 'doctor.dashboard';
        }

        return 'settings.edit';
    }

    public function defaultAuthenticatedHomeUrl(): string
    {
        return route($this->defaultAuthenticatedHomeRoute(), absolute: false);
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification((string) $token));
    }
}
