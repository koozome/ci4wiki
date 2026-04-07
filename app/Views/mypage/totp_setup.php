<?= $this->extend('layouts/admin') ?>
<?php $pageTitle = 'TOTP 設定' ?>
<?= $this->section('content') ?>

<div class="page-header d-print-none">
  <div class="container-xl">
    <div class="row g-2 align-items-center">
      <div class="col">
        <div class="page-pretitle"><a href="<?= site_url('mypage') ?>">マイページ</a></div>
        <h2 class="page-title">認証アプリ（TOTP）のセットアップ</h2>
      </div>
    </div>
  </div>
</div>

<div class="page-body">
  <div class="container-xl">
    <div class="row">
      <div class="col-lg-6">
        <div class="card">
          <div class="card-body">

            <?php if (!empty($error)): ?>
              <div class="alert alert-danger mb-3"><?= esc($error) ?></div>
            <?php endif ?>

            <ol class="mb-4">
              <li class="mb-2">Google Authenticator・Authy などのアプリで下記の QR コードをスキャン、または秘密鍵を手動入力してください。</li>
              <li class="mb-2">アプリに表示された6桁のコードを入力して確認ボタンを押してください。</li>
            </ol>

            <div class="mb-3 text-center">
              <img src="<?= esc($qrDataUri) ?>" alt="QR Code" width="200" height="200">
            </div>

            <div class="mb-3">
              <label class="form-label text-secondary small">秘密鍵（手動入力用）</label>
              <input type="text" class="form-control font-monospace" value="<?= esc($secret) ?>" readonly>
            </div>

            <form method="post" action="<?= site_url('mypage/2fa/setup-totp') ?>">
              <?= csrf_field() ?>
              <input type="hidden" name="secret" value="<?= esc($secret) ?>">
              <div class="mb-3">
                <label class="form-label required" for="code">確認コード（6桁）</label>
                <input type="text" id="code" name="code" class="form-control"
                       inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                       placeholder="000000" autocomplete="one-time-code" required autofocus>
              </div>
              <div class="row g-2">
                <div class="col">
                  <button type="submit" class="btn btn-primary w-100">有効にする</button>
                </div>
                <div class="col">
                  <a href="<?= site_url('mypage') ?>" class="btn w-100">キャンセル</a>
                </div>
              </div>
            </form>

          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>

