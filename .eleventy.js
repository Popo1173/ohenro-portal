module.exports = function (eleventyConfig) {
  eleventyConfig.addPassthroughCopy('src/assets/images')
  eleventyConfig.addPassthroughCopy('src/assets/js')
  return {
    dir: {
      input: 'src',
      includes: 'templates',
      output: 'dist',
    },
  }
}
