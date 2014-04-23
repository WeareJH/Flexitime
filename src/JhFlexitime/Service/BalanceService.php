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
class BalanceService
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
     * @var \JhFlexiTime\Service\PeriodServiceInterface
     */
    protected $periodService;

    /**
     * @param ModuleOptions $options
     * @param BalanceRepositoryInterface $balanceRepository
     * @param ObjectManager $objectManager
     * @param PeriodServiceInterface $periodService
     */
    public function __construct(
        ModuleOptions $options,
        BalanceRepositoryInterface $balanceRepository,
        ObjectManager $objectManager,
        PeriodServiceInterface $periodService
    ) {
        $this->options                  = $options;
        $this->balanceRepository        = $balanceRepository;
        $this->objectManager            = $objectManager;
        $this->periodService            = $periodService;
    }

    /**
     * @param Booking $booking
     */
    public function update(Booking $booking)
    {
        $runningBalance = $this->getRunningBalance($booking->getUser());

        list($balanceDiff, $newBalance) = $this->getBalanceDiff($booking);
        $runningBalance->addBalance($balanceDiff);
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
    protected function getRunningBalance(UserInterface $user)
    {
        $runningBalance = $this->balanceRepository->findByUser($user);
        if (!$runningBalance) {
            throw new \Exception(sprintf("Running Balance Row could not be located for User: %a", $user->getEmail()));
        }
        return $runningBalance;
    }

    /**
     * @param UserInterface $user
     */
    public function setupInitialRunningBalance(UserInterface $user)
    {
        $runningBalance = new RunningBalance();
        $runningBalance->setUser($user);
        $runningBalance->subtractBalance($this->periodService->getTotalHoursInMonth(new \DateTime));
        $this->objectManager->persist($runningBalance);
        $this->objectManager->flush();
    }

    /**
     * @param Booking $booking
     */
    public function firstBookingOfTheMonth(Booking $booking)
    {
        $monthTotalHours = $this->periodService->getTotalHoursInMonth($booking->getDate());
        list($balanceDiff, $newBalance) = $this->getBalanceDiff($booking);
        $booking->setBalance($newBalance);
        $monthBalance = (0 - $monthTotalHours) + $booking->getTotal();

        $balance = $this->getRunningBalance($booking->getUser());
        $balance->addBalance($monthBalance);

        $this->objectManager->persist($balance);
    }

    /**
     * @param Booking $booking
     */
    public function create(Booking $booking)
    {
        $runningBalance = $this->getRunningBalance($booking->getUser());
        $runningBalance->addBalance($booking->getTotal());

        list($balanceDiff, $newBalance) = $this->getBalanceDiff($booking);
        $booking->setBalance($newBalance);

        $this->objectManager->persist($runningBalance);
    }

    /**
     * @param Booking $booking
     */
    public function remove(Booking $booking)
    {
        $runningBalance = $this->getRunningBalance($booking->getUser());
        $runningBalance->subtractBalance($booking->getTotal());
    }
}
