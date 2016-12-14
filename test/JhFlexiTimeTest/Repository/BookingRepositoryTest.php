<?php

namespace JhFlexiTimeTest\Repository;

use JhFlexiTime\Entity\Booking;
use JhFlexiTime\Repository\BookingRepository;
use JhFlexiTimeTest\Fixture\FullMonthBookings;
use JhFlexiTimeTest\Fixture\MultiUserBookings;
use JhFlexiTimeTest\Fixture\MultiUserMultiMonthBookings;
use JhFlexiTimeTest\Fixture\SingleBookingInMonth;
use JhUser\Entity\User;
use JhFlexiTimeTest\Util\ServiceManagerFactory;
use JhFlexiTimeTest\Fixture\SingleUser;
use JhFlexiTimeTest\Fixture\BookingsNotInMonth;
use JhFlexiTime\DateTime\DateTime;

/**
 * Class BookingRepositoryTest
 * @package JhFlexiTimeTest\Repository
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class BookingRepositoryTest extends \PHPUnit_Framework_TestCase
{

    protected $repository;
    protected $objectRepository;
    protected $fixtureExecutor;

    public function setUp()
    {
        $this->objectRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $sm = ServiceManagerFactory::getServiceManager();
        $this->repository       = $sm->get('JhFlexiTime\Repository\BookingRepository');
        $this->fixtureExecutor  = $sm->get('Doctrine\Common\DataFixtures\Executor\AbstractExecutor');
        $this->assertInstanceOf('JhFlexiTime\Repository\BookingRepository', $this->repository);
    }

    public function testFindAllByUserReturnsRecordsOnlyByUserSortedByDateAscending()
    {
        $user = new User();
        $user
            ->setEmail("aydin@hotmail.co.uk")
            ->setPassword("password");
        $bookingsFixture = new MultiUserBookings($user);
        $this->fixtureExecutor->execute([$bookingsFixture]);
        $bookings = $this->repository->findAllByUser($user);
        $this->assertEquals(count($bookingsFixture->getBookingsForUser()), count($bookings));

        $lastDate = null;
        foreach ($bookings as $booking) {

            if (!$lastDate) {
                $lastDate = $booking->getDate();
            } else {
                $this->assertGreaterThanOrEqual($lastDate, $booking->getDate());
            }
            $this->assertEquals($booking->getUser()->getId(), $user->getId());
            $this->assertEquals($booking->getUser()->getEmail(), $user->getEmail());
        }
    }

    public function testFindByUserAndMonth()
    {
        $user = new User();
        $user
            ->setEmail("aydin@hotmail.co.uk")
            ->setPassword("password");

        $date = new DateTime("10 September 2014");
        $bookingsFixture = new MultiUserMultiMonthBookings($user);
        $this->fixtureExecutor->execute([$bookingsFixture]);
        $bookings = $this->repository->findByUserAndMonth($user, $date);
        $this->assertEquals(5, count($bookings));
        array_map(function (Booking $booking) use ($user) {
            $this->assertEquals($booking->getUser()->getId(), $user->getId());
            $this->assertEquals($booking->getUser()->getEmail(), $user->getEmail());
        }, $bookings);
    }

    public function testIsUsersFirstBookingForMonthReturnsTrueIfNoBookingsExist()
    {
        $userFixture = new SingleUser;
        $this->fixtureExecutor->execute([$userFixture]);

        $date = new DateTime("10 May 2014");
        $this->assertTrue($this->repository->isUsersFirstBookingForMonth($userFixture->getUser(), $date));
    }

    public function testIsUsersFirstBookingForMonthReturnsTrueIfNoBookingsExistInGivenMonth()
    {
        $user = new User();
        $user
            ->setEmail("aydin@hotmail.co.uk")
            ->setPassword("password");

        $bookingFixture = new BookingsNotInMonth($user);
        $this->fixtureExecutor->execute([$bookingFixture]);

        $this->assertTrue(
            $this->repository->isUsersFirstBookingForMonth($user, $bookingFixture->getMonthWithNoBookings())
        );
    }

    public function testIsUsersFirstBookingForMonthReturnsFalseIfBookingExistsInMonth()
    {
        $user = new User();
        $user
            ->setEmail("aydin@hotmail.co.uk")
            ->setPassword("password");

        $date = new DateTime("10 May 2014");

        $bookingFixture = new SingleBookingInMonth($user, $date);
        $this->fixtureExecutor->execute([$bookingFixture]);

        $this->assertFalse($this->repository->isUsersFirstBookingForMonth($user, $date));
    }

    public function testGetMonthBookedToDateTotalByUserReturnsTotalUpToDate()
    {
        $user = new User();
        $user
            ->setEmail("aydin@hotmail.co.uk")
            ->setPassword("password");

        $bookingFixture = new FullMonthBookings($user);
        $this->fixtureExecutor->execute([$bookingFixture]);

        $date = clone $bookingFixture->getMonth();
        $date->modify("+3 days");
        $bookingsTotal = $this->repository->getMonthBookedToDateTotalByUser($user, $date);

        $total = array_reduce($bookingFixture->getBookings(), function ($totalHours, Booking $booking) use ($date) {
            if ($booking->getDate() <= $date) {
                $totalHours += $booking->getTotal();
            }
            return $totalHours;
        }, 0);

        $this->assertEquals($total, $bookingsTotal);
    }

    public function testGetMonthBookedToDateTotalByUserReturnsZeroIfNoBookings()
    {
        $userFixture = new SingleUser;
        $this->fixtureExecutor->execute([$userFixture]);

        $date = new DateTime;
        $bookingsTotal = $this->repository->getMonthBookedToDateTotalByUser($userFixture->getUser(), $date);
        $this->assertEquals(0, $bookingsTotal);
    }

    public function testGetMonthBookedTotalByUserReturnsTotalForWholeMonth()
    {
        $user = new User();
        $user
            ->setEmail("aydin@hotmail.co.uk")
            ->setPassword("password");

        $bookingFixture = new FullMonthBookings($user);
        $this->fixtureExecutor->execute([$bookingFixture]);

        $date = clone $bookingFixture->getMonth();
        $date->modify("+3 days");
        $bookingsTotal = $this->repository->getMonthBookedTotalByUser($user, $date);

        $total = array_reduce($bookingFixture->getBookings(), function ($totalHours, Booking $booking) use ($date) {
            return $totalHours + $booking->getTotal();
        }, 0);

        $this->assertEquals($total, $bookingsTotal);
    }

    public function testGetMonthBookedTotalByUserReturnsZeroIfNoBookings()
    {
        $userFixture = new SingleUser;
        $this->fixtureExecutor->execute([$userFixture]);

        $date = new DateTime;
        $bookingsTotal = $this->repository->getMonthBookedTotalByUser($userFixture->getUser(), $date);
        $this->assertEquals(0, $bookingsTotal);
    }

    public function testGetTotalBookedBetweenByUserReturnsTotalForPeriod()
    {
        $user = new User();
        $user
            ->setEmail("aydin@hotmail.co.uk")
            ->setPassword("password");

        $bookingFixture = new FullMonthBookings($user);
        $this->fixtureExecutor->execute([$bookingFixture]);

        $startDate = clone $bookingFixture->getMonth();
        $startDate->modify("+3 days");
        $endDate = clone $bookingFixture->getMonth();
        $endDate->modify("+15 days");

        $bookingsTotal = $this->repository->getTotalBookedBetweenByUser($user, $startDate, $endDate);

        $total = array_reduce(
            $bookingFixture->getBookings(),
            function ($totalHours, Booking $booking) use ($startDate, $endDate) {
                if ($booking->getDate() >= $startDate && $booking->getDate() <= $endDate) {
                    $totalHours += $booking->getTotal();
                }
                return $totalHours;
            },
            0
        );

        $this->assertEquals($total, $bookingsTotal);
    }

    public function testGetTotalBookedBetweenByUserReturnsZeroIfNoBookings()
    {
        $userFixture = new SingleUser;
        $this->fixtureExecutor->execute([$userFixture]);

        $dateA = new DateTime;
        $dateB = new DateTime;
        $bookingsTotal = $this->repository->getTotalBookedBetweenByUser($userFixture->getUser(), $dateA, $dateB);
        $this->assertEquals(0, $bookingsTotal);
    }

    public function testFindByIdThrowsExceptionBecauseOfCompositeKey()
    {
        $user = new User();
        $user
            ->setEmail("aydin@hotmail.co.uk")
            ->setPassword("password");
        $bookingFixture = new SingleBookingInMonth($user, new DateTime);
        $this->fixtureExecutor->execute([$bookingFixture]);

        $message  = 'Binding an entity with a composite primary key to a query is not supported. You should split the';
        $message .= ' parameter into the explicit fields and bind them separately.';
        $this->setExpectedException('Doctrine\ORM\ORMInvalidArgumentException', $message);
        $this->repository->find($bookingFixture->getBooking()->getId());
    }

    public function testFindByReturnsEmptyIfNonExist()
    {
        $this->assertEmpty($this->repository->findBy(['user' => 1]));
    }

    public function testFindAll()
    {
        $objectRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $objectRepository
            ->expects($this->once())
            ->method('findAll');

        $repository = new BookingRepository($objectRepository);
        $repository->findAll();
    }

    public function testFindOneBy()
    {
        $objectRepository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $args = [];
        $objectRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($args);

        $repository = new BookingRepository($objectRepository);
        $repository->findOneBy($args);
    }

    public function testGetClassNameReturnsCorrectEntityClass()
    {
        $this->assertSame(
            'JhFlexiTime\Entity\Booking',
            $this->repository->getClassName()
        );
    }
}
