<?php

namespace Parsnick\Steak\Boot;

use Illuminate\Container\Container;
use Illuminate\View\Compilers\BladeCompiler;

class RegisterBladeExtensions implements Bootable
{
    /**
     * Set up required container bindings.
     *
     * @param Container $app
     */
    public function boot(Container $app)
    {
        $app->afterResolving(function (BladeCompiler $compiler, $app) {

            $this->allowHighlightTag($compiler);

        });
    }

    /**
     * Add basic syntax highlighting.
     *
     * This just adds markup that is picked up by a javascript highlighting library.
     *
     * @param BladeCompiler $compiler
     */
    protected function allowHighlightTag(BladeCompiler $compiler)
    {
        $compiler->directive('highlight', function ($expression) {
            if (is_null($expression)) {
                $expression = "('php')";
            }
            return "<?php \$__env->startSection{$expression}; ?>";
        });

        $compiler->directive('endhighlight', function () {
            return <<<'HTML'
<?php $last = $__env->stopSection(); echo '<pre><code class="language-', $last, '">', $__env->yieldContent($last), '</code></pre>'; ?>
HTML;
        });
    }
}
