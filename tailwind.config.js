/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.ts',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {},
    },
    plugins: [],
};
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

const withOpacity = (cssVariable) => {
    return `rgb(var(${cssVariable}) / <alpha-value>)`;
};

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{js,ts,vue}',
    ],
    theme: {
        extend: {
            colors: {
                bg: withOpacity('--color-bg'),
                surface: withOpacity('--color-surface'),
                'surface-2': withOpacity('--color-surface-2'),
                text: withOpacity('--color-text'),
                'text-muted': withOpacity('--color-text-muted'),
                primary: withOpacity('--color-primary'),
                'role-patient': withOpacity('--color-role-patient'),
                'role-doctor': withOpacity('--color-role-doctor'),
                'role-family': withOpacity('--color-role-family'),
                success: withOpacity('--color-success'),
                warning: withOpacity('--color-warning'),
                'warning-text': withOpacity('--color-warning-text'),
                danger: withOpacity('--color-danger'),
                focus: withOpacity('--color-focus'),
                border: withOpacity('--color-border'),
                'disabled-bg': withOpacity('--color-disabled-bg'),
                'disabled-text': withOpacity('--color-disabled-text'),
            },
            fontFamily: {
                sans: [
                    'var(--font-body)',
                    ...defaultTheme.fontFamily.sans,
                ],
            },
        },
    },
    plugins: [forms],
};
