<?php

/*************************************************************************
 *
 * AxelDownloadTest class runs PHPUnit tests of AxelDownload
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

class AxelDownloadTest extends \TestFixture {

    protected $short_download_address   = 'http://www.google.com';
    protected $long_download_address    = 'http://ipv4.download.thinkbroadband.com/1GB.zip';

    public function testAxelInstalled() {

        $download_address = $this->long_download_address;

        // Instance
        $this->assertFileNotExists(basename($download_address));
        $axel = new AxelDownload($download_address, null, null, null, true);

        $this->assertTrue($axel->checkAxelInstalled());
    }

    /**
     * @depends testAxelInstalled
     */
    public function testStartDownloadAsync() {

        $download_address = $this->long_download_address;

        // Instance
        $this->assertFileNotExists(basename($download_address));
        $axel = new AxelDownload($download_address, null, null, null, true);
        $axel->start();

        // Wait for download to initialise
        sleep(10);

        // Tests
        $this->assertSame($axel->last_command, AxelDownload::STARTED);

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

    /**
     * @depends testAxelInstalled
     */
    public function testStartDownloadSync() {

        $download_address = $this->short_download_address;

        // Instance
        $this->assertFileNotExists(basename($download_address));
        $axel = new AxelDownload($download_address);
        $this->assertSame($axel->last_command, AxelDownload::CREATED);
        $axel->start();

        // Tests
        $this->assertSame($axel->last_command, AxelDownload::COMPLETED);
        $this->assertFileExists($axel->getFullPath());
        $contents = file_get_contents($axel->getFullPath());
        $this->assertContains('input', $contents);
        $this->assertTrue($axel->clearCompleted());
        $this->assertFileNotExists($axel->getLogPath());
        $this->assertFileExists($axel->getFullPath());
        $this->assertFileNotExists($axel->getFullPath() . '.st');
        unlink($axel->getFullPath());
        $this->assertFileNotExists($axel->getFullPath());

        return $axel;
    }

    /*public function testStartDownloadAttachedWithCallback() {

        $download_address = 'http://www.google.com';

        // Instance
        $axel = new AxelDownload($download_address);
        $axel->start(function($axel, $success, $error) use ($download_address) {
            // Tests
            $this->assertTrue($success);
            $this->assertSame($axel->last_command, AxelDownload::STARTED);
            $this->assertFileExists(basename($download_address));
            $contents = file_get_contents(basename($download_address));
            $this->assertContains('input', $contents);
            $this->log($axel, 'Download object');
        });
    }*/
}