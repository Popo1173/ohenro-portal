document.addEventListener('DOMContentLoaded', () => {
  // =========================
  // パスワード表示切替
  // =========================
  const containers = document.querySelectorAll('.js-password-container')

  containers.forEach((container) => {
    const input = container.querySelector('.js-password-input')
    const toggle = container.querySelector('.js-password-toggle')
    const icon = toggle?.querySelector('img')

    if (!input || !toggle) return

    toggle.addEventListener('click', () => {
      const isActive = toggle.classList.toggle('is-active')

      input.type = isActive ? 'text' : 'password'

      if (icon) {
        icon.src = `/assets/images/icons/icon-pasword-${isActive ? 'on' : 'off'}.svg`
      }
    })
  })

  // =========================
  // Language（複数DOM対応版）
  // =========================
  document.querySelectorAll('.js-lang').forEach((lang) => {
    const langToggle = lang.querySelector('.js-lang-toggle')
    const panel = lang.querySelector('.js-lang-panel')
    const applyBtn = lang.querySelector('.js-lang-apply')
    const select = lang.querySelector('.js-lang-select')

    // =========================
    // 開閉
    // =========================
    if (langToggle && panel) {
      langToggle.addEventListener('click', (e) => {
        e.stopPropagation()
        panel.classList.toggle('is-open')
      })

      document.addEventListener('click', () => {
        panel.classList.remove('is-open')
      })

      panel.addEventListener('click', (e) => {
        e.stopPropagation()
      })
    }

    // =========================
    // 言語切替
    // =========================
    const switchLanguage = (targetLang) => {
      const url = new URL(window.location.href)
      const parts = url.pathname.split('/')

      if (parts[1]) {
        parts[1] = targetLang
      } else {
        parts.splice(1, 0, targetLang)
      }

      url.pathname = parts.join('/')
      window.location.href = url.pathname
    }

    // select変更
    if (select) {
      select.addEventListener('change', (e) => {
        switchLanguage(e.target.value)
      })
    }

    // applyボタン
    if (applyBtn && select) {
      applyBtn.addEventListener('click', () => {
        switchLanguage(select.value)
      })
    }
  })

  document.querySelectorAll('.js-drawer-toggle').forEach((trigger) => {
    const drawer = document.querySelector('.js-drawer')

    trigger.addEventListener('click', (e) => {
      e.preventDefault()
      drawer?.classList.toggle('is-open')
    })
  })
})
