<?php

namespace Parsnick\Steak\Console;

use Illuminate\Filesystem\Filesystem;
use Parsnick\Steak\Builder;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class BuildCommand extends Command
{
    /**
     * @var Builder
     */
    protected $builder;

    /**
     * Create a new BuildCommand instance.
     *
     * @param Builder $builder
     */
    public function __construct(Builder $builder)
    {
        $this->builder = $builder;

        parent::__construct();
    }

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Builds the static HTML site');
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
        $src = $this->container['config']['source'];
        $dest = $this->container['config']['output'];

        $output->writeln("<info>Compiling <path>{$src}</> into <path>{$dest}</></info>");

        $timer = new Stopwatch();
        $timer->start('task');

        $timer->start('clean');
        $this->container->make(Filesystem::class)->cleanDirectory($dest);
        $cleanTime = $timer->stop('clean');

        $output->writeln("<comment>Cleaned <path>{$dest}</path> in <time>{$cleanTime->getDuration()}ms</time>.</comment>", $output::VERBOSITY_VERBOSE);

        $timer->start('build');
        $this->builder->build($src, $dest);
        $buildTime = $timer->stop('build');

        $output->writeln("<comment>PHP built in <time>{$buildTime->getDuration()}ms</time>.</comment>", $output::VERBOSITY_VERBOSE);

        $output->writeln("<comment>Starting gulp...</comment>", $output::VERBOSITY_VERY_VERBOSE);

        $timer->start('gulp');
        $this->createGulpProcess('steak:publish')
             ->mustRun($this->getProcessLogger($output));
        $gulpTime = $timer->stop('gulp');

        $output->writeln("<comment>gulp published in <time>{$gulpTime->getDuration()}ms</time>.</comment>", $output::VERBOSITY_VERBOSE);

        $total = $timer->stop('task');

        $output->writeln("<info>Done in <time>{$total->getDuration()}ms</time>.</info>");
    }
}