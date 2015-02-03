<?php

namespace JhFlexiTime\Listener;

use JhFlexiTime\DateTime\DateTime;
use JhFlexiTime\Service\RunningBalanceService;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\Event;

/**
 * Class BookingSaveListener
 * @package JhFlexiTime\Listener
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingSaveListener extends AbstractListenerAggregate
{
    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var RunningBalanceService
     */
    protected $runningBalanceService;

    /**
     * @param DateTime             $date
     * @param RunningBalanceService $runningBalanceService
     */
    public function __construct(DateTime $date, RunningBalanceService $runningBalanceService)
    {
        $this->date                     = $date;
        $this->runningBalanceService    = $runningBalanceService;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();
        $this->listeners[] = $sharedEvents
            ->attach('JhFlexiTime\Service\BookingService', 'create.post', [$this, 'reindexBalance'], 100);
        $this->listeners[] = $sharedEvents
            ->attach('JhFlexiTime\Service\BookingService', 'update.post', [$this, 'reindexBalance'], 100);
        $this->listeners[] = $sharedEvents
            ->attach('JhFlexiTime\Service\BookingService', 'delete.post', [$this, 'reindexBalance'], 100);
    }

    /**
     * If changing anything ina  previous month
     * we need to reindex the running balance
     *
     * @param Event $e
     * @return void
     */
    public function reindexBalance(Event $e)
    {
        $booking = $e->getParam('booking');

        if ($this->date->isInPreviousMonth($booking->getDate())) {
            $this->runningBalanceService->reIndexIndividualUserRunningBalance($booking->getUser());
        }
    }
}
