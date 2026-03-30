<?= $this->extend('layouts/admin') ?>
<?php $pageTitle = '記事編集' ?>
<?= $this->section('content') ?>

<div class="page-header mb-3">
  <div class="row align-items-center">
    <div class="col"><h2 class="page-title"><?= esc($article['title']) ?></h2></div>
    <div class="col-auto">
      <?php if ($article['status'] === 'published'): ?>
        <a href="<?= site_url(($article['category_slug'] ?? '') . '/' . $article['slug']) ?>"
           target="_blank" class="btn btn-outline-secondary me-2">記事を見る</a>
      <?php endif ?>
      <a href="<?= site_url('admin/articles') ?>" class="btn btn-outline-secondary">← 一覧へ</a>
    </div>
  </div>
</div>

<form method="post" action="<?= site_url("admin/articles/edit/{$article['id']}") ?>">
  <?= csrf_field() ?>
  <?= view('admin/articles/_form', compact('article', 'categories')) ?>
</form>

<div class="card mt-3">
  <div class="card-header"><h3 class="card-title">添付ファイル</h3></div>

  <?php if (! empty($attachments)): ?>
    <div class="table-responsive">
      <table class="table table-vcenter card-table">
        <thead><tr><th>ファイル名</th><th>サイズ</th><th></th></tr></thead>
        <tbody>
          <?php foreach ($attachments as $att): ?>
            <?php
              $attName     = $att['original_name'];
              $attUrl      = site_url("attachment/{$att['id']}/" . rawurlencode($attName));
              $isImage     = str_starts_with($att['mime_type'], 'image/');
              $attMarkdown = $isImage ? "![]({$attName})" : "[{$attName}]({$attName})";
            ?>
            <tr>
              <td>
                <a href="<?= $attUrl ?>" target="_blank"><?= esc($att['original_name']) ?></a>
              </td>
              <td class="text-secondary"><?= number_format($att['file_size'] / 1024, 1) ?> KB</td>
              <td class="text-end">
                <div class="btn-group">
                  <button type="button" class="btn btn-sm btn-outline-primary js-insert-att"
                          data-markdown="<?= esc($attMarkdown) ?>">挿入</button>
                  <form method="post" action="<?= site_url("admin/articles/attachment-delete/{$att['id']}") ?>"
                        onsubmit="return confirm('削除しますか？')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-danger">削除</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach ?>
        </tbody>
      </table>
    </div>
  <?php endif ?>

  <div class="card-footer">
    <form method="post" action="<?= site_url("admin/articles/upload/{$article['id']}") ?>"
          enctype="multipart/form-data" class="row g-2 align-items-center">
      <?= csrf_field() ?>
      <div class="col">
        <input type="file" name="attachment" class="form-control" required>
        <small class="text-secondary">最大<?= (int) env('UPLOAD_MAX_MB', 64) ?>MB / JPEG・PNG・GIF・WebP・SVG・PDF・TXT・ZIP</small>
      </div>
      <div class="col-auto">
        <button type="submit" class="btn btn-primary">アップロード</button>
      </div>
    </form>
  </div>
</div>

<?= $this->section('scripts') ?>
<script>
(function () {
  const textarea = document.getElementById('content');
  if (!textarea) return;

  const uploadUrl = '<?= site_url("admin/articles/upload-ajax/{$article['id']}") ?>';
  const csrfName  = '<?= csrf_token() ?>';
  const csrfHash  = '<?= csrf_hash() ?>';

  function uploadFile(file) {
    const fd = new FormData();
    fd.append('attachment', file);
    fd.append(csrfName, csrfHash);

    const placeholder = `![アップロード中: ${file.name}]()`;
    insertAtCursor(textarea, placeholder);

    fetch(uploadUrl, { method: 'POST', body: fd })
      .then(r => r.json())
      .then(data => {
        if (data.error) {
          textarea.value = textarea.value.replace(placeholder, `<!-- エラー: ${data.error} -->`);
          alert(data.error);
          return;
        }
        textarea.value = textarea.value.replace(placeholder, data.markdown);
      })
      .catch(() => {
        textarea.value = textarea.value.replace(placeholder, '');
        alert('アップロードに失敗しました');
      });
  }

  function insertAtCursor(el, text) {
    const start = el.selectionStart;
    const end   = el.selectionEnd;
    el.value = el.value.slice(0, start) + text + el.value.slice(end);
    el.selectionStart = el.selectionEnd = start + text.length;
  }

  textarea.addEventListener('dragover', e => {
    e.preventDefault();
    textarea.classList.add('drag-over');
  });
  textarea.addEventListener('dragleave', () => textarea.classList.remove('drag-over'));
  textarea.addEventListener('drop', e => {
    e.preventDefault();
    textarea.classList.remove('drag-over');
    const files = e.dataTransfer.files;
    for (const f of files) uploadFile(f);
  });
  document.querySelectorAll('.js-insert-att').forEach(btn => {
    btn.addEventListener('click', () => {
      insertAtCursor(textarea, btn.dataset.markdown);
      textarea.focus();
    });
  });

  textarea.addEventListener('paste', e => {
    const items = e.clipboardData?.items ?? [];
    for (const item of items) {
      if (item.kind === 'file') {
        e.preventDefault();
        uploadFile(item.getAsFile());
      }
    }
  });
})();
</script>
<style>
#content.drag-over { outline: 2px dashed var(--tblr-primary); background: rgba(32,107,196,.05); }
</style>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
