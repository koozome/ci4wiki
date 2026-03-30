# CI4Wiki

CodeIgniter 4 + Shield 認証で構築したシンプルな Wiki システムです。
ブラウザだけで初期セットアップを完結できる Web インストーラーを同梱しています。

## 特徴

- **Markdown 記述** — league/commonmark + GFM 拡張。コードハイライト（highlight.js）・図表（Mermaid）対応
- **階層カテゴリー** — 任意深度のカテゴリーツリー。子孫カテゴリーの記事を同一ページで一覧表示
- **テーマ切替** — sakura / sakura-dark / github / solarized / vue / monospace / night / academic / onigiri
- **添付ファイル** — ドラッグドロップ・クリップボード貼り付けでアップロード、Markdown スニペット自動挿入
- **ロールベース認可** — administrator / moderator / editor / contributor の 4 段階
- **サイト内リンク** — `[[スラッグ]]` 記法でページ間リンク
- **Web インストーラー** — ブラウザからセットアップ完結（`install.php`）

## 動作要件

| 項目 | バージョン |
|---|---|
| PHP | 8.2 以上 |
| MySQL | 8.0 以上 |
| 必須 extension | intl / mbstring / mysqli / pdo_mysql / zip |
| Web サーバー | Apache (`mod_rewrite`) / nginx |

## インストール

### 1. ファイル配置

zip を展開してサーバーに配置し、ドキュメントルートを `public/` に設定してください。

**Apache** — `public/` に `.htaccess` が配置済みです。`AllowOverride All` が必要です。

**nginx 設定例**

```nginx
server {
    listen 443 ssl;
    server_name your-domain.com;
    root /path/to/ci4wiki/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### 2. Web インストーラーを実行

ブラウザで `https://your-domain.com/install.php` にアクセスし、ウィザードに従って設定します。

| Step | 内容 |
|---|---|
| 1 | 要件チェック（PHP バージョン・extension・書き込み権限） |
| 2 | データベース設定（接続テスト付き） |
| 3 | サイト名・ベース URL |
| 4 | 管理者アカウント作成 |
| 5 | セットアップ実行（`.env` 生成・マイグレーション・ユーザー作成） |
| 6 | 完了 → 管理画面へ |

> インストール完了後、セキュリティのため `public/install.php` を削除してください。

## ロール階層

```
administrator → moderator → editor → contributor
```

| ロール | 主な権限 |
|---|---|
| administrator | 全権限・サイト設定 |
| moderator | カテゴリー管理・全記事管理・contributor 登録 |
| editor | 全カテゴリーへの投稿・全記事編集・公開/非公開 |
| contributor | 許可カテゴリーへの投稿・自記事編集のみ |

## サイト内リンク記法

```markdown
[[スラッグ]]             # 記事スラッグで直接リンク
[[カテゴリ/スラッグ]]    # カテゴリ + スラッグで指定
```

リンク先が存在しない場合は打ち消し線スタイルで表示されます。

## テーマ変更

管理画面 → **サイト設定** → テーマ選択

利用可能なテーマ: `sakura` / `sakura-dark` / `github` / `solarized` / `vue` / `monospace` / `night` / `academic` / `onigiri`

light/dark ペアを持つテーマは `prefers-color-scheme` に応じて自動切替します。

## ディレクトリ構成（主要部分）

```
ci4wiki/
├── app/
│   ├── Config/
│   │   ├── AuthGroups.php        # ロール・権限定義
│   │   └── Routes.php
│   ├── Controllers/
│   │   ├── Wiki.php              # 公開面
│   │   └── Admin/                # 管理画面
│   ├── Models/
│   ├── Helpers/
│   │   ├── markdown_helper.php
│   │   └── wiki_helper.php       # サイト内リンク解決
│   ├── Libraries/
│   │   └── FileUploadService.php
│   └── Database/Migrations/
├── public/
│   ├── index.php
│   ├── install.php               # Web インストーラー
│   ├── css/
│   │   ├── wiki.css
│   │   ├── admin.css
│   │   └── themes/               # テーマ CSS
│   └── uploads/wiki/             # アップロードファイル保存先
└── writable/                     # キャッシュ・ログ・セッション（git 除外）
```

## ライセンス

MIT License
