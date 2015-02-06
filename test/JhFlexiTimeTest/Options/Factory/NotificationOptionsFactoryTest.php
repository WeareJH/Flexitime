<?php

namespace JhFlexiTimeTest\Options\Factory;

use JhFlexiTime\DateTime\DateTime;
use JhFlexiTime\Options\Factory\NotificationOptionsFactory;
use Zend\ServiceManager\ServiceManager;

/**
 * Class NotificationOptionsFactoryTest
 * @package JhFlexiTime\Options\OptionsTest\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class NotificationOptionsFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test options injects from config files
     */
    public function testFactoryReturnsInjectedOptions()
    {
        $config = ['remind_start' => '3 days ago', 'remind_days' => '7 days'];

        $locator = new ServiceManager();
        $locator->setService('Config', ['flexi' => ['notification_options' => $config]]);

        $factory = new NotificationOptionsFactory();
        $this->assertEquals(new DateTime('3 days ago 00:00:00'), $factory->createService($locator)->getRemindStart());
        $this->assertEquals('7 days', $factory->createService($locator)->getRemindDays());
    }

    /**
     * Test options returns defaults when no config set
     */
    public function testFactoryReturnsDefaultOptionsWithEmptyConfig()
    {
        $config = [];

        $locator = new ServiceManager();
        $locator->setService('Config', ['flexi' => ['notification_options' => $config]]);

        $factory = new NotificationOptionsFactory();
        $this->assertEquals(new DateTime('2 days ago 00:00:00'), $factory->createService($locator)->getRemindStart());
        $this->assertEquals('7 days', $factory->createService($locator)->getRemindDays());
    }

    /**
     * Test options returns defaults when no global config
     */
    public function testFactoryReturnsDefaultOptionsWithNoConfig()
    {
        $config = [];

        $locator = new ServiceManager();
        $locator->setService('Config', $config);

        $factory = new NotificationOptionsFactory();
        $this->assertEquals(new DateTime('2 days ago 00:00:00'), $factory->createService($locator)->getRemindStart());
        $this->assertEquals('7 days', $factory->createService($locator)->getRemindDays());
    }
}
