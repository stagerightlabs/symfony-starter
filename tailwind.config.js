module.exports = {
  content: [
    './templates/**/*.{twig,html}'
  ],
  theme: {
    extend: {}
  },
  plugins: [
    require('@tailwindcss/forms')
  ]
}
