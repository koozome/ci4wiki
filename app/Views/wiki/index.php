<?= $this->extend('layouts/public') ?>
<?= $this->section('content') ?>

<div class="wiki-grid">
  <section>
    <h2>カテゴリー</h2>
    <div class="category-tree">
      <?php foreach ($categories as $cat): ?>
        <div class="category-item">
          <a href="<?= site_url("category/{$cat['full_path']}") ?>" class="category-name">
            <?= esc($cat['name']) ?>
          </a>
          <?php if (! empty($cat['children'])): ?>
            <div class="category-children">
              <?php foreach ($cat['children'] as $child): ?>
                <a href="<?= site_url("category/{$child['full_path']}") ?>"><?= esc($child['name']) ?></a>
              <?php endforeach ?>
            </div>
          <?php endif ?>
        </div>
      <?php endforeach ?>
    </div>
  </section>

  <aside>
    <h2>最新記事</h2>
    <?php foreach ($latest as $article): ?>
      <?= view('wiki/_partials/article_card', ['article' => $article]) ?>
    <?php endforeach ?>
  </aside>
</div>

<?= $this->endSection() ?>
