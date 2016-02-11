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
use Symfony\Component\Stopwatch\Stopwatch;

abstract class Command extends SymfonyCommand
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Set the IoC container.
     *
     * @param Container $container
     * @return $this
     */
    public function setContainer($container)
    {
        $this->container = $container;

        return $this;
    }

    /**
     * Attach IO to command for easier access between methods.
     *
     * @param  InputInterface  $input
     * @param  OutputInterface  $output
     * @return $this
     */
    protected function setIo(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        return $this;
    }

    /**
     * Create a process builder for the given gulp task.
     *
     * @param string $task
     * @param array $options
     * @return Process
     */
    protected function createGulpProcess($task, array $options = [])
    {
        $config = $this->container['config'];

        return ProcessBuilder::create(array_flatten([
            $config['gulp.bin'],
            $task,
            $options,
            '--source', $config['source.directory'],
            '--dest', $config['build.directory'],
            '--gulpfile', $config['gulp.file'],
            '--phpwd', getcwd(),
            '--color',
        ]))
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
                $output->write($buffer, false, OutputInterface::OUTPUT_RAW);
            }

            $output->write($buffer, false, OutputInterface::VERBOSITY_VERY_VERBOSE | OutputInterface::OUTPUT_RAW);
        };
    }

    /**
     * Get the ms duration of the time taken for $task to execute.
     *
     * @param Closure $task
     * @return int
     */
    protected function runTimedTask(Closure $task)
    {
        $timer = new Stopwatch();
        $timer->start('subtask');

        $task();

        return $timer->stop('subtask')->getDuration();
    }
}
