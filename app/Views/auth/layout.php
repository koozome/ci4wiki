<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($title ?? 'ログイン') ?> — <?= esc(service('settings')->get('App.siteName') ?? 'Wiki') ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sakura.css@1.4.1/css/sakura.css" media="(prefers-color-scheme: light), (prefers-color-scheme: no-preference)">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sakura.css@1.4.1/css/sakura-dark.css" media="(prefers-color-scheme: dark)">
  <style>
    body { display: flex; flex-direction: column; min-height: 100vh; }
    main { flex: 1; display: flex; align-items: center; justify-content: center; }
    .auth-card { width: 100%; max-width: 420px; }
  </style>
</head>
<body>

<main>
  <div class="auth-card">
    <hgroup style="text-align:center; margin-bottom: 2rem;">
      <h1><a href="<?= site_url('/') ?>" style="text-decoration:none">
        <?= esc(model(\App\Models\SettingModel::class)->getValue('site_name', 'Wiki')) ?>
      </a></h1>
    </hgroup>

    <?= $this->renderSection('content') ?>
  </div>
</main>

</body>
</html>
