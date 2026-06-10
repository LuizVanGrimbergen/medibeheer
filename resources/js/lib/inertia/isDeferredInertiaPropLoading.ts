export function isDeferredInertiaPropLoading<T>(
    value: T | undefined,
): value is undefined {
    return value === undefined;
}

export function areAnyDeferredInertiaPropsLoading(
    ...values: unknown[]
): boolean {
    return values.some(isDeferredInertiaPropLoading);
}
