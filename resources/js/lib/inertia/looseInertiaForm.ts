export type LooseInertiaForm = {
    setError: (key: string, value: string) => void;
    clearErrors: (...keys: string[]) => void;
};

export function looseInertiaForm(form: object): LooseInertiaForm {
    return form as unknown as LooseInertiaForm;
}
