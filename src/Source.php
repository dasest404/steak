<?php

namespace Parsnick\Steak;

use SplFileInfo;

class Source extends SplFileInfo
{
    /**
     * @var string
     */
    protected $outputPathname;

    /**
     * Create a new Source instance.
     *
     * @param string $inputPathname
     * @param string $outputPathname
     */
    public function __construct($inputPathname, $outputPathname) {

        $this->outputPathname = $outputPathname;

        parent::__construct($inputPathname);
    }

    /**
     * Change the extension of the output pathname.
     *
     * @param array|string|null $rename
     * @return $this
     */
    public function changeExtension($rename)
    {
        $search = '';
        $replace = '';

        if (is_array($rename)) {
            $search = key($rename);
            $replace = current($rename);
        }

        if (is_string($rename)) {
            $search = pathinfo($this->outputPathname, PATHINFO_EXTENSION);
            $replace = $rename;
        }

        $this->outputPathname = preg_replace("#$search\$#", $replace, $this->outputPathname);

        return $this;
    }

    /**
     * Get the full pathname to the output file.
     *
     * @param array|string|null $rename
     * @return string
     */
    public function getOutputPathname($rename = null)
    {
        if ($rename) {
            $this->changeExtension($rename);
        }

        return $this->outputPathname;
    }
}
