export default {
    title: 'Home',
    heading: 'Home',
    medicationSetup: {
        heading: 'Uw medicatie toevoegen',
        pickOne:
            'Kies een van de volgende opties om uw medicatie toe te voegen.',
        optionsAriaLabel: 'Twee manieren om medicatie toe te voegen',
        orDivider: 'of',
        optionOne: {
            title: 'Medicatie toevoegen',
        },
        optionTwo: {
            title: 'Medicatieplan van familie',
            description:
                'Heeft een familielid u een medicatieplan per e-mail gestuurd? Bekijk het op de pagina Familie.',
            cta: 'Naar Familie',
        },
    },
    dailyCheckins: {
        title: 'Hoe voelt u zich vandaag?',
        description:
            'Tik op een smiley om aan te geven hoe u zich voelt vandaag.',
        symptoms: {
            title: 'Past één of meer van deze symptomen bij u vandaag? (optioneel)',
            hint: 'Dit is optioneel. Als u klaar bent, tikt u op de knop hieronder om verder te gaan naar de notitie.',
            continue: 'Verder',
            cancel: 'Annuleren',
            options: {
                pain: 'Pijn',
                fatigue: 'Vermoeidheid',
                dizziness: 'Duizeligheid',
                shortness_of_breath: 'Kortademig',
                nausea: 'Misselijkheid',
                poor_sleep: 'Slecht geslapen',
                loneliness: 'Eenzaam',
                anxiety_or_worry: 'Angst of zorgen maken',
                poor_appetite: 'Weinig eetlust',
                stiff_or_joint_pain: 'Stijf of pijn aan gewrichten',
            },
        },
        noteDialog: {
            title: 'Notitie toevoegen (optioneel)',
            description:
                'Optioneel: voeg een korte notitie toe. U kunt dit later teruglezen.',
            label: 'Notitie (optioneel)',
            placeholder:
                'Bijvoorbeeld: een beetje hoofdpijn, of juist ik voel mij goed vandaag',
            cancel: 'Annuleren',
            confirm: 'Opslaan',
        },
        success: {
            title: 'Bedankt voor uw check-in',
            messageComfort: 'Wij wensen u veel beterschap.',
            messageGood: 'Geniet van uw dag!',
            done: 'Gereed',
        },
    },
    medicationIntakePushSuccess: {
        eyebrow: 'Medicatie ingenomen',
        subtitle: 'Het is geregistreerd. U kunt verder.',
        done: 'Gereed',
    },
    todayMedications: {
        title: 'Medicatie vandaag',
        description:
            'Dit moet u vandaag innemen. Tik op de knop zodra u de dosis heeft genomen.',
        empty: 'Vandaag hoeft u geen medicatie in te nemen.',
        markTaken: 'Inemen',
        markTakenNow: 'Nu inemen',
        markTakenCustom: 'Aangepast',
        customTakenTimeLabel: 'Wanneer heeft u ingenomen?',
        confirmCustomTaken: 'Inname bevestigen',
        notYetTimeToTake: 'Nog niet tijd om in te nemen',
        taken: 'Ingenomen',
        takenSection: {
            title: 'Ingenomen',
            cardLead: 'Uw innames van vandaag ({count})',
            rowAria: '{name}, ingenomen om {time}',
        },
        intakeCard: {
            dose: 'Dosis',
            time: 'Inname om',
            note: 'Notitie',
        },
        takenAria: '{name} om {time} als ingenomen gemarkeerd',
        markTakenAria: 'Neem {name} om {time} in',
        periods: {
            morning: {
                title: 'Ochtend',
                hint: '05:00 – 12:00',
            },
            afternoon: {
                title: 'Middag',
                hint: '12:00 – 17:00',
            },
            evening: {
                title: 'Avond',
                hint: '17:00 – 22:00',
            },
            night: {
                title: 'Nacht',
                hint: '22:00 – 05:00',
            },
        },
    },
};
