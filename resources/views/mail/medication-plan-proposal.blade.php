<x-mail::message>
    {{ trans('mail.medication_plan_proposal.greeting') }}

    {{ trans('mail.medication_plan_proposal.line', ['medication' => $medicationName]) }}

    <x-mail::button :url="$familyPageUrl">
        {{ trans('mail.medication_plan_proposal.action') }}
    </x-mail::button>

    {{ trans('mail.medication_plan_proposal.review_hint') }}

    {{ trans('mail.medication_plan_proposal.expires', ['datetime' => $expiresAt->timezone(config('app.timezone'))->translatedFormat('d-m-Y H:i')]) }}

    {{ trans('mail.medication_plan_proposal.footer') }}

    {{ trans('mail.medication_plan_proposal.salutation') }}<br>
    {{ config('app.name') }}
</x-mail::message>
