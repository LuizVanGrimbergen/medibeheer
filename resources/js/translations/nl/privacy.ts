export default {
    metaTitle: 'Privacybeleid',
    title: 'Privacybeleid',
    versionLabel: 'Versie {version}',
    back: 'Terug',
    sections: {
        controller: {
            title: '1. Wie is verantwoordelijk?',
            body: 'Medibeheer verwerkt persoonsgegevens als verwerkingsverantwoordelijke voor deze website (PWA). Voor vragen over privacy kunt u contact opnemen via het e-mailadres dat in de app wordt vermeld.',
        },
        data: {
            title: '2. Welke gegevens verwerken we?',
            body: 'We verwerken accountgegevens (naam, e-mailadres), gezondheidsgegevens die u zelf invoert (medicatie, innames, afspraken, dagelijkse check-ins) en technische gegevens die nodig zijn voor beveiliging (bijv. sessie, beveiligingslog).',
        },
        purpose: {
            title: '3. Waarvoor gebruiken we deze gegevens?',
            body: 'Gegevens worden uitsluitend gebruikt om de dienst te leveren: medicatiebeheer, afspraken, familie-toegang waar u die zelf toestaat, en beveiliging van uw account.',
        },
        legalBasis: {
            title: '4. Op welke grondslag?',
            body: 'Voor gezondheidsgegevens vragen we uitdrukkelijke toestemming bij registratie. Voor uw account en beveiliging baseren we ons op de uitvoering van de overeenkomst en gerechtvaardigd belang (fraude- en misbruikpreventie).',
        },
        retention: {
            title: '5. Bewaartermijn',
            body: 'Account- en patiëntgegevens bewaren we zolang uw account actief is. Na verwijdering worden gekoppelde gegevens verwijderd. Beveiligingslogs en sessies worden na een beperkte termijn automatisch opgeschoond.',
        },
        rights: {
            title: '6. Uw rechten',
            body: 'U hebt recht op inzage, rectificatie, verwijdering en dataportabiliteit. In Instellingen kunt u uw gegevens exporteren en uw account verwijderen. U kunt bezwaar maken tegen verwerking of een klacht indienen bij de Autoriteit Persoonsgegevens.',
        },
        processors: {
            title: '7. Verwerkers en doorgifte',
            body: 'Hosting, e-mail en foutmonitoring kunnen persoonsgegevens verwerken namens ons. Met deze partijen sluiten we verwerkersovereenkomsten. Gegevens worden binnen de EU/EER verwerkt tenzij anders vermeld.',
        },
    },
    cookies: {
        metaTitle: 'Cookiebeleid',
        title: 'Cookiebeleid',
        sections: {
            necessary: {
                title: '1. Strikt noodzakelijke cookies',
                body: 'We gebruiken een sessiecookie om u ingelogd te houden. Deze cookie is noodzakelijk voor de werking van de website en vereist geen aparte toestemming.',
            },
            storage: {
                title: '2. Lokale opslag (PWA)',
                body: 'De PWA kan technische gegevens in de browser cache opslaan voor snellere weergave. We slaan geen patiëntdossier lokaal op buiten wat de server levert tijdens uw sessie.',
            },
            analytics: {
                title: '3. Analyse en marketing',
                body: 'We gebruiken geen tracking- of advertentiecookies. Als dat in de toekomst verandert, vragen we vooraf om toestemming.',
            },
        },
    },
    register: {
        sectionTitle: 'Privacy en toestemming',
        privacyPrefix: 'Ik ga akkoord met het',
        privacyLink: 'privacybeleid',
        privacySuffix: '(versie {version}).',
        healthDataLabel:
            'Ik geef uitdrukkelijke toestemming voor het verwerken van mijn gezondheidsgegevens in Medibeheer, zoals beschreven in het privacybeleid.',
    },
    settings: {
        title: 'Privacy en gegevens',
        description: 'Bekijk het beleid, exporteer uw gegevens of verwijder uw account.',
        exportTitle: 'Gegevens exporteren',
        exportDescription:
            'Download een JSON-bestand met uw accountgegevens en, voor patiënten, uw medicatie- en gezondheidsgegevens.',
        exportAction: 'Download mijn gegevens',
        legalLinksTitle: 'Beleid',
        privacyLink: 'Privacybeleid',
        cookiesLink: 'Cookiebeleid',
    },
};
