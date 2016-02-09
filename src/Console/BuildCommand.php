<?php

namespace Parsnick\Steak\Console;

use Illuminate\Filesystem\Filesystem;
use Parsnick\Steak\Builder;
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
            ->addArgument('files', InputArgument::IS_ARRAY, 'Blade files to render.', [])
            ->addOption('no-clean', null, InputOption::VALUE_NONE, 'Skip cleaning of the output folder.')
            ->addOption('no-gulp', null, InputOption::VALUE_NONE, 'Skip running the gulp script.')
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
        $src = $this->container['config']['source'];
        $dest = $this->container['config']['output'];

        $output->writeln("<info>Compiling <path>{$src}</> into <path>{$dest}</></info>");

        $timer = new Stopwatch();
        $timer->start('task');

        if ( ! $input->getOption('no-clean')) {
            $timer->start('clean');
            $this->container->make(Filesystem::class)->cleanDirectory($dest);
            $cleanTime = $timer->stop('clean');

            $output->writeln("<comment>Cleaned <path>{$dest}</path> in <time>{$cleanTime->getDuration()}ms</time>.</comment>", $output::VERBOSITY_VERBOSE);
        }

        $timer->start('build');
        $this->builder->build($input->getArgument('files') ?: $src, $dest);
        $buildTime = $timer->stop('build');

        $output->writeln("<comment>PHP built in <time>{$buildTime->getDuration()}ms</time>.</comment>", $output::VERBOSITY_VERBOSE);

        if ( ! $input->getOption('no-gulp')) {

            $output->writeln("<comment>Starting gulp...</comment>", $output::VERBOSITY_VERY_VERBOSE);

            $process = $this->createGulpProcess('steak:publish');
            $callback = $this->getProcessLogger($output);

            $timer->start('gulp');

            try {

                $process->mustRun($callback);

                $output->writeln(
                    "<comment>gulp published in <time>{$timer->stop('gulp')->getDuration()}ms</time>.</comment>",
                    $output::VERBOSITY_VERBOSE
                );

            } catch (ProcessFailedException $exception) {

                $output->writeln(
                    "<error>gulp process failed after <time>{$timer->stop('gulp')->getDuration()}ms</time>.</error>",
                    $output::VERBOSITY_VERBOSE
                );

                if (str_contains($process->getOutput(), 'Local gulp not found')) {

                    $output->writeln("<comment>Local gulp not found, attempting install. This might take a minute...</comment>");
                    $output->writeln('  <comment>$</comment> npm install');

                    try {
                        $timer->start('npmInstall');

                        (new Process('npm install'))->setTimeout(120)->mustRun();

                        $output->writeln("<comment>Done in <time>{$timer->stop('npmInstall')->getDuration()}</time></comment>");

                        $output->writeln('Retrying <comment>steak:publish</comment> task...');

                        $process->mustRun($callback);

                    } catch (RuntimeException $exception) {
                        $output->writeln("<error>npm install</error> failed - try a manual install?");
                    }
                }

            }

        }

        $total = $timer->stop('task');

        $output->writeln("<info>Done in <time>{$total->getDuration()}ms</time>.</info>");
    }
}
