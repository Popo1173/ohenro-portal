const fs = require('fs')
const { parse } = require('csv-parse/sync')

module.exports = () => {
  const csv = fs.readFileSync('./src/_data/glossaryJa.csv', 'utf8')

  return parse(csv, {
    columns: true,
    skip_empty_lines: true,
    trim: true,
  })
}
