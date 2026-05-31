<?php

use App\Mail\DoctorInvitationAcceptedMail;
use App\Mail\DoctorInvitationMail;
use App\Mail\FamilyInvitationAcceptedMail;
use App\Mail\FamilyInvitationMail;
use App\Mail\MedicationPlanProposalMail;
use Illuminate\Contracts\Queue\ShouldQueue;

test('accepted notification mailables are queued', function (string $mailableClass) {
    expect($mailableClass)->toImplement(ShouldQueue::class);
})->with([
    'family invitation accepted' => [FamilyInvitationAcceptedMail::class],
    'doctor invitation accepted' => [DoctorInvitationAcceptedMail::class],
]);

test('invitation and proposal mailables are sent synchronously', function (string $mailableClass) {
    expect($mailableClass)->not->toImplement(ShouldQueue::class);
})->with([
    'family invitation' => [FamilyInvitationMail::class],
    'doctor invitation' => [DoctorInvitationMail::class],
    'medication plan proposal' => [MedicationPlanProposalMail::class],
]);
