// パスワード表示、非表示時切り替え
document.addEventListener('DOMContentLoaded', () => {
  const containers = document.querySelectorAll('.js-password-container')

  containers.forEach((container) => {
    const input = container.querySelector('.js-password-input')
    const toggle = container.querySelector('.js-password-toggle')
    const icon = toggle.querySelector('img')

    // type切り替えとアイコン切り替え
    toggle.addEventListener('click', () => {
      const isActive = toggle.classList.toggle('is-active')

      // type 切り替え
      input.type = isActive ? 'text' : 'password'

      // アイコン切り替え
      icon.src = `/assets/images/icons/icon-pasword-${isActive ? 'on' : 'off'}.svg`
    })
  })
})
