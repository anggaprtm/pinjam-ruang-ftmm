import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.tsx',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                // ── BRAND UTAMA: Maroon FTMM ──────────────────────────────
                maroon: {
                    50:  '#f9eef4',
                    100: '#f2d5e5',
                    200: '#e5abcb',
                    300: '#d176a8',
                    400: '#b84d85',
                    500: '#9c2456',   // mid accent
                    600: '#741847',   // brand utama
                    700: '#5a1238',
                    800: '#3d0d26',
                    900: '#200714',
                },

                // ── SURFACE / BACKGROUND ──────────────────────────────────
                surface: {
                    0:      '#ffffff',
                    1:      '#f8f6fa',   // background halaman
                    2:      '#f1eef4',   // card ringan
                    3:      '#e8e2ee',   // hover / divider
                    border: '#ddd4e6',   // border default
                },

                // ── TEXT ──────────────────────────────────────────────────
                ink: {
                    primary:   '#1a1025',
                    secondary: '#6b6080',
                    muted:     '#a09ab8',
                    inverted:  '#ffffff',
                },

                // ── SEMANTIK ──────────────────────────────────────────────
                success: {
                    50:  '#eaf7f2',
                    400: '#34d399',
                    600: '#0d9668',
                    800: '#065f46',
                },
                warning: {
                    50:  '#fef3c7',
                    400: '#fbbf24',
                    600: '#d97706',
                    800: '#92400e',
                },
                danger: {
                    50:  '#fef2f2',
                    400: '#f87171',
                    600: '#dc2626',
                    800: '#991b1b',
                },
                info: {
                    50:  '#eff6ff',
                    400: '#60a5fa',
                    600: '#2563eb',
                    800: '#1e3a8a',
                },

                // ── BACKWARD COMPAT: navy & electric tetap ada ────────────
                // (dipakai komponen lama, bisa dihapus setelah full migration)
                navy: {
                    800: '#0a192f',
                    900: '#020c1b',
                    950: '#010810',
                },
                electric: {
                    400: '#64ffda',
                    500: '#00f2ff',
                },
            },

            // ── SHADOWS ───────────────────────────────────────────────────
            boxShadow: {
                'sm-brand':  '0 1px 3px rgba(116,24,71,0.06), 0 1px 2px rgba(0,0,0,0.04)',
                'md-brand':  '0 4px 16px rgba(116,24,71,0.10), 0 2px 6px rgba(0,0,0,0.05)',
                'lg-brand':  '0 10px 40px rgba(116,24,71,0.14), 0 4px 12px rgba(0,0,0,0.06)',
                'xl-brand':  '0 20px 60px rgba(116,24,71,0.18), 0 8px 20px rgba(0,0,0,0.08)',
                'card':      '0 1px 4px rgba(0,0,0,0.06)',
                'card-hover':'0 4px 20px rgba(116,24,71,0.12)',
            },

            // ── GRADIENTS ─────────────────────────────────────────────────
            backgroundImage: {
                'maroon-gradient':
                    'linear-gradient(135deg, #741847 0%, #9c2456 50%, #741847 100%)',
                'maroon-subtle':
                    'linear-gradient(135deg, #f9eef4 0%, #f1eef4 100%)',
                'surface-gradient':
                    'linear-gradient(180deg, #ffffff 0%, #f8f6fa 100%)',
            },

            animation: {
                'marquee-vertical': 'marqueeVertical 25s linear infinite',
                'pulse-slow':       'pulse 3s cubic-bezier(0.4,0,0.6,1) infinite',
            },
            keyframes: {
                marqueeVertical: {
                    '0%':   { transform: 'translateY(0)' },
                    '100%': { transform: 'translateY(-50%)' },
                },
            },
        },
    },

    plugins: [
        require('@tailwindcss/forms'),
    ],
};