import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

const withOpacity = (variable) => `rgb(var(${variable}) / <alpha-value>)`;

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.{js,ts,vue}',
    ],

    theme: {
        extend: {
            colors: {
                border: withOpacity('--border'),
                input: withOpacity('--input'),
                ring: withOpacity('--ring'),
                background: withOpacity('--background'),
                foreground: withOpacity('--foreground'),
                primary: {
                    DEFAULT: withOpacity('--primary'),
                    foreground: withOpacity('--primary-foreground'),
                },
                secondary: {
                    DEFAULT: withOpacity('--secondary'),
                    foreground: withOpacity('--secondary-foreground'),
                },
                destructive: {
                    DEFAULT: withOpacity('--destructive'),
                    foreground: withOpacity('--primary-foreground'),
                },
                muted: {
                    DEFAULT: withOpacity('--muted'),
                    foreground: withOpacity('--muted-foreground'),
                },
                accent: {
                    DEFAULT: withOpacity('--accent'),
                    foreground: withOpacity('--accent-foreground'),
                },
                popover: {
                    DEFAULT: withOpacity('--popover'),
                    foreground: withOpacity('--popover-foreground'),
                },
                card: {
                    DEFAULT: withOpacity('--card'),
                    foreground: withOpacity('--card-foreground'),
                },
                sidebar: {
                    DEFAULT: withOpacity('--sidebar'),
                    foreground: withOpacity('--sidebar-foreground'),
                    primary: withOpacity('--sidebar-primary'),
                    'primary-foreground': withOpacity('--sidebar-primary-foreground'),
                    accent: withOpacity('--sidebar-accent'),
                    'accent-foreground': withOpacity('--sidebar-accent-foreground'),
                    border: withOpacity('--sidebar-border'),
                    ring: withOpacity('--sidebar-ring'),
                },
            },
            borderRadius: {
                lg: 'var(--radius)',
                md: 'calc(var(--radius) - 2px)',
                sm: 'calc(var(--radius) - 4px)',
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
