<?php

namespace JhFlexiTimeTest\Options\Factory;

use JhFlexiTime\Options\Factory\BookingOptionsFactory;
use Zend\ServiceManager\ServiceManager;

/**
 * Class BookingOptionsFactoryTest
 * @package JhFlexiTime\Options\OptionsTest\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingOptionsFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test options injects from config files
     */
    public function testFactoryReturnsInjectedOptions()
    {
        $config = ['min_start_time' => '10:00'];

        $locator = new ServiceManager();
        $locator->setService('Config', ['flexi' => ['booking_options' => $config]]);

        $factory = new BookingOptionsFactory();
        $this->assertEquals('10:00', $factory->createService($locator)->getMinStartTime());
    }

    /**
     * Test options returns defaults when no config set
     */
    public function testFactoryReturnsDefaultOptionsWithEmptyConfig()
    {
        $config = [];

        $locator = new ServiceManager();
        $locator->setService('Config', ['flexi' => ['booking_options' => $config]]);

        $factory = new BookingOptionsFactory();
        $this->assertFalse($factory->createService($locator)->getMinStartTime());
    }

    /**
     * Test options returns defaults when no global config
     */
    public function testFactoryReturnsDefaultOptionsWithNoConfig()
    {
        $config = [];

        $locator = new ServiceManager();
        $locator->setService('Config', $config);

        $factory = new BookingOptionsFactory();
        $this->assertFalse($factory->createService($locator)->getMinStartTime());
    }
}
