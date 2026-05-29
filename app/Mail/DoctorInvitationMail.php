<?php

namespace App\Mail;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DoctorInvitationMail extends Mailable
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
            subject: trans('mail.doctor_invitation.subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.doctor-invitation',
            with: [
                'acceptUrl' => route('doctor.invitation.entry', absolute: true),
                'expiresAt' => $this->expiresAt,
                'patientName' => $this->patientName,
            ],
        );
    }
}
