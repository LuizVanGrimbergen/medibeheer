<x-mail::message>
# {{ trans('mail.family_invitation_accepted.greeting') }}

{{ trans('mail.family_invitation_accepted.line', ['name' => $accepterName]) }}

<x-mail::button :url="$familyPageUrl">
{{ trans('mail.family_invitation_accepted.action') }}
</x-mail::button>

{{ trans('mail.family_invitation_accepted.footer') }}

{{ trans('mail.family_invitation_accepted.salutation') }}

{{ config('mail.brand') }}
</x-mail::message>
