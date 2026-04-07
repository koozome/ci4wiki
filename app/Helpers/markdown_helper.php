<?php

declare(strict_types=1);

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

if (! function_exists('_wiki_md_converter')) {
    /** CommonMark コンバーター生成（共通設定） */
    function _wiki_md_converter(): MarkdownConverter
    {
        $env = new Environment([
            'html_input'         => 'escape',
            'allow_unsafe_links' => false,
        ]);
        $env->addExtension(new CommonMarkCoreExtension());
        $env->addExtension(new GithubFlavoredMarkdownExtension());
        return new MarkdownConverter($env);
    }
}

if (! function_exists('_wiki_include_depth')) {
    /** include 再帰深度カウンター（循環 include 防止） */
    function _wiki_include_depth(int $delta = 0): int
    {
        static $depth = 0;
        return $depth += $delta;
    }
}

if (! function_exists('markdown_to_html')) {
    /**
     * Markdown テキストを HTML に変換する。
     *
     * 処理順:
     *   0. {{include(slug)}} を解決して挿入
     *   1. {{collapse}} ブロックを抽出 → プレースホルダーへ（内部MDも変換済み）
     *   2. 添付ファイル名を URL に解決（Markdown 段階）
     *   3. CommonMark でレンダリング
     *   4. collapse プレースホルダーを <details> HTML に復元
     *   5. [[slug]] wiki リンクを変換（HTML 段階）
     */
    function markdown_to_html(string $markdown): string
    {
        helper('wiki');
        $converter = _wiki_md_converter();

        // \r\n → \n 正規化（ブラウザ送信対応）
        $markdown = str_replace("\r\n", "\n", $markdown);

        // 0. {{include(slug)}} 解決 → プレースホルダーへ（collapse と同様）
        $includeBlocks = [];
        $iIdx = 0;
        $markdown = preg_replace_callback(
            '/\{\{include\(([^)\n]+)\)\}\}/',
            static function (array $m) use (&$includeBlocks, &$iIdx): string {
                if (_wiki_include_depth() >= 5) {
                    $html = '<div class="wiki-include-error">include: 深度上限を超えました</div>';
                } else {
                    $slugPath = trim($m[1]);
                    $parts    = explode('/', $slugPath, 2);
                    [$catSlug, $artSlug] = count($parts) === 2 ? $parts : [null, $parts[0]];

                    $db = \Config\Database::connect();
                    $qb = $db->table('wiki_articles a')
                             ->select('a.content')
                             ->where('a.slug', $artSlug)
                             ->where('a.status', 'published');
                    if ($catSlug !== null) {
                        $qb->join('wiki_categories c', 'c.id = a.category_id', 'inner')
                           ->where('c.slug', $catSlug);
                    }
                    $row = $qb->limit(1)->get()->getRowArray();

                    if ($row === null) {
                        $html = '<div class="wiki-include-error">include: 記事が見つかりません: ' . esc($slugPath) . '</div>';
                    } else {
                        _wiki_include_depth(1);
                        $inner = markdown_to_html($row['content']);
                        _wiki_include_depth(-1);
                        $html = '<div class="wiki-include">' . "\n" . $inner . '</div>';
                    }
                }

                $placeholder = "WIKIINCLUDEBLOCK{$iIdx}";
                $includeBlocks[$placeholder] = $html;
                $iIdx++;
                return $placeholder;
            },
            $markdown
        );

        // 1. collapse 抽出
        $collapseBlocks = [];
        $idx = 0;
        $markdown = preg_replace_callback(
            '/\{\{collapse(?:\(([^,)\n]*)(?:,([^)\n]*))?\))?\n(.*?)\n\}\}/ms',
            static function (array $m) use (&$collapseBlocks, &$idx, $converter): string {
                $hasLabel   = trim($m[1] ?? '') !== '';
                $openLabel  = $hasLabel ? trim($m[1]) : '表示';
                $closeLabel = $hasLabel ? trim($m[2] ?? '') : '隠す';

                // 内部コンテンツも Markdown としてレンダリング
                $innerMd   = resolve_attachment_filenames($m[3]);
                $innerHtml = $converter->convert($innerMd)->getContent();
                $innerHtml = parse_wiki_links($innerHtml);

                if ($closeLabel !== '') {
                    $summary = '<span class="wiki-collapse-show">' . esc($openLabel) . '</span>'
                             . '<span class="wiki-collapse-hide">'  . esc($closeLabel) . '</span>';
                    $detailsClass = 'wiki-collapse wiki-collapse-toggle';
                } else {
                    $summary      = esc($openLabel);
                    $detailsClass = 'wiki-collapse';
                }

                $html = "<details class=\"{$detailsClass}\">\n"
                      . "<summary>{$summary}</summary>\n"
                      . $innerHtml
                      . "</details>\n";

                $placeholder = "WIKICOLLAPSEBLOCK{$idx}";
                $collapseBlocks[$placeholder] = $html;
                $idx++;
                return $placeholder;
            },
            $markdown
        );

        // 2. 添付ファイル名解決（Markdown 段階）
        $markdown = resolve_attachment_filenames($markdown);

        // 3. CommonMark レンダリング
        $html = $converter->convert($markdown)->getContent();

        // 4. collapse / include 復元（CommonMark が <p>PLACEHOLDER</p> に包む）
        foreach ($collapseBlocks + $includeBlocks as $key => $block) {
            $html = str_replace("<p>{$key}</p>\n", $block, $html);
            $html = str_replace("<p>{$key}</p>",   $block, $html);
        }

        // 5. wiki リンク変換（HTML 段階）
        return parse_wiki_links($html);
    }
}
