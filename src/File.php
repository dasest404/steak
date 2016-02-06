<?php

namespace Parsnick\Steak;

use Symfony\Component\Finder\SplFileInfo;

class File
{
    /**
     * @var SplFileInfo
     */
    public $source;

    /**
     * @var SplFileInfo
     */
    public $output;

    /**
     * @var string
     */
    public $contents;

    /**
     * Create a new File instance.
     *
     * @param SplFileInfo $source
     * @param string $outputDir
     */
    public function __construct(SplFileInfo $source, $outputDir)
    {
        $this->source = $source;
        $this->contents = $source->getContents();
        $this->output = new SplFileInfo(
            $outputDir.DIRECTORY_SEPARATOR.$source->getRelativePathname(),
            $source->getRelativePath(),
            $source->getRelativePathname()
        );
    }

    /**
     * Change the file extension on the output file.
     *
     * By default, uses same extension as the source.
     *
     * @param string $newExtension
     * @param string|null $existingExtension
     * @return $this
     */
    public function changeExtension($newExtension, $existingExtension = '*')
    {
        $newName = $this->output->getFilename();

        if (ends_with($this->output->getFilename(), $existingExtension)) {
            $newName = $this->output->getBasename($existingExtension).$newExtension;
        }

        if ($existingExtension == '*') {
            $newName = $this->output->getBasename($this->output->getExtension()).$newExtension;
        }

        $this->output = new SplFileInfo(
            $this->output->getPath().DIRECTORY_SEPARATOR.$newName,
            $this->output->getRelativePath(),
            $this->output->getRelativePath().DIRECTORY_SEPARATOR.$newName
        );

        return $this;
    }
}