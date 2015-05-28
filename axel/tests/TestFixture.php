<?php

/*
 * This file is part of the Axel package.
 *
 * (c) Ian Outterside <ian@ianbuildsapps.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require __DIR__.'/../vendor/autoload.php';

use Axel\Axel;

class TestFixture extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {

    }

    protected function tearDown()
    {
    }

    protected function assertAxel(Axel $axel)
    {

    }

    protected function assertInstanceOfAxel($d)
    {
        $this->assertInstanceOf('Axel\Axel', $d);
    }
}
