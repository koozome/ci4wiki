<?php

declare(strict_types=1);

use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;

if (! function_exists('markdown_to_html')) {
    /**
     * Markdown テキストを HTML に変換する。
     * [[slug]] 形式のWikiリンクは wiki_link() で事前処理する。
     */
    function markdown_to_html(string $markdown): string
    {
        // Wikiリンク・添付ファイルファイル名を先に変換
        helper('wiki');
        $markdown = parse_wiki_links($markdown);
        $markdown = resolve_attachment_filenames($markdown);

        $environment = new Environment([
            'html_input'         => 'escape',
            'allow_unsafe_links' => false,
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());

        return (new MarkdownConverter($environment))->convert($markdown)->getContent();
    }
}
