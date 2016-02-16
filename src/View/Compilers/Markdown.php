<?php

namespace Parsnick\Steak\View\Compilers;

use Illuminate\View\Compilers\CompilerInterface;
use Michelf\MarkdownExtra;
use Parsnick\Steak\View\InMemoryCompiler;

class Markdown extends InMemoryCompiler implements CompilerInterface
{
    /**
     * Compile markdown to HTML.
     *
     * @param string $string
     * @return string
     */
    public function compileString($string)
    {
        return MarkdownExtra::defaultTransform($string);
    }
}
