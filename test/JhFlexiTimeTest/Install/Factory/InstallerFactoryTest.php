<?php

namespace JhFlexiTimeTest\Install\Factory;

use JhFlexiTime\Install\Factory\InstallerFactory;

/**
 * Class InstallerFactoryTest
 * @package JhFlexiTimeTest\Install\Factory
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class InstallerFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryProcessesWithoutErrors()
    {
        $serviceLocator   = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $services         = [
            'Jhuser\Repository\UserRepository' =>
                $this->getMock('Jhuser\Repository\UserRepositoryInterface'),
            'JhFlexiTime\Repository\UserSettingsRepository' =>
                $this->getMock('JhFlexiTime\Repository\UserSettingsRepositoryInterface'),
            'JhFlexiTime\Repository\BalanceRepository' =>
                $this->getMock('JhFlexiTime\Repository\BalanceRepositoryInterface'),
            'JhFlexiTime\ObjectManager' =>
                $this->getMock('Doctrine\Common\Persistence\ObjectManager'),
        ];

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

        $factory = new InstallerFactory();
        $this->assertInstanceOf('JhFlexiTime\Install\Installer', $factory->createService($serviceLocator));
    }
}
