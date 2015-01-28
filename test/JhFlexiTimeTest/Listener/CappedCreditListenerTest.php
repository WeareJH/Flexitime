<?php

namespace JhFlexiTimeTest\Listener;

use JhFlexiTime\DateTime\DateTime;
use JhFlexiTime\Entity\RunningBalance;
use JhFlexiTime\Listener\CappedCreditListener;
use JhUser\Entity\User;
use PHPUnit_Framework_TestCase;
use JhFlexiTime\Options\ModuleOptions;
use Zend\EventManager\Event;

/**
 * Class CappedCreditListenerTest
 * @package JhFlexiTimeTest\Listener
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CappedCreditListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var CappedCreditListener
     */
    protected $listener;

    /**
     * @var \JhFlexiTime\Service\CappedCreditService
     */
    protected $service;

    /**
     * @var \JhFlexiTime\Options\ModuleOptions
     */
    protected $options;

    public function setUp()
    {
        $this->service = $this->getMockBuilder('JhFlexiTime\Service\CappedCreditService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->options  = new ModuleOptions([]);
        $this->listener = new CappedCreditListener($this->service, $this->options);
    }

    public function testAttachDoesNotAddListenersIfCappingDisabled()
    {
        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
        $eventManager
            ->expects($this->never())
            ->method('getSharedManager');

        $this->listener->attach($eventManager);
    }

    public function testAttachAddsListenersIfCreditCappingEnables()
    {
        $this->options->setCreditCapEnabled(true);

        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
        $sharedManager = $this->getMock('Zend\EventManager\SharedEventManagerInterface');

        $eventManager
            ->expects($this->once())
            ->method('getSharedManager')
            ->will($this->returnValue($sharedManager));

        $sharedManager
            ->expects($this->at(0))
            ->method('attach')
            ->with(
                'JhFlexiTime\Service\RunningBalanceService',
                'reIndexUserRunningBalance.pre',
                [$this->listener, 'clearCappedCreditRecords'],
                100
            );

        $sharedManager
            ->expects($this->at(1))
            ->method('attach')
            ->with(
                'JhFlexiTime\Service\RunningBalanceService',
                'addMonthBalance.post',
                [$this->listener, 'applyCreditCarryLimit'],
                100
            );

        $this->listener->attach($eventManager);
    }

    public function testCreditOverageIsDeductedIfOverCreditLimit()
    {
        $this->options->setCreditCaps([
            '10-2015' => 10,
        ]);

        $runningBalance = new RunningBalance;
        $user = new User;
        $runningBalance->setUser($user);
        $runningBalance->setBalance(25);

        $e = new Event;
        $e->setParam('runningBalance', $runningBalance);
        $e->setParam('month', new DateTime('10-10-2015'));

        $this->service
            ->expects($this->once())
            ->method('create')
            ->with($user, 15, new DateTime('10-10-2015'));

        $this->listener->applyCreditCarryLimit($e);
        $this->assertEquals(10, $runningBalance->getBalance());
    }

    public function testExistingRecordsAreRemovedOnClear()
    {
        $runningBalance = new RunningBalance;
        $user = new User;
        $runningBalance->setUser($user);

        $this->service
            ->expects($this->once())
            ->method('clearCappedCreditEntries')
            ->with($user);

        $e = new Event;
        $e->setParam('runningBalance', $runningBalance);
        $this->listener->clearCappedCreditRecords($e);
    }
}
