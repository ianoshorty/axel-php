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

    public function testOutputString() {

        //$axel = new AxelDownload();
        //$this->assertSame($axel->outputString(), 'test');
    }

    public function testStartDownload() {

        $axel = new AxelDownload('http://www.google.com');

        $axel->start();

        $this->assertSame($axel->last_command, AxelDownload::Started);

        $this->log($axel, 'Download object');
    }
}