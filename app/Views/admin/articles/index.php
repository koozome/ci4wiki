<?= $this->extend('layouts/admin') ?>
<?php $pageTitle = '記事一覧' ?>
<?= $this->section('content') ?>

<div class="page-header mb-3">
  <div class="row align-items-center">
    <div class="col"><h2 class="page-title">記事一覧</h2></div>
    <div class="col-auto ms-auto">
      <a href="<?= site_url('admin/articles/drafts') ?>" class="btn btn-outline-secondary me-2">下書き</a>
      <?php if (auth()->user()->can('articles.create')): ?>
        <a href="<?= site_url('admin/articles/add') ?>" class="btn btn-primary">
          <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
          新規作成
        </a>
      <?php endif ?>
    </div>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-vcenter card-table">
      <thead>
        <tr><th>タイトル</th><th>カテゴリー</th><th>状態</th><th>投稿者</th><th>更新日</th><th></th></tr>
      </thead>
      <tbody>
        <?php foreach ($articles as $a): ?>
          <tr>
            <td><a href="<?= site_url("admin/articles/edit/{$a['id']}") ?>"><?= esc($a['title']) ?></a></td>
            <td class="text-secondary"><?= esc($a['category_name']) ?></td>
            <td>
              <?= $a['status'] === 'published'
                ? '<span class="badge bg-green-lt">公開</span>'
                : '<span class="badge bg-yellow-lt">下書き</span>' ?>
            </td>
            <td class="text-secondary"><?= esc($a['owner']) ?></td>
            <td class="text-secondary"><?= esc(substr($a['updated_at'] ?? '', 0, 10)) ?></td>
            <td class="text-end">
              <div class="btn-group">
                <a href="<?= site_url("admin/articles/edit/{$a['id']}") ?>" class="btn btn-sm">編集</a>
                <?php if ($a['status'] === 'draft' && auth()->user()->can('articles.publish')): ?>
                  <form method="post" action="<?= site_url("admin/articles/publish/{$a['id']}") ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-success">公開</button>
                  </form>
                <?php elseif ($a['status'] === 'published' && auth()->user()->can('articles.publish')): ?>
                  <form method="post" action="<?= site_url("admin/articles/unpublish/{$a['id']}") ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-warning">非公開</button>
                  </form>
                <?php endif ?>
                <?php if (auth()->user()->can('articles.delete')): ?>
                  <form method="post" action="<?= site_url("admin/articles/delete/{$a['id']}") ?>"
                        onsubmit="return confirm('削除しますか？')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger">削除</button>
                  </form>
                <?php endif ?>
              </div>
            </td>
          </tr>
        <?php endforeach ?>
      </tbody>
    </table>
  </div>
</div>

<?= $this->endSection() ?>
