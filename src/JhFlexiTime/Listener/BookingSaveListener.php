<?php

namespace JhFlexiTime\Listener;

use JhFlexiTime\Entity\RunningBalance;
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
     * @var \JhFlexiTime\Options\ModuleOptions
     */
    protected $options;

    /**
     * @var \JhFlexiTime\Repository\BalanceRepository
     */
    protected $balanceRepository;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * @param ObjectManager $objectManager
     * @param BalanceRepositoryInterface $balanceRepository
     * @param \DateTime $date
     * @param ModuleOptions $options
     */
    public function __construct(
        ObjectManager $objectManager,
        BalanceRepositoryInterface $balanceRepository,
        \DateTime $date,
        ModuleOptions $options
    ) {
        $this->objectManager        = $objectManager;
        $this->balanceRepository    = $balanceRepository;
        $this->date                 = $date;
        $this->options              = $options;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedEvents      = $events->getSharedManager();
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

        if ($this->isDateInPreviousMonth($booking->getDate(), $this->date)) {
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
        if (!$runningBalance) {
            $runningBalance = new RunningBalance;
            $runningBalance->setUser($user);
            $this->objectManager->persist($runningBalance);
            return $runningBalance;
        }
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
}
