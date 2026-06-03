export default {
    name: 'Medibeheer',
    loading: {
        ariaLabel: 'Pagina wordt geladen',
        default: {
            title: 'Even laden…',
            description: 'Een ogenblik geduld alstublieft.',
        },
        savingMedication: {
            title: 'Medicatie wordt opgeslagen…',
            description: 'Uw medicatiegegevens worden bewaard.',
        },
        savingIntake: {
            title: 'Inname wordt geregistreerd…',
            description: 'Even geduld terwijl we uw inname opslaan.',
        },
        savingPrescription: {
            title: 'Recept wordt opgeslagen…',
            description: 'Uw receptgegevens worden bewaard.',
        },
        savingAppointment: {
            title: 'Afspraak wordt opgeslagen…',
            description: 'Uw afspraakgegevens worden bewaard.',
        },
        savingCheckin: {
            title: 'Check-in wordt opgeslagen…',
            description: 'Uw antwoorden worden bewaard.',
        },
    },
    pagination: {
        navLabel: 'Paginering',
        previous: 'Vorige',
        next: 'Volgende',
        page: 'Pagina {current} van {last}',
        goToPage: 'Ga naar pagina {page}',
    },
    navigation: {
        profile: 'Profiel',
        settings: 'Instellingen',
        logout: 'Uitloggen',
        welcomeBack: 'Welkom terug,',
    },
    pwa: {
        iosInstallAriaLabel:
            'Instructie om Medibeheer op beginscherm te zetten',
        iosInstallTitle: 'Zet Medibeheer op uw beginscherm',
        iosInstallSubtitle: 'Zo doet u dat in Safari op iPhone:',
        iosInstallStepOpenShare:
            'Tik onderaan op de Deel-knop (het vierkant met een pijltje omhoog).',
        iosInstallStepAddHome:
            "Scroll in het menu naar beneden en tik op 'Zet op beginscherm'.",
        iosInstallStepOpenApp:
            "Tik op 'Voeg toe' rechtsboven. Open Medibeheer daarna via het icoon op uw beginscherm.",
        iosInstallHelp:
            'Ziet u de Deel-knop niet? Scroll een klein beetje omhoog zodat de Safari-balk weer zichtbaar wordt.',
        iosInstallMedicationNote:
            'Medicatie-herinneringen op iPhone werken alleen als u Medibeheer via het icoon op uw beginscherm opent.',
        dismiss: 'Sluiten',
    },
};
