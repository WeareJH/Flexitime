<?php

namespace JhFlexiTime\Service;

use JhFlexiTime\Entity\RunningBalance;
use JhFlexiTime\Repository\BalanceRepositoryInterface;
use JhFlexiTime\Repository\BookingRepositoryInterface;
use JhUser\Repository\UserRepositoryInterface;
use ZfcUser\Entity\UserInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class RunningBalanceService
 * @package JhFlexiTime\Service
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class RunningBalanceService
{

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var \JhUser\Repository\UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var \JhFlexiTime\Repository\BalanceRepositoryInterface
     */
    protected $balanceRepository;

    /**
     * @var \JhFlexiTime\Repository\BookingRepositoryInterface
     */
    protected $bookingRepository;

    /**
     * @var PeriodServiceInterface
     */
    protected $periodService;

    /**
     * @var \DateTime
     */
    protected $lastMonth;

    /**
     * @param UserRepositoryInterface $userRepository
     * @param BookingRepositoryInterface $bookingRepository
     * @param BalanceRepositoryInterface $balanceRepository
     * @param PeriodServiceInterface $periodService
     * @param ObjectManager $objectManager
     * @param \DateTime $date
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        BookingRepositoryInterface $bookingRepository,
        BalanceRepositoryInterface $balanceRepository,
        PeriodServiceInterface $periodService,
        ObjectManager $objectManager,
        \DateTime $date
    ) {
        $this->userRepository       = $userRepository;
        $this->bookingRepository    = $bookingRepository;
        $this->balanceRepository    = $balanceRepository;
        $this->periodService        = $periodService;
        $this->objectManager        = $objectManager;
        $this->date                 = $date;

        $this->lastMonth = clone $this->date;
        $this->lastMonth->modify('first day of previous month 00:00');
    }

    /**
     * Calculate the previous month balance for all users
     * and add it to their running balance
     */
    public function calculatePreviousMonthBalance()
    {
        foreach($this->userRepository->findAll(true) as $user) {
            $runningBalance = $this->balanceRepository->findByUser($user);
            $this->calculateMonthBalance($user, $runningBalance, $this->lastMonth);
        }

        $this->objectManager->flush();
    }

    /**
     * Recalculate all user's running balance
     */
    public function recalculateAllUsersRunningBalance()
    {
        foreach($this->userRepository->findAll(true) as $user) {
            $runningBalance = $this->balanceRepository->findByUser($user);
            $this->recalculateUserRunningBalance($user, $runningBalance);
        }
    }

    /**
     * Recalculate the running balance of a user
     * From when they started entering time until now - 1month
     *
     * @param UserInterface $user
     */
    public function recalculateUserRunningBalance(UserInterface $user)
    {
        $period = $this->getMonthsBetweenUserStartAndLastMonth($user->getCreatedAt(), $this->lastMonth);
        $runningBalance = $this->balanceRepository->findByUser($user);
        $runningBalance->setBalance(0);

        foreach($period as $date) {
            $this->calculateMonthBalance($user, $runningBalance, $date);
        }

        $this->objectManager->flush();
    }

    /**
     * Calculate the month balance and add it to the running balance
     *
     * @param UserInterface $user
     * @param RunningBalance $runningBalance
     * @param \DateTime $date
     */
    public function calculateMonthBalance(UserInterface $user, RunningBalance $runningBalance, \DateTime $date)
    {
        $monthTotalHours    = $this->periodService->getTotalHoursInMonth($date);
        $totalBookedHours   = $this->bookingRepository->getMonthBookedTotalByUser($user, $date);
        $balance            = $totalBookedHours - $monthTotalHours;
        $runningBalance->addBalance($balance);
    }

    /**
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return \DatePeriod
     */
    public function getMonthsBetweenUserStartAndLastMonth(\DateTime $startDate, \DateTime $endDate)
    {
        $startDate  = clone $startDate;
        $endDate    = clone $endDate;

        $startDate->modify('first day of this month 00:00:00');
        $endDate->modify('last day of this month 00:00:00');
        return new \DatePeriod(
            $startDate,
            new \DateInterval("P1M"),
            $endDate
        );
    }
}