export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Poppins', 'Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                mono: ['JetBrains Mono', 'ui-monospace', 'SFMono-Regular', 'Consolas', 'monospace'],
            },
            boxShadow: {
                DEFAULT: 'none',
                sm: 'none',
                md: 'none',
                lg: 'none',
                xl: 'none',
                '2xl': 'none',
                inner: 'none',
                none: 'none',
            },
            dropShadow: {
                DEFAULT: 'none',
                sm: 'none',
                md: 'none',
                lg: 'none',
                xl: 'none',
                '2xl': 'none',
                none: 'none',
            },
        },
    },
    plugins: [],
};
