<?php

namespace JhFlexiTime\Listener;

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
     * @param BalanceService $balanceService
     * @param BookingRepository $bookingRepository
     */
    public function __construct(BalanceService $balanceService, BookingRepository $bookingRepository)
    {
        $this->balanceService = $balanceService;
        $this->bookingRepository = $bookingRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();
        $this->listeners[] = $sharedEvents->attach('JhFlexiTime\Service\BookingService', 'create.pre', array($this, 'onCreateBooking'), 100);
        $this->listeners[] = $sharedEvents->attach('JhFlexiTime\Service\BookingService', 'update.pre', array($this, 'onUpdateBooking'), 100);
        $this->listeners[] = $sharedEvents->attach('JhFlexiTime\Service\BookingService', 'delete.pre', array($this, 'onRemoveBooking'), 100);
    }

    public function onCreateBooking($e)
    {
        $booking = $e->getParam('booking');


        //if this booking is the first of the month then we need to subtract the total hours of the month
        //from the running balance.
        //if the booking is in the same month the user was created we skip this step
        //because it is automatically done for the current month when the user joins
        //instead we just call create()
        $createdAtDate = $booking->getUser()->getCreatedAt();
        $sameAsCreated = false;
        if ($createdAtDate->format('m-Y') === $booking->getDate()->format('m-Y')) {
            $sameAsCreated = true;
        }

        if ($this->bookingRepository->isUsersFirstBookingForMonth($booking->getUser(), $booking->getDate()) && !$sameAsCreated) {
            $this->balanceService->firstBookingOfTheMonth($booking);
        } else {
            $this->balanceService->create($booking);
        }
    }

    public function onUpdateBooking($e)
    {
        $this->balanceService->update($e->getParam('booking'));
    }

    public function onRemoveBooking($e)
    {
        $this->balanceService->remove($e->getParam('booking'));
    }
}
