<?php

namespace JhFlexiTime\Service;

use ZfcUser\Entity\UserInterface;
use JhFlexiTime\Repository\BalanceRepositoryInterface;
use JhFlexiTime\Entity\Booking;
use Doctrine\Common\Persistence\ObjectManager;
use JhFlexiTime\Options\ModuleOptions;
use JhFlexiTime\Entity\RunningBalance;

/**
 * Class BalanceService
 * @package JhFlexiTime\Service
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BalanceService implements BalanceServiceInterface
{

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
     * @param ModuleOptions $options
     * @param BalanceRepositoryInterface $balanceRepository
     * @param ObjectManager $objectManager
     */
    public function __construct(
        ModuleOptions $options,
        BalanceRepositoryInterface $balanceRepository,
        ObjectManager $objectManager
    ) {
        $this->options                  = $options;
        $this->balanceRepository        = $balanceRepository;
        $this->objectManager            = $objectManager;
    }

    public function updateFromPreviousMonth(Booking $booking)
    {
        $runningBalance = $this->getRunningBalance($booking->getUser());

        list($balanceDiff, $newBalance) = $this->getBalanceDiff($booking);
        $runningBalance->addBalance($balanceDiff);
        $booking->setBalance($newBalance);
    }

    /**
     * @param Booking $booking
     */
    public function updateBalance(Booking $booking)
    {
        list($balanceDiff, $newBalance) = $this->getBalanceDiff($booking);
        $booking->setBalance($newBalance);
    }

    /**
     * @param Booking $booking
     * @return array
     */
    public function getBalanceDiff(Booking $booking)
    {
        $oldBalance     = $booking->getBalance();
        $newBalance     = $booking->getTotal() - $this->options->getHoursInDay();
        $runningBalance = $newBalance - $oldBalance;
        return [
            $runningBalance,
            $newBalance,
        ];
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



}
