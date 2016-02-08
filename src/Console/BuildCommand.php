<?php

namespace Parsnick\Steak\Console;

use Closure;
use Illuminate\Filesystem\Filesystem;
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
            ->setDescription('Build the static HTML site.');
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
        $config = $this->container['config'];

        ProcessBuilder::create([
                $config['gulp.bin'],
                $config['gulp.task'],
                '--source', $src,
                '--dest', $dest,
                '--gulpfile', $config['gulp.file'],
                '--cwd', getcwd(),
                '--color',
            ])
            ->getProcess()
            ->mustRun($callback);
    }
}