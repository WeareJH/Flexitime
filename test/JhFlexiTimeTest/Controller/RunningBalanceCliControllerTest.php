<?php

namespace JhUserTest\Controller;

use JhFlexiTime\Controller\RunningBalanceCliController;
use JhUser\Entity\User;
use JhUser\Entity\Role;
use Zend\Console\Request;
use Zend\Http\Request as HttpRequest;
use Zend\Test\PHPUnit\Controller\AbstractConsoleControllerTestCase;
use Zend\Console\ColorInterface;

/**
 * Class RunningBalanceCliControllerControllerTest
 * @package JhFlexiTimeTest\Controller
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class RunningBalanceCliControllerControllerTest extends AbstractConsoleControllerTestCase
{
    protected $controller;
    protected $userRepository;
    protected $runningBalanceService;
    protected $consoleAdapter;

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . "/../../TestConfiguration.php.dist"
        );
        parent::setUp();

        $this->userRepository = $this->getMock('JhUser\Repository\UserRepositoryInterface');
        $this->runningBalanceService = $this->getMockBuilder('JhFlexiTime\Service\RunningBalanceService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->consoleAdapter = $this->getMock('Zend\Console\Adapter\AdapterInterface');


        $serviceManager = $this->getApplicationServiceLocator();
        $serviceManager->setAllowOverride(true);
        $serviceManager->setService('JhUser\Repository\UserRepository', $this->userRepository);
        $serviceManager->setService('JhFlexiTime\Service\RunningBalanceService', $this->runningBalanceService);
        $serviceManager->setService('Console', $this->consoleAdapter);
        $serviceManager->setService('Console', $this->consoleAdapter);
    }

    public function testCalculatePreviousMonth()
    {
        $this->consoleAdapter
             ->expects($this->at(0))
             ->method('writeLine')
             ->with("Calculating Running Balance for all Users for previous month", ColorInterface::GREEN);

        $this->consoleAdapter
            ->expects($this->at(1))
            ->method('writeLine')
            ->with("Finished! ", ColorInterface::GREEN);

        $this->runningBalanceService
            ->expects($this->once())
            ->method('calculatePreviousMonthBalance');

        $this->dispatch(new Request(array('scriptname.php', "calc-prev-month-balance")));

        $this->assertResponseStatusCode(0);
        $this->assertModuleName('jhflexitime');
        $this->assertControllerName('jhflexitime\controller\runningbalancecli');
        $this->assertControllerClass('runningbalanceclicontroller');
        $this->assertActionName('calc-prev-month-balance');
        $this->assertMatchedRouteName('calc-prev-month-balance');
    }

    public function testReCalculateBalanceProcessesIndividualUserIfEmailParamIsPresent()
    {
        $user   = new User;
        $email  = 'aydin@wearejh.com';

        $this->userRepository
             ->expects($this->once())
             ->method('findOneByEmail')
             ->with($email)
             ->will($this->returnValue($user));

        $this->consoleAdapter
            ->expects($this->at(0))
            ->method('writeLine')
            ->with("Recalculating Running Balance for $email", ColorInterface::GREEN);

        $this->consoleAdapter
            ->expects($this->at(1))
            ->method('writeLine')
            ->with("Finished! ", ColorInterface::GREEN);

        $this->runningBalanceService
             ->expects($this->once())
             ->method('recalculateUserRunningBalance')
             ->with($user);

        $this->runningBalanceService
            ->expects($this->never())
            ->method('recalculateAllUsersRunningBalance');

        $this->dispatch(new Request(array('scriptname.php', "re-calc-balance-user $email")));

        $this->assertResponseStatusCode(0);
        $this->assertModuleName('jhflexitime');
        $this->assertControllerName('jhflexitime\controller\runningbalancecli');
        $this->assertControllerClass('runningbalanceclicontroller');
        $this->assertActionName('re-calc-running-balance');
        $this->assertMatchedRouteName('re-calc-balance-user');
    }

    public function testReCalculateBalanceThrowsExceptionIfUserDoesNotExist()
    {
        $email  = 'aydin@wearejh.com';

        $this->userRepository
            ->expects($this->once())
            ->method('findOneByEmail')
            ->with($email)
            ->will($this->returnValue(null));

        $this->runningBalanceService
            ->expects($this->never())
            ->method('recalculateAllUsersRunningBalance');

        $this->runningBalanceService
            ->expects($this->never())
            ->method('recalculateUserRunningBalance');

        $this->dispatch(new Request(array('scriptname.php', "re-calc-balance-user $email")));
        $this->assertResponseStatusCode(1);
        $this->assertModuleName('jhflexitime');
        $this->assertControllerName('jhflexitime\controller\runningbalancecli');
        $this->assertControllerClass('runningbalanceclicontroller');
        $this->assertActionName('re-calc-running-balance');
        $this->assertMatchedRouteName('re-calc-balance-user');
    }

    public function testReCalculateBalanceProcessesAllUsersIfNoEmailIsPresent()
    {
        $this->consoleAdapter
            ->expects($this->at(0))
            ->method('writeLine')
            ->with("Recalculating Running Balance for all Users", ColorInterface::GREEN);

        $this->consoleAdapter
            ->expects($this->at(1))
            ->method('writeLine')
            ->with("Finished! ", ColorInterface::GREEN);

        $this->runningBalanceService
            ->expects($this->once())
            ->method('recalculateAllUsersRunningBalance');

        $this->dispatch(new Request(array('scriptname.php', "re-calc-balance-all")));

        $this->assertResponseStatusCode(0);
        $this->assertModuleName('jhflexitime');
        $this->assertControllerName('jhflexitime\controller\runningbalancecli');
        $this->assertControllerClass('runningbalanceclicontroller');
        $this->assertActionName('re-calc-running-balance');
        $this->assertMatchedRouteName('re-calc-balance-all');
    }

    public function testSetUserStartingBalance()
    {
        $balance = 10;

        $user   = new User;
        $email  = 'aydin@wearejh.com';

        $this->userRepository
            ->expects($this->once())
            ->method('findOneByEmail')
            ->with($email)
            ->will($this->returnValue($user));

        $this->runningBalanceService
            ->expects($this->once())
            ->method('setUserStartingBalance')
            ->with($user, $balance);

        $this->runningBalanceService
            ->expects($this->once())
            ->method('recalculateUserRunningBalance')
            ->with($user);

        $this->dispatch(new Request(array('scriptname.php', "set user init-balance $email $balance")));

        $this->assertResponseStatusCode(0);
        $this->assertModuleName('jhflexitime');
        $this->assertControllerName('jhflexitime\controller\runningbalancecli');
        $this->assertControllerClass('runningbalanceclicontroller');
        $this->assertActionName('set-user-starting-balance');
        $this->assertMatchedRouteName('set-user-starting-balance');
    }

    public function testSetUserStartingBalanceThrowsExceptionIfUserNotExist()
    {
        $balance    = 10;
        $email      = 'aydin@wearejh.com';

        $this->userRepository
            ->expects($this->once())
            ->method('findOneByEmail')
            ->with($email)
            ->will($this->returnValue(null));

        $this->runningBalanceService
            ->expects($this->never())
            ->method('setUserStartingBalance');

        $this->runningBalanceService
            ->expects($this->never())
            ->method('recalculateUserRunningBalance');

        $this->dispatch(new Request(array('scriptname.php', "set user init-balance $email $balance")));

        $this->assertResponseStatusCode(1);
        $this->assertModuleName('jhflexitime');
        $this->assertControllerName('jhflexitime\controller\runningbalancecli');
        $this->assertControllerClass('runningbalanceclicontroller');
        $this->assertActionName('set-user-starting-balance');
        $this->assertMatchedRouteName('set-user-starting-balance');
    }
}
