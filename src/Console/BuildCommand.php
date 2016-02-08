<?php

namespace Parsnick\Steak\Console;

use Closure;
use Parsnick\Steak\Builder;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
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
        $timer->start('task');

        $timer->start('clean');
        $this->files->cleanDirectory($dest);
        $cleanTime = $timer->stop('clean');

        if ($output->isVerbose()) {
            $output->writeln("<comment>Cleaned <path>{$dest}</path> in <time>{$cleanTime->getDuration()}ms</time>.</comment>");
        }

        $timer->start('build');
        $this->builder->build($src, $dest);
        $buildTime = $timer->stop('build');

        if ($output->isVerbose()) {
            $output->writeln("<comment>PHP built in <time>{$buildTime->getDuration()}ms</time>.</comment>");
        }

        if ($output->isVeryVerbose()) {
            $output->writeln("<comment>Starting gulp...</comment>");
        }

        $timer->start('gulp');
        $this->runGulp($src, $dest, function ($type, $buffer) use ($output) {
            if ($type === Process::ERR) {
                $output->writeln("<error>$buffer</error>");
            } elseif ($output->isVeryVerbose()) {
                $output->write($buffer);
            }
        });
        $gulpTime = $timer->stop('gulp');

        if ($output->isVerbose()) {
            $output->writeln("<comment>gulp published in <time>{$gulpTime->getDuration()}ms</time>.</comment>");
        }

        $total = $timer->stop('task');

        $output->writeln("<info>Done in <time>{$total->getDuration()}ms</time>.</info>");
    }

    /**
     * Run the gulp steak:publish task to compile or copy any non-php files.
     *
     * @param string $src
     * @param string $dest
     * @param Closure $callback
     */
    protected function runGulp($src, $dest, Closure $callback)
    {
        ProcessBuilder::create([
                $this->config->get('gulp.bin', 'gulp'),
                $this->config->get('gulp.task', 'steak:publish'),
                '--source', $src,
                '--dest', $dest,
                '--gulpfile', $this->config->get('gulp.file', 'gulpfile.js'),
                '--cwd', getcwd(),
                '--color',
            ])
            ->getProcess()
            ->mustRun($callback);
    }
}