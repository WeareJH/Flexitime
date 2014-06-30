<?php

namespace JhFlexiTime\Service;

use JhFlexiTime\Entity\Booking;
use JhFlexiTime\Repository\BookingRepositoryInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use ZfcUser\Entity\UserInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\EventManager;
use JhFlexiTime\Options\ModuleOptions;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * Class BookingService
 * @package JhFlexiTime\Service
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingService
{

    /**
     * @var \JhFlexiTime\Repository\BookingRepositoryInterface
     */
    protected $bookingRepository;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * @var \JhFlexiTime\Options\ModuleOptions
     */
    protected $options;

    /**
     * @var \JhFlexiTime\Service\PeriodService
     */
    protected $periodService;

    /**
     * @var \DoctrineModule\Stdlib\Hydrator\DoctrineObject
     */
    protected $hydrator;

    /**
     * @var InputFilterInterface
     */
    protected $inputFilter;

    /**
     * @param ModuleOptions $options
     * @param BookingRepositoryInterface $bookingRepository
     * @param ObjectManager $objectManager
     * @param PeriodServiceInterface $periodService
     * @param HydratorInterface $doctrineHydrator
     * @param InputFilterInterface $bookingInputFilter
     */
    public function __construct(
        ModuleOptions $options,
        BookingRepositoryInterface $bookingRepository,
        ObjectManager $objectManager,
        PeriodServiceInterface $periodService,
        HydratorInterface $doctrineHydrator,
        InputFilterInterface $bookingInputFilter
    ) {
        $this->options              = $options;
        $this->bookingRepository    = $bookingRepository;
        $this->objectManager        = $objectManager;
        $this->periodService        = $periodService;
        $this->hydrator             = $doctrineHydrator;
        $this->inputFilter          = $bookingInputFilter;
    }

    /**
     * @param array $data
     * @param UserInterface $user
     * @return array
     */
    public function create(array $data, UserInterface $user)
    {
        $this->inputFilter->setData($data);
        if (!$this->inputFilter->isValid()) {
            return array(
                'messages'  => $this->inputFilter->getMessages(),
            );
        }

        $booking = new Booking;
        $booking->setBalance(0 - $this->options->getHoursInDay());
        $this->hydrator->hydrate($this->inputFilter->getValues(), $booking);
        $booking->setUser($user);

        $totalHours = $this->periodService->calculateHourDiff($booking->getStartTime(), $booking->getEndTime());
        $booking->setTotal($totalHours);

        $this->getEventManager()->trigger(__FUNCTION__ . '.pre', null, array('booking' => $booking));
        $this->objectManager->persist($booking);
        $this->objectManager->flush();
        $this->getEventManager()->trigger(__FUNCTION__ . '.post', null, array('booking' => $booking));

        return $booking;
    }

    /**
     * @param array $data
     * @param $id
     * @param UserInterface $user
     * @return array
     */
    public function update($id, array $data, UserInterface $user)
    {
        try {
            $booking = $this->getBookingByUserAndId($user, $id);
        } catch (\Exception $e) {
            return [
                'messages' => ['Booking Does Not Exist']
            ];
        }

        $this->inputFilter->setData($data);
        if (!$this->inputFilter->isValid()) {
            return [
                'messages' => $this->inputFilter->getMessages(),
            ];
        }

        $this->hydrator->hydrate($this->inputFilter->getValues(), $booking);

        $totalHours = $this->periodService->calculateHourDiff($booking->getStartTime(), $booking->getEndTime());
        $booking->setTotal($totalHours);

        $this->getEventManager()->trigger(__FUNCTION__ . '.pre', null, ['booking' => $booking]);
        $this->objectManager->flush();
        $this->getEventManager()->trigger(__FUNCTION__ . '.post', null, ['booking' => $booking]);

        return $booking;
    }

    /**
     * @param Booking $booking
     */
    public function delete(Booking $booking)
    {
        $this->getEventManager()->trigger(__FUNCTION__ . '.pre', null, array('booking' => $booking));
        $this->objectManager->remove($booking);
        $this->objectManager->flush();
        $this->getEventManager()->trigger(__FUNCTION__ . '.post', null, array('booking' => $booking));
    }

    /**
     * @param UserInterface $user
     * @param int $id
     * @return object
     * @throws \Exception
     */
    public function getBookingByUserAndId(UserInterface $user, $id)
    {
        $row = $this->bookingRepository->findOneBy(array('id' => (int) $id, 'user' => $user));
        if (!is_object($row)) {
            throw new \Exception("Could not find Booking");
        }

        return $row;
    }

    /**
     * @param UserInterface $user
     * @param \DateTime $date
     * @return array
     */
    public function getUserBookingsForMonth(UserInterface $user, \DateTime $date)
    {
        $period = new \DatePeriod(
            new \DateTime(sprintf('first day of %s', $date->format('F Y'))),
            new \DateInterval('P1D'),
            new \DateTime(sprintf('last day of %s 23:59:59', $date->format('F Y')))
        );

        $bookedDays = $this->bookingRepository->findByUserAndMonth($user, $date);

        $bookingsToReturn =  $this->parseDatesIntoWeeks($bookedDays, $date);


        $dates = array();
        foreach ($period as $day) {
            $dayNum = $day->format('N');

            /* Excluding days 6 & 7 (Saturday & Sunday). */
            if ($dayNum < 6) {
                $dates[$day->format('d-m-y')] = array(
                    'date'      => $day,
                    'day_num'   => $dayNum
                );
            }
        }

        foreach ($bookedDays as $booking) {
            //only ass booking is it is on an allowed day
            //eg do not process any weekend bookings
            if (isset($dates[$booking->getDate()->format('d-m-y')])) {
                $dates[$booking->getDate()->format('d-m-y')]['booking'] = $booking;
            }
        }

        $weeks = array();
        $weekCounter = 0;
        $monthWorked = 0;
        foreach ($dates as $date) {
            if (!isset($weeks[$weekCounter])) {
                $weeks[$weekCounter] = array(
                    'dates'  => array($date),
                    'workedHours'  => 0,
                );

            } else {
                $weeks[$weekCounter]['dates'][] = $date;
            }

            if (isset($date['booking'])) {
                $weeks[$weekCounter]['workedHours'] += $date['booking']->getTotal();
                $monthWorked += $date['booking']->getTotal();
            }

            /* Reset day counter. Start new week after day 5 (Friday).
               Day 5 is used as we are already excluding 6 & 7. */
            if ($date['day_num'] == 5) {
                $weekCounter++;
            }
        }

        $monthAvailable = 0;
        $monthBalance = 0;
        foreach ($weeks as $key => $week) {
            $numDays    = count($week['dates']);
            $totalHours = $numDays *  $this->options->getHoursInDay();
            $monthAvailable += $totalHours;
            $weeks[$key]['totalHours']  = $totalHours;
            $weeks[$key]['balance']     =  $weeks[$key]['workedHours'] - $totalHours;
            $monthBalance += ($weeks[$key]['workedHours'] - $totalHours);
        }

        return $bookingsToReturn;
    }

    /**
     * @param Booking[] $bookings
     * @param \DateTime $date
     * @return Booking[]
     */
    public function parseDatesIntoWeeks(array $bookings, \DateTime $date = null)
    {

        $period = new \DatePeriod(
            new \DateTime(sprintf('first day of %s', $date->format('F Y'))),
            new \DateInterval('P1D'),
            new \DateTime(sprintf('last day of %s 23:59:59', $date->format('F Y')))
        );

        $returnBookings = [];
        foreach($period as $date) {
            $dayNum = $date->format('N');

            //should be config
            if ($dayNum > 5) continue;

            if (!$booking = $this->bookingExistsForDate($bookings, $date)) {
                $booking = new Booking();
                $booking->setDate($date);
            }

            $returnBookings[$date->getTimestamp()] = $booking;
        }

        return $returnBookings;
    }

    /**
     * @param Booking[] $bookings
     * @param \DateTime $date
     * @return bool
     */
    public function bookingExistsForDate(array $bookings, \DateTime $date) {
        foreach ($bookings as $booking) {
            if ($booking->getDate()->format('d-m-Y') == $date->format('d-m-Y')) {
                return $booking;
            }
        }

        return false;
    }

    /**
     * Get next/prev month/year info
     *
     * @param \DateTime $date
     * @return array
     */
    public function getPagination(\DateTime $date)
    {
        $date->setTime(0, 0);

        $prev = clone $date;
        $next = clone $date;

        $prev->modify('first day of last month');
        $next->modify('first day of next month');

        return [
            'current'   => ['m' => $date->format('M'), 'y' => $date->format('Y')],
            'next'      => ['m' => $next->format('M'), 'y' => $next->format('Y')],
            'prev'      => ['m' => $prev->format('M'), 'y' => $prev->format('Y')],
        ];
    }

    /**
     * @param  EventManagerInterface $eventManager
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->addIdentifiers(array(
            get_called_class()
        ));

        $this->eventManager = $eventManager;
    }

    /**
     * @return EventManagerInterface
     */
    public function getEventManager()
    {
        if (null === $this->eventManager) {
            $this->setEventManager(new EventManager());
        }

        return $this->eventManager;
    }
}
