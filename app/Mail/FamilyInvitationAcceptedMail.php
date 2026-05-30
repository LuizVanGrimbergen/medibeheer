<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FamilyInvitationAcceptedMail extends Mailable implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $accepterName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans('mail.family_invitation_accepted.subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.family-invitation-accepted',
            with: [
                'accepterName' => $this->accepterName,
                'familyPageUrl' => route('patient.family', absolute: true),
            ],
        );
    }
}
