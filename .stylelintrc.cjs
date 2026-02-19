module.exports = {
  extends: [
    // SCSS向け標準ルールセット
    'stylelint-config-standard-scss',
    // プロパティ順を自動統一（recess順）
    'stylelint-config-recess-order',
  ],
  plugins: ['stylelint-order'], // プロパティ順制御プラグイン
  rules: {
    'selector-class-pattern': null, // BEM命名を許可（header__inner など）
    'rule-empty-line-before': null, // ルール前の空行チェックを無効化
  },
}
