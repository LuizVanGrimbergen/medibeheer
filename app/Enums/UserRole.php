<?php

namespace App\Enums;

use App\Enums\Concerns\EncryptEnum;
use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;

enum UserRole: string implements Castable
{
    use EncryptEnum;

    case PATIENT = 'patient';
    case DOCTOR = 'doctor';
    case FAMILY_MEMBER = 'family_member';

    public function encryptForTransport(): string
    {
        return Model::currentEncrypter()->encrypt($this->value, false);
    }

    /** @return array<string, string> */
    public static function encryptedTransportTokens(): array
    {
        $tokens = [];

        foreach (self::cases() as $case) {
            $tokens[$case->value] = $case->encryptForTransport();
        }

        return $tokens;
    }

    public static function tryFromEncryptedTransport(?string $token): ?self
    {
        if ($token === null || $token === '') {
            return null;
        }

        try {
            $plain = Model::currentEncrypter()->decrypt($token, false);
        } catch (DecryptException) {
            return null;
        }

        return self::tryFrom($plain);
    }
}
