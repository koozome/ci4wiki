<?= $this->extend('layouts/admin') ?>
<?php $pageTitle = '下書き一覧' ?>
<?= $this->section('content') ?>

<div class="toolbar"><a href="<?= site_url('admin/articles') ?>" class="btn">全記事一覧へ</a></div>

<table class="admin-table">
  <thead><tr><th>タイトル</th><th>カテゴリー</th><th>投稿者</th><th>更新日</th><th>操作</th></tr></thead>
  <tbody>
    <?php foreach ($articles as $a): ?>
      <tr>
        <td><a href="<?= site_url("admin/articles/edit/{$a['id']}") ?>"><?= esc($a['title']) ?></a></td>
        <td><?= esc($a['category_name']) ?></td>
        <td><?= esc($a['owner']) ?></td>
        <td><?= esc(substr($a['updated_at'] ?? '', 0, 10)) ?></td>
        <td>
          <a href="<?= site_url("admin/articles/edit/{$a['id']}") ?>">編集</a>
          <?php if (auth()->user()->can('articles.publish')): ?>
            <form method="post" action="<?= site_url("admin/articles/publish/{$a['id']}") ?>" style="display:inline">
              <?= csrf_field() ?><button type="submit" class="btn-link">公開</button>
            </form>
          <?php endif ?>
        </td>
      </tr>
    <?php endforeach ?>
  </tbody>
</table>

<?= $this->endSection() ?>
