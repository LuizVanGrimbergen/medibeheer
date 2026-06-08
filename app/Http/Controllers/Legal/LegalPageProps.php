<?php

namespace App\Http\Controllers\Legal;

use Illuminate\Http\Request;

final class LegalPageProps
{
    /** @return array{policyVersion: string, termsVersion: string, contactEmail: string, retention: array<string, int>, controller: array{name: string, address: string|null, kbo: string|null}, documentLocale: string} */
    public static function forInertia(?Request $request = null): array
    {
        /** @var string|null $contactEmail */
        $contactEmail = config('privacy.contact_email');

        if (! is_string($contactEmail) || $contactEmail === '') {
            $contactEmail = config('mail.from.address');
        }

        /** @var array<string, int> $retention */
        $retention = config('privacy.retention');

        /** @var array{name?: string, address?: string|null, kbo?: string|null} $controllerConfig */
        $controllerConfig = config('legal.controller', []);

        $controllerName = $controllerConfig['name'] ?? 'Medibeheer';
        $controllerAddress = $controllerConfig['address'] ?? null;
        $controllerKbo = $controllerConfig['kbo'] ?? null;

        return [
            'policyVersion' => (string) config('privacy.policy_version'),
            'termsVersion' => (string) config('legal.terms_version'),
            'contactEmail' => (string) $contactEmail,
            'retention' => $retention,
            'controller' => [
                'name' => is_string($controllerName) ? $controllerName : 'Medibeheer',
                'address' => is_string($controllerAddress) && $controllerAddress !== '' ? $controllerAddress : null,
                'kbo' => is_string($controllerKbo) && $controllerKbo !== '' ? $controllerKbo : null,
            ],
            'documentLocale' => self::resolveDocumentLocale($request ?? request()),
        ];
    }

    public static function resolveDocumentLocale(Request $request): string
    {
        $lang = $request->query('lang');

        if (is_string($lang)) {
            /** @var list<string> $supportedLocales */
            $supportedLocales = config('legal.supported_document_locales', ['nl', 'en']);

            if (in_array($lang, $supportedLocales, true)) {
                return $lang;
            }
        }

        $preferred = $request->getPreferredLanguage(['nl', 'en']);

        if (is_string($preferred) && $preferred !== '') {
            return $preferred;
        }

        return 'nl';
    }
}
