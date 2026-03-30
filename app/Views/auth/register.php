<?= $this->extend('auth/layout') ?>
<?= $this->section('content') ?>

<article>
  <header><h2><?= lang('Auth.register') ?></h2></header>

  <?php if (session('errors')): ?>
    <?php foreach ((array) session('errors') as $error): ?>
      <p><mark><?= esc($error) ?></mark></p>
    <?php endforeach ?>
  <?php endif ?>

  <form action="<?= url_to('register') ?>" method="post">
    <?= csrf_field() ?>

    <label for="username">
      ユーザー名
      <input type="text" id="username" name="username"
             autocomplete="username" placeholder="username"
             value="<?= old('username') ?>" required>
    </label>

    <label for="email">
      メールアドレス
      <input type="email" id="email" name="email"
             autocomplete="email" placeholder="you@example.com"
             value="<?= old('email') ?>" required>
    </label>

    <label for="password">
      パスワード
      <input type="password" id="password" name="password"
             autocomplete="new-password" placeholder="password" required>
    </label>

    <label for="password_confirm">
      パスワード（確認）
      <input type="password" id="password_confirm" name="password_confirm"
             autocomplete="new-password" placeholder="password" required>
    </label>

    <button type="submit"><?= lang('Auth.register') ?></button>
  </form>

  <p style="text-align:center;margin-top:1rem">
    <a href="<?= url_to('login') ?>">ログインはこちら</a>
  </p>
</article>

<?= $this->endSection() ?>
