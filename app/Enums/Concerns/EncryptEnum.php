<?php

namespace App\Enums\Concerns;

use BackedEnum;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use LogicException;
use UnexpectedValueException;
use ValueError;

trait EncryptEnum
{
    public function encryptForTransport(): string
    {
        if (! $this instanceof BackedEnum) {
            throw new LogicException(
                sprintf('encryptForTransport can only be called on BackedEnum cases, [%s] is not a BackedEnum case.', static::class),
            );
        }

        return Model::currentEncrypter()->encrypt($this->value, false);
    }

    /** @return array<string, string> */
    public static function encryptedTransportTokens(): array
    {
        if (! is_a(static::class, BackedEnum::class, true)) {
            throw new LogicException(
                sprintf('encryptedTransportTokens can only be used on BackedEnum classes, [%s] is not a BackedEnum.', static::class),
            );
        }

        $tokens = [];

        foreach (static::cases() as $case) {
            /** @var BackedEnum&EncryptEnum $case */
            $tokens[$case->value] = $case->encryptForTransport();
        }

        return $tokens;
    }

    public static function tryFromEncryptedTransport(?string $token): ?static
    {
        if (! is_a(static::class, BackedEnum::class, true)) {
            throw new LogicException(
                sprintf('tryFromEncryptedTransport can only be used on BackedEnum classes, [%s] is not a BackedEnum.', static::class),
            );
        }

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

    public static function castUsing(array $arguments): CastsAttributes
    {
        if (! is_a(static::class, BackedEnum::class, true)) {
            throw new LogicException(
                sprintf('EncryptEnum can only be used on BackedEnum classes, [%s] is not a BackedEnum.', static::class),
            );
        }

        return new class(static::class) implements CastsAttributes
        {
            public function __construct(private readonly string $enumClass) {}

            public function get(Model $model, string $key, mixed $value, array $attributes): ?BackedEnum
            {
                if ($value === null) {
                    return null;
                }

                if ($value === '') {
                    Log::warning('EncryptEnum: empty string found in DB column, expected null or ciphertext.', [
                        'model' => $model::class,
                        'key' => $key,
                    ]);

                    return null;
                }

                try {
                    $plain = Model::currentEncrypter()->decrypt($value, false);
                } catch (DecryptException $e) {
                    Log::error('EncryptEnum: failed to decrypt value.', [
                        'model' => $model::class,
                        'key' => $key,
                        'exception' => $e->getMessage(),
                    ]);

                    throw $e;
                }

                try {
                    return $this->enumClass::from($plain);
                } catch (ValueError) {
                    throw new UnexpectedValueException(
                        sprintf(
                            'EncryptEnum: decrypted value is not a valid case of [%s] on [%s::$%s].',
                            $this->enumClass,
                            $model::class,
                            $key,
                        ),
                    );
                }
            }

            public function set(Model $model, string $key, mixed $value, array $attributes): array
            {
                if ($value === null) {
                    return [$key => null];
                }

                if ($value instanceof BackedEnum) {
                    $enum = $value;
                }

                if (! isset($enum)) {
                    try {
                        $enum = $this->enumClass::from((string) $value);
                    } catch (ValueError) {
                        throw new UnexpectedValueException(
                            sprintf(
                                'EncryptEnum: value is not a valid case of [%s] for [%s::$%s].',
                                $this->enumClass,
                                $model::class,
                                $key,
                            ),
                        );
                    }
                }

                return [$key => Model::currentEncrypter()->encrypt($enum->value, false)];
            }
        };
    }
}
