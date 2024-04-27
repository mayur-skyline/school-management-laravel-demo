module.exports = {
  mode: 'jit',
  content: [
    './resources/views/**/*.blade.php',
    './resources/astnext/**/*.tsx',
    './resources/atom/**/*.tsx',
    './resources/sga/**/*.tsx',
  ],
  theme: {
    extend: {
      spacing: {
        '128': '32rem',
      },
      transitionProperty: {
        'spacing': 'height, width, margin, padding'
      }
    },
  },
  plugins: [],
};
