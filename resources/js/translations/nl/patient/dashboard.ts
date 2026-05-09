export default {
    title: 'Home',
    heading: 'Home',
    dailyCheckins: {
        title: 'Hoe voelt u zich vandaag?',
        description: 'Tik op een smiley om aan te geven hoe u zich voelt vandaag.',
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
};
