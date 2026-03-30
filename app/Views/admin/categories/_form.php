<div class="row">
  <div class="col-lg-8">
    <div class="mb-3">
      <label class="form-label required" for="name">名前</label>
      <input type="text" id="name" name="name" class="form-control" required
             value="<?= esc(old('name', $category['name'] ?? '')) ?>">
    </div>

    <div class="mb-3">
      <label class="form-label required" for="slug">スラッグ</label>
      <input type="text" id="slug" name="slug" class="form-control" required
             value="<?= esc(old('slug', $category['slug'] ?? '')) ?>">
      <small class="form-hint">英数字・ハイフンのみ。URLに使用されます。</small>
    </div>

    <div class="mb-3">
      <label class="form-label" for="description">説明</label>
      <textarea id="description" name="description" class="form-control" rows="3"><?= esc(old('description', $category['description'] ?? '')) ?></textarea>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card">
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label" for="parent_id">親カテゴリー</label>
          <select id="parent_id" name="parent_id" class="form-select">
            <option value="">なし（トップレベル）</option>
            <?php foreach ($parents as $p): ?>
              <option value="<?= $p['id'] ?>"
                <?= (old('parent_id', $category['parent_id'] ?? '') == $p['id']) ? 'selected' : '' ?>>
                <?= esc($p['name']) ?>
              </option>
            <?php endforeach ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label" for="list_order">表示順</label>
          <input type="number" id="list_order" name="list_order" class="form-control"
                 value="<?= (int) old('list_order', $category['list_order'] ?? 0) ?>">
        </div>

        <label class="form-check">
          <input type="checkbox" name="post_level" value="1" class="form-check-input"
                 <?= old('post_level', $category['post_level'] ?? 0) ? 'checked' : '' ?>>
          <span class="form-check-label">Contributor の投稿を許可する</span>
        </label>
      </div>

      <div class="card-footer">
        <div class="row g-2">
          <div class="col">
            <button type="submit" class="btn btn-primary w-100">保存</button>
          </div>
          <div class="col">
            <a href="<?= site_url('admin/categories') ?>" class="btn w-100">キャンセル</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
