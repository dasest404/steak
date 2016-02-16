<?php

namespace Parsnick\Steak\Console;

use Closure;
use Illuminate\Filesystem\Filesystem;
use Parsnick\Steak\Build\Builder;
use Parsnick\Steak\Cleaner;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;
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
            ->setDescription('Builds the static HTML site')
            ->addArgument('files', InputArgument::IS_ARRAY, 'Blade file(s) to render', [])
            ->addOption('no-clean', null, InputOption::VALUE_NONE, 'Skip cleaning of the output folder')
            ->addOption('no-gulp', null, InputOption::VALUE_NONE, 'Skip running the gulp script for static assets')
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
        $this->setIo($input, $output);

        $sourceDir = $this->container['config']['source.directory'];
        $outputDir = $this->container['config']['build.directory'];

        $output->writeln("<info>Compiling <path>{$sourceDir}</path> into <path>{$outputDir}</path></info>");

        $total = $this->runTimedTask(function () use ($sourceDir, $outputDir) {

            if ( ! $this->input->getOption('no-clean')) {
                $this->runCleanTask($outputDir);
            }

            $this->runBuildTask($sourceDir, $outputDir);

            if ( ! $this->input->getOption('no-gulp')) {
                $this->runGulpTask();
            }

        });

        $output->writeln("<info>Built in <time>{$total}ms</time></info>");
    }

    /**
     * Clean the output build directory.
     *
     * @param string $outputDir
     */
    protected function runCleanTask($outputDir)
    {
        $cleanTime = $this->runTimedTask(function () use ($outputDir) {
            $this->builder->clean($outputDir);
        });

        $this->output->writeln(
            "<comment>Cleaned <path>{$outputDir}</path> in <time>{$cleanTime}ms</time></comment>",
            OutputInterface::VERBOSITY_VERBOSE
        );
    }

    /**
     * Build the new site pages.
     *
     * @param string $sourceDir
     * @param string $outputDir
     */
    protected function runBuildTask($sourceDir, $outputDir)
    {
        $buildTime = $this->runTimedTask(function () use ($sourceDir, $outputDir) {
            $this->builder->build($sourceDir, $outputDir);
        });

        $this->output->writeln(
            "<comment>PHP built in <time>{$buildTime}ms</time></comment>",
            OutputInterface::VERBOSITY_VERBOSE
        );
    }

    /**
     * Trigger gulp to copy other assets to the build dir.
     */
    protected function runGulpTask()
    {
        $this->output->writeln("<comment>Starting gulp...</comment>", OutputInterface::VERBOSITY_VERY_VERBOSE);

        $process = $this->createGulpProcess('steak:build');
        $callback = $this->getProcessLogger($this->output);

        $timer = new Stopwatch();
        $timer->start('gulp');

        try {

            $process->mustRun($callback);

            $this->output->writeln(
                "<comment>gulp published in <time>{$timer->stop('gulp')->getDuration()}ms</time></comment>",
                OutputInterface::VERBOSITY_VERBOSE
            );

        } catch (ProcessFailedException $exception) {

            $this->output->writeln(
                "<error>gulp process failed after <time>{$timer->stop('gulp')->getDuration()}ms</time></error>",
                OutputInterface::VERBOSITY_VERBOSE
            );

            if (str_contains($process->getOutput(), 'Local gulp not found') || str_contains($process->getErrorOutput(), 'Cannot find module')) {

                $this->output->writeln("<comment>Missing npm dependencies, attempting install. This might take a minute...</comment>");
                $this->output->writeln('  <comment>$</comment> npm install', OutputInterface::VERBOSITY_VERBOSE);

                try {
                    $npmInstallTime = $this->runTimedTask(function () {
                        (new Process('npm install', $this->container['config']['source.directory']))->setTimeout(180)->mustRun();
                    });

                    $this->output->writeln("  <comment>npm installed in in <time>{$npmInstallTime}ms</time></comment>", OutputInterface::VERBOSITY_VERBOSE);

                    $this->output->writeln('<comment>Retrying <b>steak:publish</b> task...</comment>');

                    $process->mustRun($callback);

                } catch (RuntimeException $exception) {
                    $this->output->writeln("We tried but <error>npm install</error> failed");
                }
            }

        }
    }
}
