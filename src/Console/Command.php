<?php

namespace Parsnick\Steak\Console;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

abstract class Command extends SymfonyCommand
{
    /**
     * @var Container
     */
    protected $container;

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
     * Create a process builder for the given gulp task.
     *
     * @param string $task
     * @return Process
     */
    protected function createGulpProcess($task)
    {
        $config = $this->container['config'];

        return ProcessBuilder::create([
            $config['gulp.bin'],
            $task,
            '--source', $config['source'],
            '--dest', $config['output'],
            '--gulpfile', $config['gulp.file'],
            '--cwd', getcwd(),
            '--color',
        ])
        ->getProcess();
    }

    /**
     * @param OutputInterface $output
     * @return Closure
     */
    protected function getProcessLogger(OutputInterface $output)
    {
        return function ($type, $buffer) use ($output) {

            if ($type === Process::ERR) {
                $output->writeln("<error>$buffer</error>");
            }

            $output->write($buffer, false, $output::VERBOSITY_VERY_VERBOSE);
        };
    }
}
