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

class AxelDownloadTest extends \TestFixture {

    protected $short_download_address   = 'http://www.google.com';

    public function testStartDownloadAsync() {

        $download_address = $this->long_download_address;

        // Instance
        $this->assertFileNotExists(basename($download_address));
        $axel = new AxelDownload($download_address, null, null, null, true);
        $axel->start();

        // Wait for download to initialise
        sleep(10);

        // Tests
        $this->assertSame($axel->last_command, AxelDownload::Started);

        return $axel;
    }

    /**
     * @depends testStartDownloadAsync
     */
    public function testReadLogDownloadAsync(AxelDownload $axel) {

        $this->assertFileExists($axel->getFullPath());
        $this->assertFileExists($axel->getFullPath() . '.st');
        $this->assertFileExists($axel->getLogPath());

        $status = $axel->updateStatus();

        $this->assertTrue(is_array($status));
        $this->assertTrue(!empty($status));
        $this->assertTrue(count($status) == 3);
        $this->assertTrue(empty($axel->error));

        return $axel;
    }

    /**
     * @depends testReadLogDownloadAsync
     */
    public function testCancelDownloadAsync(AxelDownload $axel) {

        $this->assertFileExists($axel->getFullPath());
        $this->assertFileExists($axel->getFullPath() . '.st');
        $this->assertFileExists($axel->getLogPath());

        $axel->cancel();

        $this->assertFileNotExists($axel->getFullPath());
        $this->assertFileNotExists($axel->getFullPath() . '.st');
        $this->assertFileNotExists($axel->getLogPath());

        return $axel;
    }

    /*
    public function testStartDownloadSync() {

        $download_address = $this->short_download_address;

        // Instance
        $this->assertFileNotExists(basename($download_address));
        $axel = new AxelDownload($download_address);
        $this->assertSame($axel->last_command, AxelDownload::Created);
        $axel->start();

        // Tests
        $this->assertSame($axel->last_command, AxelDownload::Completed);
        $this->assertFileExists(basename($download_address));
        $contents = file_get_contents(basename($download_address));
        $this->assertContains('input', $contents);
        $this->assertTrue($axel->clearCompleted());

        return $axel;
    }*/

    /*public function testStartDownloadAttachedWithCallback() {

        $download_address = 'http://www.google.com';

        // Instance
        $axel = new AxelDownload($download_address);
        $axel->start(function($axel, $success, $error) use ($download_address) {
            // Tests
            $this->assertTrue($success);
            $this->assertSame($axel->last_command, AxelDownload::Started);
            $this->assertFileExists(basename($download_address));
            $contents = file_get_contents(basename($download_address));
            $this->assertContains('input', $contents);
            $this->log($axel, 'Download object');
        });
    }*/
}