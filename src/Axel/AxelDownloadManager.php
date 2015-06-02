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
    protected $running                  = [];
    protected $scheduled                = [];
    protected $concurrent_downloads     = 1;
    protected $concurrent_connections   = 1;

    public function __construct($path_to_axel = null, $concurrent_downloads = 1, $concurrent_connections = 10) {

        $this->path_to_axel             = (is_string($path_to_axel))? $path_to_axel : 'axel';
        $this->concurrent_downloads     = (is_numeric($concurrent_downloads) && $concurrent_downloads >= 0)? $concurrent_downloads : 1;
        $this->concurrent_connections   = (is_numeric($concurrent_connections) && $concurrent_connections >= 0)? $concurrent_connections : 10;
    }

    public function queueDownload($address, $filename = null, $download_path = null) {

        $download = new AxelDownload($this->path_to_axel, $this->concurrent_connections);
        $download->addDownloadParameters([
            'address'           => $address,
            'filename'          => $filename,
            'download_path'     => $download_path
        ]);
        $this->enqueueDownload($download);

        return $download;
    }

    public function enqueueDownload(AxelDownload $download) {

        $this->scheduled[] = $download;
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