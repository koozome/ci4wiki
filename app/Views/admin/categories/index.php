<?= $this->extend('layouts/admin') ?>
<?php $pageTitle = 'カテゴリー管理' ?>
<?= $this->section('content') ?>

<div class="page-header mb-3">
  <div class="row align-items-center">
    <div class="col"><h2 class="page-title">カテゴリー管理</h2></div>
    <div class="col-auto">
      <a href="<?= site_url('admin/categories/add') ?>" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 5l0 14"/><path d="M5 12l14 0"/></svg>
        新規作成
      </a>
    </div>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-vcenter card-table">
      <thead>
        <tr>
          <th>名前</th>
          <th>スラッグ</th>
          <th>親カテゴリー</th>
          <th class="w-1 text-center">順序</th>
          <th class="w-1"></th>
        </tr>
      </thead>
      <tbody>
        <?php
        $flat    = [];
        $flatten = function(array $cats, int $depth = 0) use (&$flatten, &$flat): void {
            foreach ($cats as $cat) {
                $cat['_depth'] = $depth;
                $flat[] = $cat;
                if (! empty($cat['children'])) {
                    $flatten($cat['children'], $depth + 1);
                }
            }
        };
        $flatten($categories);

        // parent_id → name マップ
        $nameMap = array_column($flat, 'name', 'id');
        ?>
        <?php foreach ($flat as $cat): ?>
          <tr>
            <td>
              <?= str_repeat('<span style="display:inline-block;width:1.25rem"></span>', $cat['_depth']) ?>
              <?= esc($cat['name']) ?>
            </td>
            <td class="text-secondary"><?= esc($cat['slug']) ?></td>
            <td class="text-secondary"><?= esc($nameMap[$cat['parent_id']] ?? '—') ?></td>
            <td class="text-center text-secondary"><?= (int) $cat['list_order'] ?></td>
            <td class="text-end">
              <div class="btn-group">
                <a href="<?= site_url("admin/categories/edit/{$cat['id']}") ?>" class="btn btn-sm">編集</a>
                <form method="post" action="<?= site_url("admin/categories/delete/{$cat['id']}") ?>"
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
</div>

<?= $this->endSection() ?>
