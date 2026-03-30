<?= $this->extend('layouts/admin') ?>
<?php $pageTitle = 'カテゴリー新規作成' ?>
<?= $this->section('content') ?>

<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <div class="page-pretitle">
          <a href="<?= site_url('admin/categories') ?>">カテゴリー一覧</a>
        </div>
        <h2 class="page-title">カテゴリー新規作成</h2>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    <form method="post" action="<?= site_url('admin/categories/add') ?>">
      <?= csrf_field() ?>
      <?= view('admin/categories/_form', ['category' => null, 'parents' => $parents]) ?>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
