<?php

namespace JhFlexiTime\Listener;

use JhFlexiTime\Service\PeriodServiceInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\AbstractListenerAggregate;
use JhFlexiTime\Service\BalanceService;
use JhFlexiTime\Repository\BookingRepository;

/**
 * Class BookingSaveListener
 * @package JhFlexiTime\Listener
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingSaveListener extends AbstractListenerAggregate
{
    /**
     * @var \JhFlexiTime\Service\BalanceService
     */
    protected $balanceService;

     /**
      * @var \JhFlexiTime\Repository\BookingRepository
      */
    protected $bookingRepository;

    /**
     * @var \DateTime
     */
    protected $date;

    protected $periodService;

    /**
     * @param BalanceService $balanceService
     * @param BookingRepository $bookingRepository
     * @param \DateTime $date
     */
    public function __construct(
        BalanceService $balanceService,
        BookingRepository $bookingRepository,
        \DateTime $date,
        PeriodServiceInterface $periodService
    ) {
        $this->balanceService       = $balanceService;
        $this->bookingRepository    = $bookingRepository;
        $this->date                 = $date;
        $this->periodService        = $periodService;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();
        $this->listeners[] = $sharedEvents->attach('JhFlexiTime\Service\BookingService', 'create.pre', array($this, 'updateBalance'), 100);
        $this->listeners[] = $sharedEvents->attach('JhFlexiTime\Service\BookingService', 'update.pre', array($this, 'updateBalance'), 100);
        $this->listeners[] = $sharedEvents->attach('JhFlexiTime\Service\BookingService', 'delete.pre', array($this, 'updateBalance'), 100);
    }

    public function updateBalance($e)
    {
        $booking = $e->getParam('booking');

        if($this->periodService->isDateAfterDay($booking, $this->date)) {
            //if booking time in the future - do nothing
            return true;
        }

        if($this->bookingRepository->isUsersFirstBookingForMonth($booking->getUser(), $booking->getDate())) {
            //calculate balance of previous month
            $this->calculatePreviousMonthBalance($booking);
            return true;
        }

        if($this->periodService->isDateInPreviousMonth($booking, $this->date)) {
            $this->balanceService->updateFromPreviousMonth($booking);
            return true;
        }

        //else must be a booking in this month, calculate the balance
        //for this individual record
        $this->balanceService->updateBalance($booking);
    }

    public function calculatePreviousMonthBalance(Booking $booking)
    {
        $runningBalance         = $this->balanceService->getRunningBalance($booking->getUser());
        $totalHoursThisMonth    = $this->periodService->getTotalHoursToDateInMonth($this->referenceDate);
        $bookedThisMonth        = $this->bookingRepository->getMonthBookedToDateTotalByUser($booking->getUser(), $this->referenceDate);
        $monthBalance           = $bookedThisMonth - $totalHoursThisMonth;
        $runningBalance->addBalance($monthBalance);
    }
}
