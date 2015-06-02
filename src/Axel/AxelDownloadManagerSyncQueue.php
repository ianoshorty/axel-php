<?php

/*************************************************************************
 *
 * AxelDownloadManagerSyncQueue class runs jobs in the AxelDownloadManager
 * synchronously.
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

class AxelDownloadManagerSyncQueue implements AxelDownloadManagerQueueInterface {

    private $axelDownloadManager;

    public function setDownloadManager(AxelDownloadManager $axelDownloadManager) {
        $this->axelDownloadManager = $axelDownloadManager;
    }

    public function addDownloadToQueue(AxelDownload $download) {

        $download->start();
        $this->axelDownloadManager->notifyCompletedDownload($download);
    }
}