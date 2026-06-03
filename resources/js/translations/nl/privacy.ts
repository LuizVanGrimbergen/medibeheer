export default {
    metaTitle: 'Privacybeleid',
    metaDescription:
        'Lees hoe Medibeheer uw persoons- en gezondheidsgegevens verwerkt, bewaart en beschermt conform de AVG/GDPR.',
    title: 'Privacybeleid',
    versionLabel: 'Versie {version}',
    back: 'Terug',
    sections: {
        controller: {
            title: '1. Wie is verantwoordelijk?',
            body: 'Medibeheer is verwerkingsverantwoordelijke voor de verwerking van persoonsgegevens via deze website en progressive web app (PWA). Voor vragen over privacy, een verzoek uitoefenen of een klacht: {contactEmail}.',
        },
        data: {
            title: '2. Welke gegevens verwerken we?',
            body: 'Afhankelijk van uw rol verwerken we onder meer accountgegevens (naam, e-mailadres, rol, verificatiestatus) en gezondheidsgegevens die u zelf invoert of die uit een door u goedgekeurd medicatieplan voortkomen. Dat omvat medicatie en innameschema’s, innames, voorraad, voorschriften (vervaldatums, ophaalstatus), afspraken (inclusief vervoer door familie indien van toepassing), dagelijkse check-ins (stemming, symptomen, notities) en het apothekeroverzicht van actieve medicatie. Ook verwerken we gegevens over medicatieplanvoorstellen, uitnodigingen en koppelingen met familie of zorgverleners, technische gegevens (sessie, IP-adres bij toestemming, beveiligings- en gegevenslogboeken) en, indien ingeschakeld, push-abonnementgegevens voor herinneringen.',
        },
        purpose: {
            title: '3. Waarvoor gebruiken we deze gegevens?',
            body: 'Uw gegevens worden gebruikt om de dienst te leveren en te beveiligen: medicatie- en voorschriftenbeheer, innames en voorraad (inclusief vakantieberekening), afspraken en vervoer, medicatieplannen tussen familie en patiënt, dagelijkse check-ins, samenwerking met uitgenodigde familie of zorgverleners, push-herinneringen indien u die inschakelt, accountbeheer, e-mail voor verificatie en wachtwoordreset, export op verzoek, en preventie van misbruik.',
        },
        legalBasis: {
            title: '4. Op welke rechtsgrond (AVG)?',
            body: 'Voor gezondheidsgegevens (bijzondere categorie, art. 9 AVG) vragen we uitdrukkelijke toestemming bij registratie. U kunt die later intrekken door uw account te verwijderen. Voor uw account, uitvoering van de overeenkomst, beveiliging en fraudepreventie baseren we ons op uitvoering van de overeenkomst en gerechtvaardigd belang. Waar push-herinneringen actief zijn, baseren we die op uw keuze om meldingen in te schakelen.',
        },
        sharing: {
            title: '5. Wie krijgt toegang tot uw gegevens?',
            body: 'Alleen u en de personen of zorgverleners die u zelf uitnodigt en accepteert, krijgen toegang tot patiëntgegevens binnen hun rol (gebruiker, familie, zorgverlener). Wij verkopen gegevens niet en gebruiken ze niet voor advertenties. Onze verwerkers (zie §9) verwerken gegevens uitsluitend in onze opdracht.',
        },
        retention: {
            title: '6. Bewaartermijnen',
            body: 'Account- en patiëntgegevens bewaren we zolang uw account actief is. Na verwijdering van uw account worden gekoppelde persoons- en gezondheidsgegevens verwijderd. Beveiligingslogboeken worden na {securityLogDays} dagen opgeschoond, gegevenslogboeken na {dataLogDays} dagen, en inactieve sessies na {sessionDays} dagen.',
        },
        security: {
            title: '7. Beveiliging',
            body: 'We treffen passende technische en organisatorische maatregelen, waaronder versleutelde verbindingen (HTTPS), toegangscontrole per rol, beperkte logging en het verbieden van destructieve database-commando’s in productie. Meld vermoedens van een datalek zo snel mogelijk aan {contactEmail}.',
        },
        rights: {
            title: '8. Uw rechten',
            body: 'U hebt recht op inzage, rectificatie, verwijdering, beperking, bezwaar en dataportabiliteit. In Instellingen kunt u uw gegevens exporteren (JSON) en uw account verwijderen. U kunt een klacht indienen bij de Gegevensbeschermingsautoriteit (België) of de Autoriteit Persoonsgegevens (Nederland).',
        },
        processors: {
            title: '9. Verwerkers en doorgifte',
            body: 'Hosting, e-mailverzending, realtime-berichten (bijv. voor live updates), push-diensten (Web Push / VAPID), en foutmonitoring in productie kunnen persoonsgegevens verwerken als verwerker. Met verwerkers sluiten we afspraken die de AVG vereist. Verwerking vindt plaats binnen de EU/EER, tenzij voor een specifieke dienst anders vermeld en passend beschermd.',
        },
        changes: {
            title: '10. Wijzigingen',
            body: 'Bij wezenlijke wijzigingen passen we dit beleid aan en verhogen we het versienummer. Bij registratie leggen we vast welke versie u hebt geaccepteerd. De publieke startpagina en login tonen geen patiëntgegevens. Alleen noodzakelijke technische gegevens worden daar verwerkt.',
        },
    },
    cookiesLinkLabel: 'cookiebeleid',
    cookiesReferencePrefix: 'Zie ook ons ',
    cookiesReferenceSuffix: '.',
    cookies: {
        metaTitle: 'Cookiebeleid',
        metaDescription:
            'Informatie over cookies, lokale opslag en push-instellingen in de Medibeheer webapp.',
        title: 'Cookiebeleid',
        sections: {
            necessary: {
                title: '1. Strikt noodzakelijke cookies',
                body: 'We gebruiken een sessiecookie (en gerelateerde beveiligingstokens) om u ingelogd te houden en het formulier te beschermen. Deze cookies zijn noodzakelijk voor de werking van de dienst en vereisen geen aparte cookiebanner-toestemming.',
            },
            storage: {
                title: '2. Lokale opslag en service worker (PWA)',
                body: 'De PWA kan technische bestanden in de browser cache en via een service worker opslaan voor snellere weergave en offline ondersteuning van de app-shell. Er wordt geen volledig patiëntdossier permanent op uw apparaat opgeslagen buiten wat tijdens uw actieve sessie van de server komt.',
            },
            push: {
                title: '3. Pushmeldingen',
                body: 'Als u medicatieherinneringen inschakelt, slaat uw browser een push-abonnement op en verwerken wij endpoint-gegevens om meldingen te sturen. U kunt dit uitschakelen in de app. Het abonnement wordt dan verwijderd.',
            },
            publicSite: {
                title: '4. Publieke pagina’s',
                body: 'Op de startpagina, login, registratie en beleidspagina’s plaatsen we geen marketing- of analysecookies. Zoekmachines kunnen deze pagina’s indexeren. Patiënt-, familie- en zorgverlener-omgevingen worden technisch uitgesloten van indexering.',
            },
            analytics: {
                title: '5. Analyse en reclame',
                body: 'We gebruiken geen tracking-, reclame- of social-mediacookies. Als we dat in de toekomst toch zouden inzetten, vragen we vooraf om uw toestemming en werken we dit beleid bij.',
            },
        },
    },
    register: {
        sectionTitle: 'Privacy en toestemming',
        privacyPrefix: 'Ik ga akkoord met het',
        privacyLink: 'privacybeleid',
        privacySuffix: '(versie {version}) en het',
        privacySuffixEnd: '.',
        cookiesLink: 'cookiebeleid',
        healthDataLabel:
            'Ik geef uitdrukkelijke toestemming voor het verwerken van mijn gezondheidsgegevens (art. 9 AVG) in Medibeheer, zoals beschreven in het privacybeleid, waaronder medicatie, voorschriften, innames, voorraad, afspraken, medicatieplannen en check-ins, inclusief optionele push-herinneringen indien ik die inschakel.',
    },
    settings: {
        title: 'Privacy en gegevens',
        description:
            'Bekijk het beleid, exporteer uw gegevens (AVG) of verwijder uw account.',
        exportTitle: 'Gegevens exporteren',
        exportDescription:
            'Download een JSON-bestand met uw account, toestemmingen en, afhankelijk van uw rol, medicatie, voorschriften, innames, afspraken, check-ins en koppelingen met familie of zorgverleners.',
        exportAction: 'Download mijn gegevens',
        legalLinksTitle: 'Beleid',
        privacyLink: 'Privacybeleid',
        cookiesLink: 'Cookiebeleid',
    },
};
