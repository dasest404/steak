<?php

namespace Parsnick\Steak\Console;

use Illuminate\Container\Container;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
}