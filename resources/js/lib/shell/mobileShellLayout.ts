/** Shared scroll content wrapper for patient/family mobile shells. */
export const mobileShellScrollContentClass =
    'relative mx-auto flex min-h-full w-full max-w-7xl flex-col px-4 pt-4 pb-4 md:pt-6 lg:px-8';

/** Shared footer nav wrapper for patient/family mobile shells. */
export const mobileShellFooterNavClass =
    'relative mx-auto flex max-w-7xl items-stretch justify-around px-4 pt-2 pb-[max(0.5rem,env(safe-area-inset-bottom,0px))] lg:px-8';

/** Shared inner page column — uses full shell width (max-w-7xl). */
export const mobileShellPageContentClass =
    'mx-auto flex w-full max-w-7xl flex-col gap-6 md:gap-5';

/** Bordered section card on patient settings-style pages (family, care team). */
export const mobileShellPageSectionCardClass =
    'border-border bg-surface rounded-2xl border-2 p-6 shadow-sm sm:p-8';

/** Accent variant for action-required patient section cards. */
export const mobileShellPageSectionCardAccentClass =
    'border-primary/40 bg-primary/5 rounded-2xl border-2 p-6 shadow-sm sm:p-8';

/** Nested row inside {@link mobileShellPageSectionCardClass} sections. */
export const mobileShellPageSectionInnerRowClass =
    'border-border bg-surface rounded-2xl border-2 px-4 py-4 shadow-sm sm:px-5 sm:py-5';

/** Success-accent nested row (accepted family medication plans). */
export const mobileShellPageSectionInnerRowSuccessClass =
    'bg-surface border-success/55 dark:border-success/65 rounded-2xl border-2 px-4 py-4 shadow-sm sm:px-5 sm:py-5';

/** Compact dashboard / settings prompt card. */
export const mobileShellDashboardPromptCardClass =
    'border-border bg-surface relative rounded-2xl border-2 p-4 shadow-sm sm:p-5';
