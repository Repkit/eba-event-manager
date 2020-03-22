<?php

/**
 * MicroIceEventManagerTest.php object
 *
 * @package    MicroIceEventManagerTest.php
 * @author     Ionut Andrei Baches <ionut.baches@dcsplus.net>
 * @copyright  dcsplus.net
 * @since      2016-08-18
 */

namespace MicroIceEventManager;

use PHPUnit_Framework_TestCase as TestCase;
use ApplicationTest\Bootstrap;


class MicroIceEventManagerTest extends TestCase
{
    private $serviceManager;

    /**
     * Set up
     */
    public function setUp()
    {
        $this->serviceManager = Bootstrap::getServiceManager();
    }

    public function test_Fake() {

        $this->assertEquals(1,1);
    }

}