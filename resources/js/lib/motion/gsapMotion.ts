import type gsap from 'gsap';
import { getGsapSync, loadGsap } from '@/lib/motion/loadGsap';

export interface GsapKillable {
    kill(): void;
}

export type GsapTween = GsapKillable;
export type GsapTimeline = GsapKillable;

function asGsapTween(tween: gsap.core.Tween): GsapTween {
    return tween as GsapTween;
}

function asGsapTimeline(timeline: gsap.core.Timeline): GsapTimeline {
    return timeline as GsapTimeline;
}

const ACTION_CONFIRM_DURATION_SECONDS = 0.2;
const ATTENTION_PULSE_DURATION_SECONDS = 0.55;
const ATTENTION_PULSE_REPEAT = 1;
const CHECKMARK_DRAW_DURATION_SECONDS = 0.5;
const PROGRESS_DURATION_SECONDS = 0.2;
const SUCCESS_FLASH_DURATION_SECONDS = 0.35;
const BUTTON_PRESS_DURATION_SECONDS = 0.1;
const FOOTER_NAV_INDICATOR_DURATION_SECONDS = 0.25;
const WIZARD_STEP_ENTER_DURATION_SECONDS = 0.22;
const WIZARD_STEP_ENTER_X_OFFSET_PX = 12;
const LOADING_SCREEN_EXIT_DURATION_SECONDS = 0.2;

const SVG_STROKE_SELECTOR = 'path, circle, polyline, line, rect';

const BUTTON_PRESS_PROPS = 'scale';
const ACTION_CONFIRM_PROPS = 'opacity,scale';
const WIZARD_STEP_PROPS = 'opacity,x';
const LOADING_OVERLAY_PROPS = 'opacity';
const ATTENTION_PULSE_PROPS = 'opacity';
const SUCCESS_FLASH_PROPS = 'opacity';
const PROGRESS_WIDTH_PROPS = 'width';
const FOOTER_NAV_INDICATOR_PROPS = 'left,top,width,height,opacity';

function killTweenProps(
    gsapInstance: typeof gsap,
    target: gsap.TweenTarget,
    props: string,
): void {
    gsapInstance.killTweensOf(target, props);
}

export function prefersReducedMotion(): boolean {
    return globalThis.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

export function motionDurationSeconds(defaultSeconds: number): number {
    if (prefersReducedMotion()) {
        return 0;
    }

    return defaultSeconds;
}

export function resetActionConfirmVisibility(target: Element): void {
    const gsapInstance = getGsapSync();

    if (gsapInstance === null) {
        return;
    }

    killTweenProps(gsapInstance, target, ACTION_CONFIRM_PROPS);
    gsapInstance.set(target, { opacity: 1, scale: 1, clearProps: 'transform' });
}

export type WizardStepDirection = 'forward' | 'backward';

export function resetWizardStepEnterVisibility(target: Element): void {
    const gsapInstance = getGsapSync();

    if (gsapInstance === null) {
        return;
    }

    killTweenProps(gsapInstance, target, WIZARD_STEP_PROPS);
    gsapInstance.set(target, {
        opacity: 1,
        x: 0,
        clearProps: 'transform,opacity',
    });
}

export async function animateWizardStepEnter(
    target: Element,
    direction: WizardStepDirection,
): Promise<GsapTween> {
    const gsapInstance = await loadGsap();
    const duration = motionDurationSeconds(WIZARD_STEP_ENTER_DURATION_SECONDS);
    const xOffset =
        direction === 'forward'
            ? WIZARD_STEP_ENTER_X_OFFSET_PX
            : -WIZARD_STEP_ENTER_X_OFFSET_PX;

    killTweenProps(gsapInstance, target, WIZARD_STEP_PROPS);

    if (duration === 0) {
        resetWizardStepEnterVisibility(target);

        return asGsapTween(gsapInstance.to(target, { duration: 0 }));
    }

    return asGsapTween(
        gsapInstance.fromTo(
            target,
            { opacity: 0, x: xOffset },
            {
                opacity: 1,
                x: 0,
                duration,
                ease: 'power2.out',
                overwrite: 'auto',
                clearProps: 'transform,opacity',
                onComplete: () => {
                    resetWizardStepEnterVisibility(target);
                },
                onInterrupt: () => {
                    resetWizardStepEnterVisibility(target);
                },
            },
        ),
    );
}

export function resetLoadingScreenOverlay(target: Element): void {
    const gsapInstance = getGsapSync();

    if (gsapInstance === null) {
        return;
    }

    killTweenProps(gsapInstance, target, LOADING_OVERLAY_PROPS);
    gsapInstance.set(target, { opacity: 1, clearProps: 'opacity' });
}

export async function animateLoadingScreenExit(
    target: Element,
    onComplete: () => void,
): Promise<GsapTween> {
    const gsapInstance = await loadGsap();
    const duration = motionDurationSeconds(
        LOADING_SCREEN_EXIT_DURATION_SECONDS,
    );

    killTweenProps(gsapInstance, target, LOADING_OVERLAY_PROPS);

    if (duration === 0) {
        resetLoadingScreenOverlay(target);
        onComplete();

        return asGsapTween(gsapInstance.to(target, { duration: 0 }));
    }

    return asGsapTween(
        gsapInstance.to(target, {
            opacity: 0,
            duration,
            ease: 'power2.out',
            overwrite: 'auto',
            onComplete: () => {
                resetLoadingScreenOverlay(target);
                onComplete();
            },
            onInterrupt: () => {
                resetLoadingScreenOverlay(target);
                onComplete();
            },
        }),
    );
}

export async function animateActionConfirm(
    target: Element,
): Promise<GsapTween> {
    const gsapInstance = await loadGsap();
    const duration = motionDurationSeconds(ACTION_CONFIRM_DURATION_SECONDS);

    killTweenProps(gsapInstance, target, ACTION_CONFIRM_PROPS);

    if (duration === 0) {
        resetActionConfirmVisibility(target);

        return asGsapTween(gsapInstance.to(target, { duration: 0 }));
    }

    return asGsapTween(
        gsapInstance.fromTo(
            target,
            { opacity: 0, scale: 0.97 },
            {
                opacity: 1,
                scale: 1,
                duration,
                ease: 'power2.out',
                immediateRender: true,
                overwrite: 'auto',
                clearProps: 'transform',
                onComplete: () => {
                    resetActionConfirmVisibility(target);
                },
                onInterrupt: () => {
                    resetActionConfirmVisibility(target);
                },
            },
        ),
    );
}

export async function animateProgressWidth(
    target: Element,
    percent: number,
): Promise<GsapTween> {
    const gsapInstance = await loadGsap();
    const duration = motionDurationSeconds(PROGRESS_DURATION_SECONDS);
    const width = `${percent}%`;
    const currentWidth = gsapInstance.getProperty(
        target,
        'width',
        'px',
    ) as number;

    killTweenProps(gsapInstance, target, PROGRESS_WIDTH_PROPS);

    if (duration === 0) {
        gsapInstance.set(target, { width });

        return asGsapTween(gsapInstance.to(target, { duration: 0 }));
    }

    if (!Number.isFinite(currentWidth) || currentWidth <= 0) {
        gsapInstance.set(target, { width: '0%' });
    }

    return asGsapTween(
        gsapInstance.to(target, {
            width,
            duration,
            ease: 'power2.out',
            overwrite: 'auto',
        }),
    );
}

export function resetAttentionPulseVisibility(target: Element): void {
    const gsapInstance = getGsapSync();

    if (gsapInstance === null) {
        return;
    }

    killTweenProps(gsapInstance, target, ATTENTION_PULSE_PROPS);
    gsapInstance.set(target, { opacity: 1, clearProps: 'opacity' });
}

export async function animateAttentionPulse(
    target: Element,
): Promise<GsapTween> {
    const gsapInstance = await loadGsap();
    const duration = motionDurationSeconds(ATTENTION_PULSE_DURATION_SECONDS);

    killTweenProps(gsapInstance, target, ATTENTION_PULSE_PROPS);

    if (duration === 0) {
        resetAttentionPulseVisibility(target);

        return asGsapTween(gsapInstance.to(target, { duration: 0 }));
    }

    return asGsapTween(
        gsapInstance.fromTo(
            target,
            { opacity: 1 },
            {
                opacity: 0.45,
                duration,
                ease: 'power1.inOut',
                overwrite: 'auto',
                repeat: ATTENTION_PULSE_REPEAT,
                yoyo: true,
                onComplete: () => {
                    resetAttentionPulseVisibility(target);
                },
                onInterrupt: () => {
                    resetAttentionPulseVisibility(target);
                },
            },
        ),
    );
}

export function resetSuccessFlashOverlay(target: Element): void {
    const gsapInstance = getGsapSync();

    if (gsapInstance === null) {
        return;
    }

    killTweenProps(gsapInstance, target, SUCCESS_FLASH_PROPS);
    gsapInstance.set(target, { opacity: 0, clearProps: 'opacity' });
}

export async function animateSuccessFlashOverlay(
    target: Element,
): Promise<GsapTween> {
    const gsapInstance = await loadGsap();
    const duration = motionDurationSeconds(SUCCESS_FLASH_DURATION_SECONDS);

    killTweenProps(gsapInstance, target, SUCCESS_FLASH_PROPS);

    if (duration === 0) {
        resetSuccessFlashOverlay(target);

        return asGsapTween(gsapInstance.to(target, { duration: 0 }));
    }

    return asGsapTween(
        gsapInstance.fromTo(
            target,
            { opacity: 1 },
            {
                opacity: 0,
                duration,
                ease: 'power2.out',
                overwrite: 'auto',
                onComplete: () => {
                    resetSuccessFlashOverlay(target);
                },
                onInterrupt: () => {
                    resetSuccessFlashOverlay(target);
                },
            },
        ),
    );
}

export function resetButtonPressScale(target: Element): void {
    const gsapInstance = getGsapSync();

    if (gsapInstance === null) {
        return;
    }

    killTweenProps(gsapInstance, target, BUTTON_PRESS_PROPS);
    gsapInstance.set(target, { scale: 1, clearProps: 'transform' });
}

export async function animateButtonPress(
    target: Element,
    pressed: boolean,
): Promise<GsapTween> {
    const gsapInstance = await loadGsap();
    const duration = motionDurationSeconds(BUTTON_PRESS_DURATION_SECONDS);

    killTweenProps(gsapInstance, target, BUTTON_PRESS_PROPS);

    if (duration === 0) {
        resetButtonPressScale(target);

        return asGsapTween(gsapInstance.to(target, { duration: 0 }));
    }

    const vars: gsap.TweenVars = {
        scale: pressed ? 0.98 : 1,
        duration,
        ease: 'power2.out',
        overwrite: 'auto',
        onInterrupt: () => {
            resetButtonPressScale(target);
        },
    };

    if (!pressed) {
        vars.clearProps = 'transform';
        vars.onComplete = () => {
            resetButtonPressScale(target);
        };
    }

    return asGsapTween(gsapInstance.to(target, vars));
}

export type FooterNavIndicatorMetrics = {
    left: number;
    top: number;
    width: number;
    height: number;
};

export async function animateFooterNavIndicator(
    target: HTMLElement,
    metrics: FooterNavIndicatorMetrics,
    animated: boolean,
): Promise<GsapTween> {
    const gsapInstance = await loadGsap();
    const duration = animated
        ? motionDurationSeconds(FOOTER_NAV_INDICATOR_DURATION_SECONDS)
        : 0;

    killTweenProps(gsapInstance, target, FOOTER_NAV_INDICATOR_PROPS);

    const properties = {
        left: metrics.left,
        top: metrics.top,
        width: metrics.width,
        height: metrics.height,
        opacity: metrics.width > 0 ? 1 : 0,
    };

    if (duration === 0) {
        gsapInstance.set(target, properties);

        return asGsapTween(gsapInstance.to(target, { duration: 0 }));
    }

    return asGsapTween(
        gsapInstance.to(target, {
            ...properties,
            duration,
            ease: 'power2.out',
            overwrite: 'auto',
        }),
    );
}

function resolveCheckmarkSvg(target: Element): SVGSVGElement | null {
    if (target instanceof SVGSVGElement) {
        return target;
    }

    const nestedSvg = target.querySelector('svg');

    if (nestedSvg instanceof SVGSVGElement) {
        return nestedSvg;
    }

    return null;
}

function collectSvgStrokeElements(svg: SVGSVGElement): SVGGeometryElement[] {
    return [...svg.querySelectorAll(SVG_STROKE_SELECTOR)].filter(
        (element): element is SVGGeometryElement =>
            element instanceof SVGGeometryElement,
    );
}

export function resetCheckmarkDraw(target: Element): void {
    const gsapInstance = getGsapSync();
    const svg = resolveCheckmarkSvg(target);

    if (gsapInstance === null || svg === null) {
        return;
    }

    const strokeElements = collectSvgStrokeElements(svg);

    gsapInstance.killTweensOf(strokeElements);

    for (const element of strokeElements) {
        gsapInstance.set(element, {
            strokeDashoffset: 0,
            clearProps: 'strokeDashoffset,strokeDasharray',
        });
    }
}

export async function animateCheckmarkDraw(
    target: Element,
): Promise<GsapTimeline> {
    const gsapInstance = await loadGsap();
    const duration = motionDurationSeconds(CHECKMARK_DRAW_DURATION_SECONDS);
    const svg = resolveCheckmarkSvg(target);
    const timeline = gsapInstance.timeline();

    if (svg === null) {
        return asGsapTimeline(timeline);
    }

    const strokeElements = collectSvgStrokeElements(svg);

    gsapInstance.killTweensOf(strokeElements);

    if (duration === 0 || strokeElements.length === 0) {
        resetCheckmarkDraw(target);

        return asGsapTimeline(timeline);
    }

    for (const [index, element] of strokeElements.entries()) {
        const length = element.getTotalLength();

        if (length <= 0) {
            continue;
        }

        gsapInstance.set(element, {
            strokeDasharray: length,
            strokeDashoffset: length,
        });

        timeline.to(
            element,
            {
                strokeDashoffset: 0,
                duration: duration * 0.65,
                ease: 'power2.out',
            },
            index * 0.12,
        );
    }

    return asGsapTimeline(timeline);
}
