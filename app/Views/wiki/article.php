<?= $this->extend('layouts/public') ?>
<?php $pageTitle = $article['title'] ?>
<?= $this->section('content') ?>

<nav aria-label="breadcrumb">
  <ul>
    <?php foreach (wiki_breadcrumb($category, $article['title']) as $crumb): ?>
      <li><?= $crumb['url'] ? '<a href="' . esc($crumb['url']) . '">' . esc($crumb['label']) . '</a>' : esc($crumb['label']) ?></li>
    <?php endforeach ?>
  </ul>
</nav>

<article>
  <header>
    <hgroup>
      <h1><?= esc($article['title']) ?></h1>
      <p>
        <small class="muted">
          <?= esc($article['owner']) ?>
          <?php if ($article['editor']): ?> · 編集: <?= esc($article['editor']) ?><?php endif ?>
          · <?= $article['published_at'] ? date('Y-m-d', strtotime($article['published_at'])) : '' ?>
          · 閲覧 <?= (int) $article['view_count'] ?>
          <?php if (auth()->loggedIn() && auth()->user()->can('articles.create')): ?>
            · <a href="<?= site_url("admin/articles/edit/{$article['id']}") ?>">編集</a>
          <?php endif ?>
        </small>
      </p>
    </hgroup>
  </header>

  <div class="wiki-content"><?= markdown_to_html((string) $article['content']) ?></div>

  <?php if (! empty($attachments)): ?>
    <footer>
      <h2>添付ファイル</h2>
      <ul>
        <?php foreach ($attachments as $att): ?>
          <li>
            <a href="<?= site_url("attachment/{$att['id']}/{$att['original_name']}") ?>">
              <?= esc($att['original_name']) ?>
            </a>
            <small class="muted">(<?= number_format($att['file_size'] / 1024, 1) ?> KB)</small>
          </li>
        <?php endforeach ?>
      </ul>
    </footer>
  <?php endif ?>
</article>

<?= $this->endSection() ?>
