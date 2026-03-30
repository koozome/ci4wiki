<article>
  <h4 style="margin-bottom:.25rem">
    <a href="<?= article_url($article) ?>">
      <?= esc($article['title']) ?>
    </a>
  </h4>
  <small class="muted">
    <?= esc($article['owner']) ?>
    · <?= $article['published_at'] ? date('Y-m-d', strtotime($article['published_at'])) : '' ?>
    · 閲覧 <?= (int) $article['view_count'] ?>
  </small>
</article>
