<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DoctorInvitationAcceptedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $accepterName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans('mail.doctor_invitation_accepted.subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.doctor-invitation-accepted',
            with: [
                'accepterName' => $this->accepterName,
                'doctorsPageUrl' => route('patient.doctors', absolute: true),
            ],
        );
    }
}
