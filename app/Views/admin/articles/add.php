<?= $this->extend('layouts/admin') ?>
<?php $pageTitle = '記事新規作成' ?>
<?= $this->section('content') ?>

<div class="page-header mb-3">
  <div class="row align-items-center">
    <div class="col"><h2 class="page-title">記事新規作成</h2></div>
    <div class="col-auto">
      <a href="<?= site_url('admin/articles') ?>" class="btn btn-outline-secondary">← 一覧へ</a>
    </div>
  </div>
</div>

<form method="post" action="<?= site_url('admin/articles/add') ?>">
  <?= csrf_field() ?>
  <?= view('admin/articles/_form', ['article' => $article ?? null, 'categories' => $categories, 'categoryId' => $categoryId ?? null]) ?>
</form>

<div class="card mt-3">
  <div class="card-body text-secondary">
    添付ファイルは保存後の編集画面からアップロードできます。
  </div>
</div>

<?= $this->endSection() ?>
