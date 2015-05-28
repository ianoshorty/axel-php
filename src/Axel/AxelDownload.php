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

use mikehaertl\shellcommand\Command;

class AxelDownload {

    private $axel_path;
    private $pid;
    private $address;
    private $filename;
    private $download_path;
    public  $error;
    public  $last_message;
    public  $last_command   = AxelDownload::Created;

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

    public function __construct($address, $axel_path = '/usr/local/bin/axel', $filename = null, $download_path = null) {

        $this->address          = $address;
        $this->axel_path        = (is_string($axel_path) && !empty($axel_path)) ? $axel_path : '/usr/local/bin/axel';
        $this->filename         = (is_string($filename) && !empty($filename)) ? $filename : null;
        $this->download_path    = (is_string($download_path) && !empty($download_path)) ? $download_path : null;
    }

    public function start() {
        $this->pid = 1;

        //$command = new Command('/usr/local/bin/mycommand -a -b');
        $command = new Command('ls');

        if ($command->execute()) {
            $this->last_message = $command->getOutput();
        }
        else {
            $this->error        =  $command->getError();
            $this->last_message = $this->error;
        }

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