<div class="row">
  <div class="col-lg-8">
    <div class="mb-3">
      <label class="form-label required">タイトル</label>
      <input type="text" name="title" class="form-control" required
             value="<?= esc(old('title', $article['title'] ?? '')) ?>"
             placeholder="記事タイトル">
    </div>
    <div class="mb-3">
      <label class="form-label required">本文 <small class="text-secondary">（Markdown）</small></label>

      <div class="md-toolbar d-flex flex-wrap align-items-center gap-1 px-2 py-1 border border-bottom-0 rounded-top bg-muted-lt">
        <div class="btn-group btn-group-sm me-1">
          <button type="button" class="btn btn-secondary active" id="md-tab-edit">編集</button>
          <button type="button" class="btn btn-secondary" id="md-tab-preview">プレビュー</button>
        </div>
        <div class="vr me-1"></div>
        <div class="btn-group btn-group-sm me-1">
          <button type="button" class="btn btn-outline-secondary" data-md="bold"    title="太字"><strong>B</strong></button>
          <button type="button" class="btn btn-outline-secondary" data-md="italic"  title="斜体"><em>I</em></button>
          <button type="button" class="btn btn-outline-secondary" data-md="strike"  title="取り消し線"><s>S</s></button>
          <button type="button" class="btn btn-outline-secondary" data-md="code"    title="インラインコード"><code>C</code></button>
        </div>
        <div class="btn-group btn-group-sm me-1">
          <button type="button" class="btn btn-outline-secondary" data-md="h1" title="見出し1">H1</button>
          <button type="button" class="btn btn-outline-secondary" data-md="h2" title="見出し2">H2</button>
          <button type="button" class="btn btn-outline-secondary" data-md="h3" title="見出し3">H3</button>
        </div>
        <div class="btn-group btn-group-sm me-1">
          <button type="button" class="btn btn-outline-secondary" data-md="ul"    title="箇条書き">≡</button>
          <button type="button" class="btn btn-outline-secondary" data-md="ol"    title="番号リスト">⊟</button>
          <button type="button" class="btn btn-outline-secondary" data-md="quote" title="引用">&ldquo;</button>
        </div>
        <div class="btn-group btn-group-sm me-1">
          <button type="button" class="btn btn-outline-secondary" data-md="pre"   title="コードブロック">pre</button>
          <button type="button" class="btn btn-outline-secondary" data-md="hr"    title="水平線">&mdash;</button>
        </div>
        <div class="btn-group btn-group-sm">
          <button type="button" class="btn btn-outline-secondary" data-md="link"  title="リンク">🔗</button>
          <button type="button" class="btn btn-outline-secondary" data-md="image" title="画像">🖼</button>
          <button type="button" class="btn btn-outline-secondary" data-md="table" title="テーブル">⊞</button>
        </div>
      </div>

      <textarea name="content" id="content" class="form-control font-monospace rounded-0 rounded-bottom" rows="28"><?= esc(old('content', $article['content'] ?? '')) ?></textarea>
      <div id="md-preview" class="d-none border rounded-bottom p-3 wiki-content" style="min-height:400px;overflow:auto"></div>
      <small class="form-hint">サイト内リンク: <code>[[スラッグ]]</code> または <code>[[カテゴリ/スラッグ]]</code></small>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card">
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label required">カテゴリー</label>
          <select name="category_id" class="form-select" required>
            <option value="">選択してください</option>
            <?php foreach ($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>"
                <?= (old('category_id', $article['category_id'] ?? $categoryId ?? '') == $cat['id']) ? 'selected' : '' ?>>
                <?= esc(str_repeat('　', $cat['depth']) . $cat['name']) ?>
              </option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label required">スラッグ</label>
          <input type="text" name="slug" class="form-control" required
                 value="<?= esc(old('slug', $article['slug'] ?? '')) ?>"
                 pattern="[a-zA-Z0-9\-_]+"
                 placeholder="article-slug">
          <small class="form-hint">英数字・ハイフン・アンダースコアのみ</small>
        </div>
      </div>
      <div class="card-footer">
        <div class="row g-2">
          <div class="col">
            <button type="submit" name="status" value="draft" class="btn w-100">下書き保存</button>
          </div>
          <?php if (auth()->user()->can('articles.publish')): ?>
          <div class="col">
            <button type="submit" name="status" value="published" class="btn btn-primary w-100">公開</button>
          </div>
          <?php endif ?>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
(function () {
  const ta      = document.getElementById('content');
  const preview = document.getElementById('md-preview');
  const tabEdit = document.getElementById('md-tab-edit');
  const tabPrev = document.getElementById('md-tab-preview');
  if (!ta) return;

  const previewUrl = '<?= site_url('admin/articles/preview') ?>';
  const csrfName   = '<?= csrf_token() ?>';
  let   csrfHash   = '<?= csrf_hash() ?>';

  /* ---- tab switching ---- */
  tabEdit?.addEventListener('click', () => {
    ta.classList.remove('d-none');
    preview.classList.add('d-none');
    tabEdit.classList.add('active');
    tabPrev.classList.remove('active');
  });
  tabPrev?.addEventListener('click', () => {
    ta.classList.add('d-none');
    preview.classList.remove('d-none');
    tabEdit.classList.remove('active');
    tabPrev.classList.add('active');
    const fd = new FormData();
    fd.append(csrfName, csrfHash);
    fd.append('content', ta.value);
    fetch(previewUrl, { method: 'POST', body: fd })
      .then(r => r.json())
      .then(d => {
        csrfHash = d.csrf ?? csrfHash;
        preview.innerHTML = d.html ?? '';
        if (window.hljs) preview.querySelectorAll('pre code').forEach(b => hljs.highlightElement(b));
        if (window.mermaid) {
          preview.querySelectorAll('pre code.language-mermaid').forEach(el => {
            const div = document.createElement('div');
            div.className = 'mermaid';
            div.textContent = el.textContent;
            el.closest('pre').replaceWith(div);
          });
          mermaid.run({ nodes: preview.querySelectorAll('.mermaid') });
        }
      });
  });

  /* ---- toolbar actions ---- */
  function wrap(before, after, placeholder) {
    const start = ta.selectionStart, end = ta.selectionEnd;
    const sel   = ta.value.slice(start, end) || placeholder;
    const text  = before + sel + after;
    ta.value    = ta.value.slice(0, start) + text + ta.value.slice(end);
    ta.selectionStart = start + before.length;
    ta.selectionEnd   = start + before.length + sel.length;
    ta.focus();
  }
  function linePrefix(prefix) {
    const start = ta.value.lastIndexOf('\n', ta.selectionStart - 1) + 1;
    ta.value    = ta.value.slice(0, start) + prefix + ta.value.slice(start);
    ta.focus();
  }
  function insert(text) {
    const pos = ta.selectionStart;
    ta.value  = ta.value.slice(0, pos) + text + ta.value.slice(pos);
    ta.selectionStart = ta.selectionEnd = pos + text.length;
    ta.focus();
  }

  const actions = {
    bold:   () => wrap('**', '**', '太字'),
    italic: () => wrap('*', '*', '斜体'),
    strike: () => wrap('~~', '~~', '打ち消し'),
    code:   () => wrap('`', '`', 'code'),
    h1:     () => linePrefix('# '),
    h2:     () => linePrefix('## '),
    h3:     () => linePrefix('### '),
    ul:     () => linePrefix('- '),
    ol:     () => linePrefix('1. '),
    quote:  () => linePrefix('> '),
    pre:    () => wrap('```\n', '\n```', 'code'),
    hr:     () => insert('\n---\n'),
    link:   () => wrap('[', '](https://)', 'リンクテキスト'),
    image:  () => wrap('![', '](https://)', '画像の説明'),
    table:  () => insert('\n| 列1 | 列2 | 列3 |\n| --- | --- | --- |\n| セル | セル | セル |\n'),
  };

  document.querySelectorAll('[data-md]').forEach(btn => {
    btn.addEventListener('click', () => actions[btn.dataset.md]?.());
  });
})();
</script>
