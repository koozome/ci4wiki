<?= $this->extend('layouts/admin') ?>
<?php $pageTitle = 'ダッシュボード' ?>
<?= $this->section('content') ?>

<div class="page-header mb-3">
  <div class="row align-items-center">
    <div class="col"><h2 class="page-title">ダッシュボード</h2></div>
  </div>
</div>

<div class="row row-deck row-cards mb-4">
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="subheader">記事総数</div>
        <div class="h1 mb-3"><?= $totalArticles ?></div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="subheader">公開中</div>
        <div class="h1 mb-3 text-green"><?= $publishedArticles ?></div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="subheader">下書き</div>
        <div class="h1 mb-3 text-yellow"><?= $draftArticles ?></div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-lg-3">
    <div class="card">
      <div class="card-body">
        <div class="subheader">カテゴリー</div>
        <div class="h1 mb-3"><?= $totalCategories ?></div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header"><h3 class="card-title">最近の記事</h3></div>
  <div class="table-responsive">
    <table class="table table-vcenter card-table">
      <thead>
        <tr><th>タイトル</th><th>カテゴリー</th><th>状態</th><th>更新日</th></tr>
      </thead>
      <tbody>
        <?php foreach ($recentArticles as $a): ?>
          <tr>
            <td><a href="<?= site_url("admin/articles/edit/{$a['id']}") ?>"><?= esc($a['title']) ?></a></td>
            <td class="text-secondary"><?= esc($a['category_name'] ?? '') ?></td>
            <td>
              <?php if ($a['status'] === 'published'): ?>
                <span class="badge bg-green-lt">公開</span>
              <?php else: ?>
                <span class="badge bg-yellow-lt">下書き</span>
              <?php endif ?>
            </td>
            <td class="text-secondary"><?= esc(substr($a['updated_at'] ?? '', 0, 10)) ?></td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
