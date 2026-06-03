import type gsap from 'gsap';

type Gsap = typeof gsap;

let gsapInstance: Gsap | null = null;
let loadPromise: Promise<Gsap> | null = null;

export function loadGsap(): Promise<Gsap> {
    if (gsapInstance !== null) {
        return Promise.resolve(gsapInstance);
    }

    if (loadPromise === null) {
        loadPromise = import('gsap').then((module) => {
            gsapInstance = module.default;

            return gsapInstance;
        });
    }

    return loadPromise;
}

export function getGsapSync(): Gsap | null {
    return gsapInstance;
}
