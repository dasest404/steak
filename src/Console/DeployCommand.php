<?php

namespace Parsnick\Steak\Console;

use Carbon\Carbon;
use GitWrapper\GitException;
use GitWrapper\GitWorkingCopy;
use GitWrapper\GitWrapper;
use Parsnick\Steak\Builder;
use SplFileInfo;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class DeployCommand extends Command
{
    /**
     * @var GitWrapper
     */
    protected $git;

    /**
     * @var Builder
     */
    protected $builder;

    /**
     * Create a new DeployCommand instance.
     *
     * @param GitWrapper $git
     * @param Builder $builder
     */
    public function __construct(GitWrapper $git, Builder $builder)
    {
        $this->git = $git;
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
            ->setName('deploy')
            ->setDescription('Deploys the generated site')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Push without asking for confirmation')
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

        $repo = $this->prepareRepository();

        $this->rebuild();

        $this->deployWithGit($repo);
    }

    /**
     * Prepare the build directory as a git repository.
     *
     * Maybe there's already a git repo in the build folder, but is it pointing to
     * the correct origin and is it on the correct branch? Instead of checking for
     * things that might be wrong, we'll just start from scratch.
     *
     * @returns GitWorkingCopy
     * @throws RuntimeException
     */
    protected function prepareRepository()
    {
        $config = $this->container['config'];

        $this->builder->clean($config['build.directory']); // clear out everything, including .git metadata

        $workingCopy = $this->git->workingCopy($config['build.directory']);

        $this->doGitInit($workingCopy, $config['deploy.git.url'], $config['deploy.git.branch']); // start again at last commit

        $this->builder->clean($config['build.directory'], true); // clear the old content but keep .git folder

        return $workingCopy;
    }

    /**
     * Set up the git repository for our build.
     *
     * @param GitWorkingCopy $workingCopy
     * @param string $url
     * @param string $branch
     * @return mixed
     */
    protected function doGitInit(GitWorkingCopy $workingCopy, $url, $branch)
    {
        $workingCopy->init();
        $workingCopy->remote('add', 'origin', $url);

        try {

            $this->output->writeln("Attempting to fetch <path>origin/{$branch}</path>", OutputInterface::VERBOSITY_VERBOSE);

            $workingCopy
                ->fetch('origin', $branch)
                ->checkout('-f', $branch);

        } catch (GitException $exception) {

            $this->output->writeln("Fetch failed, creating new branch instead", OutputInterface::VERBOSITY_VERBOSE);

            $workingCopy
                ->checkout('--orphan', $branch)
                ->run(['commit', '--allow-empty', '-m', "Branch created by steak"])
                ->push('-u', 'origin', $branch);

        }

        return $workingCopy->clearOutput();
    }

    /**
     * Delegate to the build command to rebuild the site.
     *
     * @return bool
     */
    protected function rebuild()
    {
        $command = $this->getApplication()->find('build');

        $input = new ArrayInput([
            '--no-clean' => true,
        ]);

        return $command->run($input, $this->output) === 0;
    }

    /**
     * Deploy the current contents of our working copy.
     *
     * @param GitWorkingCopy $workingCopy
     */
    protected function deployWithGit(GitWorkingCopy $workingCopy)
    {
        if ( ! $workingCopy->hasChanges()) {
            return $this->output->writeln('<comment>No changes to deploy!</comment>');
        }

        $this->output->writeln('<info>Ready to deploy changes:</info>');
        $this->output->write($workingCopy->getStatus());

        if ( ! $this->input->getOption('force')) {
            if ( ! $this->askForChangesConfirmation($workingCopy)) {
                return $this->output->writeln('<error>Aborted!</error>');
            }
        }

        $this->output->writeln('<info>Deploying...</info>');

        $this->output->write(
            $workingCopy->add('.')->commit($this->getCommitMessage())->push()->getOutput(),
            OutputInterface::VERBOSITY_VERBOSE
        );
    }

    /**
     * Ask user to confirm changes listed by git status.
     *
     * @param GitWorkingCopy $workingCopy
     * @return bool
     */
    protected function askForChangesConfirmation(GitWorkingCopy $workingCopy)
    {
        $deployTo = "{$this->container['config']['deploy.git.url']}#{$this->container['config']['deploy.git.branch']}";

        $confirm = new ConfirmationQuestion("<comment>Commit all and push to <path>{$deployTo}</path>?</comment> [Yn] ");

        return $this->getHelper('question')->ask($this->input, $this->output, $confirm);
    }

    /**
     * Get the commit message for a deployment.
     *
     * @return string
     */
    protected function getCommitMessage()
    {
        return 'Updated by steak';
    }
}
