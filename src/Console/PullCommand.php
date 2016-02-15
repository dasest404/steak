<?php

namespace Parsnick\Steak\Console;

use Carbon\Carbon;
use GitWrapper\GitException;
use GitWrapper\GitWorkingCopy;
use GitWrapper\GitWrapper;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class PullCommand extends Command
{
    /**
     * @var GitWrapper
     */
    protected $git;

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new PullCommand instance.
     *
     * @param GitWrapper $git
     * @param Filesystem $files
     */
    public function __construct(GitWrapper $git, Filesystem $files)
    {
        $this->git = $git;
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
            ->setName('pull')
            ->setDescription('Fetches the site sources')
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

        /** @var Repository $config */
        $config = $this->container['config'];

        $sourceRepoPath = $config['source.directory'];
        $sourceRepoUrl = $config['source.git.url'];
        $sourceRepoBranch = $config['source.git.branch'];

        $this->checkSourceRepoSettings($sourceRepoPath, $sourceRepoUrl);

        $output->writeln([
            "<b>steak pull configuration:</b>",
            " Source repository remote is <path>{$sourceRepoUrl}</path>",
            " Source repository branch is <path>{$sourceRepoBranch}</path>",
            " Path to local repository is <path>{$sourceRepoPath}</path>",
        ], OutputInterface::VERBOSITY_VERBOSE);

        if ($this->files->exists($sourceRepoPath)) {

            $workingCopy = $this->git->workingCopy($sourceRepoPath);

            if ( ! $workingCopy->isCloned()) {
                throw new RuntimeException("<path>{$sourceRepoPath}</path> exists but is not a git repository.");
            }

            if ($workingCopy->getBranches()->head() != $sourceRepoBranch) {
                throw new RuntimeException("<path>{$sourceRepoPath}</path> exists but isn't on the <path>{$sourceRepoBranch}</path> branch.");
            }

            $this->git->streamOutput();
            $workingCopy->pull();

        } else {
            $output->writeln([
                "The source directory <path>$sourceRepoPath</path> does not exist.",
                "  Attempting clone from {$sourceRepoUrl}",
            ]);

            $this->git->streamOutput();
            $this->git->cloneRepository($sourceRepoUrl, $sourceRepoPath, [
                'single-branch' => true,
                'branch' => $sourceRepoBranch
            ]);

            $output->writeln("<info>Clone complete! Edit your sources in <path>{$sourceRepoPath}</path></info>");
        }

        $output->writeln("Try <comment>steak serve</comment> to fire up the local development server...");
    }

    /**
     * @param $sourceRepoPath
     * @param $sourceRepoUrl
     */
    protected function checkSourceRepoSettings($sourceRepoPath, $sourceRepoUrl)
    {
        if (empty($sourceRepoPath) || empty($sourceRepoUrl)) {
            throw new RuntimeException('No source repository configured. Update your config file or run steak init.');
        }
    }
}
