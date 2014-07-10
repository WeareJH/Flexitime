<?php

namespace JhFlexiTime\Listener;

use JhFlexiTime\Entity\RunningBalance;
use JhFlexiTime\Repository\UserSettingsRepositoryInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\AbstractListenerAggregate;
use JhFlexiTime\Repository\BalanceRepositoryInterface;
use JhFlexiTime\Entity\Booking;
use JhFlexiTime\Options\ModuleOptions;
use ZfcUser\Entity\UserInterface;
use Doctrine\Common\Persistence\ObjectManager;
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
     * @var ModuleOptions
     */
    protected $options;

    /**
     * @var BalanceRepository
     */
    protected $balanceRepository;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var UserSettingsRepositoryInterface
     */
    protected $userSettingsRepository;

    /**
     * @param ObjectManager $objectManager
     * @param BalanceRepositoryInterface $balanceRepository
     * @param \DateTime $date
     * @param ModuleOptions $options
     * @param UserSettingsRepositoryInterface $userSettingsRepository
     */
    public function __construct(
        ObjectManager $objectManager,
        BalanceRepositoryInterface $balanceRepository,
        \DateTime $date,
        ModuleOptions $options,
        UserSettingsRepositoryInterface $userSettingsRepository
    ) {
        $this->objectManager            = $objectManager;
        $this->balanceRepository        = $balanceRepository;
        $this->date                     = $date;
        $this->options                  = $options;
        $this->userSettingsRepository   = $userSettingsRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents = $events->getSharedManager();
        $this->listeners[]
            = $sharedEvents->attach('JhFlexiTime\Service\BookingService', 'create.pre', [$this, 'updateBalance'], 100);
        $this->listeners[]
            = $sharedEvents->attach('JhFlexiTime\Service\BookingService', 'update.pre', [$this, 'updateBalance'], 100);
        $this->listeners[]
            = $sharedEvents->attach('JhFlexiTime\Service\BookingService', 'delete.pre', [$this, 'updateBalance'], 100);
    }

    /**
     * @param Event $e
     * @return void
     */
    public function updateBalance(Event $e)
    {
        $booking = $e->getParam('booking');

        //if this booking date is in a previous month
        //but not before the user's start date
        //then update the running balance
        if (
            $this->isDateInPreviousMonth($booking->getDate(), $this->date) &&
            $this->isDateAfterUsersStartTrackingDate($booking)
        ) {
            $this->updateRunningBalance($booking, $this->getRunningBalance($booking->getUser()));
        }

        $newBalance = $booking->getTotal() - $this->options->getHoursInDay();
        $booking->setBalance($newBalance);
    }

    /**
     * @param Booking $booking
     * @param RunningBalance $runningBalance
     */
    public function updateRunningBalance(Booking $booking, RunningBalance $runningBalance)
    {
        $oldBalance     = $booking->getBalance();
        $newBalance     = $booking->getTotal() - $this->options->getHoursInDay();
        $balanceDiff    = $newBalance - $oldBalance;

        $runningBalance->addBalance($balanceDiff);
    }

    /**
     * @param UserInterface $user
     * @return RunningBalance
     * @throws \Exception
     */
    public function getRunningBalance(UserInterface $user)
    {
        $runningBalance = $this->balanceRepository->findByUser($user);
        return $runningBalance;
    }

    /**
     * @param \DateTime $dateA
     * @param \DateTime $dateB
     * @return bool
     */
    public function isDateInPreviousMonth(\DateTime $dateA, \DateTime $dateB)
    {
        $date = clone $dateB;
        $date->modify('first day of this month 00:00:00');
        return $dateA < $date;
    }

    /**
     * Check whether the date being booked is after the user's start
     * tracking time date. We rewind this start tracking date to the beginning of the month,
     * and count the whole month.
     *
     * @param Booking $booking
     * @return bool
     */
    public function isDateAfterUsersStartTrackingDate(Booking $booking)
    {
        $user           = $booking->getUser();
        $userSettings   = $this->userSettingsRepository->findOneByUser($user);

        $startDate = clone $userSettings->getFlexStartDate();
        $startDate->modify('first day of this month 00:00:00');

        return $booking->getDate() >= $startDate;
    }
}
