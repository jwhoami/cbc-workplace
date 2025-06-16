/** @type {import('tailwindcss').Config} */

import preset from './vendor/filament/support/tailwind.config.preset';

export default {
  darkMode: 'class',
  content: [
    "./app/Filament/**/*.php",
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    './resources/views/filament/**/*.blade.php',
    './vendor/filament/**/*.blade.php',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
};
