/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./src/**/*.{html,js}",
    "./public/js/**/*.{js}",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Vazirmatn', 'Tahoma', 'Arial', 'sans-serif'],
        vazirmatn: ['Vazirmatn', 'Tahoma', 'Arial', 'sans-serif'],
      },
      colors: {
        primary: {
          50: '#f0f9ff',
          100: '#e0f2fe',
          200: '#bae6fd',
          300: '#7dd3fc',
          400: '#38bdf8',
          500: '#0ea5e9',
          600: '#0284c7',
          700: '#0369a1',
          800: '#075995',
          900: '#0c4a6e',
          950: '#082f49',
        },
        secondary: {
          50: '#fdf4f3',
          100: '#fbe9e7',
          200: '#f5c2bf',
          300: '#ea9b96',
          400: '#e07471',
          500: '#d64d4d',
          600: '#bd3a35',
          700: '#9e2f27',
          800: '#80251a',
          900: '#631b0f',
        },
      },
    },
    plugins: [],
  }
}