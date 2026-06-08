export default {
    metaTitle: 'Privacy Policy',
    metaDescription:
        'Read how Medibeheer processes, stores, and protects your personal and health data in accordance with GDPR.',
    title: 'Privacy Policy',
    versionLabel: 'Version {version}',
    back: 'Back',
    localeSwitcherLabel: 'Language',
    localeNl: 'Nederlands',
    localeEn: 'English',
    controllerAddressLabel: 'Address: {address}.',
    controllerKboLabel: 'Company number (KBO): {kbo}.',
    sections: {
        controller: {
            title: '1. Who is responsible?',
            body: '{controllerName} is the data controller for the processing of personal data via this website and progressive web app (PWA).{controllerDetails} For privacy questions, exercising your rights, or complaints: {contactEmail}.',
        },
        data: {
            title: '2. What data do we process?',
            body: 'Depending on your role, we process account data (name, email address, role, verification status) and health data that you enter yourself or that results from a medication plan you approved. This includes medication and intake schedules, intakes, stock, prescriptions (expiry dates, pickup status), appointments (including family transport where applicable), daily check-ins (mood, symptoms, notes), and the pharmacist overview of active medication. We also process data about medication plan proposals, invitations, and links with family or healthcare providers, technical data (session, IP address when consent is given, security and data activity logs), and, if enabled, push subscription data for reminders.',
        },
        purpose: {
            title: '3. Why do we use this data?',
            body: 'Your data is used to provide and secure the service: medication and prescription management, intakes and stock (including vacation calculation), appointments and transport, medication plans between family and user, daily check-ins, collaboration with invited family or healthcare providers, push notifications if you enable them (medication intake, low stock, nearly expired prescriptions, and appointments two days and two hours in advance), account management, email for verification and password reset, export on request, and abuse prevention.',
        },
        legalBasis: {
            title: '4. Legal basis (GDPR)',
            body: 'We process personal data in accordance with the General Data Protection Regulation (GDPR) and the Belgian law of 3 December 2017 establishing the Data Protection Authority. For health data (special category, Art. 9 GDPR), we request explicit consent at registration. You may withdraw this later by deleting your account. For your account, contract performance, security, and fraud prevention, we rely on contract performance and legitimate interest. Where push notifications are active, we rely on your choice to enable notifications in Settings.',
        },
        sharing: {
            title: '5. Who has access to your data?',
            body: 'Only you and the persons or healthcare providers you invite and accept have access to patient data within their role (user, family, healthcare provider). We do not sell data or use it for advertising. Our processors (see section 9) process data solely on our instructions.',
        },
        retention: {
            title: '6. Retention periods',
            body: 'We retain account and patient data while your account is active. After you delete your account, linked personal and health data is removed. Security logs are purged after {securityLogDays} days, data activity logs after {dataLogDays} days, and inactive sessions after {sessionDays} days.',
        },
        security: {
            title: '7. Security',
            body: 'We implement appropriate technical and organisational measures, including encrypted connections (HTTPS), role-based access control, limited logging, and prohibiting destructive database commands in production. Report suspected data breaches as soon as possible to {contactEmail}.',
        },
        rights: {
            title: '8. Your rights',
            body: 'You have the right to access, rectification, erasure, restriction, objection, and data portability. In Settings you can export your data (JSON) and delete your account. You may file a complaint with the Belgian Data Protection Authority (GBA, www.gegevensbeschermingsautoriteit.be) or the Dutch Data Protection Authority.',
        },
        processors: {
            title: '9. Processors and transfers',
            body: 'Hosting, email delivery, real-time messaging (e.g. for live updates), push services (Web Push / VAPID), production error monitoring, and an AI processor for short, automatically generated check-in messages may process personal data as processors. For AI we only process check-in context you enter (mood, symptoms, note) to generate one short message; it is not used for medical decisions. We conclude agreements with processors as required by GDPR. Processing takes place within the EU/EEA unless stated otherwise for a specific service and appropriately protected.',
        },
        changes: {
            title: '10. Changes',
            body: 'We update this policy and increase the version number when material changes occur. At registration we record which version you accepted. The public homepage and login do not display patient data. Only necessary technical data is processed there.',
        },
    },
    cookiesLinkLabel: 'cookie policy',
    cookiesReferencePrefix: 'See also our ',
    cookiesReferenceSuffix: '.',
    cookies: {
        metaTitle: 'Cookie Policy',
        metaDescription:
            'Information about cookies, local storage, and push settings in the Medibeheer web app.',
        title: 'Cookie Policy',
        sections: {
            necessary: {
                title: '1. Strictly necessary cookies',
                body: 'We use a session cookie (and related security tokens) to keep you signed in and protect forms. These cookies are necessary for the service and do not require separate cookie banner consent.',
            },
            storage: {
                title: '2. Local storage and service worker (PWA)',
                body: 'The PWA may store technical files in the browser cache and via a service worker for faster display and offline support of the app shell. No complete patient record is permanently stored on your device beyond what is loaded from the server during your active session.',
            },
            push: {
                title: '3. Push notifications',
                body: 'If you enable notifications in Settings, your browser stores a push subscription and we process endpoint data to send notifications (medication intake, low stock, nearly expired prescriptions, and appointments two days and two hours in advance). Notifications may contain limited health data, such as medication name or appointment date and time. You can disable notifications in the app; the subscription is then removed.',
            },
            publicSite: {
                title: '4. Public pages',
                body: 'On the homepage, login, registration, and policy pages we do not place marketing or analytics cookies. Search engines may index these pages. Patient, family, and healthcare provider areas are technically excluded from indexing.',
            },
            analytics: {
                title: '5. Analytics and advertising',
                body: 'We do not use tracking, advertising, or social media cookies. If we were to use them in the future, we would ask for your consent in advance and update this policy.',
            },
        },
    },
    register: {
        sectionTitle: 'Privacy and consent',
        privacyPrefix: 'I agree to the',
        privacyLink: 'privacy policy',
        privacySuffix: '(version {version}) and the',
        privacySuffixEnd: '.',
        cookiesLink: 'cookie policy',
        healthDataLabel:
            'I give explicit consent for the processing of my health data (Art. 9 GDPR) in Medibeheer, as described in the privacy policy, including medication, prescriptions, intakes, stock, appointments, medication plans, and check-ins, including optional push notifications (medication, stock, prescriptions, and appointments) if I enable them.',
    },
    settings: {
        title: 'Privacy and data',
        description:
            'View policies, export your data (GDPR), or delete your account.',
        exportTitle: 'Export data',
        exportDescription:
            'Download a JSON file with your account, consents, and depending on your role, medication, prescriptions, intakes, appointments, check-ins, and links with family or healthcare providers.',
        exportAction: 'Download my data',
        legalLinksTitle: 'Policies',
        privacyLink: 'Privacy Policy',
        cookiesLink: 'Cookie Policy',
        termsLink: 'Terms of Service',
    },
};
