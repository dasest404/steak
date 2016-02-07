<?php

namespace Parsnick\Steak;

use Symfony\Component\Finder\SplFileInfo;

class Source extends SplFileInfo
{
    /**
     * @var string
     */
    protected $outputFile;

    /**
     * Create a new File instance.
     *
     * @param string $file
     * @param string $relativePath
     * @param string $relativePathname
     * @param string $outputFile
     */
    public function __construct(
        $file,
        $relativePath,
        $relativePathname,
        $outputFile
    ) {
        $this->outputFile = $outputFile;

        parent::__construct($file, $relativePath, $relativePathname);
    }

    /**
     * Change the file extension on the output file.
     *
     * By default, uses same extension as the source.
     *
     * @param string $to
     * @param string $from
     * @return $this
     */
    public function changeExtension($to, $from = '*')
    {
        if ($from == '*') {
            $from = pathinfo($this->outputFile, PATHINFO_EXTENSION);
        }

        if (ends_with($this->outputFile, $from)) {
            $this->outputFile = preg_replace("#$from\$#", $to, $this->outputFile);
        }

        return $this;
    }

    /**
     * @param SplFileInfo $existing
     * @param string $outputDir
     * @return static
     */
    public static function extend(SplFileInfo $existing, $outputDir)
    {
        return new static(
            $existing->getPathname(),
            $existing->getRelativePath(),
            $existing->getRelativePathname(),
            $outputDir.DIRECTORY_SEPARATOR.$existing->getRelativePathname()
        );
    }

    /**
     * Get the full pathname to the output file.
     *
     * @param array|string|null $extension
     * @return string
     */
    public function getOutputPathname($extension = null)
    {
        $search = '';
        $replace = '';

        if (is_array($extension)) {
            $search = key($extension);
            $replace = current($extension);
        }

        if (is_string($extension)) {
            $search = pathinfo($this->outputFile, PATHINFO_EXTENSION);
            $replace = $extension;
        }

        return preg_replace("#$search\$#", $replace, $this->outputFile);
    }
}