export default {
    title: 'Voorschriften',
    metaDescription:
        'Overzicht van de vervaldatums van uw medicatievoorschriften in Medibeheer.',
    listHeading: 'Uw voorschriften per medicatie',
    cardActionsAriaLabel: 'Acties voor dit voorschrift',
    actions: {
        edit: 'Wijzigen',
        delete: 'Verwijderen',
        save: 'Opslaan',
        cancel: 'Annuleren',
    },
    dialogEditTitle: 'Voorschrift wijzigen',
    deleteConfirm: {
        title: 'Voorschrift verwijderen?',
        message:
            'Weet u zeker dat u dit voorschrift voor “{name}” wilt verwijderen? Dit kan niet ongedaan worden gemaakt.',
        confirm: 'Ja, verwijderen',
        cancel: 'Annuleren',
    },
    empty: 'U heeft nog geen voorschriften toegevoegd. Tik op “Voorschrift toevoegen” om te beginnen.',
    emptyMedications:
        'U heeft nog geen medicaties. Voeg eerst medicatie toe om voorschriften te kunnen registreren.',
    prescriptionExpiryMissing: 'Nog geen vervaldatum ingevuld',
    prescriptionCollapsedSummaryNumbered: 'Voorschrift {number} · {status}',
    prescriptionNumberHeading: 'Voorschrift {number}',
    addPrescription: 'Voorschrift toevoegen',
    dialogTitle: 'Voorschrift toevoegen',
    stepsProgress: 'Stap {current} van {total}',
    overview: {
        title: 'Controleer uw gegevens',
        sectionDetails: 'Voorschrift',
        sectionExpiryDates: 'Vervaldata',
        editRowAria: '{field} bewerken',
    },
    medicationLabel: 'Medicatie',
    medicationPlaceholder: 'Kies een medicatie',
    quantityLabel: 'Aantal voorschriften',
    quantityInvalid: 'Voer een geldig aantal tussen 1 en 24 in.',
    quantity: {
        one: '1 voorschrift',
        nPrescriptions: '{n} voorschriften',
        customSelectAriaLabel: 'Aantal voorschriften tussen 5 en 24',
        customPlaceholder: 'andere mogelijkheden',
    },
    medicationRequired: 'Kies een medicatie.',
    expiryDatesRequired: 'Vul voor elk voorschrift een vervaldatum in.',
    expiryDateLabel: 'Vervaldatum voorschrift {number}',
    expiryDateLastPrescriptionLabel:
        'Vervaldatum laatste voorschrift ({number})',
    lastPrescriptionAppointmentTag: 'Laatste voorschrift',
    expiredBadge: 'Voorschrift verlopen',
    expiryCriticalAlertLabel: 'Dringend vernieuwen',
    expiryWarningAria: 'Uw voorschrift verloopt binnenkort.',
    expiryProgressAria: 'Vervaldatum voorschrift over nog {days} dagen.',
    expiryProgressUnknownAria: 'Vervaldatum voorschrift',
    expiryStatusExpired: 'Voorschrift verlopen.',
    expiryStatusExpiresToday: 'Verloopt vandaag.',
    expiryStatusOneDay: 'Nog 1 dag tot verlopen.',
    expiryStatusDays: 'Nog {days} dagen tot verlopen.',
    completeToggle: {
        label: 'Nieuw voorschrift',
        labelNumbered: 'Voorschrift {number} vervangen',
        ariaLabel: 'Bevestigen: nieuw voorschrift ontvangen',
        ariaLabelNumbered:
            'Bevestigen: voorschrift {number} is vervangen door een nieuw voorschrift',
    },
    pickupStatus: {
        markButton: 'Opgehaald bij de apotheek',
        markButtonNumbered: 'Voorschrift {number} opgehaald bij de apotheek',
        markButtonAriaLabel:
            'Bevestigen: voorschrift opgehaald bij de apotheek',
        markButtonAriaLabelNumbered:
            'Bevestigen: voorschrift {number} opgehaald bij de apotheek',
        pickedUpBadge: 'Opgehaald',
        notPickedUpBadge: 'Niet opgehaald',
        statusLineNumbered: 'Voorschrift {number} · Opgehaald',
    },
};
