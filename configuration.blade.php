@extends('_layouts.docs')

@section('content')

    <h1 class="ui huge header">Configuration</h1>

    <p>
        <code>steak</code> supports various configuration options in your <code>steak.yml</code>, described below.
        You might also want to take a look at <code>steak</code>'s own
        <a href="https://github.com/parsnick/steak/blob/master/steak.yml"><code>steak.yml</code></a> to see how
        this site was built.
    </p>

    <div class="ui code segment">
@highlight('yaml')
#---------------------------------------------------------------------
# This is an example configuration file that shows all the available
# options. You can use the `steak init` command to set most of these
# values but for advanced usage, you might need to make manual changes.
#---------------------------------------------------------------------

#
# Source files (blade, markdown, etc.)
#
source:

    # This is the directory from which steak will build your site.
    directory: docs

    # If you want to keep your source files under version control, you can set
    # the git repository and branch to use.
    #
    # This is only required if you want to keep the sources in a *different*
    # repo/branch to the main project. Otherwise, you can leave this blank
    # and just commit the sources as part of your normal git workflow.
    git:
        url: 'https://github.com/parsnick/steak.git'
        branch: gh-pages-src


#
# The build takes the source.directory and creates a static HTML site.
# Configure it here.
#
build:

    # This is the directory where the generated static HTML files will be saved.
    directory: build

    # The build pipeline itself is similar to middleware in Laravel.
    # 'skip' and 'compile' are registered bindings in the container, used
    # only for convenience. You can also use a FQCN.
    #
    # Source files are sent along the pipeline, and each handler can process
    # the file itself and/or pass it to the next step.
    #
    # The ':' denotes arguments to send alongside the source files to the handler
    # Here, we want to skip patterns matching "_*" and "node_modules" and compile
    # files with "php" and "md" extensions.
    pipeline: [ skip:_*,node_modules, compile:php,md ]

#---------------------------------------------------------------------
# The rest of this file is optional - source.directory and build.directory
# are the only required values.
#---------------------------------------------------------------------

#
# `steak deploy` builds your site and pushes to the repository and
# branch specified here. For github pages, use the gh-pages branch.
#
deploy:
    git:
        url: 'https://github.com/parsnick/steak.git'
        branch: gh-pages


#
# The gulp taskrunner is responsible for
#   1. copying static assets from source.directory to build.directory
#   2. running the development server and rebuilding the site on file change
#
gulp:

    # If you need to use a different binary to run gulp, set it here.
    bin: gulp

    # Location of the gulpfile that provides steak:publish and steak:serve
    # tasks, relative to your source.directory.
    file: gulpfile.js


#
# `steak serve` can publish your site in a subdirectory.
# This is mainly useful for sites destined for github pages, where the
# webroot will be http://username.github.io/projectname/
# To help avoid any issues with relative paths, you can emulate this
# behaviour by having steak also serve from a /projectname/ directory.
#
serve:
    subdirectory: steak

#
# The bootstrapper is used to set up bindings with Laravel's container.
# For customising steak, you'll likely want to add something here...
#
# It can be a single value or an array of multiple bootstrap classes,
# with each value being a FQCN for a class that implements
# Parsnick\Steak\Boot\Bootable
#
bootstrap: My\Custom\Bootstrap

@endhighlight
    </div>

    <br>

    <p>
        For more on the <code>bootstrap</code> option, check out the section on
        <a href="extending-steak.html">extending steak</a>.
    </p>

@stop
