export default {
    title: 'Profiel',
    backToHome: 'Terug naar home',
    backToOverview: 'Terug naar overzicht',
    backToDoctorHome: 'Terug naar data',
    information: {
        title: 'Profielinformatie',
        description:
            'Werk de profielinformatie en het e-mailadres van uw account bij.',
        nameLabel: 'Naam',
        emailLabel: 'E-mailadres',
        emailUnverified: 'Uw e-mailadres is nog niet geverifieerd.',
        resendVerification:
            'Klik hier om de verificatiemail opnieuw te versturen.',
        verificationSent:
            'Er is een nieuwe verificatielink naar uw e-mailadres verstuurd.',
        save: 'Opslaan',
        saved: 'Opgeslagen.',
    },
    password: {
        title: 'Wachtwoord wijzigen',
        description:
            'Gebruik een lang en willekeurig wachtwoord om uw account veilig te houden.',
        currentPassword: 'Huidig wachtwoord',
        newPassword: 'Nieuw wachtwoord',
        confirmPassword: 'Bevestig wachtwoord',
        save: 'Opslaan',
        saved: 'Opgeslagen.',
    },
    securityActivity: {
        title: 'Beveiligingsactiviteit',
        description:
            'Overzicht van inlogpogingen en andere beveiligingsgebeurtenissen op uw account.',
        empty: 'Nog geen beveiligingsactiviteit geregistreerd.',
        descriptions: {
            auth_login_succeeded: 'Ingelogd',
            auth_login_failed: 'Mislukte inlogpoging',
            auth_logout: 'Uitgelogd',
            auth_registration_succeeded: 'Account aangemaakt',
            auth_password_reset_link_sent: 'Wachtwoord-reset aangevraagd',
            auth_password_reset_link_failed: 'Wachtwoord-reset mislukt',
            auth_password_reset_completed: 'Wachtwoord gereset',
            auth_password_reset_failed: 'Wachtwoord-reset mislukt',
            auth_password_updated: 'Wachtwoord gewijzigd',
            user_profile_updated: 'Profiel bijgewerkt',
            user_account_deleted: 'Account verwijderd',
            family_active_patient_switched: 'Actieve patiënt gewisseld',
            family_invitation_created: 'Familie-uitnodiging verstuurd',
            family_invitation_revoked: 'Familie-uitnodiging ingetrokken',
            family_invitation_accepted: 'Familie-uitnodiging geaccepteerd',
            transport_invitation_accepted: 'Vervoersuitnodiging geaccepteerd',
            transport_invitation_declined: 'Vervoersuitnodiging geweigerd',
            authorization_denied: 'Geen toegang (beleid)',
            access_forbidden: 'Geen toegang',
        },
    },
    delete: {
        title: 'Account verwijderen',
        description:
            'Zodra uw account is verwijderd, worden alle bijbehorende gegevens permanent verwijderd. Download eerst alle data die u wilt bewaren.',
        action: 'Account verwijderen',
        modalTitle: 'Weet u zeker dat u uw account wilt verwijderen?',
        modalDescription:
            'Na het verwijderen van uw account worden alle gegevens permanent verwijderd. Deze actie kan niet ongedaan gemaakt worden.',
        passwordLabel: `Wacht${'woord'}`,
        passwordPlaceholder: `Wacht${'woord'}`,
        cancel: 'Annuleren',
        confirmDelete: 'Account verwijderen',
    },
};
