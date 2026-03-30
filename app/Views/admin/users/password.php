<?= $this->extend('layouts/admin') ?>
<?php $pageTitle = 'パスワード変更: ' . esc($user->username) ?>
<?= $this->section('content') ?>

<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <div class="page-pretitle"><a href="<?= site_url('admin/users') ?>">ユーザー管理</a></div>
        <h2 class="page-title">パスワード変更: <?= esc($user->username) ?></h2>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    <form method="post" action="<?= site_url("admin/users/password/{$user->id}") ?>">
      <?= csrf_field() ?>
      <div class="row">
        <div class="col-lg-8">
          <div class="mb-3">
            <label class="form-label required" for="password">新しいパスワード</label>
            <input type="password" id="password" name="password" class="form-control" required minlength="8">
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card">
            <div class="card-footer">
              <div class="row g-2">
                <div class="col">
                  <button type="submit" class="btn btn-primary w-100">変更</button>
                </div>
                <div class="col">
                  <a href="<?= site_url("admin/users/edit/{$user->id}") ?>" class="btn w-100">キャンセル</a>
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
