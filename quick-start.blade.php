@extends('_layouts.docs')

@section('content')

    <h1 class="ui huge header">Quick start</h1>

    <h2 class="ui header">Installation</h2>

    <div class="ui basic segment">
        <div class="ui right rail">
            <div class="ui basic secondary segment">
                This guide assumes familiarity with the <a href="https://getcomposer.org/">Composer</a>
                dependency manager for <span class="acronym">PHP</span>.
            </div>
        </div>

        <p>
            To add <code>steak</code> to an existing or new project, use composer:
        </p>

        <p>
            <code class="terminal">
                composer
                require
                parsnick/steak:<span class="help popup" data-content="There are currently no tagged stable releases of steak, so you may need to be explicit about fetching the dev version.">dev</span>
                --<span class="help popup" data-content="Unless - for some reason - your application relies on steak, you probably want to install as a dev dependency.">dev</span>
            </code>
        </p>

        <p>
            You can then use the <code>steak</code> command as a standard composer-installed binary:
        </p>

        <p>
            <code class="terminal">
                ./vendor/bin/steak [command]
            </code>
        </p>

        <div class="ui inverted segment">
            For brevity, the rest of the docs use <code class="terminal">steak</code> in place of the full
            <code class="terminal">./vendor/bin/steak</code>.
        </div>

    </div>

    <h2 class="ui header">Configuration</h2>

    <div class="ui basic segment">

        <div class="ui right rail">
            <div class="ui basic segment">
                For more on the steak.yml options, read the <a href="configuration.html">configuration section</a>.
            </div>
        </div>

        <p>
            <code>steak</code> uses a <code>steak.yml</code> configuration file in the current working directory,
            although this can be altered with the <code>-c [config_file]</code> option.
        </p>

        <p>
            To get started, run the <code class="terminal">steak init</code> command. This guides you
            through a series of questions and generates the appropriate <code>steak.yml</code> file.
        </p>

    </div>

    <h2 class="ui header">Usage</h2>

    <p>
        <code>steak</code> provides six commands:
    </p>

    <table class="ui celled table">
        <tbody>

        <tr>
            <td><code>init</code></td>
            <td>
                Runs the interactive setup to generate <code>steak.yml</code>, use with <code>--dry-run</code> to
                preview the config file before clobbering an existing one.
            </td>
        </tr>

        <tr>
            <td><code>status</code></td>
            <td>Displays a summary of your steak configuration and the local build.</td>
        </tr>

        <tr>
            <td><code>build</code></td>
            <td>Builds the static site in the output directory.</td>
        </tr>

        <tr>
            <td><code>serve</code></td>
            <td>
                Starts a <a href="https://browsersync.io/">BrowserSync</a> server to view the site with live reloading,
                and starts file watchers to monitor your sources and rebuild on change.
            </td>
        </tr>

        <tr>
            <td><code>pull</code></td>
            <td>
                If a separate git repository is configured for your sources, this clones the source repo to the
                local source directory.
            </td>
        </tr>

        <tr>
            <td><code>deploy</code></td>
            <td>
                If a git repository is configured for deployment, this clones the target repo and rebuilds the site on
                top of it. You'll be asked to confirm the list of changes, at which point all changes are committed and
                pushed.
            </td>
        </tr>

        </tbody>
    </table>

    <p>
        You can view the options signature for any command with
        <code class="terminal">steak help [command]</code>
    </p>

    <h2 class="ui header">Workflow</h2>

    <ol class="ui list">
        <li class="item">Fetch the latest sources (if applicable) with <code class="terminal">steak pull</code></li>
        <li class="item">Start the local dev server and file watchers with <code class="terminal">steak serve</code></li>
        <li class="item">Make changes to your sources until ready to publish.</li>
        <li class="item">Run <code class="terminal">steak deploy</code> to push the updated site.</li>
    </ol>


@stop
