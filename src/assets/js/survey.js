document.addEventListener('DOMContentLoaded', () => {
  const selects = [...document.querySelectorAll('.js-select')]
  const blocks = [...document.querySelectorAll('.js-q')]

  const state = {}

  const readState = () => {
    selects.forEach((sel) => {
      state[sel.dataset.q] = sel.value
    })
  }

  const evalShowIf = (expr) => {
    if (!expr) return true

    // 例: "q2 != 0 && q3 != no"
    const parts = expr.split('&&').map((s) => s.trim())

    return parts.every((part) => {
      const m = part.match(/^(\w+)\s*(==|!=)\s*([\w\-\+]+)$/)
      if (!m) return true
      const [, key, op, val] = m
      const cur = state[key] ?? ''
      return op === '==' ? cur === val : cur !== val
    })
  }

  const update = () => {
    readState()

    // other input
    document.querySelectorAll('.js-other').forEach((input) => {
      const q = input.dataset.otherFor
      input.hidden = state[q] !== 'other'
    })

    // showIf blocks
    blocks.forEach((block) => {
      const visible = evalShowIf(block.dataset.showIf)
      block.hidden = !visible

      // 非表示になったら値もクリア（分岐の整合性が崩れない）
      if (!visible) {
        const sel = block.querySelector('.js-select')
        if (sel) sel.value = ''
        const other = block.querySelector('.js-other')
        if (other) {
          other.value = ''
          other.hidden = true
        }
      }
    })
  }

  selects.forEach((sel) => sel.addEventListener('change', update))
  update()
})
