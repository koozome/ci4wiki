<?= $this->extend('layouts/admin') ?>
<?php $pageTitle = 'ユーザー追加' ?>
<?= $this->section('content') ?>

<form method="post" action="<?= site_url('admin/users/add') ?>">
  <?= csrf_field() ?>
  <div class="form-group">
    <label>ユーザー名 <span class="required">*</span></label>
    <input type="text" name="username" required value="<?= esc(old('username')) ?>">
  </div>
  <div class="form-group">
    <label>メールアドレス <span class="required">*</span></label>
    <input type="email" name="email" required value="<?= esc(old('email')) ?>">
  </div>
  <div class="form-group">
    <label>パスワード <span class="required">*</span></label>
    <input type="password" name="password" required>
  </div>
  <div class="form-group">
    <label>ロール <span class="required">*</span></label>
    <select name="role" required>
      <?php foreach ($canAssignRoles as $role): ?>
        <option value="<?= $role ?>"><?= esc($role) ?></option>
      <?php endforeach ?>
    </select>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn btn-primary">作成</button>
    <a href="<?= site_url('admin/users') ?>" class="btn">キャンセル</a>
  </div>
</form>

<?= $this->endSection() ?>
