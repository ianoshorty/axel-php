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

class AxelTest extends \TestFixture {

    public function testOutputString() {
        $axel = new Axel();
        $this->assertSame($axel->outputString(), 'test');
    }
}