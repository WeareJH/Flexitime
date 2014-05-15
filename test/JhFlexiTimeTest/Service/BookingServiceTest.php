<?php

namespace JhFlexiTimeTest\Service;

use JhFlexiTime\Entity\Booking;
use JhFlexiTime\Service\BookingService;
use JhFlexiTime\Options\ModuleOptions;
use JhFlexiTime\Service\PeriodServiceInterface;
use JhFlexiTime\Repository\BookingRepositoryInterface;
use JhUser\Entity\User;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\EventManager\EventManagerInterface;

/**
 * Class BookingServiceTest
 * @package JhFlexiTimeTest\Service
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BookingServiceTest extends \PHPUnit_Framework_TestCase
{
    protected $periodService;
    protected $options;
    protected $bookingRepository;
    protected $objectManager;
    protected $hydrator;
    protected $inputFilter;
    protected $bookingService;


    /**
     * Create Service
     */
    public function setUp()
    {
        $this->periodService = $this->getMock('JhFlexiTime\Service\PeriodServiceInterface');
        $this->options = $this->getOptions();
        $this->bookingRepository = $this->getMock('JhFlexiTime\Repository\BookingRepositoryInterface');
        $this->objectManager = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->hydrator = $this->getMock('Zend\Stdlib\Hydrator\HydratorInterface');
        $this->inputFilter = $this->getMock('Zend\InputFilter\InputFilterInterface');
        $this->bookingService = new BookingService(
            $this->options,
            $this->bookingRepository,
            $this->objectManager,
            $this->periodService,
            $this->hydrator,
            $this->inputFilter
        );
    }

    public function testCreateBookingReturnsErrorIfValidationFails()
    {
        $data   = ['notes' => 'yo'];
        $user   = new User();

        $this->inputFilter
            ->expects($this->once())
            ->method('setData')
            ->with($data);

        $this->inputFilter
            ->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $this->inputFilter
            ->expects($this->once())
            ->method('getMessages')
            ->will($this->returnValue(['notes' => 'ERROR!']));

        $ret = $this->bookingService->create($data, $user);
        $this->assertSame(['messages' => ['notes' => 'ERROR!']], $ret);
    }

    public function testCreateSavesAfterSuccessfulValidation()
    {
        $data       = ['notes' => 'yo' ];
        $user       = new User();
        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');

        $this->inputFilter
            ->expects($this->once())
            ->method('setData')
            ->with($data);

        $this->inputFilter
            ->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->inputFilter
            ->expects($this->once())
            ->method('getValues')
            ->will($this->returnValue(['notes' => 'yo']));

        $this->hydrator
            ->expects($this->once())
            ->method('hydrate')
            ->with(['notes' => 'yo'], $this->isInstanceOf('JhFlexiTime\Entity\Booking'));

        $this->periodService
            ->expects($this->once())
            ->method('calculateHourDiff')
            ->with($this->isInstanceOf('DateTime'), $this->isInstanceOf('DateTime'))
            ->will($this->returnValue(2));

        $this->objectManager
            ->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf('JhFlexiTime\Entity\Booking'));

        $this->objectManager
            ->expects($this->once())
            ->method('flush');

        $eventManager
            ->expects($this->at(1))
            ->method('trigger');

        $eventManager
            ->expects($this->at(2))
            ->method('trigger');

        $this->bookingService->setEventManager($eventManager);
        $ret = $this->bookingService->create($data, $user);
        $this->assertEquals(-7.5, $ret->getBalance());
        $this->assertEquals(2, $ret->getTotal());
        $this->assertSame($user, $ret->getUser());
    }

    public function testUpdateBookingReturnsErrorIfBookingNotExist()
    {
        $id     = 10;
        $data   = [];
        $user   = new User();

        $this->bookingRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => $id, 'user' => $user])
            ->will($this->returnValue(null));

        $ret = $this->bookingService->update($id, $data, $user);
        $this->assertSame(['messages' => ['Booking Does Not Exist']], $ret);
    }

    public function testUpdateBookingReturnsErrorIfValidationFails()
    {
        $id     = 10;
        $data = [
            'notes' => 'yo'
        ];
        $user   = new User();
        $booking = new Booking();

        $this->bookingRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => $id, 'user' => $user])
            ->will($this->returnValue($booking));

        $this->inputFilter
             ->expects($this->once())
             ->method('setData')
             ->with($data);

        $this->inputFilter
             ->expects($this->once())
             ->method('isValid')
             ->will($this->returnValue(false));

        $this->inputFilter
            ->expects($this->once())
            ->method('getMessages')
            ->will($this->returnValue(['notes' => 'ERROR!']));

        $ret = $this->bookingService->update($id, $data, $user);
        $this->assertSame(['messages' => ['notes' => 'ERROR!']], $ret);
    }

    public function testUpdateSavesAfterSuccessfulValidation()
    {
        $id         = 10;
        $data       = ['notes' => 'yo' ];
        $user       = new User();
        $booking    = new Booking();
        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');

        $this->bookingRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => $id, 'user' => $user])
            ->will($this->returnValue($booking));

        $this->inputFilter
            ->expects($this->once())
            ->method('setData')
            ->with($data);

        $this->inputFilter
            ->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->inputFilter
            ->expects($this->once())
            ->method('getValues')
            ->will($this->returnValue(['notes' => 'yo']));

        $this->hydrator
             ->expects($this->once())
             ->method('hydrate')
             ->with(['notes' => 'yo'], $booking);

        $this->periodService
             ->expects($this->once())
             ->method('calculateHourDiff')
             ->with($booking->getStartTime(), $booking->getEndTime())
             ->will($this->returnValue(2));

        $this->objectManager
            ->expects($this->once())
            ->method('flush');

        $eventManager
            ->expects($this->at(1))
            ->method('trigger')
            ->with('update.pre', null, ['booking' => $booking]);

        $eventManager
            ->expects($this->at(2))
            ->method('trigger')
            ->with('update.post', null, ['booking' => $booking]);

        $this->bookingService->setEventManager($eventManager);
        $ret = $this->bookingService->update($id, $data, $user);
        $this->assertEquals(2, $booking->getTotal());
        $this->assertSame($ret, $booking);
    }

    public function testDeleteBooking()
    {
        $booking = new Booking();
        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
        $eventManager
            ->expects($this->at(1))
            ->method('trigger')
            ->with('delete.pre', null, ['booking' => $booking]);

        $eventManager
            ->expects($this->at(2))
            ->method('trigger')
            ->with('delete.post', null, ['booking' => $booking]);

        $this->objectManager
             ->expects($this->once())
             ->method('remove')
             ->with($booking);

        $this->objectManager
            ->expects($this->once())
            ->method('flush');

        $this->bookingService->setEventManager($eventManager);
        $this->bookingService->delete($booking);
    }

    public function testGetBookingByUserAndIdThrowsExceptionIfNotExist()
    {
        $user = new User();

        $this->bookingRepository
             ->expects($this->once())
             ->method('findOneBy')
             ->with(['id' => 1, 'user' => $user])
             ->will($this->returnValue(null));

        $this->setExpectedException('Exception', 'Could not find Booking');
        $this->bookingService->getBookingByUserAndId($user, 1);
    }

    public function testGetBookingByUserAndIdReturnsBooking()
    {
        $user = new User();
        $booking = new Booking();

        $this->bookingRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => 1, 'user' => $user])
            ->will($this->returnValue($booking));

        $ret = $this->bookingService->getBookingByUserAndId($user, 1);
        $this->assertSame($booking, $ret);
    }

    public function testGetPagination()
    {
        $date = new \DateTime("15 May 2014");

        $return = $this->bookingService->getPagination($date);
        $expected = [
            'current'   => ['m' => 'May', 'y' => '2014'],
            'next'      => ['m' => 'Jun', 'y' => '2014'],
            'prev'      => ['m' => 'Apr', 'y' => '2014'],
        ];

        $this->assertSame($expected, $return);
        $this->assertEquals('15-May-2014', $date->format('d-M-Y'));
    }

    public function testGetEventManagerReturnsNewEventManagerIfNotSet()
    {
        $refObject   = new \ReflectionObject($this->bookingService);
        $refProperty = $refObject->getProperty('eventManager');
        $refProperty->setAccessible(true);
        $this->assertNull($refProperty->getValue($this->bookingService));
        $this->assertInstanceOf('Zend\EventManager\EventManagerInterface', $this->bookingService->getEventManager());
    }

    public function testSetEventManager()
    {
        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
        $this->bookingService->setEventManager($eventManager);

        $refObject   = new \ReflectionObject($this->bookingService);
        $refProperty = $refObject->getProperty('eventManager');
        $refProperty->setAccessible(true);
        $this->assertSame($eventManager, $refProperty->getValue($this->bookingService));
    }

    /**
     * @return ModuleOptions
     */
    public function getOptions()
    {
        $options = new ModuleOptions();
        $options->setHoursInDay(7.5)
            ->setLunchDuration(1);

        return $options;
    }



}
