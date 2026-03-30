# CI4Wiki

A lightweight Wiki system built with **CodeIgniter 4** and **Shield** authentication.  
Includes a browser-based web installer — no CLI setup required.

## Features

- **Markdown editing** — league/commonmark with GFM extension, syntax highlighting (highlight.js) and diagrams (Mermaid)
- **Hierarchical categories** — unlimited depth category tree; articles from subcategories displayed grouped on the parent category page
- **Theme switching** — sakura / sakura-dark / github / solarized / vue / monospace / night / academic / onigiri
- **File attachments** — drag-and-drop or clipboard paste to upload; Markdown snippet auto-inserted
- **Role-based access** — four roles: administrator / moderator / editor / contributor
- **Wiki-style links** — `[[slug]]` notation resolves to internal article links
- **Web installer** — complete setup from the browser via `install.php`

## Requirements

| Item | Version |
|---|---|
| PHP | 8.2 or higher |
| MySQL | 8.0 or higher |
| Required extensions | intl / mbstring / mysqli / pdo_mysql / zip |
| Web server | Apache (`mod_rewrite`) or nginx |

## Installation

### 1. Deploy files

Extract the zip archive and place the files on your server.  
Set the document root to the `public/` directory.

**Apache** — `.htaccess` is included in `public/`. `AllowOverride All` is required.

**nginx example**

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

### 2. Run the web installer

Open `https://your-domain.com/install.php` in your browser and follow the wizard.

| Step | Description |
|---|---|
| 1 | Requirements check (PHP version, extensions, write permissions) |
| 2 | Database configuration (with live connection test) |
| 3 | Site name and base URL |
| 4 | Administrator account |
| 5 | Execute setup (generate `.env`, run migrations, create user) |
| 6 | Done — link to the admin panel |

> **Security:** Delete `public/install.php` after installation is complete.

## Role Hierarchy

```
administrator → moderator → editor → contributor
```

| Role | Capabilities |
|---|---|
| administrator | Full access, site settings |
| moderator | Category management, all articles, create contributors |
| editor | Post to any category, edit all articles, publish/unpublish |
| contributor | Post to permitted categories, edit own articles only |

## Wiki Link Syntax

```markdown
[[slug]]               # Link by article slug
[[category/slug]]      # Link by category + slug
```

Broken links are rendered with strikethrough styling.

## Themes

Go to **Admin panel → Site Settings → Theme** to switch themes.

| Theme | Dark mode |
|---|---|
| sakura | Auto (light/dark pair) |
| sakura-dark | Always dark |
| github | Auto |
| solarized | Auto |
| vue | Auto |
| monospace | Auto |
| night | Always dark |
| academic | Auto |
| onigiri | Auto |

Themes with a light/dark pair switch automatically based on `prefers-color-scheme`.

## Directory Structure

```
ci4wiki/
├── app/
│   ├── Config/
│   │   ├── AuthGroups.php        # Role and permission definitions
│   │   └── Routes.php
│   ├── Controllers/
│   │   ├── Wiki.php              # Public-facing pages
│   │   └── Admin/                # Admin panel
│   ├── Models/
│   ├── Helpers/
│   │   ├── markdown_helper.php
│   │   └── wiki_helper.php       # Wiki-link resolution
│   ├── Libraries/
│   │   └── FileUploadService.php
│   └── Database/Migrations/
├── public/
│   ├── index.php
│   ├── install.php               # Web installer
│   ├── css/
│   │   ├── wiki.css
│   │   ├── admin.css
│   │   └── themes/               # Theme CSS files
│   └── uploads/wiki/             # Uploaded files (git-ignored)
└── writable/                     # Cache, logs, sessions (git-ignored)
```

## License

MIT License
