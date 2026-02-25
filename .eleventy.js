module.exports = function (eleventyConfig) {
  /**
   * distへ各フォルダへのコピー
   * JSはバンドルしないため、記載（バンドルする場合は削除する）
   *  */
  eleventyConfig.addPassthroughCopy('src/assets/images')
  eleventyConfig.addPassthroughCopy('src/assets/js')
  eleventyConfig.addWatchTarget('/src/assets/scss/')

  // Eleventy v3用 js,SCSSはデフォルトで監視対象外のため
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
