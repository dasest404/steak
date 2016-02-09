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
            ->setDescription('Shows current steak status')
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
                ['<b>Builds</b>'],
                [' Last build time', $this->formatLastBuildStatus()],
                [' Last source modified', $this->getSourceLastModifiedTime()->diffForHumans()],
                [''],
                ['<b>Directories</b>'],
                [' Sources', $config['source']],
                [' Output', $this->formatDirectoryState($config['output'])],
                [' Blade cache', $this->formatDirectoryState($config['cache'])],
                [''],
                ['<b>Git</b>'],
                [' Source', $this->formatSourceGitStatus()],
                [' Output', $this->formatOutputGitStatus()],
                [''],
                ['<b>Gulp</b>'],
                [' Binary', $config['gulp.bin']],
                [' Gulpfile', $config['gulp.file']],
                [''],
                ['<b>Build pipe</b>', implode("\n", $config['pipeline'])],
                [''],
                ['<b>Bootstrap</b>', $config['bootstrap']],
            ])
        ;

        $table->render();
    }

    /**
     * @return string
     */
    protected function formatLastBuildStatus()
    {
        $outputDir = $this->container['config']['output'];

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
        $sourceDir = $this->container['config']['source'];

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
     * @return mixed
     */
    protected function formatSourceGitStatus()
    {
        $sourceDir = $this->container['config']['source'];

        $repo = (new GitWrapper())->workingCopy($sourceDir);

        if ( ! $repo->isCloned()) {
            return "<comment>no repository found</comment>";
        }

        // @todo
        return '';
    }

    /**
     * @return string
     */
    protected function formatOutputGitStatus()
    {
        $outputDir = $this->container['config']['output'];

        $repo = (new GitWrapper())->workingCopy($outputDir);

        if ( ! $repo->isCloned()) {
            return "<comment>no repository found</comment>";
        }

        // @todo
        return '';
    }
}
