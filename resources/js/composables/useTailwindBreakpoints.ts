import { breakpointsTailwind, useBreakpoints } from '@vueuse/core';

export function useTailwindBreakpoints() {
    const breakpoints = useBreakpoints(breakpointsTailwind);

    return {
        breakpoints,
        smAndUp: breakpoints.greaterOrEqual('sm'),
        mdAndUp: breakpoints.greaterOrEqual('md'),
        lgAndUp: breakpoints.greaterOrEqual('lg'),
    };
}
