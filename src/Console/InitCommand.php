<?php

namespace Parsnick\Steak\Console;

use GitWrapper\GitWorkingCopy;
use GitWrapper\GitWrapper;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
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

        $generated = [];

        $output->writeln("<info>Configuring <b>steak</b>...</info>");

        $this->title('Directories');
        $generated['source'] = trim($this->ask("Where should the site source files be stored?", "{$config['source']}/"), '/');
        $generated['output'] = trim($this->ask("Where should the generated site files be created?", "{$config['output']}/"), '/');

        $this->title('Deployment via Git');
        if ($this->isGitRepo($generated['output'])) {
            $output->writeln(
                "<comment>git repository detected in {$generated['output']}</comment>",
                $output::VERBOSITY_VERBOSE
            );

            // @todo

        } else {
            $output->writeln(
                "<comment>No existing repository detected in output directory <path>{$generated['output']}</path></comment>",
                $output::VERBOSITY_VERBOSE
            );

            $generated['deploy.git'] = $this->ask(
                "Specify a target repository for the <path>steak deploy</path> command to use:",
                $config->get('deploy.git', $this->getPushUrl('.'))
            );
            $generated['deploy.branch'] = $this->ask(
                "Specify the branch to use:",
                $config->get('deploy.branch', 'gh-pages')
            );
        }

        $this->title('Gulp integration');
        $generated['gulp.file'] = $this->ask("Which gulp file should we use?", 'gulpfile.js');

        $yaml = $this->createYaml($generated);

        $output->writeln("\n<comment>Ready to write config file...</comment>");
        $output->writeln($yaml);

        $saveTo = $input->getOption('generate');

        if ($this->confirmGeneration($saveTo)) {

            if ($this->files->put($saveTo, $yaml)) {
                $output->writeln("<info>Success! <path>{$saveTo}</path> written.</info>");
            } else {
                $output->writeln('<error>Failed to save config file.</error>');
            }

        } else {
            $output->writeln('<error>Aborted!</error>');
        }
    }

    /**
     * Ask a question.
     *
     * @param string $question
     * @param null|string $default
     * @return string
     */
    protected function ask($question, $default = null)
    {
        if ($default) {
            $question .= " [$default]";
        }

        $question = new Question($question . "\n> ", $default);

        return trim($this->getHelper('question')->ask($this->input, $this->output, $question));
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
     * Check if a directory is the root of a git repository.
     *
     * @param string $dir
     * @return bool
     */
    protected function isGitRepo($dir)
    {
        return $this->git->workingCopy($dir)->isCloned();
    }

    /**
     * Get the origin's push URL.
     *
     * @param string $dir
     * @return null|string
     */
    protected function getPushUrl($dir)
    {
        $remotes = $this->git->workingCopy($dir)->remote('show', 'origin',  '-n')->getOutput();

        if (preg_match('!Push\s*URL:\s*([^\s#]+)!', $remotes, $matches)) {
            return $matches[1];
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

        array_walk($config, function ($value, $key) use (&$nested) {
            array_set($nested, $key, $value);
        });

        return $this->yaml->dump($nested, 4);
    }

    /**
     * Ask user to confirm YML generation.
     *
     * @param string $file
     * @return bool
     */
    protected function confirmGeneration($file)
    {
        $confirm = new ConfirmationQuestion("Write the above configuration to <path>{$file}</path>? [Yn]");

        return $this->getHelper('question')->ask($this->input, $this->output, $confirm);
    }
}
