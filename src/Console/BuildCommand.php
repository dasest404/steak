<?php

namespace Parsnick\Steak\Console;

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
            ->setDescription('Build the static HTML site.')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'path to configuration file', null);
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
        $src = $this->config->get('source', 'source');
        $dest = $this->config->get('output', 'build');

        $output->writeln("<info>Compiling <path>{$src}</> into <path>{$dest}</></info>");

        $timer = new Stopwatch();
        $timer->start('build');

        $this->builder->build($src, $dest);

        $profile = $timer->stop('build');

        $output->writeln("<comment>Built in {$profile->getDuration()}ms.</comment>");
    }
}