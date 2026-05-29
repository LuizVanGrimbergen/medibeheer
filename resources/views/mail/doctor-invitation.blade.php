<x-mail::message>
# {{ trans('mail.doctor_invitation.greeting') }}

{{ trans('mail.doctor_invitation.line', ['patient' => $patientName]) }}

<x-mail::button :url="$acceptUrl">
{{ trans('mail.doctor_invitation.action') }}
</x-mail::button>

{{ trans('mail.doctor_invitation.review_hint') }}

{{ trans('mail.doctor_invitation.expires', ['datetime' => $expiresAt->timezone(config('app.timezone'))->translatedFormat('d-m-Y H:i')]) }}

{{ trans('mail.doctor_invitation.footer') }}

{{ trans('mail.doctor_invitation.salutation') }}

{{ config('mail.brand') }}
</x-mail::message>
