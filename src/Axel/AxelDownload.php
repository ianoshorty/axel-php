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
     * Enums for download states
     */
    const CREATED = 0;
    const STARTED = 1;
    const PAUSED = 2;
    const CANCELLED = 3;
    const COMPLETED = 4;
    const CLEARED = 5;

    /**
     * @var string Full path to Axel binary
     * @example '/usr/bin/axel'
     */
    protected $axel_path;

    /**
     * @var string File to download
     * @example 'http://www.google.com' or 'http://ipv4.download.thinkbroadband.com/1GB.zip'
     */
    protected $address;

    /**
     * @var null|string Filename to save the downloaded file with
     */
    protected $filename;

    /**
     * @var null|string Path to save the downloaded file at
     */
    protected $download_path;

    /**
     * @var array Internal array of callback functions to call on completed download
     */
    protected $callbacks      = [];

    /**
     * @var bool To perform Async downloads set to true
     */
    protected $detach         = false;

    /**
     * @var int The number of connections to attempt to use to download the file
     */
    protected $connections    = 10;

    /**
     * @var string The path to the log file that is parsed to get progress information
     */
    protected $log_path;

    /**
     * @var array Array containing process information if the process is running.
     * @example May contain ['pid' => 1234]
     */
    protected $process_info;

    /**
     * @var string The last error encountered
     */
    public  $error;

    /**
     * @var int Const value of the last download state. Starts with CREATED:0
     */
    public  $last_command   = AxelDownload::CREATED;

    /**
     * @var array Download progress information
     */
    protected $status         = [
        'percentage'        => 0,
        'speed'             => '0.0KB/s',
        'ttl'               => 0
    ];

    /**
     * Class constructor
     *
     * @param string $axel_path Full path to Axel binary
     * @param int $connections The number of connections to attempt to use to download the file
     */
    public function __construct($axel_path = 'axel', $connections = 10) {

        $this->axel_path            = (is_string($axel_path) && !empty($axel_path))         ? $axel_path : 'axel';
        $this->connections          = (is_int($connections) && $connections >= 1)           ? $connections : 10;
    }

    /**
     * Check if the specified Axel binary is installed / callable
     *
     * @return bool Whether Axel is installed
     */
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

    /**
     * Start the download process - async
     *
     * @param string $address File to download
     * @param string $filename Filename to save the downloaded file with
     * @param null $download_path Path to save the downloaded file at
     * @param callable $callback An optional callback to provide progress updates
     * @return $this
     */
    public function startAsync($address, $filename = null, $download_path = null, \Closure $callback = null) {

        $this->detach = true;
        return $this->start($address, $filename, $download_path, $callback);
    }

    /**
     * Start the download process
     *
     * @param string $address File to download
     * @param string $filename Filename to save the downloaded file with
     * @param null $download_path Path to save the downloaded file at
     * @param callable $callback An optional callback to provide progress updates
     * @return $this
     */
    public function start($address, $filename = null, $download_path = null, \Closure $callback = null) {

        $this->address              = $address;
        $this->filename             = (is_string($filename) && !empty($filename))           ? $filename : basename($this->address);
        $this->download_path        = (is_string($download_path) && !empty($download_path)) ? $download_path : null;
        if (is_callable($callback)) $this->callbacks[] = $callback;

        $this->log_path = $this->download_path . time() . '.log';

        $command = $this->axel_path;                                            // Path to Axel downloader
        $options_string = " -avn $this->connections -o {$this->getFullPath()} $this->address > {$this->getLogPath()}";

        $this->last_command = AxelDownload::STARTED;

        if ($this->execute($command, $options_string)) {

            if (!$this->detach) {

                $this->updateStatus();
                $this->runCallbacks(true);
            }
        }
        else if (!$this->detach) $this->runCallbacks(false);

        return $this;
    }

    /**
     * Pause the download
     *
     * @return $this
     */
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
                $this->last_command = AxelDownload::PAUSED;
            }
        }
        else {
            $this->error = 'Unable to pause download. Download not running.';
        }

        return $this;
    }

    /**
     * Cancel the download
     *
     * @return $this
     */
    public function cancel() {

        $this->pause();

        if ($this->last_command == AxelDownload::PAUSED) {

            // Do file removal

            // Remove the downloaded file
            unlink($this->getFullPath());
            // Remove the tracking file
            unlink($this->getFullPath() . '.st');
        }

        return $this;
    }

    /**
     * Perform some cleanup.
     *
     * @return bool If cleanup was successful
     */
    public function clearCompleted() {

        if ($this->last_command == AxelDownload::COMPLETED) {

            unlink($this->getLogPath());
            $this->last_command = AxelDownload::CLEARED;

            return true;
        }
        else {
            $this->error = 'Unable to remove download. Download has not completed yet.';

            return false;
        }
    }

    /**
     * Parse the download log to get progress updates
     *
     * @return bool If the download has completed
     */
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

            if ($this->detach) $this->runCallbacks(false);

            return false;
        }
        else {

            if ($this->detach) $this->runCallbacks(true);

            return true;
        }
    }

    /**
     * @param bool $success Wether to pass a success state or an error state to callbacks
     */
    protected function runCallbacks($success) {

        foreach ((array)$this->callbacks as $callback) {

            if (is_callable($callback)) {
                $callback($this, $this->status, $success, $this->error);
            }
        }
    }

    /**
     * Force a status update.
     *
     * @return array Updated progress status
     */
    public function updateStatus() {

        if ($this->checkDownloadFile() === true) {
            $this->last_command = AxelDownload::COMPLETED;
        }

        return $this->getStatus();
    }

    /**
     * @return array Last progress status
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * The filename used
     *
     * @return string
     */
    public function getFilename() {
        return $this->filename;
    }

    /**
     * The path to the download location
     *
     * @return null|string
     */
    private function getDownloadPath() {
        return $this->download_path;
    }

    /**
     * The full path to the download file
     *
     * @return string
     */
    public function getFullPath() {
        return $this->getDownloadPath() . $this->getFilename();
    }

    /**
     * The full path to the log file
     *
     * @return string
     */
    public function getLogPath() {
        return $this->log_path;
    }

    /**
     * Executes the download
     *
     * @param $command The download command
     * @param $command_args Optional arguments
     * @return bool If the command executed successfully
     */
    protected function execute($command, $command_args) {

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