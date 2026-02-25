module.exports = function (eleventyConfig) {
  // 監視
  eleventyConfig.addPassthroughCopy('src/assets/images')
  eleventyConfig.addPassthroughCopy('src/assets/js')
  eleventyConfig.addPassthroughCopy('src/assets/css')
  eleventyConfig.addWatchTarget('/src/assets/scss/')

  // Eleventy v3用
  eleventyConfig.setServerOptions({
    watch: ['dist/assets/css/**/*.css', 'dist/assets/js/**/*.js'],
  })

  return {
    dir: {
      input: 'src',
      includes: 'templates',
      output: 'dist',
    },
  }
}
