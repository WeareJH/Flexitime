<?php

namespace JhFlexiTimeTest\Controller\Factory;

use JhFlexiTime\Controller\Factory\RunningBalanceCliControllerFactory;
use Zend\Mvc\Controller\PluginManager;

/**
 * Class RunningBalanceCliControllerFactoryTest
 * @package JhFlexiTimeTest\Controller\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class RunningBalanceCliControllerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryProcessesWithoutErrors()
    {
        $mockRunningBalanceService = $this->getMockBuilder('JhFlexiTime\Service\RunningBalanceService')
            ->disableOriginalConstructor()
            ->getMock();

        $services = array(
            'JhUser\Repository\UserRepository'          => $this->getMock('JhUser\Repository\UserRepositoryInterface'),
            'JhFlexiTime\Service\RunningBalanceService' => $mockRunningBalanceService,
            'Console'                                   => $this->getMock('Zend\Console\Adapter\AdapterInterface')
        );

        $serviceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $serviceLocator
            ->expects($this->any())
            ->method('get')
            ->will(
                $this->returnCallback(
                    function ($serviceName) use ($services) {
                        return $services[$serviceName];
                    }
                )
            );

        $controllerPluginManager = new PluginManager();
        $controllerPluginManager->setServiceLocator($serviceLocator);

        $factory = new RunningBalanceCliControllerFactory();
        $this->assertInstanceOf(
            'JhFlexiTime\Controller\RunningBalanceCliController',
            $factory->createService($controllerPluginManager)
        );
    }
}
