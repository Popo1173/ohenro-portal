module.exports = function (eleventyConfig) {
  eleventyConfig.addPassthroughCopy('src/assets/images')
  eleventyConfig.addPassthroughCopy('src/assets/js')
  eleventyConfig.addPassthroughCopy('src/assets/css')

  //　BrowserSync
  eleventyConfig.setBrowserSyncConfig({
    files: ['./dist/assets/css/**/*.css', './dist/assets/js/**/*.js'],
  })

  return {
    dir: {
      input: 'src',
      includes: 'templates',
      output: 'dist',
    },
  }
}
