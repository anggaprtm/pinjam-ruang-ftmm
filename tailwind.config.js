import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.tsx', // Pastikan baris ini ada biar file React terbaca
    ],

    theme: {
        extend: {
            fontFamily: {
                // Kita ganti font bawaan jadi Inter (lebih modern)
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                mono: ['JetBrains Mono', ...defaultTheme.fontFamily.mono], // Font coding ala hacker
            },
            colors: {
                // INI WARNA KUSTOM DARI DESAIN FUTURISTIK ITU
                navy: {
                    800: '#0a192f',
                    900: '#020c1b', // Warna background utama
                },
                electric: {
                    400: '#64ffda',
                    500: '#00f2ff', // Warna highlight/selection
                },
            },
            animation: {
                'marquee-vertical': 'marqueeVertical 25s linear infinite',
            },
            keyframes: {
                marqueeVertical: {
                '0%': { transform: 'translateY(0)' },
                '100%': { transform: 'translateY(-50%)' }, // Gerak setengah karena list diduplikasi
                },
            },
        },
    },

    plugins: [
        require('@tailwindcss/forms'),
    ],
};