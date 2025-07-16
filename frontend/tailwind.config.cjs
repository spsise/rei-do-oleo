/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./index.html', './src/**/*.{js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        brand: {
          500: '#465fff',
          600: '#3641f5',
          700: '#2a31d8',
        },
      },
    },
  },
  plugins: [],
};
