<?php

/*
 * This file is part of the Axel package.
 *
 * (c) Ian Outterside <ian@ianbuildsapps.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Axel;

class AxelDownload {

    private $axel_path;
    private $pid;
    private $address;
    private $filename;
    private $download_path;
    public  $error;

    private $last_command   = AxelDownload::Created;
    private $status         = [
        'percentage'        => 0,
        'speed'             => 0,
        'ttl'               => 0
    ];

    const Created = 0;
    const Started = 1;
    const Paused = 2;
    const Cancelled = 3;
    const Completed = 4;
    const Cleared = 5;
    
    public function __construct($axel_path, $address, $filename = null, $download_path = null) {

        $this->axel_path        = $axel_path;
        $this->address          = $address;
        $this->filename         = (is_string($filename) && !empty($filename)) ? $filename : null;
        $this->download_path    = (is_string($download_path) && !empty($download_path)) ? $download_path : null;
    }

    public function start() {
        $this->pid = 1;

        $this->last_command = AxelDownload::Started;
    }

    public function pause() {
        $this->pid = null;

        $this->last_command = AxelDownload::Paused;
    }

    public function cancel() {
        $this->cancel();

        $this->last_command = AxelDownload::Cancelled;
    }

    public function clearCompleted() {

        if ($this->last_command == AxelDownload::Completed) {

            $this->last_command = AxelDownload::Cleared;

            return true;
        }
        else {
            $this->error = 'Unable to remove download. Download has not completed yet.';

            return false;
        }
    }

    protected function checkDownloadFile() {

        return true;
    }

    protected function updateStatus() {

        if ($this->checkDownloadFile() == false) {

            $this->last_command = AxelDownload::Completed;
        }
    }

    public function getStatus() {
        return $this->status;
    }
}