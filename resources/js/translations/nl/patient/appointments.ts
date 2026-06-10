export default {
    title: 'Afspraken',
    metaDescription:
        'Overzicht van uw geplande afspraken in Medibeheer. Voeg nieuwe afspraken toe en beheer transport.',
    heading: 'Afspraken',
    newAppointment: 'Nieuwe afspraak',
    plannedHeading: 'Geplande afspraken',
    cardActionsAriaLabel: 'Acties voor deze afspraak',
    cardExpandHint: 'Meer details bekijken',
    cardCollapseHint: 'Details sluiten',
    showDetails: 'Details tonen',
    hideDetails: 'Details verbergen',
    empty: 'U heeft nog geen afspraken. Tik op de knop “Nieuwe afspraak” om te beginnen.',
    emptyPlanned:
        'Er zijn momenteel geen geplande afspraken. Nieuwe afspraken verschijnen hier zodra u ze toevoegt.',
    dialogTitle: 'Nieuwe afspraak',
    dialogEditTitle: 'Afspraak wijzigen',
    overview: {
        title: 'Controleer uw gegevens',
        sectionProvider: 'Zorgverlener',
        sectionAddress: 'Adres',
        sectionSchedule: 'Datum en tijd',
        sectionTransport: 'Transport',
        sectionNotes: 'Notities',
        editRowAria: '{field} bewerken',
    },
    steps: {
        progress: 'Stap {current} van {total}',
        continue: 'Volgende',
        back: 'Terug',
        provider: {
            title: 'Zorgverlener',
        },
        address: {
            title: 'Adres',
        },
        schedule: {
            title: 'Datum en tijd',
        },
        transport: {
            title: 'Transport',
            description:
                'Laat weten of familie een uitnodiging voor transport moet ontvangen.',
        },
        notes: {
            title: 'Notities',
            description:
                'Optioneel: extra informatie voor uzelf of uw familie.',
        },
    },
    fields: {
        doctorType: 'Soort zorgverlener',
        doctorTypePlaceholder: 'Kies een type',
        providerName: 'Naam (praktijk of zorgverlener)',
        providerNamePlaceholder: 'Bijv. Huisartsenpraktijk De Linde',
        addressGroupLegend: 'Adres',
        addressSearch: 'Adres zoeken',
        addressSearchOptional: 'Adres zoeken (optioneel)',
        addressSearchPlaceholder: 'Voer het adres in',
        addressSearchUnavailable:
            'Adreszoeken is niet beschikbaar. Vul de velden hieronder handmatig in.',
        addressOverviewTitle: 'Gekozen adres',
        street: 'Straat',
        streetPlaceholder: 'Bijv. Joossenlei',
        houseNumber: 'Huisnummer (optioneel)',
        houseNumberPlaceholder: 'Bijv. 29 of 29a',
        postalCode: 'Postcode',
        postalCodePlaceholder: 'Bijv. 2970',
        city: 'Plaats',
        cityPlaceholder: 'Bijv. schilde',
        startsAtGroupLegend: 'Wanneer is de afspraak?',
        startsAtDate: 'Dag',
        startsAtDatePlaceholder: 'dd-mm-jjjj',
        startsAtTime: 'Uur',
        startsAtTimePlaceholder: 'uu:mm',
        note: 'Notitie',
        notes: 'Notities (optioneel)',
        notesPlaceholder: 'Bijv. meenemen: verwijsbrief, ID',
        needsTransport: 'Transport nodig?',
        transportNotify: 'Wie wilt u uitnodigen?',
        transportNotes: 'Transport (optioneel)',
        status: 'Status',
    },
    doctorTypes: {
        dentist: 'Tandarts',
        hospital: 'Ziekenhuis',
        general_practitioner: 'Huisarts',
        specialist: 'Specialist',
        fallback: 'Zorgverlener',
    },
    statuses: {
        scheduled: 'Gepland',
        done: 'Afgerond',
        cancelled: 'Geannuleerd',
    },
    actions: {
        save: 'Opslaan',
        cancel: 'Annuleren',
        delete: 'Verwijderen',
        edit: 'Wijzigen',
    },
    deleteConfirm: {
        title: 'Afspraak verwijderen?',
        message:
            'Weet u zeker dat u deze afspraak wilt verwijderen? Dit kan niet ongedaan worden gemaakt.',
        confirm: 'Ja, verwijderen',
        cancel: 'Annuleren',
    },
    labels: {
        when: 'Wanneer',
        time: 'Tijd',
        where: 'Waar',
        openInGoogleMaps: 'Adres op kaart bekijken',
        openInGoogleMapsAria:
            'Bekijk het adres {address} op de kaart. Opent in Google Maps in een nieuw venster.',
        transport: 'Transport',
        afterVisit: 'Na uw bezoek',
    },
    transport: {
        requested: 'Aangevraagd',
        acceptedBy: 'Geaccepteerd door {name}',
        declined: 'Afgewezen',
        notNeeded: 'Niet nodig',
    },
    doneToggle: {
        markDone: 'Afgerond',
        markCancelled: 'Geannuleerd',
        markUndone: 'Terug naar gepland',
        cancelledNotice:
            'Deze afspraak is geannuleerd. U kunt de status niet meer wijzigen.',
    },
    cancelDialog: {
        title: 'Afspraak geannuleerd',
        description:
            'Optioneel: waarom gaat de afspraak niet door? U vindt dit later hier terug.',
        reasonLabel: 'Reden (optioneel)',
        reasonPlaceholder:
            'Bijvoorbeeld: ziek, verzet door de praktijk, dubbele afspraak',
        confirm: 'Annulering opslaan',
        savedReasonLabel: 'Reden: ',
    },
    doneDialog: {
        title: 'Afspraak afgerond',
        visitSummaryLabel: 'Na het bezoek (optioneel)',
        visitSummaryPlaceholder:
            'Bijvoorbeeld: uitleg van de dokter, vervolgafspraak, of een korte boodschap voor thuis',
        confirm: 'Opslaan',
    },
    outcomePages: {
        backToAppointments: 'Terug naar afspraken',
    },
    validation: {
        startsAtDateInvalid: 'Kies een geldige dag.',
        startsAtTimeInvalid: 'Kies een geldig uur.',
        startsAtMustNotBeInPast:
            'Kies een datum en tijd vanaf nu. Een afspraak kan niet in het verleden liggen.',
        postalCodeMinLength: 'De postcode moet minstens 4 tekens bevatten.',
        postalCodeBelgianInvalid:
            'Vul een geldige Belgische postcode in (4 cijfers).',
        addressNotFound:
            'Dit adres kon niet worden gevonden in België. Controleer straat, postcode en plaats.',
        addressComponentMismatch:
            'Dit komt niet overeen met een bestaand adres in België.',
        addressVerificationUnavailable:
            'Het adres kon niet worden gecontroleerd. Probeer het later opnieuw.',
    },
    stepValidation: {
        doctorTypeRequired: 'Kies een soort zorgverlener.',
        providerNameRequired: 'Vul de naam van de praktijk of zorgverlener in.',
        streetRequired: 'Vul de straat in.',
        postalCodeRequired: 'Vul de postcode in.',
        cityRequired: 'Vul de plaats in.',
        startsAtDateRequired: 'Kies een dag voor de afspraak.',
        startsAtTimeRequired: 'Kies een uur voor de afspraak.',
        transportRecipientsRequired:
            'Kies minstens één familie om uit te nodigen voor transport.',
    },
    scheduleNext: {
        title: 'Nog een afspraak inplannen?',
        descriptionDone:
            'Wilt u meteen een volgende afspraak in uw overzicht zetten?',
        descriptionCancelled:
            'Wilt u meteen een nieuwe afspraak in uw overzicht zetten?',
        yes: 'Ja, nieuwe afspraak',
        no: 'Nee, naar overzicht',
    },
};
