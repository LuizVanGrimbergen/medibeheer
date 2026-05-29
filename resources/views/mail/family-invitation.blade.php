<x-mail::message>
# {{ trans('mail.family_invitation.greeting') }}

{{ trans('mail.family_invitation.line', ['patient' => $patientName]) }}

<x-mail::button :url="$acceptUrl">
{{ trans('mail.family_invitation.action') }}
</x-mail::button>

{{ trans('mail.family_invitation.review_hint') }}

{{ trans('mail.family_invitation.expires', ['datetime' => $expiresAt->timezone(config('app.timezone'))->translatedFormat('d-m-Y H:i')]) }}

{{ trans('mail.family_invitation.footer') }}

{{ trans('mail.family_invitation.salutation') }}

{{ config('mail.brand') }}
</x-mail::message>
