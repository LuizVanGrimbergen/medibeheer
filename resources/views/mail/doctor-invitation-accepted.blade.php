<x-mail::message>
# {{ trans('mail.doctor_invitation_accepted.greeting') }}

{{ trans('mail.doctor_invitation_accepted.line', ['name' => $accepterName]) }}

<x-mail::button :url="$doctorsPageUrl">
{{ trans('mail.doctor_invitation_accepted.action') }}
</x-mail::button>

{{ trans('mail.doctor_invitation_accepted.footer') }}

{{ trans('mail.doctor_invitation_accepted.salutation') }}

{{ config('mail.brand') }}
</x-mail::message>
