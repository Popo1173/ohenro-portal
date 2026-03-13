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
      // 演算子が含まれているか確認
      const isEquals = part.includes('==');
      const isNotEquals = part.includes('!=');
      
      if (!isEquals && !isNotEquals) return true;

      const op = isEquals ? '==' : '!=';
      // 演算子で分割して [キー, 値] を取得
      let [key, val] = part.split(op).map(s => s.trim());

      // 引用符（' や "）がついている場合は除去する
      val = val.replace(/^['"]|['"]$/g, '');

      const cur = state[key] ?? ''

      return op === '==' ? cur === val : cur !== val
    })
  }

  const update = () => {
    readState()

    // other input
    document.querySelectorAll('.js-other').forEach((input) => {
      const q = input.dataset.otherFor
      const targetValue = input.dataset.showOn; // HTMLの data-show-on を取得

      // 選択されている値が、指定された値（その他/其他/Other）と一致するか判定
      input.hidden = state[q] !== targetValue;
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