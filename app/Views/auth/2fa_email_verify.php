<?= $this->extend('auth/layout') ?>
<?= $this->section('content') ?>

<article>
  <header><h2>認証コードを入力</h2></header>

  <?php if (session('error')): ?>
    <p><mark><?= esc(session('error')) ?></mark></p>
  <?php endif ?>

  <p>メールに届いた6桁のコードを入力してください。</p>

  <form action="<?= url_to('auth-action-verify') ?>" method="post">
    <?= csrf_field() ?>
    <label for="token">
      認証コード
      <input type="text" id="token" name="token" inputmode="numeric"
             pattern="[0-9]{6}" maxlength="6" placeholder="000000"
             autocomplete="one-time-code" required autofocus>
    </label>
    <button type="submit">確認</button>
  </form>
</article>

<?= $this->endSection() ?>
