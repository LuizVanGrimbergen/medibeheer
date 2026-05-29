<?php

namespace App\Mail;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FamilyInvitationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public CarbonInterface $expiresAt,
        public string $patientName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans('mail.family_invitation.subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.family-invitation',
            with: [
                'acceptUrl' => route('family.invitation.entry', absolute: true),
                'expiresAt' => $this->expiresAt,
                'patientName' => $this->patientName,
            ],
        );
    }
}
