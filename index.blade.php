@extends('_layouts.master')

@section('bodyClass', 'homepage')

@section('body')

    <div class="masthead">

        <div class="ui container">

            <h1 class="ui inverted header projectname">
                Steak
            </h1>
            <h1 class="ui inverted header tagline">
                simplified publishing to <a href="https://pages.github.com/" title="&quot;Websites for you and your projects&quot;">GitHub Pages</a> for your <span class="acronym">PHP</span> project
            </h1>

        </div>

    </div>


    <div class="ui large borderless main menu">

        <div class="ui container">

            @include('_partials.menu')

        </div>

    </div>

    <div class="ui vertical clearing segment">

        <div class="ui container">

            <h2 class="ui header">Just add &mdash;</h2>

            <ol class="ui list">
                <li>
                    <code>parsnick/steak</code> as a dev dependency in <code>composer.json</code>, and
                </li>
                <li>
                    a <code>steak.yml</code> configuration file
                </li>
            </ol>

            <br>

            <h2 class="ui header centered">
                &mdash; then, anyone contributing to your project can &mdash;
            </h2>

            <div class="ui list right floated steak commands">
                <div class="item">
                    <div class="ui right pointing horizontal label">
                        <i class="dollar icon"></i>
                        <code>steak pull</code>
                    </div>
                    fetch the latest source code for your GitHub Pages website
                </div>
                <div class="item">
                    <div class="ui right pointing horizontal label">
                        <i class="dollar icon"></i>
                        <code>steak serve</code>
                    </div>
                    start a local development server to make and view changes
                </div>
                <div class="item">
                    <div class="ui right pointing horizontal label">
                        <i class="dollar icon"></i>
                        <code>steak deploy</code>
                    </div>
                    build, commit and push the website
                </div>

                <div class="footnote item">
                    <span>Note</span>
                    <div class="ui horizontal label">
                        <code>steak</code>
                    </div>
                    refers to the composer-installed binary found at
                    <div class="ui horizontal label">
                        <code>./vendor/bin/steak</code>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <div class="ui vertical segment">

        <div class="ui text container">

            <h1 class="ui header">
                <i class="configure icon"></i>
                <div class="content">
                    No need for a new toolbox!
                    <div class="sub header">Use the tools you already know&hellip;</div>
                </div>
            </h1>

            <p>
                GitHub Pages uses <a href="https://help.github.com/articles/using-jekyll-with-pages/">Jekyll</a>,
                a static site generator written in Ruby and using the
                <a href="https://github.com/Shopify/liquid/wiki">Liquid</a> templating engine.
                Now maybe you want to install the <code>gh-pages</code>
                <span class="help popup" data-content="a Ruby package">gem</span> and its stack
                of dependencies, but if not&hellip;
            </p>


            <p>
                A <span class="acronym">PHP</span> project likely already uses
                <a href="https://getcomposer.org/">Composer</a> as dependency manager
                and a node-based task runner like <a href="http://gulpjs.com/">Gulp</a>
                or <a href="http://gruntjs.com/">Grunt</a>. <code>steak</code> lets you
                use those same tools to generate your static site.
            </p>

        </div>

    </div>

    <div class="ui vertical inverted segment">

        <div class="ui very relaxed grid container">

            <div class="ten wide column">

                <div class="ui big inverted header">
                    A minimal example&hellip;
                </div>

                <p>
                    Of course, <code>steak</code> doesn't <em>have</em> to publish to GitHub Pages -
                    the only required values are a source directory to build from, and an output directory
                    to build into.
                </p>

            </div>

            <div class="six wide column">
@highlight('yaml')
# steak.yml
source: { directory: src }
build: { directory: output }
@endhighlight
            </div>

        </div>

        <div class="ui container very relaxed middle aligned stackable grid steak sequence">

            <div class="six wide column">
                <div class="ui piled segment">

                    <div class="ui list">
                        @include('_partials.tree', ['structure' => [
                            'src' => [
                                'getting-started' => [
                                    '01-overview.php',
                                    '02-installation.php',
                                ],
                                'examples.php',
                                'index.php',
                            ],
                        ]])
                    </div>

                </div>
            </div>

            <div class="four wide column center aligned">
                <div>
                    <h3>
                        <code class="terminal">./vendor/bin/steak build</code>
                    </h3>
                    <i class="huge long right arrow icon"></i>
                </div>
            </div>

            <div class="six wide column">
                <div class="ui piled segment">
                    <div class="ui list">
                        @include('_partials.tree', ['structure' => [
                            'output' => [
                                'getting-started' => [
                                    '01-overview.html',
                                    '02-installation.html',
                                ],
                                'examples.html',
                                'index.html',
                            ],
                        ]])
                    </div>

                </div>
            </div>

        </div>

    </div>

    <div class="ui vertical segment">

        <div class="ui container">

            <div class="ui big header">
                <code>gulp</code>-ready
            </div>

            <p>
                Some tasks just aren't well suited to <span class="acronym">PHP</span>, and for those
                we can defer to gulp. The <code>steak</code> application triggers two gulp tasks, which it expects
                to find in a <code>gulpfile.js</code> alongside your source files.
            </p>

            <div class="ui small header">
                steak:build
            </div>
            <p>
                This provides an opportunity to copy across static assets (images, fonts, etc.) as well
                compiling <span class="acronym">SASS</span>, browserifying scripts, etc.
                It is executed from within the <code class="terminal">steak build</code> command <em>after</em>
                the <span class="acronym">PHP</span> files have been compiled.
            </p>

            <div class="ui small header">
                steak:serve
            </div>
            <p>
                This should start a server and watch the source files to rebuild on change. It is executed as part of
                the <code class="terminal">steak serve</code> command.
            </p>

        </div>
    </div>

    <div class="ui vertical segment">

        <div class="ui text container">

            <div class="ui big header">So what exactly <em>is</em> <code>steak</code>?</div>

            <div class="ui dividing sub header">1) a few git aliases</div>

            <p>
                <code class="terminal">steak pull</code> and <code class="terminal">steak deploy</code> provide convenient aliases
                for cloning your project's site
                sources<span class="help popup" data-content="(if not kept in the main project repository)">*</span>
                and pushing the latest build respectively.
                And by specifying the relevant branches/repositories in a tracked <code>steak.yml</code>
                configuration file, you can cut down on documenting how to contribute to the documentation.
            </p>

            <div class="ui dividing sub header">2) <span class="acronym">PHP</span> and gulp build system</div>

            <p>
                <code class="terminal">steak build</code> takes a set of PHP files and runs them through a simple
                but customisable build pipeline. In addition, it executes a <code class="terminal">gulp steak:build</code> command
                (soon to be an arbitrary build command) to copy across any static assets, compile
                <span class="acronym">SASS</span>, or whatever else you may need.
            </p>

            <div class="ui big header">And what <code>steak</code> is <em>not</em></div>
            <p>
                Behind the scenes, <code>steak</code> uses the <a href="https://github.com/illuminate/view">View</a>
                component from Laravel. It comes pre-configured with the <a href="https://laravel.com/docs/5.2/blade">Blade</a>
                templating engine and a <a href="https://github.com/michelf/php-markdown">Markdown engine</a> to get you
                started. The real aim of <code>steak</code> - however - is not so much a complete publishing solution
                akin to Jekyll, but rather a means of generating a static site using your preferred tools and existing
                knowledge.
            </p>
        </div>

    </div>

    <div class="ui inverted vertical footer segment">
        <div class="ui grid container">
            <div class="ten wide column">
                <div class="ui inverted sub header">
                    License
                </div>
                MIT
            </div>
            <div class="six wide column">
                <ul class="ui list">
                    <li class="item">
                        Hosted by <a href="https://pages.github.com/">GitHub Pages</a>
                    </li>
                    <li class="item">
                        Built and deployed by <code>steak</code>
                    </li>
                    <li class="item">
                        Uses <a href="http://semantic-ui.com/">semantic ui</a> front-end framework
                    </li>
                </ul>
            </div>
        </div>
    </div>

@stop

@push('scripts')
<script>
    // fix main menu to page on passing
    $('.main.menu').visibility({
        type: 'fixed'
    });
</script>
@endpush
