module.exports = function (eleventyConfig) {
  eleventyConfig.addWatchTarget('./src/assets/scss/')

  return {
    dir: {
      input: 'src',
      includes: 'templates',
      output: 'dist',
    },
  }
}
