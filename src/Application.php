<?php

namespace Parsnick\Steak;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Illuminate\Filesystem\Filesystem;
use Parsnick\Steak\Boot\ConfigureViewEngines;
use Parsnick\Steak\Boot\RegisterBladeExtensions;
use Parsnick\Steak\Boot\RunBootstrapsFromConfig;
use Parsnick\Steak\Boot\RegisterCoreBindings;
use Symfony\Component\Console\Application as SymfonyApplication;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Yaml\Yaml;

class Application
{
    /**
     * @var SymfonyApplication
     */
    protected $symfony;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var ConsoleOutput
     */
    protected $output;

    /**
     * @var array
     */
    protected $bootClasses = [
        RegisterCoreBindings::class,
        ConfigureViewEngines::class,
        RegisterBladeExtensions::class,
        RunBootstrapsFromConfig::class,
    ];

    /**
     * Create a new steak application.
     *
     * @param string $version
     * @param Container $container
     */
    public function __construct($version, Container $container = null)
    {
        $this->symfony = new SymfonyApplication('RegisterCoreBindings', $version);
        $this->container = $container ?: new Container();

        $this->container->instance('app', $this);

        $this->configureOutput();
        $this->loadExternalConfig();
        $this->bootstrap();
    }

    /**
     * Configure the console output with custom styles.
     */
    protected function configureOutput()
    {
        $this->output = new ConsoleOutput();

        $this->output->getFormatter()->setStyle('path', new OutputFormatterStyle('green', null, ['bold']));
        $this->output->getFormatter()->setStyle('time', new OutputFormatterStyle('cyan', null, ['bold']));
        $this->output->getFormatter()->setStyle('b', new OutputFormatterStyle(null, null, ['bold']));
    }


    /**
     * Load the external config files specified by the command line option.
     */
    protected function loadExternalConfig()
    {
        $config = new Repository(static::getDefaultConfig());
        $filesystem = new Filesystem();

        foreach ($this->getConfigFiles($filesystem) as $filename) {

            $this->output->writeln("<info>Reading config from <path>{$filename}</path></info>");

            if ($filesystem->extension($filename) == 'php') {
                $configValues = $filesystem->getRequire($filename);
            } else {
                $configValues = Yaml::parse($filesystem->get($filename));
            }

            $config->set(array_dot($configValues));
        }

        $this->container->instance('config', $config);
    }

    /**
     * Parse the command line option for config file to use.
     *
     * Multiple files can be given as a comma-separated list.
     * If no option is given, defaults as used.
     *
     * @param Filesystem $files
     * @param array $defaults
     * @return array
     */
    protected function getConfigFiles($files, array $defaults = ['steak.yml', 'steak.php'])
    {
        $option = (new ArgvInput())->getParameterOption(['--config', '-c']);

        if ( ! $option) {
            foreach ($defaults as $default) {
                if ($files->exists($default)) {
                    $option = ($option ? ',' : '') . $default;
                }
            }
        }

        return $option ? explode(',', $option) : [];
    }

    /**
     * Bootstrap the application.
     *
     * @return bool
     */
    protected function bootstrap()
    {
        return array_walk($this->bootClasses, function ($bootstrapClass) {
            $this->container->make($bootstrapClass)->boot($this->container);
        });
    }

    /**
     * Register an array of commands.
     *
     * @param array $commands
     * @return $this
     */
    public function commands(array $commands)
    {
        array_walk($commands, function ($commandClass) {
            $this->registerCommand($commandClass);
        });

        return $this;
    }

    /**
     * Register a single command.
     *
     * @param string $commandClass
     * @return \Symfony\Component\Console\Command\Command
     */
    public function registerCommand($commandClass)
    {
        $command = $this->container->make($commandClass);

        $command->setContainer($this->container);

        return $this->symfony->add($command);
    }

    /**
     * Run the application.
     *
     * @return int
     * @throws \Exception
     */
    public function run()
    {
        $this->symfony->getDefinition()->addOption(
            new InputOption('config', 'c', InputOption::VALUE_REQUIRED, 'Path to steak configuration file', null)
        );

        return $this->symfony->run(null, $this->output);
    }

    /**
     * Get the default configuration to use when no config file is supplied.
     *
     * @return array
     */
    public static function getDefaultConfig()
    {
        return [
            'source' => [
                'directory' => 'source',
            ],
            'build' => [
                'directory' => 'build',
                'pipeline' => [
                    'skip:_*,node_modules',
                    'compile:php,md',
                ],
            ],
            'gulp' => [
                'bin' => 'gulp',
                'file' => 'gulpfile.js',
            ],
        ];
    }
}
