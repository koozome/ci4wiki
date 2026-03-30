<?= $this->extend('layouts/admin') ?>
<?php $pageTitle = 'ユーザー編集: ' . esc($user->username) ?>
<?= $this->section('content') ?>

<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <div class="page-pretitle"><a href="<?= site_url('admin/users') ?>">ユーザー管理</a></div>
        <h2 class="page-title">ユーザー編集: <?= esc($user->username) ?></h2>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    <form method="post" action="<?= site_url("admin/users/edit/{$user->id}") ?>">
      <?= csrf_field() ?>
      <div class="row">
        <div class="col-lg-8">
          <div class="mb-3">
            <label class="form-label required" for="username">ユーザー名</label>
            <input type="text" id="username" name="username" class="form-control" required
                   value="<?= esc(old('username', $user->username)) ?>">
          </div>
          <div class="mb-3">
            <label class="form-label required" for="email">メールアドレス</label>
            <input type="email" id="email" name="email" class="form-control" required
                   value="<?= esc(old('email', $user->email)) ?>">
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card">
            <div class="card-body">
              <div class="mb-3">
                <label class="form-label" for="role">ロール</label>
                <?php $currentGroups = $user->getGroups() ?>
                <select id="role" name="role" class="form-select">
                  <?php foreach ($roles as $role): ?>
                    <option value="<?= $role ?>" <?= in_array($role, $currentGroups, true) ? 'selected' : '' ?>>
                      <?= esc($role) ?>
                    </option>
                  <?php endforeach ?>
                </select>
              </div>
              <a href="<?= site_url("admin/users/password/{$user->id}") ?>" class="btn btn-sm w-100">
                パスワード変更
              </a>
            </div>
            <div class="card-footer">
              <div class="row g-2">
                <div class="col">
                  <button type="submit" class="btn btn-primary w-100">更新</button>
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
