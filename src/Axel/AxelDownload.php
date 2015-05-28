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

    private $status;
    private $pid;
    private $address;
    private $filename;
    private $download_path;
    private $command;

    public function __construct($address, $filename = null, $download_path = null) {

    }

    public function start() {

    }

    public function pause() {

    }

    public function cancel() {

    }

    public function clearCompleted() {

    }

    protected function updateStatus() {

    }

    public function getStatus() {

    }
}