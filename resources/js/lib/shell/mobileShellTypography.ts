/** Centered hero title on {@link ActionSuccessScreen} and {@link PatientConfirmDialog}. */
export const mobileShellActionSuccessTitleClass =
    'text-text-heading max-w-full text-3xl leading-tight font-bold tracking-tight text-balance sm:text-4xl lg:text-5xl';

/** Same as {@link mobileShellActionSuccessTitleClass} when an eyebrow line is shown above. */
export const mobileShellActionSuccessTitleAfterEyebrowClass =
    'text-text-heading mt-4 max-w-full text-3xl leading-tight font-bold tracking-tight text-balance sm:text-4xl lg:text-5xl';

/** Subtitle under the success / confirm hero title. */
export const mobileShellActionSuccessSubtitleClass =
    'text-text-muted mt-4 max-w-sm text-base leading-relaxed sm:text-lg';

/** Visible page title (h1) on patient screens with header actions. */
export const mobileShellPageTitleClass =
    'min-w-0 text-3xl font-bold leading-tight text-text-heading sm:text-4xl sm:leading-tight';

/** Row wrapping a page title and optional primary action(s). */
export const mobileShellPageHeaderRowClass =
    'flex min-w-0 w-full flex-col gap-5 sm:flex-row sm:items-start sm:justify-between sm:gap-6';

/** Secondary section heading below the page title (h2). */
export const mobileShellPageSectionTitleClass =
    'text-2xl font-bold leading-tight text-text-heading';

/** Section heading inside patient cards (family, care team). */
export const mobileShellSectionHeadingClass =
    'text-xl font-bold leading-snug text-text-heading md:text-2xl';

/** Subheading under {@link mobileShellSectionHeadingClass}. */
export const mobileShellSectionSubHeadingClass =
    'text-lg font-semibold leading-snug text-text-heading';

/** Body copy under patient section headings. */
export const mobileShellSectionBodyTextClass =
    'text-base leading-relaxed text-text-muted';

/** Intro copy under a visible page title in PatientPageShell. */
export const mobileShellPageIntroClass =
    'text-base leading-relaxed text-text-muted md:text-lg';

/** Wraps page-level action buttons; always full content width. */
export const mobileShellPageActionsBarClass = 'flex w-full min-w-0';

/** Stacked page actions with equal full width per row. */
export const mobileShellPageActionsGridClass =
    'grid w-full min-w-0 grid-cols-1 gap-3';

/** Shared width and height for full-width patient CTAs (mobile tap height on all breakpoints). */
export const mobileShellPageWideButtonClass =
    'h-auto min-h-14 w-full min-w-0 touch-manipulation';

/** Primary/secondary actions in page intros (Medicatie toevoegen, Vakantie, …). */
export const mobileShellPageIntroButtonClass = `${mobileShellPageWideButtonClass} gap-2.5 justify-center px-6 font-body text-lg font-bold sm:px-8 [&_svg]:size-6`;

/** Wrapper for expand toggle on patient list cards — subtle, below primary actions. */
export const mobileShellPageCardDetailsExpandWrapperClass = 'mt-3';

/** Wrapper for collapse toggle below expanded card details. */
export const mobileShellPageCardDetailsCollapseWrapperClass =
    'mt-4 border-t border-border/30 pt-3';

/** Expand/collapse toggle — outline intro styling at compact card size (~44px). */
export const mobileShellPageCardDetailsButtonClass =
    'h-auto min-h-11 w-full min-w-0 touch-manipulation gap-1.5 justify-center px-4 py-2.5 text-base font-bold leading-snug sm:min-h-12';

/** Chevron on patient list card expand/collapse toggles. */
export const mobileShellPageCardDetailsChevronClass = 'size-5 shrink-0 sm:size-6';

/** Edit/delete toolbar on patient list cards. */
export const mobileShellPageCardToolbarClass =
    'absolute right-6 top-6 z-10 flex flex-row items-center gap-0.5 sm:right-7 sm:top-7';

/** Header padding when a list card shows the edit/delete toolbar. */
export const mobileShellPageCardHeaderWithActionsClass = 'pr-21 sm:pr-28';

/** Divider and spacing for stacked sections below a list card header. */
export const mobileShellPageCardFooterSectionClass =
    'mt-4 border-t border-border/50 pt-3 sm:mt-5 sm:pt-4';

/** Row layout for MedicationListCardLead (icon + title stack). */
export const mobileShellMedicationListCardLeadRowClass =
    'flex min-w-0 items-start gap-4';

/** Icon pill on MedicationListCardLead and matching taken-section header. */
export const mobileShellMedicationListCardLeadIconWrapClass =
    'flex size-12 shrink-0 items-center justify-center rounded-xl';

/** Primary title on MedicationListCardLead. */
export const mobileShellMedicationListCardLeadTitleClass =
    'text-text-heading text-lg leading-snug font-bold sm:text-xl';

/** Summary line under the title on patient list cards. */
export const mobileShellPageCardHeaderSummaryClass =
    'text-base font-medium leading-snug text-text sm:text-lg';

export const mobileShellPageCardToolbarButtonClass = 'size-11 [&_svg]:size-6';

export const mobileShellPageCardToolbarIconClass = 'size-6';

/** Label above a value on patient list card detail rows. */
export const mobileShellPageCardDetailLabelClass =
    'text-base font-semibold leading-tight text-text-heading';

/** Stacked detail rows inside an expanded patient list card. */
export const mobileShellPageCardDetailsGroupClass = 'space-y-5';

/** Icon column on patient list card detail rows (leading icon from lucide). */
export const mobileShellPageCardDetailIconWrapperClass =
    'mt-0.5 shrink-0 self-start text-primary [&_svg]:size-6';

/** Value text on patient list card detail rows (matches appointment cards). */
export const mobileShellPageCardDetailValueClass =
    'text-lg font-medium leading-relaxed tracking-tight text-text sm:text-xl sm:leading-snug';
