/** Visible page title (h1) on patient screens with header actions. */
export const patientPageTitleClass =
    'min-w-0 text-3xl font-bold leading-tight text-text-heading sm:text-4xl sm:leading-tight';

/** Row wrapping a page title and optional primary action(s). */
export const patientPageHeaderRowClass =
    'flex min-w-0 w-full flex-col gap-5 sm:flex-row sm:items-start sm:justify-between sm:gap-6';

/** Secondary section heading below the page title (h2). */
export const patientPageSectionTitleClass =
    'text-2xl font-bold leading-tight text-text-heading';

/** Intro copy under a visible page title in PatientPageShell. */
export const patientPageIntroClass =
    'text-base leading-relaxed text-text-muted md:text-lg';

/** Wraps page-level action buttons; always full content width. */
export const patientPageActionsBarClass = 'flex w-full min-w-0';

/** Stacked page actions with equal full width per row. */
export const patientPageActionsGridClass =
    'grid w-full min-w-0 grid-cols-1 gap-3';

/** Shared width and height for full-width patient CTAs (mobile tap height on all breakpoints). */
export const patientPageWideButtonClass =
    'h-auto min-h-14 w-full min-w-0 touch-manipulation';

/** Primary/secondary actions in page intros (Medicatie toevoegen, Vakantie, …). */
export const patientPageIntroButtonClass = `${patientPageWideButtonClass} gap-2.5 justify-center px-6 font-body text-lg font-bold sm:px-8 [&_svg]:size-6`;

/** Expand/collapse toggle on patient list cards (Tik voor meer details / Tik om te sluiten). */
export const patientPageCardDetailsButtonClass = `${patientPageWideButtonClass} group justify-between gap-3 rounded-2xl border-0 bg-surface-2/80 px-4 py-3 text-base font-semibold text-text-heading shadow-none transition-colors hover:bg-primary/8 hover:opacity-100 focus-visible:ring-primary/20`;

/** Chevron badge on patient list card expand/collapse toggles. */
export const patientPageCardDetailsChevronClass =
    'flex size-11 shrink-0 items-center justify-center rounded-xl bg-text-heading/8 text-text-heading transition-colors group-hover:bg-text-heading/12 [&_svg]:size-6';

/** Edit/delete toolbar on patient list cards. */
export const patientPageCardToolbarClass =
    'absolute right-6 top-6 z-10 flex flex-row items-center gap-0.5 sm:right-7 sm:top-7';

/** Header padding when a list card shows the edit/delete toolbar. */
export const patientPageCardHeaderWithActionsClass = 'pr-21 sm:pr-28';

/** Divider and spacing for stacked sections below a list card header. */
export const patientPageCardFooterSectionClass =
    'mt-5 border-t border-border/50 pt-4';

/** Summary line under the title on patient list cards. */
export const patientPageCardHeaderSummaryClass =
    'text-base font-medium leading-snug text-text sm:text-lg';

export const patientPageCardToolbarButtonClass = 'size-11 [&_svg]:size-6';

export const patientPageCardToolbarIconClass = 'size-6';

/** Label above a value on patient list card detail rows. */
export const patientPageCardDetailLabelClass =
    'text-base font-semibold leading-tight text-text-heading';

/** Stacked detail rows inside an expanded patient list card. */
export const patientPageCardDetailsGroupClass = 'space-y-5';

/** Icon column on patient list card detail rows (leading icon from lucide). */
export const patientPageCardDetailIconWrapperClass =
    'mt-0.5 shrink-0 self-start text-primary [&_svg]:size-6';

/** Value text on patient list card detail rows (matches appointment cards). */
export const patientPageCardDetailValueClass =
    'text-lg font-medium leading-relaxed tracking-tight text-text sm:text-xl sm:leading-snug';
