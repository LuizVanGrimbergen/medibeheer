<x-mail::message>
    {{ trans('mail.family_invitation.greeting') }}

    {{ trans('mail.family_invitation.line') }}

    <x-mail::panel>
        <code style="word-break: break-all;">{{ $inviteCode }}</code>
    </x-mail::panel>

    {{ trans('mail.family_invitation.expires', ['datetime' => $expiresAt->timezone(config('app.timezone'))->translatedFormat('d-m-Y H:i')]) }}

    {{ trans('mail.family_invitation.footer') }}

    {{ trans('mail.family_invitation.salutation') }}<br>
    {{ config('app.name') }}
</x-mail::message>
