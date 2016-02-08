<?php

namespace Parsnick\Steak\Console;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class ServeCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('serve')
            ->setDescription('Serves the site and rebuilds on change')
            ->addOption('host', null, InputOption::VALUE_OPTIONAL, 'The host address to serve the site on.', 'localhost')
            ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'The port to serve the site on.', '8001')
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
        $this->triggerInitialBuild($input, $output);

        $this->startGulpWatcher($input, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
     */
    protected function triggerInitialBuild(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<info>Building...</info>", $output::VERBOSITY_VERBOSE);

        $timer = new Stopwatch();
        $timer->start('build');

        $bufferedOutput = new BufferedOutput();
        $exitCode = $this->getApplication()->find('build')->run($input, $bufferedOutput);

        if ($exitCode > 0) {
            $output->writeln("<error>Build command returned {$exitCode}</error>");
            $output->write($bufferedOutput->fetch());
        }

        $profile = $timer->stop('build');

        $output->writeln("<info>Finished initial build in <time>{$profile->getDuration()}ms</time></info>");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function startGulpWatcher(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<info>Starting gulp watcher...</info>", $output::VERBOSITY_VERBOSE);

        $command = $this->createGulpProcess('steak:serve')->getCommandLine();

        $output->writeln("  \$ <comment>$command</comment>", $output::VERBOSITY_VERY_VERBOSE);

        passthru($command);
    }
}