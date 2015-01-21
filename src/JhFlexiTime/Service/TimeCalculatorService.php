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
        $startDate          = $this->getStartDate($userStartDate, $period);
        $endDate            = $this->getEndDate($period, $this->today);

        $monthTotalWorked   = $this->getTotalWorkedHours($user, $startDate, $endDate, $this->today);
        $monthBalance       = $this->getMonthBalance($startDate, $endDate, $this->today, $monthTotalWorked);

        $balanceForward = 0;
        $runningBalance = $monthBalance;
        if (!$this->userStartedInMonth($userStartDate, $this->today)) {
            $balanceForward = $this->getBalanceForward($user);
            $runningBalance = $this->getRunningBalance($user, $balanceForward);
        }

        $monthTotalHours    = $this->periodService->getTotalHoursBetweenDates($startDate, $startDate->endOfMonth());
        $remainingHours     = $this->getRemainingHours($period, $this->today);

        $totals = [
            'monthTotalWorkedHours' => $monthTotalWorked,
            'monthTotalHours'       => $monthTotalHours,
            'monthBalance'          => $monthBalance,
            'runningBalance'        => $runningBalance,
            'monthRemainingHours'   => $remainingHours,
            'balanceForward'        => $balanceForward,
        ];

        return array_map(function ($val) {
            return round($val, 2);
        }, $totals);
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
        $totalHoursThisMonth = $this->periodService->getTotalHoursBetweenDates(
            $this->today->startOfMonth(),
            $this->today
        );
        $bookedThisMonth     = $this->bookingRepository->getMonthBookedToDateTotalByUser($user, $this->today);
        $monthBalance        = $bookedThisMonth - $totalHoursThisMonth;
        $balanceForward      += $monthBalance;

        return $balanceForward;
    }

    /***
     * @param UserInterface $user
     * @return float
     */
    private function getBalanceForward(UserInterface $user)
    {
        $balanceEntity = $this->balanceRepository->findOneByUser($user);
        return ($balanceEntity) ? $balanceEntity->getBalance() : 0;
    }

    /**
     * @param DateTime $period
     * @param DateTime $today
     * @return float
     */
    private function getRemainingHours(DateTime $period, DateTime $today)
    {
        //previous month
        if ($period < $today->startOfMonth()) {
            return 0;
        }

        //current month
        if ($period->isSameMonthAndYear($today)) {
            return $this->periodService->getTotalHoursBetweenDates($today, $today->endOfMonth());
        }

        //future month
        return $this->periodService->getTotalHoursBetweenDates($period->startOfMonth(), $period->endOfMonth());
    }

    /**
     * Get the total hours worked for the given period
     *
     * @param UserInterface $user
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param DateTime $today
     * @return float|int
     */
    public function getTotalWorkedHours(UserInterface $user, DateTime $startDate, DateTime $endDate, DateTime $today)
    {
        if ($startDate <= $today->endOfMonth()) {
            return $this->bookingRepository->getTotalBookedBetweenByUser($user, $startDate, $endDate);
        }

        //future booking, you can't work hours in the future
        return 0;
    }

    /**
     * Get the month balance. How much has been worked out of how many hours have passed.
     * If we are looking at a future date, no hours have passed
     *
     * @param DateTime $startDate
     * @param DateTime $endDate
     * @param DateTime $today
     * @param float $monthTotalWorked
     * @return float
     */
    private function getMonthBalance(DateTime $startDate, DateTime $endDate, DateTime $today, $monthTotalWorked)
    {
        //future
        $hoursElapsed = 0;

        //the past + present
        if ($startDate <= $today->endOfMonth()) {
            $hoursElapsed = $this->periodService->getTotalHoursBetweenDates($startDate, $endDate);
        }

        return $monthTotalWorked - $hoursElapsed;
    }

    /**
     * Compute the start date to base total calculation on
     * If the user joined started in the month passed in, then the start date
     * should be the user's start date.
     *
     * If not, the start date should be the first day of the passed in month.
     * This is because we don't want base calculations on a date which was before the
     * user started
     *
     * @param DateTime $userStartDate
     * @param DateTime $period
     * @return DateTime
     */
    private function getStartDate(DateTime $userStartDate, DateTime $period)
    {
        if ($userStartDate->isSameMonthAndYear($period)) {
            return clone $userStartDate;
        }
        return $period->startOfMonth();
    }

    /**
     * Compute the end date to base total calculation on
     * If the passed in month is the current month, then the end date should be the current day
     *  - we don't want to include days which haven't gone by yet
     *
     * Other wise the end date should just be the last day in the period we are querying
     *
     * @param DateTime $period
     * @param DateTime $today
     * @return DateTime
     */
    private function getEndDate(DateTime $period, DateTime $today)
    {
        if ($period < $today->startOfMonth()) {
            return $period->endOfMonth();
        }

        return clone $today;
    }

    /**
     * Did the user start in the given month?
     *
     * @param DateTime $userStartDate
     * @param DateTime $today
     * @return bool
     */
    private function userStartedInMonth(DateTime $userStartDate, DateTime $today)
    {
        return $userStartDate->isSameMonthAndYear($today);
    }
}
