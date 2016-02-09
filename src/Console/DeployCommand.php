<?php

namespace Parsnick\Steak\Console;

use GitWrapper\GitWrapper;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Stopwatch\Stopwatch;

class DeployCommand extends Command
{
    /**
     * @var GitWrapper
     */
    protected $git;

    /**
     * Create a new DeployCommand instance.
     *
     * @param GitWrapper $git
     */
    public function __construct(GitWrapper $git)
    {
        $this->git = $git;

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
            ->setName('deploy')
            ->setDescription('Deploys the generated site')
            ->addOption('no-build', null, InputOption::VALUE_NONE)
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
        if ( ! $input->getOption('no-build')) {
            $this->triggerBuild($input, $output);
        }

        $this->deployWithGit($input, $output);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
     */
    protected function triggerBuild(InputInterface $input, OutputInterface $output)
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

        $output->writeln("<info>Finished build in <time>{$profile->getDuration()}ms</time></info>");
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function deployWithGit(InputInterface $input, OutputInterface $output)
    {
        $config = $this->container['config'];

        $workingCopy = $this->getWorkingCopy($config['output']);

        if ( ! $workingCopy->isCloned()) {
            $output->writeln("<error>{$config['output']} is not a repository</error>");
        }

    }

    /**
     * Get a working copy for the given dir.
     *
     * @param string $dir
     * @return \GitWrapper\GitWorkingCopy
     */
    protected function getWorkingCopy($dir)
    {
        return $this->git->workingCopy($dir);
    }
}
