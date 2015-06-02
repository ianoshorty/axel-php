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
    protected $processing               = false;
    protected $running                  = [];
    protected $scheduled                = [];
    protected $concurrent_downloads     = 1;
    protected $concurrent_connections   = 1;
    public $completed                   = [];

    public function __construct(AxelDownloadManagerQueueInterface $queue, $path_to_axel = null, $concurrent_downloads = 1, $concurrent_connections = 10) {

        $this->queue                    = $queue;
        $this->queue->setDownloadManager($this);
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
        array_push($this->scheduled, $download);
    }

    public function processQueue() {

        if (!empty($this->scheduled)) {

            $this->processing = true;

            while(count($this->running) < $this->concurrent_downloads) {

                $download = array_shift($this->scheduled);
                array_push($this->running, $download);
                $this->queue->addDownloadToQueue($download);
            }
        }
        else {

            //At present, queue will stop running when no more jobs have been added to it

            $this->processing = false;
        }
    }

    public function notifyCompletedDownload(AxelDownload $download) {

        if (!empty($this->running)) {

            $array_key = null;

            foreach ($this->running as $key => $job) {

                if ($job === $download) { // Todo check this

                    $download->clearCompleted();
                    array_push($this->completed, $download);
                    $array_key = $key;
                    break;
                }
            }

            if (!empty($array_key)) {
                unset($this->running[$array_key]);
            }
        }

        // Add next jobs to the queue if the queue is still active
        if ($this->processing) {
            $this->processQueue();
        }
    }

    /*
    public function pauseQueue() {

        if (!empty($this->running)) {

            foreach($this->running as $download) {

                $download->pause();
                array_unshift($this->scheduled, $download);
            }
        }
    }*/

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