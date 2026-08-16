/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./app/Http/Controllers/**/*.php",
    ],
    theme: {
        extend: {
            colors: {
                brand: {
                    primary: '#1F6B45',      // Primary Green
                    'primary-dark': '#165034', // Darker Green for hover
                    'primary-light': '#2a8b5b', // Lighter Green
                    dark: '#17231D',         // Dark / Heading & dark backgrounds
                    'dark-soft': '#22322a',  // Softer dark
                    cream: '#F7F5EF',        // Warm Cream background
                    'cream-light': '#FCFBF8', // Ultra light cream
                    white: '#FFFFFF',
                    accent: '#E7A93B',       // Warm Accent / Gold
                    'accent-hover': '#cf942e',
                    'soft-green': '#EAF3ED', // Soft green pill & tint
                    'soft-green-border': '#c8e0d0',
                    gray: {
                        50: '#F9FAFB',
                        100: '#F3F4F6',
                        200: '#E5E7EB',
                        300: '#D1D5DB',
                        400: '#9CA3AF',
                        500: '#6B7280',
                        600: '#4B5563',
                        700: '#374151',
                        800: '#1F2937',
                    }
                }
            },
            fontFamily: {
                sans: ['Poppins', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                display: ['Poppins', 'sans-serif'],
            },
            borderRadius: {
                'modern-sm': '10px',
                'modern': '14px',
                'modern-lg': '18px',
                'modern-xl': '24px',
            },
            boxShadow: {
                'subtle': '0 2px 10px rgba(23, 35, 29, 0.04)',
                'card': '0 8px 24px -4px rgba(23, 35, 29, 0.06), 0 2px 6px -2px rgba(23, 35, 29, 0.04)',
                'card-hover': '0 20px 32px -8px rgba(23, 35, 29, 0.12), 0 8px 16px -4px rgba(31, 107, 69, 0.08)',
                'floating': '0 12px 36px rgba(31, 107, 69, 0.25)',
            },
            animation: {
                'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                'float': 'float 4s ease-in-out infinite',
            },
            keyframes: {
                float: {
                    '0%, 100%': { transform: 'translateY(0px)' },
                    '50%': { transform: 'translateY(-6px)' },
                }
            }
        },
    },
    plugins: [],
}
