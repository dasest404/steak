<?php

namespace Parsnick\Steak;

use FilesystemIterator;
use Illuminate\Filesystem\Filesystem;
use SplFileInfo;

class Cleaner
{
    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * Create a new Cleaner instance.
     *
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        $this->files = $files;
    }

    /**
     * Remove all files from $directory except for those specified by $ignore.
     *
     * @param string $target
     * @param array $ignore
     * @return bool
     */
    public function clean($target, array $ignore = ['.git'])
    {
        foreach (new FilesystemIterator($target) as $file) {

            if ( ! $this->matchesPattern($file, $ignore)) {
                $this->deleteDirectoryOrFile($file);
            }

        }

        return true;
    }

    /**
     * Check if the file matches any of the patterns.
     *
     * @param SplFileInfo $file
     * @param array $patterns
     * @return bool
     */
    protected function matchesPattern(SplFileInfo $file, array $patterns)
    {
        foreach ($patterns as $pattern) {

            if (str_is($pattern, $file->getFilename())) {
                return true;
            }

        }

        return false;
    }

    /**
     * Delete the given directory or file.
     *
     * @param SplFileInfo $file
     * @return bool
     */
    protected function deleteDirectoryOrFile(SplFileInfo $file)
    {
        if ($file->isDir() && !$file->isLink()) {
            return $this->files->deleteDirectory($file);
        }

        return $this->files->delete($file);
    }
}
