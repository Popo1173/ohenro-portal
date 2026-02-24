document.addEventListener('DOMContentLoaded', () => {
  const containers = document.querySelectorAll('.js-password-container')

  containers.forEach((container) => {
    const input = container.querySelector('.js-password-input')
    const toggle = container.querySelector('.js-password-toggle')

    if (input && toggle) {
      toggle.addEventListener('click', () => {
        // type属性を切り替える
        const isPassword = input.getAttribute('type') === 'password'
        input.setAttribute('type', isPassword ? 'text' : 'password')

        // (オプション) アイコンの色やスタイルを切り替える場合はここに追加
        toggle.classList.toggle('is-active')
      })
    }
  })
})
