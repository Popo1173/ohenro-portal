## モジュールのインストール
```
npm install @11ty/eleventy
npm install -D vite sass
npm install -D stylelint-order stylelint-config-recess-order // CSSプロパティの並び順を整形
npm install -D npm-run-all
npm install -D vite-plugin-nunjucks
npm install -D prettier
npm install -D eslint eslint-config-prettier eslint-plugin-prettier
npm install -D stylelint stylelint-config-standard-scss
```

/*
========================================
SCSS 自動整形ルールについて
========================================

このプロジェクトでは：

■ HTML / JS / Nunjucks
  → Prettier で整形

■ SCSS / CSS
  → stylelint で整形（プロパティ順も自動統一）

----------------------------------------

▼ なぜ分けているか？

Prettier はフォーマット専用で、
CSSプロパティの並び順は整えないため。

stylelint + stylelint-config-recess-order により：

・プロパティ順を自動統一
・不要ルールの検出
・BEM命名を許可
・SCSS構文チェック

を行っています。

----------------------------------------

▼ 保存時の動作

SCSS保存時：

1. stylelint が実行
2. プロパティ順を自動整形
3. lintエラーを修正
4. 保存完了

----------------------------------------

▼ VSCode設定

SCSSのみ stylelint をフォーマッターとして使用：

"[scss]": {
  "editor.defaultFormatter": "stylelint.vscode-stylelint"
}

それ以外は Prettier を使用。

----------------------------------------

▼ 注意

dist/ は lint 対象外。
必ず src/**/*.scss を編集してください。

========================================
*/
