<?= $this->extend('layouts/admin') ?>
<?php $pageTitle = 'ユーザー管理' ?>
<?= $this->section('content') ?>

<div class="page-header mb-3">
  <div class="row align-items-center">
    <div class="col"><h2 class="page-title">ユーザー管理</h2></div>
    <div class="col-auto">
      <a href="<?= site_url('admin/users/add') ?>" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
        ユーザー追加
      </a>
    </div>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-vcenter card-table">
      <thead><tr><th>ユーザー名</th><th>メール</th><th>ロール</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= esc($u->username) ?></td>
            <td class="text-secondary"><?= esc($u->email) ?></td>
            <td><span class="badge bg-blue-lt"><?= esc(implode(', ', $u->getGroups())) ?></span></td>
            <td class="text-end">
              <div class="btn-group">
                <a href="<?= site_url("admin/users/edit/{$u->id}") ?>" class="btn btn-sm">編集</a>
                <a href="<?= site_url("admin/users/password/{$u->id}") ?>" class="btn btn-sm">PW変更</a>
                <?php if ($u->id !== auth()->user()->id): ?>
                  <form method="post" action="<?= site_url("admin/users/delete/{$u->id}") ?>"
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
