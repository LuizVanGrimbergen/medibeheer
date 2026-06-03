<?php

namespace App\Enums\Concerns;

use BackedEnum;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use UnexpectedValueException;
use ValueError;

/**
 * @phpstan-require-extends BackedEnum
 *
 * @method static list<static&\BackedEnum> cases()
 * @method static static|null tryFrom(mixed $value)
 */
trait EncryptEnum
{
    public function encryptForTransport(): string
    {
        return Model::currentEncrypter()->encrypt($this->value, false);
    }

    /** @return array<string, string> */
    public static function encryptedTransportTokens(): array
    {
        $tokens = [];

        foreach (static::cases() as $case) {
            $tokens[$case->value] = $case->encryptForTransport();
        }

        return $tokens;
    }

    public static function tryFromEncryptedTransport(?string $token): ?static
    {
        if ($token === null || $token === '') {
            return null;
        }

        try {
            $plain = Model::currentEncrypter()->decrypt($token, false);
        } catch (DecryptException) {
            return null;
        }

        return static::tryFrom($plain);
    }

    public static function castUsing(array $arguments = []): CastsAttributes
    {
        return new class(static::class) implements CastsAttributes
        {
            public function __construct(private readonly string $enumClass) {}

            public function get(Model $model, string $key, mixed $value, array $attributes): ?BackedEnum
            {
                if ($value === null || $value === '') {
                    return null;
                }

                $plain = Model::currentEncrypter()->decrypt($value, false);

                try {
                    return $this->enumClass::from($plain);
                } catch (ValueError) {
                    throw new UnexpectedValueException(
                        sprintf('EncryptEnum: decrypted value is not a valid case of [%s] on [%s::$%s].', $this->enumClass, $model::class, $key),
                    );
                }
            }

            public function set(Model $model, string $key, mixed $value, array $attributes): array
            {
                if ($value === null) {
                    return [$key => null];
                }

                $enum = $value instanceof BackedEnum
                    ? $value
                    : $this->enumClass::from((string) $value);

                return [$key => Model::currentEncrypter()->encrypt($enum->value, false)];
            }
        };
    }
}
