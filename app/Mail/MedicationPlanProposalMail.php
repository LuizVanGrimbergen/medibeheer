<?php

namespace App\Mail;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MedicationPlanProposalMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public CarbonInterface $expiresAt,
        public string $medicationName,
        public string $familyPageUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans('mail.medication_plan_proposal.subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.medication-plan-proposal',
            with: [
                'expiresAt' => $this->expiresAt,
                'medicationName' => $this->medicationName,
                'familyPageUrl' => $this->familyPageUrl,
            ],
        );
    }
}
