<?= $this->extend('layouts/admin') ?>
<?php $pageTitle = 'サイト設定' ?>
<?= $this->section('content') ?>

<div class="page-header mb-3">
  <div class="row align-items-center">
    <div class="col"><h2 class="page-title">サイト設定</h2></div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <form method="post" action="<?= site_url('admin/settings') ?>">
      <?= csrf_field() ?>
      <div class="mb-3">
        <label class="form-label">サイト名</label>
        <input type="text" name="site_name" class="form-control" value="<?= esc($settings['site_name'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">サイト説明</label>
        <textarea name="site_description" class="form-control" rows="3"><?= esc($settings['site_description'] ?? '') ?></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">著作権表記</label>
        <input type="text" name="copyright" class="form-control" value="<?= esc($settings['copyright'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">1ページの記事件数</label>
        <input type="number" name="articles_per_page" class="form-control"
               value="<?= (int) ($settings['articles_per_page'] ?? 20) ?>" min="5" max="100" style="max-width:120px">
      </div>
      <div class="mb-3">
        <label class="form-label">公開面テーマ</label>
        <?php
          $themes = [
            'sakura'      => 'Sakura（デフォルト・自動切替）',
            'sakura-dark' => 'Sakura Dark（ダーク固定）',
            'academic'    => 'Academic（セリフ体）',
            'github'      => 'GitHub',
            'onigiri'     => 'Onigiri（日本語向け）',
            'solarized'   => 'Solarized',
            'vue'         => 'Vue.js Docs',
            'monospace'   => 'Monospace',
            'night'       => 'Night（ダーク固定）',
          ];
          $currentTheme = $settings['theme'] ?? 'sakura';
        ?>
        <select name="theme" class="form-select" style="max-width:320px">
          <?php foreach ($themes as $val => $label): ?>
          <option value="<?= $val ?>"<?= $currentTheme === $val ? ' selected' : '' ?>><?= $label ?></option>
          <?php endforeach ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">管理画面テーマ</label>
        <?php $adminTheme = $settings['admin_theme'] ?? 'auto'; ?>
        <div class="d-flex gap-3">
          <?php foreach (['auto' => 'Auto（システム連動）', 'light' => 'ライト', 'dark' => 'ダーク'] as $val => $label): ?>
          <label class="form-check">
            <input class="form-check-input" type="radio" name="admin_theme" value="<?= $val ?>"<?= $adminTheme === $val ? ' checked' : '' ?>>
            <span class="form-check-label"><?= $label ?></span>
          </label>
          <?php endforeach ?>
        </div>
      </div>
      <button type="submit" class="btn btn-primary">保存</button>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
