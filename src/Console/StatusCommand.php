<?php

namespace Parsnick\Steak\Console;

use Carbon\Carbon;
use GitWrapper\GitWrapper;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class StatusCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('status')
            ->setDescription('Shows current steak configuration')
        ;
    }

    /**
     * Execute the command.
     *
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->container['config'];
        $table = new Table($output);

        $table
            ->setRows([
                ['<b>Build status</b>'],
                [' Last build time', $this->formatLastBuildStatus()],
                [' Last source modified', $this->getSourceLastModifiedTime()->diffForHumans()],
                [''],
                ['<b>Sources</b>'],
                [' Directory', $this->formatDirectoryState($config['source.directory'])],
                [' Git repository', $this->getSourceRepo()],
                [''],
                ['<b>Builds</b>'],
                [' Output directory', $this->formatDirectoryState($config['build.directory'])],
                [' Pipeline', implode(" | ", $config['build.pipeline'])],
                [''],
                ['<b>Deployment</b>'],
                [' Pushes to', $this->getDeploymentRepo()],
                [''],
                ['<b>Gulp</b>'],
                [' Binary', $config['gulp.bin']],
                [' Gulpfile', $config['source.directory'] . DIRECTORY_SEPARATOR . $config['gulp.file']],
                [''],
                ['<b>Server</b>'],
                [' Relative URL', $config['serve.subdirectory']],
                [''],
                ['<b>Extensions</b>'],
                [' Bootstrap classes', $config['bootstrap'] ?: '(none)']
            ])
        ;

        $table->render();
    }

    /**
     * @return string
     */
    protected function formatLastBuildStatus()
    {
        $outputDir = $this->container['config']['build.directory'];

        $date = Carbon::createFromTimestamp($this->container['files']->lastModified($outputDir));

        if ($date->lt($this->getSourceLastModifiedTime())) {
            return "<error>{$date->diffForHumans()}</error>";
        }

        return "<info>{$date->diffForHumans()}</info>";
    }

    /**
     * @return Carbon
     */
    protected function getSourceLastModifiedTime()
    {
        $sourceDir = $this->container['config']['source.directory'];

        $sourceFiles = iterator_to_array(Finder::create()->in($sourceDir)->files()->sortByModifiedTime(), false);

        $mostRecent = last($sourceFiles);

        return Carbon::createFromTimestamp($this->container['files']->lastModified($mostRecent));
    }

    /**
     * @param $path
     * @return string
     */
    protected function formatDirectoryState($path)
    {
        $files = $this->container['files'];

        if ( ! $files->exists($path)) {
            return "<error>$path</error> <comment>(not found)</comment>";
        }

        if ( ! $files->isDirectory($path)) {
            return "<error>$path</error> <comment>(not a directory)</comment>";
        }

        if ( ! $files->isWritable($path)) {
            return "<error>$path</error> <comment>(not writable)</comment>";
        }

        return "<info>$path</info>";
    }

    /**
     * @return string
     */
    protected function getSourceRepo()
    {
        $config = $this->container['config'];

        if (empty($config['source.git.url'])) {
            return 'not tracked by steak';
        }

        return "{$config['source.git.url']}#{$config['source.git.branch']}";
    }

    /**
     * @return string
     */
    protected function getDeploymentRepo()
    {
        $config = $this->container['config'];

        if (empty($config['deploy.git.url'])) {
            return 'not tracked by steak';
        }

        return "{$config['deploy.git.url']}#{$config['deploy.git.branch']}";
    }
}
