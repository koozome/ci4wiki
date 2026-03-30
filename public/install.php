<?php

/**
 * Wiki Web Installer
 * 配置場所: ci4wiki/public/install.php
 * 完了後  : このファイルを削除してください
 */
declare(strict_types=1);

define('LOCK_FILE', __DIR__ . '/../writable/installed.lock');
define('CI4_ROOT',  __DIR__ . '/../');

// ── インストール済みチェック ────────────────────────────────────────────────
if (file_exists(LOCK_FILE)) {
    http_response_code(403);
    exit('<!DOCTYPE html><html lang="ja"><body><h1>403</h1><p>インストール済みです。install.php を削除してください。</p></body></html>');
}

// ── AJAX: DB 接続テスト ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'test_db') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $_POST['db_host'] ?? 'localhost',
            (int)($_POST['db_port'] ?? 3306),
            $_POST['db_name'] ?? ''
        );
        new PDO($dsn, $_POST['db_user'] ?? '', $_POST['db_pass'] ?? '', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5,
        ]);
        echo json_encode(['ok' => true, 'msg' => '接続成功']);
    } catch (PDOException $e) {
        echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
    }
    exit;
}

// ── ヘルパー ─────────────────────────────────────────────────────────────────
function e(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function v(string $key, string $default = ''): string { return e((string)($_POST[$key] ?? $default)); }

// ── 要件チェック ─────────────────────────────────────────────────────────────
function checkRequirements(): array
{
    $items = [];
    $items[] = ['PHP >= 8.2',            PHP_VERSION_ID >= 80200,                         PHP_VERSION];
    foreach (['intl', 'mbstring', 'mysqli', 'pdo_mysql', 'zip'] as $ext) {
        $ok = extension_loaded($ext);
        $items[] = ["extension: $ext", $ok, $ok ? '有効' : '無効'];
    }
    foreach ([
        'writable/'       => CI4_ROOT . 'writable/',
        'public/uploads/' => __DIR__ . '/uploads/',
        'app/ (.env生成)' => CI4_ROOT,
    ] as $label => $path) {
        $ok = is_writable($path);
        $items[] = ["書き込み権限: $label", $ok, $ok ? '書き込み可' : '書き込み不可'];
    }
    $ok = file_exists(CI4_ROOT . 'vendor/autoload.php');
    $items[] = ['vendor/ (composer install)', $ok, $ok ? '存在' : '見つかりません'];
    return $items;
}

// ── PHP 8.2+ CLI バイナリ検出 ────────────────────────────────────────────────
function findPhpBinary(): string
{
    $home = rtrim((string)(getenv('HOME') ?: ($_SERVER['HOME'] ?? '')), '/');
    $candidates = [
        $home . '/bin/php82',
        $home . '/bin/php83',
        $home . '/bin/php84',
        'php82', 'php83', 'php84',
        'php8.2', 'php8.3', 'php8.4',
        PHP_BINARY,
        '/usr/local/php84/bin/php',
        '/usr/local/php83/bin/php',
        '/usr/local/php82/bin/php',
        '/opt/php84/bin/php',
        '/opt/php83/bin/php',
        '/opt/php82/bin/php',
        '/usr/bin/php8.4',
        '/usr/bin/php8.3',
        '/usr/bin/php8.2',
        '/usr/local/bin/ea-php84',
        '/usr/local/bin/ea-php83',
        '/usr/local/bin/ea-php82',
    ];
    foreach ($candidates as $bin) {
        $ver = shell_exec(escapeshellarg($bin) . ' -r "echo PHP_VERSION_ID;" 2>/dev/null');
        if ($ver !== null && (int) trim($ver) >= 80200) {
            return $bin;
        }
    }
    throw new \RuntimeException(
        'PHP 8.2 以上の CLI バイナリが見つかりません。' .
        ' PHP_BINARY=' . PHP_BINARY . ' (version=' . PHP_VERSION . ')'
    );
}

// ── セットアップ実行 ─────────────────────────────────────────────────────────
function runSetup(array $d): array
{
    $logs = [];

    try {
        // 1. .env 生成
        $encKey = bin2hex(random_bytes(32));
        $env = <<<ENV
CI_ENVIRONMENT = production

app.baseURL = '{$d['base_url']}'
app.encryptionKey = 'hex2bin:{$encKey}'

database.default.hostname = {$d['db_host']}
database.default.database = {$d['db_name']}
database.default.username = {$d['db_user']}
database.default.password = {$d['db_pass']}
database.default.DBDriver = MySQLi
database.default.port = {$d['db_port']}

UPLOAD_MAX_MB = 64
ENV;
        file_put_contents(CI4_ROOT . '.env', $env . "\n");
        $logs[] = [true, '.env を生成しました'];

        // 2. spark migrate --all をサブプロセスで実行（CI4 HTTP ブートストラップを回避）
        $phpBin = findPhpBinary();
        $logs[] = [true, 'PHP CLI: ' . $phpBin];

        $spark = realpath(CI4_ROOT . 'spark');
        if ($spark === false) {
            throw new \RuntimeException('spark ファイルが見つかりません: ' . CI4_ROOT . 'spark');
        }
        $proc = proc_open(
            [$phpBin, $spark, 'migrate', '--all', '--no-interaction'],
            [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            realpath(CI4_ROOT)
        );
        if (! is_resource($proc)) {
            throw new \RuntimeException('proc_open が失敗しました');
        }
        fclose($pipes[0]);
        $stdout   = stream_get_contents($pipes[1]);
        $stderr   = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $exitCode = proc_close($proc);
        if ($exitCode !== 0) {
            throw new \RuntimeException('マイグレーション失敗 (exit:' . $exitCode . '): ' . trim($stderr ?: $stdout));
        }
        $logs[] = [true, 'マイグレーション完了'];

        // 3. 直接 PDO でサイト設定・管理者ユーザーを挿入
        $pdo = new PDO(
            sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
                $d['db_host'], (int) $d['db_port'], $d['db_name']),
            $d['db_user'],
            $d['db_pass'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // 4. サイト設定（migration が site_name 行を挿入済み → UPDATE; theme は INSERT IGNORE）
        $pdo->prepare("UPDATE wiki_settings SET setting_value = ? WHERE setting_key = 'site_name'")
            ->execute([$d['site_name']]);
        $pdo->prepare("INSERT IGNORE INTO wiki_settings (setting_key, setting_value) VALUES ('theme', 'sakura')")
            ->execute();
        $logs[] = [true, 'サイト設定を初期化しました'];

        // 5. 管理者ユーザー作成（Shield テーブルへ直接 INSERT）
        $now = date('Y-m-d H:i:s');

        $pdo->prepare('INSERT INTO users (username, active, created_at, updated_at) VALUES (?, 1, ?, ?)')
            ->execute([$d['admin_user'], $now, $now]);
        $userId = (int) $pdo->lastInsertId();

        $pdo->prepare(
            "INSERT INTO auth_identities
                (user_id, type, name, secret, secret2, created_at, updated_at)
             VALUES (?, 'email_password', ?, ?, ?, ?, ?)"
        )->execute([
            $userId,
            $d['admin_email'],
            $d['admin_email'],
            password_hash($d['admin_pass'], PASSWORD_DEFAULT),
            $now, $now,
        ]);

        $pdo->prepare("INSERT INTO auth_groups_users (user_id, `group`, created_at) VALUES (?, 'administrator', ?)")
            ->execute([$userId, $now]);

        $logs[] = [true, "管理者ユーザー「{$d['admin_user']}」を作成しました"];

        // 6. ロックファイル生成
        file_put_contents(LOCK_FILE, $now);
        $logs[] = [true, 'installed.lock を生成しました'];

        return ['success' => true, 'logs' => $logs, 'base_url' => $d['base_url']];

    } catch (\Throwable $e) {
        $logs[] = [false, 'エラー: ' . $e->getMessage() . ' (' . basename($e->getFile()) . ':' . $e->getLine() . ')'];
        return ['success' => false, 'logs' => $logs];
    }
}

// ── Step 判定 ─────────────────────────────────────────────────────────────────
$step   = (int)($_POST['step'] ?? 1);
$result = null;

if ($step === 5 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = runSetup($_POST);
    $step   = $result['success'] ? 6 : 5;
}

$requirements = ($step === 1) ? checkRequirements() : [];
$reqAllOk     = empty(array_filter($requirements, fn($r) => !$r[1]));

// ── ベース URL 推測 ────────────────────────────────────────────────────────────
$scheme  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$guessedUrl = $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '/';

?><!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Wiki インストーラー</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <style>
    body { background: #f8f9fa; }
    .wrap { max-width: 620px; margin: 3rem auto; padding: 0 1rem; }
    .steps { display: flex; gap: .4rem; margin-bottom: 2rem; }
    .steps span { flex: 1; height: 4px; border-radius: 2px; background: #dee2e6; }
    .steps span.done { background: #0d6efd; }
    .steps span.cur  { background: #6ea8fe; }
  </style>
</head>
<body>
<div class="wrap">
  <h1 class="h4 mb-1">Wiki インストーラー</h1>
  <p class="text-muted small mb-3">Step <?= min($step, 5) ?> / 5</p>

  <div class="steps mb-4">
    <?php for ($i = 1; $i <= 5; $i++): ?>
      <span class="<?= $i < $step ? 'done' : ($i === $step ? 'cur' : '') ?>"></span>
    <?php endfor ?>
  </div>

  <div class="card shadow-sm"><div class="card-body p-4">

<?php if ($step === 1): // ────── Step 1: 要件チェック ?>
  <h2 class="h5 mb-3">要件チェック</h2>
  <table class="table table-sm small mb-3">
    <?php foreach ($requirements as [$label, $ok, $value]): ?>
    <tr class="<?= $ok ? '' : 'table-danger' ?>">
      <td style="width:1.5rem"><?= $ok ? '✓' : '✗' ?></td>
      <td><?= e($label) ?></td>
      <td class="text-muted"><?= e($value) ?></td>
    </tr>
    <?php endforeach ?>
  </table>
  <?php if ($reqAllOk): ?>
    <form method="post"><input type="hidden" name="step" value="2">
      <button class="btn btn-primary">次へ →</button>
    </form>
  <?php else: ?>
    <div class="alert alert-danger small">要件を満たしていない項目があります。解消後にリロードしてください。</div>
  <?php endif ?>

<?php elseif ($step === 2): // ── Step 2: DB 設定 ?>
  <h2 class="h5 mb-3">データベース設定</h2>
  <form method="post" id="db-form">
    <input type="hidden" name="step" value="3">
    <div class="row g-2 mb-2">
      <div class="col-8">
        <label class="form-label">ホスト</label>
        <input type="text" name="db_host" class="form-control" value="<?= v('db_host','localhost') ?>" required>
      </div>
      <div class="col-4">
        <label class="form-label">ポート</label>
        <input type="number" name="db_port" class="form-control" value="<?= v('db_port','3306') ?>" required>
      </div>
    </div>
    <div class="mb-2">
      <label class="form-label">データベース名</label>
      <input type="text" name="db_name" class="form-control" value="<?= v('db_name') ?>" required>
    </div>
    <div class="mb-2">
      <label class="form-label">ユーザー名</label>
      <input type="text" name="db_user" class="form-control" value="<?= v('db_user') ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">パスワード</label>
      <input type="password" name="db_pass" class="form-control">
    </div>
    <div class="d-flex align-items-center gap-2 mb-3">
      <button type="button" class="btn btn-sm btn-outline-secondary" id="test-btn">接続テスト</button>
      <span id="test-msg" class="small"></span>
    </div>
    <hr>
    <button class="btn btn-primary">次へ →</button>
  </form>
  <script>
  document.getElementById('test-btn').addEventListener('click', () => {
    const fd = new FormData(document.getElementById('db-form'));
    fd.set('action', 'test_db'); fd.delete('step');
    const msg = document.getElementById('test-msg');
    msg.textContent = '確認中...'; msg.style.color = '';
    fetch('install.php', {method:'POST', body:fd})
      .then(r => r.json())
      .then(d => { msg.textContent = d.msg; msg.style.color = d.ok ? 'green' : 'red'; });
  });
  </script>

<?php elseif ($step === 3): // ── Step 3: サイト設定 ?>
  <h2 class="h5 mb-3">サイト設定</h2>
  <form method="post">
    <input type="hidden" name="step" value="4">
    <?php foreach (['db_host','db_port','db_name','db_user','db_pass'] as $k): ?>
    <input type="hidden" name="<?= $k ?>" value="<?= v($k) ?>">
    <?php endforeach ?>
    <div class="mb-3">
      <label class="form-label">サイト名</label>
      <input type="text" name="site_name" class="form-control" value="<?= v('site_name','Wiki') ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">ベース URL</label>
      <input type="url" name="base_url" class="form-control" value="<?= v('base_url', $guessedUrl) ?>" required>
      <div class="form-text">末尾に / を含めてください（例: https://example.com/）</div>
    </div>
    <button class="btn btn-primary">次へ →</button>
  </form>

<?php elseif ($step === 4): // ── Step 4: 管理者アカウント ?>
  <h2 class="h5 mb-3">管理者アカウント</h2>
  <form method="post">
    <input type="hidden" name="step" value="5">
    <?php foreach (['db_host','db_port','db_name','db_user','db_pass','site_name','base_url'] as $k): ?>
    <input type="hidden" name="<?= $k ?>" value="<?= v($k) ?>">
    <?php endforeach ?>
    <div class="mb-3">
      <label class="form-label">ユーザー名</label>
      <input type="text" name="admin_user" class="form-control" value="<?= v('admin_user','admin') ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">メールアドレス</label>
      <input type="email" name="admin_email" class="form-control" value="<?= v('admin_email') ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">パスワード</label>
      <input type="password" name="admin_pass" class="form-control" minlength="8" required>
      <div class="form-text">8文字以上</div>
    </div>
    <button class="btn btn-primary"
            onclick="return confirm('セットアップを実行します。よろしいですか？')">
      セットアップ実行
    </button>
  </form>

<?php elseif ($step === 5): // ── Step 5: 失敗 ?>
  <h2 class="h5 mb-3 text-danger">セットアップ失敗</h2>
  <ul class="list-unstyled small">
    <?php foreach ($result['logs'] as [$ok, $msg]): ?>
    <li class="<?= $ok ? 'text-success' : 'text-danger fw-bold' ?>"><?= $ok ? '✓' : '✗' ?> <?= e($msg) ?></li>
    <?php endforeach ?>
  </ul>
  <a href="install.php?step=2" class="btn btn-secondary btn-sm mt-2">← DB設定に戻る</a>

<?php elseif ($step === 6): // ── Step 6: 完了 ?>
  <div class="text-center">
    <div class="mb-2" style="font-size:2.5rem">✅</div>
    <h2 class="h5 mb-3">インストール完了</h2>
    <ul class="list-unstyled text-start small mb-4">
      <?php foreach ($result['logs'] as [$ok, $msg]): ?>
      <li class="<?= $ok ? 'text-success' : 'text-danger' ?>"><?= $ok ? '✓' : '✗' ?> <?= e($msg) ?></li>
      <?php endforeach ?>
    </ul>
    <a href="<?= e($result['base_url']) ?>admin" class="btn btn-primary mb-3">管理画面へ</a>
    <p class="text-danger small">セキュリティのため <code>install.php</code> を削除してください。</p>
  </div>
<?php endif ?>

  </div></div>
</div>
</body>
</html>
