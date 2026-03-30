<?= $this->extend('layouts/admin') ?>
<?php $pageTitle = 'パスワード変更: ' . esc($user->username) ?>
<?= $this->section('content') ?>

<form method="post" action="<?= site_url("admin/users/password/{$user->id}") ?>">
  <?= csrf_field() ?>
  <div class="form-group">
    <label>新しいパスワード <span class="required">*</span></label>
    <input type="password" name="password" required minlength="8">
  </div>
  <div class="form-actions">
    <button type="submit" class="btn btn-primary">変更</button>
    <a href="<?= site_url("admin/users/edit/{$user->id}") ?>" class="btn">キャンセル</a>
  </div>
</form>

<?= $this->endSection() ?>
