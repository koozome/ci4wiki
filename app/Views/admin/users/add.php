<?= $this->extend('layouts/admin') ?>
<?php $pageTitle = 'ユーザー追加' ?>
<?= $this->section('content') ?>

<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <div class="page-pretitle"><a href="<?= site_url('admin/users') ?>">ユーザー管理</a></div>
        <h2 class="page-title">ユーザー追加</h2>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    <form method="post" action="<?= site_url('admin/users/add') ?>">
      <?= csrf_field() ?>
      <div class="row">
        <div class="col-lg-8">
          <div class="mb-3">
            <label class="form-label required" for="username">ユーザー名</label>
            <input type="text" id="username" name="username" class="form-control" required
                   value="<?= esc(old('username')) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label required" for="email">メールアドレス</label>
            <input type="email" id="email" name="email" class="form-control" required
                   value="<?= esc(old('email')) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label required" for="password">パスワード</label>
            <input type="password" id="password" name="password" class="form-control" required>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card">
            <div class="card-body">
              <div class="mb-3">
                <label class="form-label required" for="role">ロール</label>
                <select id="role" name="role" class="form-select" required>
                  <?php foreach ($canAssignRoles as $role): ?>
                    <option value="<?= $role ?>"><?= esc($role) ?></option>
                  <?php endforeach ?>
                </select>
              </div>
            </div>
            <div class="card-footer">
              <div class="row g-2">
                <div class="col">
                  <button type="submit" class="btn btn-primary w-100">作成</button>
                </div>
                <div class="col">
                  <a href="<?= site_url('admin/users') ?>" class="btn w-100">キャンセル</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>
