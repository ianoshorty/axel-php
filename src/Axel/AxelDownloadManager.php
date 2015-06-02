<?php

/*************************************************************************
 *
 * AxelDownloadManager class manages a series of downloads.
 *
 * =======================================================================
 *
 * This file is part of the Axel package.
 *
 * @author (c) Ian Outterside <ian@ianbuildsapps.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Axel;

class AxelDownloadManager {

    protected $queue;
    protected $path_to_axel;
    protected $running      = array();
    protected $scheduled    = array();
    protected $concurrent   = 1;

    public function __construct($concurrent = 1, $path_to_axel = null) {

        $this->concurrent   = (is_numeric($concurrent) && $concurrent >= 0)? $concurrent : 1;
        $this->path_to_axel = (is_string($path_to_axel))? $path_to_axel : 'axel';
    }

    public function queueDownload($address, $filename = null, $download_path = null) {
        $download = new AxelDownload($address, $filename, $download_path);
        $this->enqueueDownload($download);
        return $download;
    }

    public function enqueueDownload(AxelDownload $download) {
        $this->queue[] = $download;
    }

    public function processQueue() {

    }

    public function pauseQueue() {

    }

    public function clearQueueCompleted() {

    }

    public function resumeQueue() {

    }

    /**
     * A test method used to confirm project is setup and installed correctly
     * @todo Remove this once project has been cleaned up with full test suite
     *
     * @return string 'test'
     */
    public function outputString() {

        return 'test';
    }
}