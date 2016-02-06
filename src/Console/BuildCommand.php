<?php

namespace Parsnick\Steak\Console;

use Illuminate\Filesystem\Filesystem;
use Parsnick\Steak\Builder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Yaml\Yaml;

class BuildCommand extends Command
{
    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new BuildCommand instance.
     *
     * @param Builder $builder
     * @param Config $config
     * @param Filesystem $files
     */
    public function __construct(Builder $builder, Config $config, Filesystem $files)
    {
        $this->builder = $builder;
        $this->config = $config;
        $this->files = $files;

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
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'path to configuration file', 'steak.yml');
    }

    /**
     * Initializes the command just after the input has been validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getOption('config');

        if ($this->files->exists($path)) {

            $output->writeln("<info>Reading config from {$path}</info>");

            $this->config->import(
                Yaml::parse($this->files->get($path))
            );
        }
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

        $output->writeln("<info>Compiling <bg=yellow>{$src}</> into <bg=yellow>{$dest}</></info>");

        $timer = new Stopwatch();
        $timer->start('build');

        $this->builder->build($src, $dest);

        $profile = $timer->stop('build');

        $output->writeln("<comment>Built in {$profile->getDuration()}ms.</comment>");
    }
}