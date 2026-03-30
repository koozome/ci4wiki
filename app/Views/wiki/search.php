<?= $this->extend('layouts/public') ?>
<?php $pageTitle = $keyword ? "「{$keyword}」の検索結果" : '検索' ?>
<?= $this->section('content') ?>

<hgroup>
  <h1>検索結果</h1>
  <?php if ($keyword): ?>
    <p><mark><?= esc($keyword) ?></mark> — <?= count($articles) ?> 件</p>
  <?php endif ?>
</hgroup>

<?php if (! empty($articles)): ?>
  <?php foreach ($articles as $article): ?>
    <?= view('wiki/_partials/article_card', ['article' => $article]) ?>
  <?php endforeach ?>
<?php elseif ($keyword): ?>
  <p>「<?= esc($keyword) ?>」に一致する記事が見つかりませんでした。</p>
<?php endif ?>

<?= $this->endSection() ?>
