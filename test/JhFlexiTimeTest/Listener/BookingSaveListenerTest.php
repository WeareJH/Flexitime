<?php

namespace JhFlexiTimeTest\Listener;

use JhFlexiTime\Entity\UserSettings;
use JhFlexiTime\Listener\BookingSaveListener;
use JhFlexiTime\Options\ModuleOptions;
use JhFlexiTime\Entity\RunningBalance;
use JhUser\Entity\User;
use ZfcUser\Entity\UserInterface;
use Zend\EventManager\Event;
use JhFlexiTime\Entity\Booking;
use JhFlexiTime\DateTime\DateTime;

/**
 * Class BookingSaveListenerTest
 * @package JhFlexiTimeTest\Listener
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BookingSaveListenerTest extends \PHPUnit_Framework_TestCase
{
    protected $bookingSaveListener;
    protected $runningBalanceService;

    public function setUp()
    {
        $this->runningBalanceService = $this->getMockBuilder('JhFlexiTime\Service\RunningBalanceService')
            ->disableOriginalConstructor()
            ->getMock();

        $this->bookingSaveListener  = new BookingSaveListener(
            new DateTime("12 April 2014"),
            $this->runningBalanceService
        );
    }

    public function testAttach()
    {
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
                'JhFlexiTime\Service\BookingService',
                'create.pre',
                [$this->bookingSaveListener, 'reindexBalance'],
                100
            );

        $sharedManager
            ->expects($this->at(1))
            ->method('attach')
            ->with(
                'JhFlexiTime\Service\BookingService',
                'update.pre',
                [$this->bookingSaveListener, 'reindexBalance'],
                100
            );

        $sharedManager
            ->expects($this->at(2))
            ->method('attach')
            ->with(
                'JhFlexiTime\Service\BookingService',
                'delete.pre',
                [$this->bookingSaveListener, 'reindexBalance'],
                100
            );

        $this->bookingSaveListener->attach($eventManager);
    }

    public function testIfBookingHasDateInPreviousMonthReIndexerIsCalled()
    {
        $user = new User;
        $booking = new Booking;
        $booking->setDate(new DateTime('11 March 2014'));
        $booking->setUser($user);

        $this->runningBalanceService
            ->expects($this->once())
            ->method('reIndexIndividualUserRunningBalance')
            ->with($user);

        $e = new Event();
        $e->setParam('booking', $booking);

        $this->bookingSaveListener->reindexBalance($e);
    }
}
