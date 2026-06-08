import { createI18n } from 'vue-i18n';
import en from '@/translations/en';
import nl from '@/translations/nl';

const defaultLocale = 'nl';

export const i18n = createI18n({
    legacy: false,
    locale: defaultLocale,
    fallbackLocale: defaultLocale,
    messages: {
        nl,
        en,
    },
});
