/** Centered hero title on {@link ActionSuccessScreen} and {@link PatientConfirmDialog}. */
export const patientActionSuccessTitleClass =
    'text-text-heading max-w-full text-3xl leading-tight font-bold tracking-tight text-balance sm:text-4xl lg:text-5xl';

/** Same as {@link patientActionSuccessTitleClass} when an eyebrow line is shown above. */
export const patientActionSuccessTitleAfterEyebrowClass =
    'text-text-heading mt-4 max-w-full text-3xl leading-tight font-bold tracking-tight text-balance sm:text-4xl lg:text-5xl';

/** Subtitle under the success / confirm hero title. */
export const patientActionSuccessSubtitleClass =
    'text-text-muted mt-4 max-w-sm text-base leading-relaxed sm:text-lg';

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

/** Wrapper for expand toggle on patient list cards — subtle, below primary actions. */
export const patientPageCardDetailsExpandWrapperClass = 'mt-3';

/** Wrapper for collapse toggle below expanded card details. */
export const patientPageCardDetailsCollapseWrapperClass =
    'mt-4 border-t border-border/30 pt-3';

/** Expand/collapse toggle — outline intro styling at compact card size (~44px). */
export const patientPageCardDetailsButtonClass =
    'h-auto min-h-11 w-full min-w-0 touch-manipulation gap-1.5 justify-center px-4 py-2.5 text-base font-bold leading-snug sm:min-h-12';

/** Chevron on patient list card expand/collapse toggles. */
export const patientPageCardDetailsChevronClass = 'size-5 shrink-0 sm:size-6';

/** Edit/delete toolbar on patient list cards. */
export const patientPageCardToolbarClass =
    'absolute right-6 top-6 z-10 flex flex-row items-center gap-0.5 sm:right-7 sm:top-7';

/** Header padding when a list card shows the edit/delete toolbar. */
export const patientPageCardHeaderWithActionsClass = 'pr-21 sm:pr-28';

/** Divider and spacing for stacked sections below a list card header. */
export const patientPageCardFooterSectionClass =
    'mt-4 border-t border-border/50 pt-3 sm:mt-5 sm:pt-4';

/** Row layout for MedicationListCardLead (icon + title stack). */
export const patientMedicationListCardLeadRowClass =
    'flex min-w-0 items-start gap-4';

/** Icon pill on MedicationListCardLead and matching taken-section header. */
export const patientMedicationListCardLeadIconWrapClass =
    'flex size-12 shrink-0 items-center justify-center rounded-xl';

/** Primary title on MedicationListCardLead. */
export const patientMedicationListCardLeadTitleClass =
    'text-text-heading text-lg leading-snug font-bold sm:text-xl';

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
