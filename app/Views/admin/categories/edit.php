<?= $this->extend('layouts/admin') ?>
<?php $pageTitle = 'カテゴリー編集: ' . esc($category['name']) ?>
<?= $this->section('content') ?>

<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <div class="page-pretitle">
          <a href="<?= site_url('admin/categories') ?>">カテゴリー一覧</a>
        </div>
        <h2 class="page-title"><?= esc($category['name']) ?> — 編集</h2>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    <form method="post" action="<?= site_url("admin/categories/edit/{$category['id']}") ?>">
      <?= csrf_field() ?>
      <?= view('admin/categories/_form', compact('category', 'parents')) ?>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
