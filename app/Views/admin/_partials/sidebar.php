<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <h1 class="navbar-brand navbar-brand-autodark">
      <a href="<?= site_url('admin') ?>">
        <span class="navbar-brand-text"><?= esc($siteSettings['site_name'] ?? 'Wiki') ?></span>
      </a>
    </h1>
    <div class="collapse navbar-collapse" id="sidebar-menu">
      <ul class="navbar-nav pt-lg-3">

        <li class="nav-item">
          <a class="nav-link" href="<?= site_url('admin') ?>">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l-2 0l9 -9l9 9l-2 0"/><path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7"/><path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6"/></svg>
            </span>
            <span class="nav-link-title">ダッシュボード</span>
          </a>
        </li>

        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/><path d="M9 9l1 0"/><path d="M9 13l6 0"/><path d="M9 17l6 0"/></svg>
            </span>
            <span class="nav-link-title">記事</span>
          </a>
          <div class="dropdown-menu">
            <a class="dropdown-item" href="<?= site_url('admin/articles') ?>">記事一覧</a>
            <a class="dropdown-item" href="<?= site_url('admin/articles/drafts') ?>">下書き</a>
            <?php if (auth()->user()->can('articles.create')): ?>
              <a class="dropdown-item" href="<?= site_url('admin/articles/add') ?>">新規作成</a>
            <?php endif ?>
          </div>
        </li>

        <?php if (auth()->user()->can('categories.manage')): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M9 4h-4a1 1 0 0 0 -1 1v4a1 1 0 0 0 1 1h4a1 1 0 0 0 1 -1v-4a1 1 0 0 0 -1 -1z"/><path d="M19 4h-4a1 1 0 0 0 -1 1v4a1 1 0 0 0 1 1h4a1 1 0 0 0 1 -1v-4a1 1 0 0 0 -1 -1z"/><path d="M9 14h-4a1 1 0 0 0 -1 1v4a1 1 0 0 0 1 1h4a1 1 0 0 0 1 -1v-4a1 1 0 0 0 -1 -1z"/><path d="M19 17a2 2 0 1 1 -4 0a2 2 0 0 1 4 0"/><path d="M17 11v-1a1 1 0 0 0 -1 -1h-1"/><path d="M12 17h-1a1 1 0 0 0 -1 1v1"/></svg>
            </span>
            <span class="nav-link-title">カテゴリー</span>
          </a>
          <div class="dropdown-menu">
            <a class="dropdown-item" href="<?= site_url('admin/categories') ?>">カテゴリー管理</a>
            <a class="dropdown-item" href="<?= site_url('admin/categories/add') ?>">新規作成</a>
          </div>
        </li>
        <?php endif ?>

        <?php if (auth()->user()->can('users.manage') || auth()->user()->can('users.create-contributor')): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0"/><path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2"/></svg>
            </span>
            <span class="nav-link-title">ユーザー</span>
          </a>
          <div class="dropdown-menu">
            <?php if (auth()->user()->can('users.manage')): ?>
              <a class="dropdown-item" href="<?= site_url('admin/users') ?>">ユーザー管理</a>
            <?php endif ?>
            <a class="dropdown-item" href="<?= site_url('admin/users/add') ?>">ユーザー追加</a>
          </div>
        </li>
        <?php endif ?>

        <?php if (auth()->user()->can('admin.settings')): ?>
        <li class="nav-item">
          <a class="nav-link" href="<?= site_url('admin/settings') ?>">
            <span class="nav-link-icon d-md-none d-lg-inline-block">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.325 4.317c.426 -1.756 2.924 -1.756 3.35 0a1.724 1.724 0 0 0 2.573 1.066c1.543 -.94 3.31 .826 2.37 2.37a1.724 1.724 0 0 0 1.065 2.572c1.756 .426 1.756 2.924 0 3.35a1.724 1.724 0 0 0 -1.066 2.573c.94 1.543 -.826 3.31 -2.37 2.37a1.724 1.724 0 0 0 -2.572 1.065c-.426 1.756 -2.924 1.756 -3.35 0a1.724 1.724 0 0 0 -2.573 -1.066c-1.543 .94 -3.31 -.826 -2.37 -2.37a1.724 1.724 0 0 0 -1.065 -2.572c-1.756 -.426 -1.756 -2.924 0 -3.35a1.724 1.724 0 0 0 1.066 -2.573c-.94 -1.543 .826 -3.31 2.37 -2.37c1 .608 2.296 .07 2.572 -1.065z"/><path d="M9 12a3 3 0 1 0 6 0a3 3 0 0 0 -6 0"/></svg>
            </span>
            <span class="nav-link-title">サイト設定</span>
          </a>
        </li>
        <?php endif ?>

      </ul>

      <div class="mt-auto p-3">
        <a href="<?= site_url('/') ?>" target="_blank" class="btn btn-outline-secondary btn-sm w-100">
          サイトを見る
        </a>
      </div>
    </div>
  </div>
</aside>
