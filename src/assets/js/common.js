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
  // Languageトグル
  // =========================
  const lang = document.querySelector('.js-lang')
  const langToggle = document.querySelector('.js-lang-toggle')
  const panel = document.querySelector('.js-lang-panel')
  const applyBtn = document.querySelector('.js-lang-apply')
  const select = document.querySelector('.js-lang-select')

  // 開閉
  if (lang && langToggle && panel) {
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
  // 言語切替（URL維持版）
  // =========================

  const switchLanguage = (targetLang) => {
    const url = new URL(window.location.href)
    const parts = url.pathname.split('/')

    // /ja/... or /en/... の言語部分を置換
    if (parts[1]) {
      parts[1] = targetLang
    } else {
      parts.splice(1, 0, targetLang)
    }

    url.pathname = parts.join('/')
    window.location.href = url.pathname
  }

  // select変更で即切替
  if (select) {
    select.addEventListener('change', (e) => {
      switchLanguage(e.target.value)
    })
  }

  // 適用ボタン対応（あっても壊れない）
  if (applyBtn && select) {
    applyBtn.addEventListener('click', () => {
      switchLanguage(select.value)
    })
  }
})
