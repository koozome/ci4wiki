<?= $this->extend('auth/layout') ?>
<?= $this->section('content') ?>

<article>
  <header><h2>メール認証</h2></header>

  <?php if (session('error')): ?>
    <p><mark><?= esc(session('error')) ?></mark></p>
  <?php endif ?>

  <p><?= esc($user->email) ?> に認証コードを送信します。</p>

  <form action="<?= url_to('auth-action-handle') ?>" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="email" value="<?= esc($user->email) ?>">
    <button type="submit">コードを送信</button>
  </form>
</article>

<?= $this->endSection() ?>
