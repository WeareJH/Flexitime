<?php

namespace JhFlexiTimeTest\Service;

use JhFlexiTime\DateTime\DateTime;
use JhFlexiTime\Entity\Booking;
use JhFlexiTime\Service\BookingService;
use JhFlexiTime\Options\ModuleOptions;
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
        $ret = $this->bookingService->create($data);
        $this->assertEquals(-5.5, $ret->getBalance());
        $this->assertEquals(2, $ret->getTotal());
    }

    public function testUpdateBookingReturnsErrorIfBookingNotExist()
    {
        $data   = [];
        $userId = 2;
        $date   = new DateTime("20 September 2014");

        $this->bookingRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['date' => $date, 'user' => $userId])
            ->will($this->returnValue(null));

        $ret = $this->bookingService->update($userId, $date, $data);
        $this->assertSame(['messages' => ['Booking Does Not Exist']], $ret);
    }

    public function testUpdateBookingReturnsErrorIfValidationFails()
    {
        $data = [
            'notes' => 'yo'
        ];
        $booking    = new Booking();
        $userId     = 2;
        $date       = new DateTime("20 September 2014");

        $this->bookingRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['date' => $date, 'user' => $userId])
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

        $ret = $this->bookingService->update($userId, $date, $data);
        $this->assertSame(['messages' => ['notes' => 'ERROR!']], $ret);
    }

    public function testUpdateSavesAfterSuccessfulValidation()
    {
        $data       = ['notes' => 'yo' ];
        $booking    = new Booking();
        $userId     = 2;
        $date       = new DateTime("20 September 2014");
        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');

        $this->bookingRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['date' => $date, 'user' => $userId])
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
        $ret = $this->bookingService->update($userId, $date, $data);
        $this->assertEquals(2, $booking->getTotal());
        $this->assertSame($ret, $booking);
    }

    public function testDeleteBooking()
    {
        $booking    = new Booking();
        $userId     = 2;
        $date       = new DateTime("20 September 2014");

        $this->bookingRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['date' => $date, 'user' => $userId])
            ->will($this->returnValue($booking));

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
        $ret = $this->bookingService->delete($userId, $date);
        $this->assertSame($booking, $ret);
    }

    public function testDeleteBookingReturnsErrorIfBookingNotExist()
    {
        $userId     = 2;
        $date       = new DateTime("20 September 2014");

        $this->bookingRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['date' => $date, 'user' => $userId])
            ->will($this->returnValue(null));

        $ret = $this->bookingService->delete($userId, $date);
        $this->assertSame(['messages' => ['Booking Does Not Exist']], $ret);
    }

    public function testGetBookingByUserAndIdReturnsBooking()
    {
        $userId     = 2;
        $date       = new DateTime("20 September 2014");
        $booking    = new Booking();

        $this->bookingRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['date' => $date, 'user' => $userId])
            ->will($this->returnValue($booking));

        $ret = $this->bookingService->getBookingByUserAndDate($userId, $date);
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

    public function testGetUserBookingsForMonth()
    {
        $user = new User();
        $date = new DateTime("14 May 2014");

        $bookings = $this->getBookingCollection($date);

        $this->bookingRepository
             ->expects($this->once())
             ->method('findByUserAndMonth')
             ->with($user, $date)
             ->will($this->returnValue($bookings));

        $ret = $this->bookingService->getUserBookingsForMonth($user, $date);

        $expected = [
            'weeks' => [
                [
                    'dates' => [
                        [
                            'date'      => new \DateTime('1-05-2014'),
                            'day_num'   => 4,
                            'booking'   => $bookings[6]
                        ],
                        [
                            'date'      => new \DateTime('2-05-2014'),
                            'day_num'   => 5,
                        ],

                    ],
                    'totalHours'    => 15,
                    'balance'       => -7.5,
                    'workedHours'   => 7.5
                ],
                [
                    'dates' => [
                        [
                            'date'      => new \DateTime('5-05-2014'),
                            'day_num'   => 1,
                        ],
                        [
                            'date'      => new \DateTime('6-05-2014'),
                            'day_num'   => 2,
                        ],
                        [
                            'date'      => new \DateTime('7-05-2014'),
                            'day_num'   => 3,
                        ],
                        [
                            'date'      => new \DateTime('8-05-2014'),
                            'day_num'   => 4,
                        ],
                        [
                            'date'      => new \DateTime('9-05-2014'),
                            'day_num'   => 5,
                        ],

                    ],
                    'totalHours'    => 37.5,
                    'balance'       => -37.5,
                    'workedHours'   => 0
                ],
                [
                    'dates' => [
                        [
                            'date'      => new \DateTime('12-05-2014'),
                            'day_num'   => 1,
                        ],
                        [
                            'date'      => new \DateTime('13-05-2014'),
                            'day_num'   => 2,
                        ],
                        [
                            'date'      => new \DateTime('14-05-2014'),
                            'day_num'   => 3,
                            'booking'   => $bookings[0]
                        ],
                        [
                            'date'      => new \DateTime('15-05-2014'),
                            'day_num'   => 4,
                            'booking'   => $bookings[1]
                        ],
                        [
                            'date'      => new \DateTime('16-05-2014'),
                            'day_num'   => 5,
                            'booking'   => $bookings[2]
                        ],

                    ],
                    'totalHours'    => 37.5,
                    'balance'       => -15,
                    'workedHours'   => 22.5
                ],
                [
                    'dates' => [
                        [
                            'date'      => new \DateTime('19-05-2014'),
                            'day_num'   => 1,
                            'booking'   => $bookings[5]
                        ],
                        [
                            'date'      => new \DateTime('20-05-2014'),
                            'day_num'   => 2,
                        ],
                        [
                            'date'      => new \DateTime('21-05-2014'),
                            'day_num'   => 3,
                        ],
                        [
                            'date'      => new \DateTime('22-05-2014'),
                            'day_num'   => 4,
                        ],
                        [
                            'date'      => new \DateTime('23-05-2014'),
                            'day_num'   => 5,
                        ],

                    ],
                    'totalHours'    => 37.5,
                    'balance'       => -30,
                    'workedHours'   => 7.5
                ],
                [
                    'dates' => [
                        [
                            'date'      => new \DateTime('26-05-2014'),
                            'day_num'   => 1,
                        ],
                        [
                            'date'      => new \DateTime('27-05-2014'),
                            'day_num'   => 2,
                        ],
                        [
                            'date'      => new \DateTime('28-05-2014'),
                            'day_num'   => 3,
                        ],
                        [
                            'date'      => new \DateTime('29-05-2014'),
                            'day_num'   => 4,
                        ],
                        [
                            'date'      => new \DateTime('30-05-2014'),
                            'day_num'   => 5,
                            'booking'   => $bookings[7]
                        ],

                    ],
                    'totalHours'    => 37.5,
                    'balance'       => -30,
                    'workedHours'   => 7.5
                ],
            ],
            'workedMonth'       => [
                'availableHours'    => 165,
                'monthBalance'      => -120,
                'hoursWorked'       => 45
            ],
        ];

        $this->assertEquals($expected, $ret);

    }

    protected function getBookingCollection(\DateTime $date, $addFalsePositives = true)
    {
        $bookings = [
            $this->getBooking(new DateTime("14 May 2014")),
            $this->getBooking(new DateTime("15 May 2014")),
            $this->getBooking(new DateTime("16 May 2014")),
            $this->getBooking(new DateTime("17 May 2014")),
            $this->getBooking(new DateTime("18 May 2014")),
            $this->getBooking(new DateTime("19 May 2014")),
            $this->getBooking(new DateTime("1 May 2014")),
            $this->getBooking(new DateTime("30 May 2014")),
            $this->getBooking(new DateTime("31 May 2014")),
        ];

        if ($addFalsePositives) {
            $bookings[] = $this->getBooking(new DateTime("5 June 2014"));
            $bookings[] = $this->getBooking(new DateTime("3 April 2014"));
        }

        return $bookings;
    }

    protected function getBooking(DateTime $date)
    {
        $booking = new Booking();
        $booking->setDate($date);
        $booking->setTotal(7.5);
        return $booking;
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
