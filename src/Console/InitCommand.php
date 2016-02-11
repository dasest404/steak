<?php

namespace Parsnick\Steak\Console;

use Closure;
use GitWrapper\GitException;
use GitWrapper\GitWorkingCopy;
use GitWrapper\GitWrapper;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Dumper;

class InitCommand extends Command
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
     * @var Dumper
     */
    protected $yaml;

    /**
     * @var string
     */
    protected $banner = <<<'ASCI'
      _             _
  ___| |_ ___  __ _| | __
 / __| __/ _ \/ _` | |/ /
 \__ \ ||  __/ (_| |   <
 |___/\__\___|\__,_|_|\_\
ASCI;


    /**
     * Create a new DeployCommand instance.
     *
     * @param GitWrapper $git
     * @param Filesystem $files
     * @param Dumper $yaml
     */
    public function __construct(GitWrapper $git, Filesystem $files, Dumper $yaml)
    {
        $this->git = $git;
        $this->files = $files;
        $this->yaml = $yaml;

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
            ->setName('init')
            ->setDescription('Sets up the project for steak')
            ->addOption('generate', 'g', InputOption::VALUE_OPTIONAL, 'Path to the config file to save', 'steak.yml')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Display the generated config and prompt before saving')
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

        $outputFile = $input->getOption('generate');

        $output->writeln("<info>Generating your {$outputFile}...</info>");
        $output->writeln("<comment>{$this->banner}\n</comment>");

        $generated['source'] = $this->setupSources();
        $generated['build'] = $this->setupBuild();
        $generated['deploy'] = $this->setupDeploy();
        $generated['gulp'] = $this->setupGulp();
        $generated['serve'] = $this->setupServe($generated);

        $yaml = $this->createYaml($generated);

        if ($input->getOption('dry-run')) {

            $output->writeln("\n<info>Ready to write config file</info>");
            $output->writeln($yaml);

            if ( ! $this->confirm("Write the above configuration to <path>{$outputFile}</path>?")) {
                return $output->writeln('<error>Aborted!</error>');
            }
        }

        if ( ! $this->files->put($outputFile, $yaml)) {
            return $output->writeln('<error>Failed to save config file.</error>');
        }

        $output->writeln("<info>Success! <path>{$outputFile}</path> written.</info>");

        if ( ! empty($generated['source']['git.url'])) {
            $output->writeln("Try a <comment>steak pull</comment> next to fetch your sources...");
        } else {
            $output->writeln("Try <comment>steak serve</comment> to start building your site...");
        }
    }

    /**
     * Get the config values related to steak sources.
     *
     * @return array
     */
    protected function setupSources()
    {
        /** @var Repository $config */
        $config = $this->container['config'];
        $cwd = getcwd();
        $source = [];

        $this->output->writeln("Working directory is <path>{$cwd}</path>");

        $this->title('Sources');

        if ($this->isGitRepo('.')) {

            $this->output->writeln("It looks your project already has a git repository.\n");

            if ($this->confirm('Would you like to store your steak sources in a <b>separate</b> repo/branch?', false)) {

                $source['git.root'] = $this->ask(
                    "Where should we check out the site sources <b>repository</b>?",
                    $config->get('source.git.root', 'steak')
                );
                $source['directory'] = $this->ask(
                    "Where should we look for the <b>source files</b> that make up the site?",
                    $this->guessSourceSubdirectory($config['source.directory'], $source['git.root'])
                );
                // @todo if the sources repo already exists, try reading values from there instead of config?
                $source['git.url'] = $this->ask(
                    "Enter the source repository URL:",
                    $config->get('source.git.url', $this->getPushUrl('.'))
                );
                $source['git.branch'] = $this->ask(
                    "Specify the branch to use:",
                    $config->get('source.git.branch', 'gh-pages-src')
                );

            } else { // not using a separate source vcs

                $this->output->writeln([
                    "  Okay, no problem, just commit your steak sources as part of your standard workflow.",
                    "  The <comment>steak pull</comment> command will have no effect.",
                    ""
                ]);

                $source['directory'] = $this->ask(
                    "Where are the <b>source files</b> kept?",
                    $config->get('source.directory', 'source')
                );
            }

        } else { // not running inside a git repo

            $this->output->writeln("working directory not under git", OutputInterface::VERBOSITY_VERBOSE);

            if ($this->confirm('Would you like to store your steak sources in a git repo?')) {

                $source['git.root'] = $this->ask(
                    "Where should we check out the site sources <b>repository</b>?",
                    $config->get('source.git.root', 'steak')
                );
                $source['directory'] = $this->ask(
                    "Where should we look for the <b>source files</b> that make up the site?",
                    $this->guessSourceSubdirectory($config['source.directory'], $source['git.root'])
                );
                $source['git.url'] = $this->ask(
                    "Enter the source repository URL:",
                    $config->get('source.git.url'),
                    $this->valueRequiredValidator()
                );
                $source['git.branch'] = $this->ask(
                    "Specify the branch to use:",
                    $config->get('source.git.branch', 'master')
                );

            } else {

                $this->output->writeln("  Okay, no git.");

                $source['directory'] = $this->ask(
                    "Where to put the steak <b>source files</b>?",
                    $config->get('source.directory', 'sources')
                );

            }
        }

        return $source;
    }

    /**
     * Get the config values related to the build process.
     *
     * @return array
     */
    protected function setupBuild()
    {
        /** @var Repository $config */
        $config = $this->container['config'];

        $build = [];

        $this->title('Build');

        $build['directory'] = $this->ask("Where should we put the generated site files?", $config['build.directory']);

        return $build;
    }

    /**
     * Get the config values related to deployment.
     *
     * @return array
     */
    protected function setupDeploy()
    {
        /** @var Repository $config */
        $config = $this->container['config'];

        $this->title('Deployment via Git');

        $deploy = [];

        if ($this->confirm('Push the generated static site to a git repository?')) {

            $deploy['git.url'] = $this->ask(
                "Specify a destination repository for the <path>steak deploy</path> command to use:",
                $config->get('deploy.git.url', $this->getPushUrl('.')),
                $this->valueRequiredValidator()
            );

            $deploy['git.branch'] = $this->ask(
                "Specify the branch to use:",
                $config->get('deploy.git.branch', 'gh-pages')
            );

        } else {

            $this->output->writeln([
                "  Okay, no problem, steak will not attempt deployments.",
                "  The <comment>steak deploy</comment> command will have no effect.",
            ]);

        }

        return $deploy;
    }

    /**
     * Get the config values related to gulp.
     *
     * @return array
     */
    protected function setupGulp()
    {
        /** @var Repository $config */
        $config = $this->container['config'];

        $this->title('Gulp');

        $gulp = [];

        $this->output->writeln([
            "steak uses the gulp taskrunner to:",
            "  1. publish non-PHP files from your source directory to the build directory with <comment>steak:publish</comment>",
            "  2. run the local development server and rebuild the site on change with <comment>steak:serve</comment>",
        ]);

        $gulp['file'] = $this->ask("Which gulpfile should steak use?", $config['gulp.file']);

        return $gulp;
    }

    /**
     * Get the config values related to the development server.
     *
     * @param array $newConfig
     * @return array
     */
    protected function setupServe(array $newConfig)
    {
        $newConfig = array_dot($newConfig);

        if (array_get($newConfig, 'deploy.git.branch') != 'gh-pages') {
            return [];
        }

        $this->title('Local development server');

        $this->output->writeln([
            "When you publish to github pages, your site is available from username.github.io/projectname",
            "To help avoid problems with relative URLs, use a matching subdirectory for the local server.",
            "",
        ]);

        return [
            'subdirectory' => $this->ask(
                "Subdirectory to use for the <comment>steak serve</comment> command?",
                $this->git->parseRepositoryName(array_get($newConfig, 'deploy.git.url'))
            )
        ];
    }


    /**
     * Ask a question.
     *
     * @param string $question
     * @param null|string $default
     * @param Closure $validator
     * @return string
     */
    protected function ask($question, $default = null, Closure $validator = null)
    {
        if ($default) {
            $question .= " [$default]";
        }

        $question = new Question($question . "\n > ", $default);

        if ($validator) {
            $question->setValidator($validator);
        }

        return trim($this->getHelper('question')->ask($this->input, $this->output, $question));
    }

    /**
     * Ask for user confirmation.
     *
     * @param string $text
     * @param bool $default
     * @return bool
     */
    protected function confirm($text, $default = true)
    {
        $choices = $default ? 'Yn' : 'yN';
        $confirm = new ConfirmationQuestion("$text [$choices] ", $default);

        return $this->getHelper('question')->ask($this->input, $this->output, $confirm);
    }

    /**
     * Ask user to choose from a list of options.
     *
     * @param string $text
     * @param array $choices
     * @param mixed $default
     * @return mixed
     */
    protected function select($text, $choices, $default = null)
    {
        $question = new ChoiceQuestion($text, $choices, $default);

        return $this->getHelper('question')->ask($this->input, $this->output, $question);
    }

    /**
     * Print a title.
     *
     * @param $string
     */
    protected function title($string)
    {
        $this->output->writeln('');
        $this->output->writeln("<b>$string</b>");
    }

    /**
     * Get the origin's push URL.
     *
     * @param string $dir
     * @param bool $throws
     * @return null|string
     */
    protected function getPushUrl($dir, $throws = false)
    {
        try {
            $remotes = $this->git->workingCopy($dir)->remote('show', 'origin',  '-n')->getOutput();

            if (preg_match('!Push\s*URL:\s*([^\s#]+)!', $remotes, $matches)) {
                return $matches[1];
            }
        } catch (GitException $exception) {
            if ($throws) {
                throw $exception;
            }
        }

        return null;
    }

    /**
     * Create a YML string from a dotted array.
     *
     * @param array $config
     * @return string
     */
    protected function createYaml(array $config)
    {
        $nested = [];

        array_walk(array_dot($config), function ($value, $key) use (&$nested) {
            array_set($nested, $key, $value);
        });

        return $this->yaml->dump($nested, 4);
    }


    /**
     * Check if a directory exists.
     *
     * @param string $dir
     * @return bool
     */
    protected function dirExists($dir)
    {
        if ($this->files->exists($dir)) {

            if ( ! $this->files->isDirectory($dir)) {
                throw new RuntimeException("The given directory [$dir] is not a directory.");
            }

            return true;
        }

        return false;
    }

    /**
     * Check if the directory is the root of a git repository.
     *
     * @param string $dir
     * @return bool
     */
    protected function isGitRepo($dir)
    {
        return $this->git
            ->workingCopy($dir)
            ->isCloned();
    }

    /**
     * Get the current branch name of the repository at $dir.
     *
     * @param string $dir
     * @return string
     */
    protected function getBranchName($dir)
    {
        return trim($this->git
            ->workingCopy($dir)
            ->run(['rev-parse', '--abbrev-ref', 'HEAD'])
            ->getOutput());
    }

    /**
     * Suggest a source.directory when source.git.root already exists.
     *
     * Since source.directory should always be a subdirectory of source.git.root,
     * we check if that is the case before suggesting the existing config value.
     *
     * @param string $existingConfigValue
     * @param string $newVcsRoot
     * @return string
     */
    protected function guessSourceSubdirectory($existingConfigValue, $newVcsRoot)
    {
        if ($existingConfigValue && starts_with($existingConfigValue, $newVcsRoot)) {
            return $existingConfigValue;
        }

        return $newVcsRoot . '/sources';
    }

    /**
     * Get a required-value validator.
     *
     * @param string $message
     * @return Closure
     */
    protected function valueRequiredValidator($message = 'Please enter a value.')
    {
        return function ($value) use ($message) {

            if (empty($value)) {
                throw new RuntimeException($message);
            }

            return $value;
        };
    }
}
