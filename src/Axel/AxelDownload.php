<?php

/*************************************************************************
 *
 * AxelDownload class represents a single AXEL download.
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

use Symfony\Component\Process\Process;

class AxelDownload {

    /**
     * @var string
     */
    private $axel_path;
    private $address;
    private $filename;
    private $download_path;
    private $callbacks      = [];
    private $detach         = false;
    private $connections    = 10;
    private $log_path;
    private $process_info;

    public  $error;
    public  $last_command   = AxelDownload::Created;

    private $status         = [
        'percentage'        => 0,
        'speed'             => '0.0KB/s',
        'ttl'               => 0
    ];

    const Created = 0;
    const Started = 1;
    const Paused = 2;
    const Cancelled = 3;
    const Completed = 4;
    const Cleared = 5;

    public function __construct($address, $filename = null, $download_path = null, \Closure $callback = null, $detach = false, $axel_path = '/usr/bin/axel', $connections = 10) {

        $this->address              = $address;
        $this->filename             = (is_string($filename) && !empty($filename))           ? $filename : basename($this->address);
        $this->download_path        = (is_string($download_path) && !empty($download_path)) ? $download_path : null;
        $this->detach               = (is_bool($detach))                                    ? $detach : false;
        if (is_callable($callback)) $this->callbacks[] = $callback;
        $this->axel_path            = (is_string($axel_path) && !empty($axel_path))         ? $axel_path : '/usr/bin/axel';
        $this->connections          = (is_int($connections) && $connections >= 1)           ? $connections : 10;
    }

    public function checkAxelInstalled() {
        $process = new Process($this->axel_path . ' --version');

        $process->run();
        if (!$process->isSuccessful()) {
            $this->error = $process->getErrorOutput();

            return false;
        }
        else {

            return true;
        }
    }

    public function start(\Closure $callback = null) {

        if (is_callable($callback)) $this->callbacks[] = $callback;

        $this->log_path = $this->download_path . time() . '.log';

        $command = $this->axel_path;                                            // Path to Axel downloader
        $options_string = " -avn $this->connections -o {$this->getFullPath()} $this->address > {$this->getLogPath()}";

        $this->last_command = AxelDownload::Started;

        if ($this->execute($command, $options_string)) {

            if (!$this->detach) {

                $this->updateStatus();

                foreach((array) $this->callbacks as $callback) {
                    $callback($this, true);
                }
            }
        }
        else {
            if (!$this->detach) {

                foreach((array) $this->callbacks as $callback) {
                    $callback($this, false, $this->error);
                }
            }
        }

        return $this;
    }

    public function pause() {

        if (isset($this->process_info['pid'])) {

            // Spawn off the process
            $process = new Process('kill -9 ' . $this->process_info['pid']);

            $process->run();
            if (!$process->isSuccessful()) {
                $this->error = $process->getErrorOutput();
            }
            else {
                $this->process_info = null;
                // Remove the log file
                unlink($this->getLogPath());
                $this->last_command = AxelDownload::Paused;
            }
        }
        else {
            $this->error = 'Unable to pause download. Download not running.';
        }

        return $this;
    }

    public function cancel() {

        $this->pause();

        if ($this->last_command == AxelDownload::Paused) {

            // Do file removal

            // Remove the downloaded file
            unlink($this->getFullPath());
            // Remove the tracking file
            unlink($this->getFullPath() . '.st');
        }

        return $this;
    }

    public function clearCompleted() {

        if ($this->last_command == AxelDownload::Completed) {

            unlink($this->getLogPath());
            $this->last_command = AxelDownload::Cleared;

            return true;
        }
        else {
            $this->error = 'Unable to remove download. Download has not completed yet.';

            return false;
        }
    }

    protected function checkDownloadFile() {

        if (file_exists($this->getLogPath())) {

            $contents = file_get_contents($this->getLogPath());

            $regex = '/\[\s*([0-9]{1,3})%\].*\[\s*([0-9]+\.[0-9]+[A-Z][a-zA-Z]\/s)\]\s*\[([0-9]+:[0-9]+)\]/i';

            $last_match = substr($contents, -150);

            preg_match($regex, $last_match, $matches);

            if (isset($matches) && !empty($matches) && count($matches) == 4) {

                $this->status['percentage'] = $matches[1];
                $this->status['speed']      = $matches[2];
                $this->status['ttl']        = $matches[3];
            }
        }

        if (file_exists($this->getFullPath() . '.st')) {
            return false;
        }
        else {
            return true;
        }
    }

    public function updateStatus() {

        if ($this->checkDownloadFile() === true) {
            $this->last_command = AxelDownload::Completed;
        }

        return $this->getStatus();
    }

    public function getStatus() {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getAxelPath() {
        return $this->axel_path;
    }

    /**
     * @return mixed
     */
    public function getPID() {
        return (isset($this->process_info['pid']))?$this->process_info['pid']:null;
    }

    /**
     * @return mixed
     */
    public function getAddress() {
        return $this->address;
    }

    /**
     * @return string
     */
    public function getFilename() {
        return $this->filename;
    }

    /**
     * @return null|string
     */
    public function getDownloadPath() {
        return $this->download_path;
    }

    /**
     * @return string
     */
    public function getFullPath() {
        return $this->getDownloadPath() . $this->getFilename();
    }

    /**
     * @return string
     */
    public function getLogPath() {
        return $this->log_path;
    }

    public function execute($command, $command_args) {

        $detach = ($this->detach) ? ' 2>&1 &': '';

        // Spawn off the process
        $process = new Process($command . $command_args . $detach . ' echo $!');

        $process->run();
        if (!$process->isSuccessful()) {
            $this->error = $process->getErrorOutput();

            return false;
        }
        else {
            $this->process_info['pid'] = $process->getOutput();

            return true;
        }
    }
}