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
        public string $plainToken,
        public CarbonInterface $expiresAt,
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
                'inviteCode' => $this->plainToken,
                'expiresAt' => $this->expiresAt,
            ],
        );
    }
}
