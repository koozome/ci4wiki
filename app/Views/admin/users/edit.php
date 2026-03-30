<?= $this->extend('layouts/admin') ?>
<?php $pageTitle = 'ユーザー編集: ' . esc($user->username) ?>
<?= $this->section('content') ?>

<form method="post" action="<?= site_url("admin/users/edit/{$user->id}") ?>">
  <?= csrf_field() ?>
  <div class="form-group">
    <label>ユーザー名 <span class="required">*</span></label>
    <input type="text" name="username" required value="<?= esc(old('username', $user->username)) ?>">
  </div>
  <div class="form-group">
    <label>メールアドレス <span class="required">*</span></label>
    <input type="email" name="email" required value="<?= esc(old('email', $user->email)) ?>">
  </div>
  <div class="form-group">
    <label>ロール</label>
    <select name="role">
      <?php $currentGroups = $user->getGroups() ?>
      <?php foreach ($roles as $role): ?>
        <option value="<?= $role ?>" <?= in_array($role, $currentGroups, true) ? 'selected' : '' ?>>
          <?= esc($role) ?>
        </option>
      <?php endforeach ?>
    </select>
  </div>
  <div class="form-actions">
    <button type="submit" class="btn btn-primary">更新</button>
    <a href="<?= site_url('admin/users') ?>" class="btn">キャンセル</a>
  </div>
</form>

<?= $this->endSection() ?>
