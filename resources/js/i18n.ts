import { createI18n } from 'vue-i18n';
import nl from '@/translations/nl';

const defaultLocale = 'nl';

export const i18n = createI18n({
    legacy: false,
    locale: defaultLocale,
    fallbackLocale: defaultLocale,
    messages: {
        [defaultLocale]: nl,
    },
});
