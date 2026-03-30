<?= $this->extend('layouts/public') ?>
<?php $pageTitle = $category['name'] ?>
<?= $this->section('content') ?>

<nav aria-label="breadcrumb">
  <ul>
    <?php foreach (wiki_breadcrumb($category) as $crumb) : ?>
      <li><?= $crumb['url'] ? '<a href="' . esc($crumb['url']) . '">' . esc($crumb['label']) . '</a>' : esc($crumb['label']) ?></li>
    <?php endforeach ?>
  </ul>
</nav>

<hgroup>
  <h1><?= esc($category['name']) ?></h1>
  <?php if ($category['description']) : ?>
    <p><?= esc($category['description']) ?></p>
  <?php endif ?>
</hgroup>

<?php $totalArticles = array_sum(array_map(fn($s) => count($s['articles']), $sections)) ?>
<?php if ($totalArticles === 0): ?>
  <p>このカテゴリーにはまだ記事がありません。</p>
<?php else: ?>
  <?php foreach ($sections as $sec): ?>
    <?php $d = $sec['depth'] ?>
    <?php if ($d > 0): ?>
      <?php $hTag = 'h' . min($d + 1, 4) ?>
      <<?= $hTag ?> class="cat-section-heading" style="--d:<?= $d ?>"><?= esc($sec['category']['name']) ?></<?= $hTag ?>>
    <?php endif ?>
    <?php if (!empty($sec['articles'])): ?>
      <div class="cat-section-articles" style="--d:<?= $d ?>">
        <?php foreach ($sec['articles'] as $article): ?>
          <?= view('wiki/_partials/article_card', compact('article')) ?>
        <?php endforeach ?>
      </div>
    <?php endif ?>
  <?php endforeach ?>
<?php endif ?>

<?= $this->endSection() ?>
