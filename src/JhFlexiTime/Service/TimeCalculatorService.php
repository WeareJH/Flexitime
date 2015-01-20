<?php

namespace JhFlexiTime\Service;

use JhFlexiTime\Entity\UserSettings;
use JhFlexiTime\Repository\BookingRepositoryInterface;
use ZfcUser\Entity\UserInterface;
use JhFlexiTime\DateTime\DateTime;
use JhFlexiTime\Options\ModuleOptions;
use JhFlexiTime\Repository\BalanceRepositoryInterface;

/**
 *
 * Class TimeCalculatorService
 * @package JhFlexiTime\Service
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class TimeCalculatorService
{

    /**
     * @var \JhFlexiTime\Repository\BookingRepositoryInterface
     */
    protected $bookingRepository;

    /**
     * @var BalanceRepositoryInterface
     */
    protected $balanceRepository;

    /**
     * @var \JhFlexiTime\Service\PeriodServiceInterface
     */
    protected $periodService;

    /**
     * @var \JhFlexiTime\Options\ModuleOptions
     */
    protected $options;

    /**
     * @var DateTime
     */
    protected $today;

    /**
     * @param ModuleOptions $options
     * @param BookingRepositoryInterface $bookingRepository
     * @param BalanceRepositoryInterface $balanceRepository
     * @param PeriodServiceInterface $periodService
     * @param DateTime $date
     */
    public function __construct(
        ModuleOptions $options,
        BookingRepositoryInterface $bookingRepository,
        BalanceRepositoryInterface $balanceRepository,
        PeriodServiceInterface $periodService,
        DateTime $date
    ) {
        $this->options              = $options;
        $this->bookingRepository    = $bookingRepository;
        $this->balanceRepository    = $balanceRepository;
        $this->periodService        = $periodService;
        $this->today                = $date;
    }

    /**
     * PUBLIC
     * @param UserInterface $user
     * @param DateTime $period
     * @return array
     */
    public function getWeekTotals(UserInterface $user, DateTime $period)
    {
        $week    = $this->periodService->getFirstAndLastDayOfWeek($period);
        $tWorked = $this->bookingRepository->getTotalBookedBetweenByUser($user, $week['firstDay'], $week['lastDay']);
        $tHours  = $this->periodService->getNumWorkingDaysInWeek($period) * $this->options->getHoursInDay();

        return [
            'weekTotalWorkedHours'  => $tWorked,
            'weekTotalHours'        => $tHours,
            'balance'               => $tWorked - $tHours,
        ];
    }

    /**
     *
     * @param UserInterface $user
     * @param DateTime $userStartDate
     * @param DateTime $period
     * @return array
     */
    public function getTotals(UserInterface $user, DateTime $userStartDate, DateTime $period)
    {
        $firstDayOfMonth = clone $this->today;
        $firstDayOfMonth->modify('first day of this month 00:00:00');

        $startOfMonth = clone $period;
        $startOfMonth->modify('first day of this month 00:00:00');
        $endOfMonth = clone $period;
        $endOfMonth->modify('last day of this month 23:59:59');

        $userStartedInThisMonth = $userStartDate->isSameMonthAndYear($period);

        if ($userStartedInThisMonth) {
            $startDate          = clone $userStartDate;
            $monthTotalHours    = $this->periodService->getTotalHoursFromDateToEndOfMonth($userStartDate);
        } else {
            $startDate          = clone $startOfMonth;
            $monthTotalHours    = $this->periodService->getTotalHoursInMonth($period);
        }

        if ($period < $firstDayOfMonth) {
            $endDate = clone $endOfMonth;
        } else {
            $endDate = clone $this->today;
        }

        //future month
        $remainingHours     = $this->periodService->getTotalHoursInMonth($period);
        $monthTotalWorked   = 0;
        $hoursElapsed       = 0;
        if ($period < $firstDayOfMonth || $period->isSameMonthAndYear($this->today)) {

            if ($period < $firstDayOfMonth) {
                //previous month
                $remainingHours = 0;
            } elseif ($period->isSameMonthAndYear($this->today)) {
                //current month

                $today = clone $this->today;
                $today->modify('-1 day');
                $remainingHours = $this->periodService->getRemainingHoursInMonth($today);
            }

            $monthTotalWorked   = $this->bookingRepository->getTotalBookedBetweenByUser($user, $startDate, $endDate);
            $hoursElapsed       = $this->periodService->getTotalHoursBetweenDates($startDate, $endDate);
        }

        $monthBalance = $monthTotalWorked - $hoursElapsed;

        if ($userStartedInThisMonth && $period->isSameMonthAndYear($this->today)) {
            $runningBalance = $monthBalance;
            $balanceForward = 0;
        } else {
            $balanceForward = $this->getBalanceForward($user);
            $runningBalance = $this->getRunningBalance($user, $balanceForward);
        }

        $totals = [
            'monthTotalWorkedHours' => $monthTotalWorked,
            'monthTotalHours'       => $monthTotalHours,
            'monthBalance'          => $monthBalance,
            'runningBalance'        => $runningBalance,
            'monthRemainingHours'   => $remainingHours,
            'balanceForward'        => $balanceForward,
        ];

        return $totals;
//        return array_map(function ($val) {]
//            return (float) $val;
//        }, $totals);
    }

    /**
     * This gets the balance of the CURRENT month, regardless of which month you are viewing.
     * Viewing the running balance and balance forward of a particular month is not supported.
     *
     * @param UserInterface $user
     * @param float         $balanceForward
     *
     * @return float
     */
    private function getRunningBalance(UserInterface $user, $balanceForward)
    {
        $totalHoursThisMonth = $this->periodService->getTotalHoursFromBeginningOfMonthToDate($this->today);
        $bookedThisMonth     = $this->bookingRepository->getMonthBookedToDateTotalByUser($user, $this->today);
        $monthBalance        = $bookedThisMonth - $totalHoursThisMonth;
        $balanceForward      += $monthBalance;

        return round($balanceForward, 2);
    }

    /**
     * PRIVATE
     *
     * @param UserInterface $user
     * @return float
     */
    private function getBalanceForward(UserInterface $user)
    {
        $balanceEntity = $this->balanceRepository->findOneByUser($user);
        return ($balanceEntity) ? $balanceEntity->getBalance() : 0;
    }
}
