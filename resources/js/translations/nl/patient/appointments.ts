export default {
    title: 'Afspraken',
    heading: 'Afspraken',
    newAppointment: 'Nieuwe afspraak',
    plannedHeading: 'Geplande afspraken',
    plannedDescription:
        'Hier ziet u alleen geplande afspraken, op volgorde van datum. Afgeronde en geannuleerde afspraken verdwijnen uit dit overzicht.',
    empty: 'U heeft nog geen afspraken. Tik op de knop “Nieuwe afspraak” om te beginnen.',
    emptyPlanned:
        'Er zijn geen geplande afspraken. Afgeronde en geannuleerde afspraken worden hier niet getoond.',
    dialogTitle: 'Nieuwe afspraak',
    dialogEditTitle: 'Afspraak wijzigen',
    dialogDescription:
        'Vul de velden zo volledig mogelijk in. U kunt dit later nog wijzigen.',
    dialogEditDescription:
        'Pas de gegevens aan en tik op Opslaan om ze bij te werken.',
    fields: {
        doctorType: 'Soort zorgverlener',
        doctorTypePlaceholder: 'Kies een type',
        providerName: 'Naam (praktijk of zorgverlener)',
        providerNamePlaceholder: 'Bijv. Huisartsenpraktijk De Linde',
        address: 'Adres',
        addressPlaceholder: 'Straat, postcode, plaats',
        startsAtGroupLegend: 'Wanneer is de afspraak?',
        startsAtDate: 'Dag',
        startsAtTime: 'Uur',
        startsAtHint: 'Eerst de dag, daarna het uur.',
        notes: 'Notities (optioneel)',
        notesPlaceholder: 'Bijv. meenemen: verwijsbrief, ID',
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
    deleteConfirm:
        'Weet je zeker dat je deze afspraak wilt verwijderen? Dit kan niet ongedaan worden gemaakt.',
    labels: {
        when: 'Wanneer',
        time: 'Tijd',
        where: 'Waar',
        type: 'Type',
        notes: 'Notities',
    },
    doneToggle: {
        groupLabel: 'Afronden of annuleren',
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
        description:
            'Optioneel: wat de dokter heeft gezegd of wat u met de familie wilt delen. Later kunt u dit terugvinden.',
        visitSummaryLabel: 'Na het bezoek (optioneel)',
        visitSummaryPlaceholder:
            'Bijvoorbeeld: uitleg van de dokter, vervolgafspraak, of een korte boodschap voor thuis',
        confirm: 'Opslaan',
    },
};
