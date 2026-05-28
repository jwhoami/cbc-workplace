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
    extend: {
      colors: {
        brand: {
          blue: '#2563eb',     // Faith Blue
          amber: '#d97706',    // Warm Glory
          charcoal: '#1f2937', // Foundation Charcoal
          darkBg: '#0b0f19',   // Premium Slate-Dark
        }
      }
    },
  },
  plugins: [],
};
