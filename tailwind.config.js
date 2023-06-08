/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: 'class',

  content: [
    './src/**',
  ],

  theme: {
    extend: {
      fontFamily: {
        sans: '-apple-system,BlinkMacSystemFont,"Segoe UI",Helvetica,Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji"'
      },
      colors: {
        transparent: 'transparent',
        current: 'currentColor',
        white: '#ffffff',
        gray: {
          50: '#d1dbe5',
          100: '#aab2bb',
          200: '#8b929a',
          300: '#7f848c',
          400: '#61666e',
          500: '#4d5460',
          600: '#3f4550',
          700: '#282c34',
          800: '#202329',
          900: '#1a1c21',
        },
      },
    }
  },

  plugins: []
}
