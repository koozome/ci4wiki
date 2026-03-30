<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($pageTitle ?? '') ?><?= isset($pageTitle) ? ' — ' : '' ?><?= esc($siteSettings['site_name'] ?? 'Wiki') ?></title>
  <?php
    $theme = $siteSettings['theme'] ?? 'sakura';
    // テーマ定義: [ダークバリアント有無, ダーク固定]
    $themes = [
      'sakura'      => ['dark' => true,  'fixed' => false],
      'sakura-dark' => ['dark' => false, 'fixed' => true],  // Sakura dark固定
      'academic'    => ['dark' => false, 'fixed' => false],
      'github'      => ['dark' => false, 'fixed' => false],
      'onigiri'     => ['dark' => true,  'fixed' => false],
      'solarized'   => ['dark' => true,  'fixed' => false],
      'vue'         => ['dark' => true,  'fixed' => false],
      'monospace'   => ['dark' => true,  'fixed' => false],
      'night'       => ['dark' => false, 'fixed' => true],  // dark固定
    ];
    $t = $themes[$theme] ?? $themes['sakura'];
    $lightMedia = '(prefers-color-scheme: light), (prefers-color-scheme: no-preference)';
    $darkMedia  = '(prefers-color-scheme: dark)';
  ?>
  <?php if ($t['fixed']): ?>
    <!-- ダーク固定テーマ: Sakura dark をベースに使用 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sakura.css@1.4.1/css/sakura-dark.css">
  <?php else: ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sakura.css@1.4.1/css/sakura.css" media="<?= $lightMedia ?>">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sakura.css@1.4.1/css/sakura-dark.css" media="<?= $darkMedia ?>">
  <?php endif ?>
  <?php if (!in_array($theme, ['sakura', 'sakura-dark'], true)): ?>
    <?php if ($t['fixed']): ?>
      <link rel="stylesheet" href="<?= base_url("css/themes/{$theme}.css") ?>">
    <?php else: ?>
      <link rel="stylesheet" href="<?= base_url("css/themes/{$theme}.css") ?>" media="<?= $lightMedia ?>">
      <?php if ($t['dark']): ?>
      <link rel="stylesheet" href="<?= base_url("css/themes/{$theme}-dark.css") ?>" media="<?= $darkMedia ?>">
      <?php endif ?>
    <?php endif ?>
  <?php endif ?>
  <?php
    // highlight.js テーマ: night/solarized-dark/vue-dark はダーク系テーマを優先
    $hljsDark = ($t['fixed'] || ($theme !== 'sakura' && $theme !== 'github')) ? 'github-dark' : 'github';
  ?>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.10.0/styles/github.min.css" media="<?= $lightMedia ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.10.0/styles/github-dark.min.css" media="<?= $darkMedia ?>">
  <link rel="stylesheet" href="<?= base_url('css/wiki.css') ?>">
</head>
<body>

<header class="container-fluid site-header">
  <div class="site-header-top">
    <strong><a href="<?= site_url('/') ?>"><?= esc($siteSettings['site_name'] ?? 'Wiki') ?></a></strong>
    <ul class="site-header-actions">
      <?php if (auth()->loggedIn()): ?>
        <li>
          <a href="<?= site_url('admin') ?>" aria-label="管理画面" title="管理画面">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20a8 8 0 1 0 0-16 8 8 0 0 0 0 16z"/><path d="M12 14a2 2 0 1 0 0-4 2 2 0 0 0 0 4z"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/></svg>
          </a>
        </li>
        <li><a href="<?= site_url('logout') ?>">ログアウト</a></li>
      <?php else: ?>
        <li><a href="<?= site_url('login') ?>">ログイン</a></li>
      <?php endif ?>
    </ul>
  </div>
  <form action="<?= site_url('search') ?>" method="get" role="search" class="site-search">
    <input type="search" name="q" placeholder="検索..." value="<?= esc($keyword ?? '') ?>">
  </form>
</header>

<main class="container">
  <?= $this->renderSection('content') ?>
</main>

<footer class="container-fluid">
  <small><?= esc($siteSettings['copyright'] ?? '') ?></small>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.10.0/highlight.min.js"></script>
<script>hljs.highlightAll();</script>
<script type="module">
import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@11/dist/mermaid.esm.min.mjs';
document.querySelectorAll('pre code.language-mermaid').forEach(el => {
  const div = document.createElement('div');
  div.className = 'mermaid';
  div.textContent = el.textContent;
  el.closest('pre').replaceWith(div);
});
mermaid.initialize({ startOnLoad: true });
</script>
<script src="<?= base_url('js/wiki.js') ?>"></script>
</body>
</html>
