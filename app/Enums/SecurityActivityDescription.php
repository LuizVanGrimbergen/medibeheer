<?php

namespace App\Enums;

enum SecurityActivityDescription: string
{
    case AUTH_LOGIN_SUCCEEDED = 'auth_login_succeeded';
    case AUTH_LOGIN_FAILED = 'auth_login_failed';
    case AUTH_LOGOUT = 'auth_logout';
    case AUTH_REGISTRATION_SUCCEEDED = 'auth_registration_succeeded';
    case AUTH_PASSWORD_RESET_LINK_SENT = 'auth_password_reset_link_sent';
    case AUTH_PASSWORD_RESET_LINK_FAILED = 'auth_password_reset_link_failed';
    case AUTH_PASSWORD_RESET_COMPLETED = 'auth_password_reset_completed';
    case AUTH_PASSWORD_RESET_FAILED = 'auth_password_reset_failed';
    case AUTH_PASSWORD_UPDATED = 'auth_password_updated';
    case USER_PROFILE_UPDATED = 'user_profile_updated';
    case USER_ACCOUNT_DELETED = 'user_account_deleted';
    case FAMILY_ACTIVE_PATIENT_SWITCHED = 'family_active_patient_switched';
    case FAMILY_INVITATION_CREATED = 'family_invitation_created';
    case FAMILY_INVITATION_REVOKED = 'family_invitation_revoked';
    case FAMILY_INVITATION_ACCEPTED = 'family_invitation_accepted';
    case FAMILY_MEMBER_UNLINKED = 'family_member_unlinked';
    case MEDICATION_PLAN_PROPOSAL_PUBLISHED = 'medication_plan_proposal_published';
    case MEDICATION_PLAN_PROPOSAL_REVOKED = 'medication_plan_proposal_revoked';
    case MEDICATION_PLAN_PROPOSAL_REDEEMED = 'medication_plan_proposal_redeemed';
    case MEDICATION_PLAN_PROPOSAL_DECLINED = 'medication_plan_proposal_declined';
    case DOCTOR_INVITATION_CREATED = 'doctor_invitation_created';
    case DOCTOR_INVITATION_REVOKED = 'doctor_invitation_revoked';
    case DOCTOR_INVITATION_ACCEPTED = 'doctor_invitation_accepted';
    case DOCTOR_UNLINKED = 'doctor_unlinked';
    case TRANSPORT_INVITATION_ACCEPTED = 'transport_invitation_accepted';
    case TRANSPORT_INVITATION_DECLINED = 'transport_invitation_declined';
    case AUTHORIZATION_DENIED = 'authorization_denied';
    case ACCESS_FORBIDDEN = 'access_forbidden';
}
