@extends('_layouts.docs')

@section('content')

    <h1 class="ui huge header">Extending steak</h1>

    <p>
        <code>steak</code> is designed to be extended and takes inspiration from <a href="https://laravel.com">Laravel</a>.
        If you've used Laravel, you should feel right at home - otherwise, you may want to read up on its
        <a href="https://laravel.com/docs/5.2/container">service container</a> implementation.
    </p>

    <p>
        The first (and currently only) point of extension is via the <code>bootstrap</code> option set in your
        <code>steak.yml</code> configuration and briefly mentioned in the <a href="configuration.html">section on
        configuring steak</a>.
    </p>

    <div class="ui code segment">
@highlight('yaml')
# steak.yml
bootstrap: Acme\Steak\Bootstrap
@endhighlight
    </div>

    <p>
        Simply set the <code>bootstrap</code> option to the fully qualified class name of any number of custom
        bootstrappers. Here we only specify one, but use the YAML array syntax if you need more.
        It doesn't matter where you put the classes, as long as they can be loaded with composer's autoloader.
    </p>

    <p>
        Each bootstrapper should implement
        <a href="https://github.com/parsnick/steak/blob/master/src/Boot/Bootable.php">
            <code>Parsnick\Steak\Boot\Bootable</code>
        </a>.
        A <code>boot()</code> method is the only requirement, and it receives the service container as its
        only argument. You can swap out, reconfigure or extend components in the container as you see fit.
        Internally, all components are set up using this <code>Bootable</code> interface - take a look at
        <a href="https://github.com/parsnick/steak/tree/master/src/Boot"><code>Parsnick\Steak\Boot\</code></a>
        if you like.
    </p>

    <h3 class="ui header">Container reference</h3>

    <p>The following have named instances in the container:</p>

    <table class="ui celled table">
        <tbody>
            <tr>
                <td><code>app</code></td>
                <td>
                    the underlying <code>Symfony\Component\Console\Application</code>
                </td>
            </tr>
            <tr>
                <td><code>config</code></td>
                <td>
                    an instance of <code>Illuminate\Config\Repository</code> that contains the current configuration,
                    as read from <code>steak.yml</code>
                </td>
            </tr>
            <tr>
                <td><code>files</code></td>
                <td>an instance of <code>Illuminate\Filesystem\Filesystem</code> used for locating views</td>
            </tr>
            <tr>
                <td><code>events</code></td>
                <td>an instance of <code>Illuminate\Events\Dispatcher</code> used by the view factory</td>
            </tr>
        </tbody>
    </table>

    <p>
        Anything else can be modified with a <code>resolving()</code> or <code>afterResolving()</code> callback.
    </p>

    <br>

    <h2 class="ui dividing header">Example</h2>

    <div class="ui basic segment">
        <div class="ui right rail">
            Remember that Blade first compiles to basic PHP, which is in turn rendered to HTML by steak.
        </div>

        <p>
            This listens for <code>BladeCompiler</code> being resolved and adds support for a <code>{{ '@' }}buildTime</code>
            tag in blade, which renders a formatted date of when the page was built.
        </p>
    </div>

    <div class="ui code segment">
@highlight('php')
&lt;?php
namespace Acme\Steak;

use Illuminate\Container\Container;
use Illuminate\View\Compilers\BladeCompiler;
use Parsnick\Steak\Boot\Bootable;

class Bootstrap implements Bootable
{
    public function boot(Container $app)
    {
        $app->afterResolving(function (BladeCompiler $blade) {

            $blade->directive('buildTime', function ($expression) {
                return '&lt;?php echo date("c"); ?>
            });

        });
    }
}
@endhighlight
    </div>

    <p>
        See also - <a href="https://laravel.com/docs/5.2/blade#extending-blade">the Laravel docs on extending Blade</a>.
    </p>

@stop
