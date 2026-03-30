<?= $this->extend('auth/layout') ?>
<?= $this->section('content') ?>

<article>
  <header><h2><?= lang('Auth.login') ?></h2></header>

  <?php if (session('error')): ?>
    <p><mark><?= esc(session('error')) ?></mark></p>
  <?php elseif (session('errors')): ?>
    <?php foreach ((array) session('errors') as $error): ?>
      <p><mark><?= esc($error) ?></mark></p>
    <?php endforeach ?>
  <?php endif ?>

  <?php if (session('message')): ?>
    <p><ins><?= esc(session('message')) ?></ins></p>
  <?php endif ?>

  <form action="<?= url_to('login') ?>" method="post">
    <?= csrf_field() ?>

    <label for="email">
      メールアドレス
      <input type="email" id="email" name="email"
             autocomplete="email" placeholder="you@example.com"
             value="<?= old('email') ?>" required>
    </label>

    <label for="password">
      パスワード
      <input type="password" id="password" name="password"
             autocomplete="current-password" placeholder="password" required>
    </label>

    <?php if (setting('Auth.sessionConfig')['allowRemembering']): ?>
      <label>
        <input type="checkbox" name="remember" role="switch"
               <?= old('remember') ? 'checked' : '' ?>>
        <?= lang('Auth.rememberMe') ?>
      </label>
    <?php endif ?>

    <button type="submit"><?= lang('Auth.login') ?></button>
  </form>

  <?php if (setting('Auth.allowMagicLinkLogins')): ?>
    <p style="text-align:center;margin-top:1rem">
      <a href="<?= url_to('magic-link') ?>"><?= lang('Auth.useMagicLink') ?></a>
    </p>
  <?php endif ?>
</article>

<?= $this->endSection() ?>
