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

    <div class="ui vertical segment">

        <div class="ui very relaxed grid container">

            <div class="ten wide column">

                <div class="ui big header">
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
                we can defer to gulp.
            </p>

            <div class="ui secondary inverted segment">
                <h3 class="ui header">
                    <i class="configure icon"></i>
                    <div class="content">
                        refactor required
                    </div>
                </h3>
                <p>
                    There's currently a hard dependency on gulp with <code>steak:build</code> and <code>steak:serve</code>
                    as required tasks in your source's gulpfile.
                </p>
            </div>

            @highlight
            // todo - the rest of these docs...
            @endhighlight

        </div>
    </div>

    <div class="ui vertical segment">

        <div class="ui text container">

            <div class="ui big header">So what exactly <em>is</em> <code>steak</code>?</div>

            <div class="ui dividing sub header">1) a git wrapper</div>

            <p>
                <code>steak pull</code> and <code>steak deploy</code> provide convenient aliases
                for cloning your project's site
                sources<span class="help popup" data-content="(if not kept in the main project repository)">*</span>
                and pushing the latest build respectively.
                And by specifying the relevant branches/repositories in a tracked <code>steak.yml</code>
                configuration file, you can cut down on documenting how to contribute to the documentation.
            </p>

            <div class="ui dividing sub header">2) <span class="acronym">PHP</span> and gulp build system</div>

            <p>
                <code>steak build</code> takes a set of PHP files and runs them through a simple
                but customisable build pipeline. In addition, it executes a <code>gulp steak:build</code> command
                (soon to be an arbitrary build command) to copy across any static assets, compile
                <span class="acronym">SASS</span>, or whatever else you may need.
            </p>

            <div class="ui big header">And what steak is <em>not</em></div>
            <p>
                Behind the scenes, steak uses the <a href="https://github.com/illuminate/view">View</a> component from
                Laravel. It comes pre-configured with the <a href="https://laravel.com/docs/5.2/blade">Blade</a> templating
                engine and a <a href="https://github.com/michelf/php-markdown">Markdown engine</a> to get you started.
                The real aim of <code>steak</code> - however - is not so much a complete publishing solution akin to Jekyll,
                but rather providing a way of generating a static site for your project using your preferred tools and
                existing knowledge.
            </p>
        </div>

    </div>

    <div class="ui inverted vertical footer segment">
        <div class="ui container">

            <a href="https://github.com/parsnick/steak" class="ui inverted large right floated button">
                <i class="github icon"></i>
                View on GitHub
            </a>

            <div class="ui large inverted horizontal divided link list">
                <a class="active item">Overview</a>
                <a href="quick-start.html" class="item">Quick start</a>
                <a href="configuration.html" class="item">Configuration</a>
                <a href="extending-steak.html" class="item">Extending steak</a>
            </div>

        </div>
    </div>
@stop




@push('scripts')

@endpush