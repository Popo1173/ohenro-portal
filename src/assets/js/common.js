// パスワード表示、非表示時切り替え
document.addEventListener('DOMContentLoaded', () => {
  const containers = document.querySelectorAll('.js-password-container')

  containers.forEach((container) => {
    const input = container.querySelector('.js-password-input')
    const toggle = container.querySelector('.js-password-toggle')
    const icon = toggle.querySelector('img')

    toggle.addEventListener('click', () => {
      const isActive = toggle.classList.toggle('is-active')

      input.type = isActive ? 'text' : 'password'

      icon.src = `/assets/images/icons/icon-pasword-${isActive ? 'on' : 'off'}.svg`
    })
  })
})
