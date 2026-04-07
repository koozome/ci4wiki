<?= $this->extend('layouts/admin') ?>
<?php $pageTitle = 'マイページ' ?>
<?= $this->section('content') ?>

<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <h2 class="page-title">マイページ</h2>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    <div class="row">
      <div class="col-lg-6">

        <div class="card">
          <div class="card-header">
            <h3 class="card-title">2段階認証（2FA）</h3>
          </div>
          <div class="card-body">
            <?php $twofaType = $user->twofa_type ?? null; ?>

            <?php if ($twofaType === null): ?>
              <p class="text-secondary mb-3">現在 2FA は無効です。</p>
              <div class="d-flex gap-2">
                <form method="post" action="<?= site_url('mypage/2fa/enable-email') ?>">
                  <?= csrf_field() ?>
                  <button class="btn btn-outline-primary">メール認証を有効にする</button>
                </form>
                <a href="<?= site_url('mypage/2fa/setup-totp') ?>" class="btn btn-outline-primary">
                  認証アプリ（TOTP）を有効にする
                </a>
              </div>

            <?php elseif ($twofaType === 'email'): ?>
              <p class="mb-3">
                <span class="badge bg-green-lt me-1">有効</span>
                メール認証が有効です。ログイン時にメールでコードを送信します。
              </p>
              <div class="d-flex gap-2">
                <a href="<?= site_url('mypage/2fa/setup-totp') ?>" class="btn btn-sm btn-outline-secondary">
                  認証アプリに切り替える
                </a>
                <form method="post" action="<?= site_url('mypage/2fa/disable') ?>"
                      onsubmit="return confirm('2FAを無効にしますか？')">
                  <?= csrf_field() ?>
                  <button class="btn btn-sm btn-outline-danger">無効にする</button>
                </form>
              </div>

            <?php elseif ($twofaType === 'totp'): ?>
              <p class="mb-3">
                <span class="badge bg-green-lt me-1">有効</span>
                認証アプリ（TOTP）が有効です。
              </p>
              <div class="d-flex gap-2">
                <form method="post" action="<?= site_url('mypage/2fa/enable-email') ?>">
                  <?= csrf_field() ?>
                  <button class="btn btn-sm btn-outline-secondary">メール認証に切り替える</button>
                </form>
                <a href="<?= site_url('mypage/2fa/setup-totp') ?>" class="btn btn-sm btn-outline-secondary">
                  認証アプリを再設定する
                </a>
                <form method="post" action="<?= site_url('mypage/2fa/disable') ?>"
                      onsubmit="return confirm('2FAを無効にしますか？')">
                  <?= csrf_field() ?>
                  <button class="btn btn-sm btn-outline-danger">無効にする</button>
                </form>
              </div>
            <?php endif ?>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
