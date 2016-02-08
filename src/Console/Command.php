<?php

namespace Parsnick\Steak\Console;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

abstract class Command extends SymfonyCommand
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * Set the IoC container.
     *
     * @param Container $container
     * @return Command
     */
    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Initializes the command just after the input has been validated.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->pullDependencies();
        $this->addConsoleFormatting($output);
        $this->importExternalConfig($this->readConfigPaths($input), $output);
    }

    /**
     * Pull dependencies from container.
     *
     * @return void
     */
    protected function pullDependencies()
    {
        $this->files = $this->container->make(Filesystem::class);
        $this->config = $this->container->make(Repository::class);
    }

    /**
     * Add custom styling tags to console output.
     *
     * @param OutputInterface $output
     */
    protected function addConsoleFormatting(OutputInterface $output)
    {
        $output->getFormatter()->setStyle('path', new OutputFormatterStyle('green', null, ['bold']));
        $output->getFormatter()->setStyle('time', new OutputFormatterStyle('cyan', null, ['bold']));
    }

    /**
     * Load configuration from external files.
     *
     * @param array $files
     * @param OutputInterface $output
     */
    protected function importExternalConfig(array $files, OutputInterface $output)
    {
        foreach ($files as $file) {
            $this->config->set(Yaml::parse($this->files->get($file)));

            $output->writeln("<info>Reading config from <path>{$file}</></info>");
        }
    }

    /**
     * Get location of config file(s) from console input.
     *
     * @param InputInterface $input
     * @return array
     */
    protected function readConfigPaths(InputInterface $input)
    {
        $path = $input->getOption('config');

        if (is_null($path) && $this->files->exists('steak.yml')) {
            $path = 'steak.yml';
        }

        return explode(',', $path);
    }
}