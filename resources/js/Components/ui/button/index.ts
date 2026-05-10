import type { VariantProps } from 'class-variance-authority';
import { cva } from 'class-variance-authority';

export { default as Button } from './Button.vue';

export const buttonVariants = cva(
    'inline-flex cursor-pointer items-center justify-center gap-2 whitespace-nowrap rounded-xl text-base font-semibold transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-focus/20 disabled:cursor-not-allowed disabled:bg-disabled-bg disabled:text-disabled-text disabled:opacity-100 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0',
    {
        variants: {
            variant: {
                default: 'bg-primary text-white',
                destructive: 'bg-danger text-white',
                outline: 'border border-border bg-white text-text hover:bg-surface-hover',
                secondary: 'bg-surface-2 text-text',
                ghost: 'bg-transparent text-text hover:bg-surface-hover',
                link: 'text-primary underline-offset-4 hover:underline',
            },
            size: {
                default: 'h-10 px-4 py-2',
                sm: 'h-9 rounded-lg px-3',
                lg: 'h-11 rounded-xl px-8',
                icon: 'h-10 w-10',
                'icon-sm': 'size-9 rounded-lg',
                'icon-lg': 'size-11 rounded-xl',
            },
        },
        defaultVariants: {
            variant: 'default',
            size: 'default',
        },
    },
);

export type ButtonVariants = VariantProps<typeof buttonVariants>;
