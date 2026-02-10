/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./app/Views/**/*.php",
    "./includes/**/*.php",
    "./public/**/*.html",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#0a0a0a',
        secondary: '#525252',
        subtle: '#e5e5e5',
        accent: '#000000',
      },
      fontFamily: {
        display: ['Cinzel', 'serif'],
        body: ['Inter', 'sans-serif'],
      },
      letterSpacing: {
        luxury: '0.2em',
      },
      container: {
        center: true,
        padding: '2rem',
      },
    },
  },
  plugins: [],
}
