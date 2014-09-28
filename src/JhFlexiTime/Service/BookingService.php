<?php

namespace JhFlexiTime\Service;

use JhFlexiTime\DateTime\DateTime;
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
     * @return array
     */
    public function create(array $data)
    {
        $this->inputFilter->setData($data);

        if (!$this->inputFilter->isValid()) {
            return [
                'messages'  => $this->inputFilter->getMessages(),
            ];
        }

        $booking = new Booking;
        $booking->setBalance(0 - $this->options->getHoursInDay());
        $this->hydrator->hydrate($this->inputFilter->getValues(), $booking);

        $totalHours = $this->periodService->calculateHourDiff($booking->getStartTime(), $booking->getEndTime());
        $booking->setTotal($totalHours);

        $this->getEventManager()->trigger(__FUNCTION__ . '.pre', null, ['booking' => $booking]);

        try {
            $this->objectManager->persist($booking);
            $this->objectManager->flush();
        } catch (\Exception $e) {
           //log
        }

        $this->getEventManager()->trigger(__FUNCTION__ . '.post', null, ['booking' => $booking]);

        return $booking;
    }

    /**
     * @param $userId
     * @param DateTime $date
     * @param array $data
     * @return Booking|array
     */
    public function update($userId, DateTime $date, array $data)
    {

        $booking = $this->getBookingByUserAndDate($userId, $date);
        if (null === $booking) {
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
     * @param int $userId
     * @param DateTime $date
     * @return array|Booking
     */
    public function delete($userId, DateTime $date)
    {
        $booking = $this->getBookingByUserAndDate($userId, $date);
        if (null === $booking) {
            return [
                'messages' => ['Booking Does Not Exist']
            ];
        }

        $this->getEventManager()->trigger(__FUNCTION__ . '.pre', null, ['booking' => $booking]);
        $this->objectManager->remove($booking);
        $this->objectManager->flush();
        $this->getEventManager()->trigger(__FUNCTION__ . '.post', null, ['booking' => $booking]);

        return $booking;
    }

    /**
     * @param $userId
     * @param DateTime|string $date
     * @throws \Exception
     * @return object
     */
    public function getBookingByUserAndDate($userId, DateTime $date)
    {
        return $this->bookingRepository->findOneBy(['date' => $date, 'user' => $userId]);
    }

    /**
     * @param UserInterface $user
     * @param DateTime $date
     * @return array
     */
    public function getUserBookingsForMonth(UserInterface $user, DateTime $date)
    {
        $period = new \DatePeriod(
            new \DateTime(sprintf('first day of %s', $date->format('F Y'))),
            new \DateInterval('P1D'),
            new \DateTime(sprintf('last day of %s 23:59:59', $date->format('F Y')))
        );

        $bookedDays = $this->bookingRepository->findByUserAndMonth($user, $date);

        $dates = [];
        foreach ($period as $day) {
            $dayNum = $day->format('N');

            /* Excluding days 6 & 7 (Saturday & Sunday). */
            if ($dayNum < 6) {
                $dates[$day->format('d-m-y')] = [
                    'date'      => $day,
                    'day_num'   => $dayNum
                ];
            }
        }

        foreach ($bookedDays as $booking) {
            //only ass booking is it is on an allowed day
            //eg do not process any weekend bookings
            if (isset($dates[$booking->getDate()->format('d-m-y')])) {
                $dates[$booking->getDate()->format('d-m-y')]['booking'] = $booking;
            }

        }

        $weeks = [];
        $weekCounter = 0;
        $monthWorked = 0;
        foreach ($dates as $date) {
            if (!isset($weeks[$weekCounter])) {
                $weeks[$weekCounter] = [
                    'dates'  => [$date],
                    'workedHours'  => 0,
                ];

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

        return [
            'weeks'             => $weeks,
            'workedMonth'       => [
                'availableHours'    => $monthAvailable,
                'monthBalance'      => $monthBalance,
                'hoursWorked'       => $monthWorked
            ],
        ];
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
        $eventManager->addIdentifiers([
            get_called_class()
        ]);

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
