<header class="navbar navbar-expand-md d-none d-lg-flex d-print-none">
  <div class="container-xl">
    <div class="navbar-nav flex-row order-md-last">
      <div class="nav-item dropdown">
        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
          <span class="avatar avatar-sm">
            <?= strtoupper(substr($authUser['username'] ?? 'A', 0, 1)) ?>
          </span>
          <div class="d-none d-xl-block ps-2">
            <div><?= esc($authUser['username'] ?? '') ?></div>
            <div class="mt-1 small text-secondary"><?= esc($authUser['email'] ?? '') ?></div>
          </div>
        </a>
        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
          <a href="<?= site_url('mypage') ?>" class="dropdown-item">マイページ</a>
          <div class="dropdown-divider"></div>
          <a href="<?= site_url('logout') ?>" class="dropdown-item">ログアウト</a>
        </div>
      </div>
    </div>
    <div class="collapse navbar-collapse" id="navbar-menu">
      <div class="d-flex flex-column flex-md-row flex-fill align-items-stretch align-items-md-center">
        <ul class="navbar-nav">
          <li class="nav-item">
            <span class="nav-link text-secondary">
              <?= esc($pageTitle ?? '') ?>
            </span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</header>
