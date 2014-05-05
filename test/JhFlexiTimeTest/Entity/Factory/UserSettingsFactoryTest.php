<?php

namespace JhFlexiTimeTest\Entity\Factory;

use JhFlexiTime\Entity\Factory\UserSettingsFactory;
use JhFlexiTime\Entity\UserSettings;
use JhUser\Entity\User;

/**
 * Class UserSettingsFactoryTest
 * @package JhFlexiTimeTest\Entity\Factory
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class UserSettingsFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testFactoryThrowsExceptionIfUserSettingsDoesNotExist()
    {
        $serviceLocator   = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockUserService  = $this->getMock('Zend\Authentication\AuthenticationService');

        $mockUserService
            ->expects($this->once())
            ->method('hasIdentity')
            ->will($this->returnValue(true));

        $serviceLocator
            ->expects($this->at(0))
            ->method('get')
            ->with('zfcuser_auth_service')
            ->will($this->returnValue($mockUserService));

        $user = new User();
        $mockUserService
            ->expects($this->once())
            ->method('getIdentity')
            ->will($this->returnValue($user));


        $userSettingsRepository = $this->getMock('JhFlexiTime\Repository\UserSettingsRepositoryInterface');
        $userSettingsRepository
            ->expects($this->once())
            ->method('findOneByUser')
            ->with($user)
            ->will($this->returnValue(null));

        $serviceLocator
            ->expects($this->at(1))
            ->method('get')
            ->with('JhFlexiTime\Repository\UserSettingsRepository')
            ->will($this->returnValue($userSettingsRepository));

        $this->setExpectedException('InvalidArgumentException', 'User does not have a settings row');

        $factory = new UserSettingsFactory();
        $factory->createService($serviceLocator);

        //$this->assertInstanceOf('JhFlexiTime\Entity\UserSettings', $factory->createService($serviceLocator));*/
    }

    public function testFactoryThrowsExceptionIfUserNotAuthenticated()
    {
        $serviceLocator   = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockUserService  = $this->getMock('Zend\Authentication\AuthenticationServiceInterface');

        $mockUserService
            ->expects($this->once())
            ->method('hasIdentity')
            ->will($this->returnValue(false));

        $serviceLocator
            ->expects($this->once())
            ->method('get')
            ->with('zfcuser_auth_service')
            ->will($this->returnValue($mockUserService));

        $this->setExpectedException('InvalidArgumentException', 'User is not authenticated');

        $factory = new UserSettingsFactory();
        $factory->createService($serviceLocator);
    }

    public function testFactoryReturnsInstanceIfUserAndSettingsExist()
    {
        $serviceLocator   = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockUserService  = $this->getMock('Zend\Authentication\AuthenticationService');

        $mockUserService
            ->expects($this->once())
            ->method('hasIdentity')
            ->will($this->returnValue(true));

        $serviceLocator
            ->expects($this->at(0))
            ->method('get')
            ->with('zfcuser_auth_service')
            ->will($this->returnValue($mockUserService));

        $user = new User();
        $mockUserService
            ->expects($this->once())
            ->method('getIdentity')
            ->will($this->returnValue($user));

        $userSettings = new UserSettings();
        $userSettingsRepository = $this->getMock('JhFlexiTime\Repository\UserSettingsRepositoryInterface');
        $userSettingsRepository
            ->expects($this->once())
            ->method('findOneByUser')
            ->with($user)
            ->will($this->returnValue($userSettings));

        $serviceLocator
            ->expects($this->at(1))
            ->method('get')
            ->with('JhFlexiTime\Repository\UserSettingsRepository')
            ->will($this->returnValue($userSettingsRepository));

        $factory = new UserSettingsFactory();
        $this->assertSame($userSettings, $factory->createService($serviceLocator));
    }
}
