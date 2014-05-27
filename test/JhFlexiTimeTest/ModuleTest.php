<?php

namespace JhFlexiTimeTest;

use JhFlexiTime\Module;
use JhUser\Entity\User;
use Zend\ServiceManager\ServiceManager;
use JhFlexiTime\Entity\UserSettings;

/**
 * Class ModuleTest
 * @package JhFlexiTimeTest
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class ModuleTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Zend\ServiceManager\ServiceManager
     */
    protected $serviceLocator;
    protected $sharedEvm;

    /**
     * @var \Zend\EventManager\EventManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    public function testGetConfig()
    {
        $module = new Module();

        $this->assertInternalType('array', $module->getConfig());
        $this->assertSame($module->getConfig(), unserialize(serialize($module->getConfig())), 'Config is serializable');
    }

    public function testGetAutoloaderConfig()
    {
        $module = new Module;
        $this->assertInternalType('array', $module->getAutoloaderConfig());
    }

    public function testListenersAreRegistered()
    {
        $event = $this->getEvent(true);
        $module = new Module();

        $bookingSaveListener = $this->getMockBuilder('JhFlexiTime\Listener\BookingSaveListener')
            ->disableOriginalConstructor()
            ->getMock();

        $this->serviceLocator->setService('JhFlexiTime\Listener\BookingSaveListener', $bookingSaveListener);
        $this->eventManager
            ->expects($this->once())
            ->method('attach')
            ->with($bookingSaveListener);

        $this->sharedEvm
            ->expects($this->at(0))
            ->method('attach')
            ->with(
                'ScnSocialAuth\Authentication\Adapter\HybridAuth',
                'registerViaProvider.post',
                array($module, 'onRegister')
            );

        $this->sharedEvm
            ->expects($this->at(1))
            ->method('attach')
            ->with('ZfcUser\Service\User', 'register.post', array($module, 'onRegister'));

        $module->onBootstrap($event);
    }

    /**
     * @return \Zend\EventManager\EventInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEvent($addEventManager = false)
    {
        $this->serviceLocator = new ServiceManager();
        $this->eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
        $application = $this->getMock('Zend\Mvc\Application', [], [], '', false);

        $application->expects($this->any())
            ->method('getServiceManager')
            ->will($this->returnValue($this->serviceLocator));

        if ($addEventManager) {
            $application->expects($this->any())
                ->method('getEventManager')
                ->will($this->returnValue($this->eventManager));

            $this->sharedEvm = $this->getMock('Zend\EventManager\SharedEventManagerInterface');
            $this->eventManager
                ->expects($this->once())
                ->method('getSharedManager')
                ->will($this->returnValue($this->sharedEvm));
        }

        $event = $this->getMock('Zend\EventManager\EventInterface');
        $event->expects($this->any())->method('getTarget')->will($this->returnValue($application));

        return $event;
    }

    public function testConsoleUsage()
    {
        $mockConsole = $this->getMock('Zend\Console\Adapter\AdapterInterface');
        $module = new Module();

        $expected = [
            're-calc-balance-user <userEmail>' =>
                "Recalculate a User's running balance",
            're-calc-balance-all ' =>
                "Recalculate all User's running balance",
            'calc-prev-month-balance' =>
                "Calculate the previous month balance for all users and add it on to their running balance",
            'set user init-balance <userEmail> <balance>' =>
                "Set a user's starting balance"
        ];
        $this->assertSame($expected, $module->getConsoleUsage($mockConsole));
    }

    public function testUserIsInstalledOnRegister()
    {
        $event      = $this->getEvent();
        $user = new User();
        $event
            ->expects($this->once())
            ->method('getParam')
            ->with('user')
            ->will($this->returnValue($user));

        $installer  = $this->getMockBuilder('JhFlexiTime\Install\Installer')
            ->disableOriginalConstructor()
            ->getMock();

        $installer
            ->expects($this->once())
            ->method('createSettingsRow')
            ->with($user);

        $installer
            ->expects($this->once())
            ->method('createRunningBalanceRow')
            ->with($user);

        $this->serviceLocator->setService('JhFlexiTime\Install\Installer', $installer);

        $module = new Module();
        $module->onRegister($event);
    }

    public function testGetInstallService()
    {
        $module = new Module();
        $this->assertSame('JhFlexiTime\Install\Installer', $module->getInstallService());
    }
}
