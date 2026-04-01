<!DOCTYPE html>
<?php $adminTheme = $siteSettings['admin_theme'] ?? 'auto'; ?>
<html lang="ja">
<head>
<script>
(function(){
  var t='<?= $adminTheme ?>';
  if(t==='auto') t=window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light';
  document.documentElement.setAttribute('data-bs-theme',t);
})();
</script>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($pageTitle ?? '管理画面') ?> - <?= esc($siteSettings['site_name'] ?? 'Wiki') ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/css/tabler.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.10.0/styles/github.min.css">
  <link rel="stylesheet" href="<?= base_url('css/admin.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/wiki.css') ?>">
</head>
<body class="antialiased">
<div class="wrapper">

  <?= view('admin/_partials/sidebar') ?>

  <div class="page-wrapper">

    <?= view('admin/_partials/header') ?>

    <div class="page-body">
      <div class="container-fluid">

        <?php if (session()->getFlashdata('message')): ?>
          <div class="alert alert-success alert-dismissible mb-3" role="alert">
            <div><?= esc(session()->getFlashdata('message')) ?></div>
            <a class="btn-close" data-bs-dismiss="alert"></a>
          </div>
        <?php endif ?>

        <?php if (session()->getFlashdata('errors')): ?>
          <div class="alert alert-danger alert-dismissible mb-3" role="alert">
            <?php foreach ((array) session()->getFlashdata('errors') as $e): ?>
              <div><?= esc($e) ?></div>
            <?php endforeach ?>
            <a class="btn-close" data-bs-dismiss="alert"></a>
          </div>
        <?php endif ?>

        <?= $this->renderSection('content') ?>

      </div>
    </div>

    <footer class="footer footer-transparent d-print-none">
      <div class="container-fluid">
        <div class="row text-center align-items-center">
          <div class="col-12 col-lg-auto">
            <p class="text-secondary mb-0"><?= esc($siteSettings['copyright'] ?? '') ?></p>
          </div>
        </div>
      </div>
    </footer>

  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.10.0/highlight.min.js"></script>
<script>hljs.highlightAll();</script>
<script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.4.0/dist/js/tabler.min.js"></script>
<script src="<?= base_url('js/admin.js') ?>"></script>
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
<?= $this->renderSection('scripts') ?>
</body>
</html>
