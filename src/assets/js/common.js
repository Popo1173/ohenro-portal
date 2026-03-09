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
// Language
// =========================
document.querySelectorAll('.js-lang').forEach((lang) => {
  const langToggle = lang.querySelector('.js-lang-toggle')
  const panel = lang.querySelector('.js-lang-panel')
  const applyBtn = lang.querySelector('.js-lang-apply')
  const select = lang.querySelector('.js-lang-select')

  // 開閉
  if (langToggle && panel) {
    langToggle.addEventListener('click', (e) => {
      e.stopPropagation()
      panel.classList.toggle('is-open')
      langToggle.classList.toggle('is-open')
    })

    document.addEventListener('click', () => {
      panel.classList.remove('is-open')
      langToggle.classList.remove('is-open')
    })

    panel.addEventListener('click', (e) => {
      e.stopPropagation()
    })
  }

  // 言語切替
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

  if (select) {
    select.addEventListener('change', (e) => {
      switchLanguage(e.target.value)
    })
  }

  if (applyBtn && select) {
    applyBtn.addEventListener('click', () => {
      switchLanguage(select.value)
    })
  }
})

// =========================
// Drawer（ハンバーガー等）+ スクロールロック
// =========================
const drawer = document.querySelector('.js-drawer')
const triggers = document.querySelectorAll('.js-drawer-toggle')

const openDrawer = () => {
  drawer?.classList.add('is-open')
  document.body.classList.add('is-scroll-lock')
}

const closeDrawer = () => {
  drawer?.classList.remove('is-open')
  document.body.classList.remove('is-scroll-lock')
}

const toggleDrawer = () => {
  const isOpen = drawer?.classList.toggle('is-open')
  document.body.classList.toggle('is-scroll-lock', Boolean(isOpen))
}

triggers.forEach((trigger) => {
  trigger.addEventListener('click', (e) => {
    e.preventDefault()
    toggleDrawer()
  })
})

// 背景クリックで閉じる（.js-drawer がオーバーレイ要素想定）
drawer?.addEventListener('click', (e) => {
  // パネル内クリックは閉じない（必要なら .js-drawer-panel に合わせて変更）
  if (e.target === drawer) closeDrawer()
})

// ESCで閉じる
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') closeDrawer()
})

// =========================
// ヘッダースクロール追従
// =========================
const header = document.querySelector('.my-page-header')

if (header) {
  let lastY = window.scrollY

  window.addEventListener(
    'scroll',
    () => {
      const y = window.scrollY

      // 下スクロールでヘッダー非表示
      if (y > lastY && y > 50) {
        header.classList.add('is-hidden')
      } else {
        header.classList.remove('is-hidden')
      }

      // 上スクロールでヘッダー表示してshadowを追加
      if (y > 0) {
        header.classList.add('is-shadow')
      } else {
        header.classList.remove('is-shadow')
      }

      lastY = y
    },
    { passive: true },
  )
}

// =========================
// タブ切り替え
// =========================

const params = new URLSearchParams(window.location.search)
const tab = params.get('tab') || 'tokushima'

// コンテンツ切替
document.querySelectorAll('.tab-panel').forEach((panel) => {
  panel.classList.toggle('is-active', panel.dataset.tab === tab)
})

// アクティブリンク
document.querySelectorAll('.tab__link').forEach((link) => {
  link.classList.toggle('is-active', link.dataset.tab === tab)
})

// =========================
// お気に入り
// =========================

document.querySelectorAll('.js-favorite').forEach((btn) => {
  btn.addEventListener('click', () => {
    btn.classList.toggle('is-active')
  })
})
