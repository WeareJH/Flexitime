<?php

namespace JhFlexiTimeTest\Options\Factory;

use JhFlexiTime\Options\Factory\ModuleOptionsFactory;
use Zend\ServiceManager\ServiceManager;

/**
 * Class ModuleOptionsFactoryTest
 * @package JhFlexiTime\Options\OptionsTest\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class ModuleOptionsFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test options injects from config files
     */
    public function testFactoryReturnsInjectedOptions()
    {
        $config = ['lunch_duration' => 2];

        $locator = new ServiceManager();
        $locator->setService('Config', ['flexi' => ['policy_options' => $config]]);

        $factory = new ModuleOptionsFactory();
        $this->assertEquals(2, $factory->createService($locator)->getLunchDuration());
    }

    /**
     * Test options returns defaults when no config set
     */
    public function testFactoryReturnsDefaultOptionsWithEmptyConfig()
    {
        $config = [];

        $locator = new ServiceManager();
        $locator->setService('Config', ['flexi' => ['policy_options' => $config]]);

        $factory = new ModuleOptionsFactory();
        $this->assertEquals(1, $factory->createService($locator)->getLunchDuration());
    }

    /**
     * Test options returns defaults when no global config
     */
    public function testFactoryReturnsDefaultOptionsWithNoConfig()
    {
        $config = [];

        $locator = new ServiceManager();
        $locator->setService('Config', $config);

        $factory = new ModuleOptionsFactory();
        $this->assertEquals(1, $factory->createService($locator)->getLunchDuration());
    }
}
