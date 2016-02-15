<?php


namespace Parsnick\Steak;


class Highlighter
{
    public function highlight($source, $language = null)
    {
        $source = trim($source);

        return <<<HTML
<pre><code class="language-{$language}">{$source}</code></pre>
HTML;
    }
}
