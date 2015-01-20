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
    protected $referenceDate;

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
        $this->referenceDate        = $date;
    }

    /**
     * PRIVATE
     *
     * @param UserInterface $user
     * @param DateTime $userStartDate
     * @param DateTime $period
     * @return float
     */
    private function getMonthBalance(UserInterface $user, DateTime $userStartDate, DateTime $period)
    {
        $firstDayOfMonth = clone $this->referenceDate;
        $firstDayOfMonth->modify('first day of this month');

        //if this is the month the user joined, calculate from the day they joined
        if ($period->isSameMonthAndYear($userStartDate)) {

            if ($period->isSameMonthAndYear($this->referenceDate)) {

                $tWorkedHours   = $this->bookingRepository->getTotalBookedBetweenByUser($user, $userStartDate, $this->referenceDate);
                $hoursElapsed   = $this->periodService->getTotalHoursBetweenDates($userStartDate, $this->referenceDate);
                $balance        = $tWorkedHours - $hoursElapsed;
                return $balance;
            }

            $endOfMonth = clone $userStartDate;
            $endOfMonth->modify('last day of this month 23:59:59');

            $tWorkedHours   = $this->bookingRepository->getTotalBookedBetweenByUser($user, $userStartDate, $endOfMonth);
            $hoursFromDate  = $this->periodService->getTotalHoursFromDateToEndOfMonth($userStartDate);
            $balance        = $tWorkedHours - $hoursFromDate;
            return $balance;
        }

        if ($period < $firstDayOfMonth) {

            $tWorkedHours   = $this->bookingRepository->getMonthBookedTotalByUser($user, $period);
            $hoursToDate    = $this->periodService->getTotalHoursInMonth($period);
            $balance        = $tWorkedHours - $hoursToDate;

        } elseif ($period->format('m-Y') === $firstDayOfMonth->format('m-Y')) {

            //get the amount of hours booked between the first day of the month
            //and the current date
            $tWorkedHours   = $this->bookingRepository->getMonthBookedToDateTotalByUser($user, $this->referenceDate);
            $hoursToDate    = $this->periodService->getTotalHoursFromBeginningOfMonthToDate($this->referenceDate);
            $balance        = $tWorkedHours - $hoursToDate;
        } else {
            $balance = 0;
        }

        return $balance;
    }

    /**
     * PRIVATE
     *
     * @param UserInterface $user
     * @param DateTime $userStartDate
     * @param DateTime $period
     * @return float
     */
    private function getMonthTotalWorked(UserInterface $user, DateTime $userStartDate, DateTime $period)
    {
        $firstDayOfMonth = clone $this->referenceDate;
        $firstDayOfMonth->modify('first day of this month');

        //if this is the month the user joined, calculate from the day they joined
        if ($period->isSameMonthAndYear($userStartDate)) {

            if ($period->isSameMonthAndYear($this->referenceDate)) {
                return $this->bookingRepository->getTotalBookedBetweenByUser($user, $userStartDate, $this->referenceDate);
            }

            $endOfMonth = clone $userStartDate;
            $endOfMonth->modify('last day of this month 23:59:59');
            return $this->bookingRepository->getTotalBookedBetweenByUser($user, $userStartDate, $endOfMonth);
        }

        if ($period < $firstDayOfMonth) {
            $totalWorkedHours = $this->bookingRepository->getMonthBookedTotalByUser($user, $period);
        } elseif($period->isSameMonthAndYear($this->referenceDate)) {
            $totalWorkedHours = $this->bookingRepository->getMonthBookedToDateTotalByUser($user, $this->referenceDate);
        } else {
            return 0;
        }

        return $totalWorkedHours;
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
     * PUBLIC
     *
     * @param UserInterface $user
     * @param DateTime $userStartDate
     * @param DateTime $period
     * @return array
     */
    public function getTotals(UserInterface $user, DateTime $userStartDate, DateTime $period)
    {
        $firstDayOfMonth = clone $this->referenceDate;
        $firstDayOfMonth->modify('first day of this month');

        if ($period < $firstDayOfMonth) {
            $remainingHours = 0;
        } elseif ($period->isSameMonthAndYear($this->referenceDate)) {
            $today = clone $this->referenceDate;
            $today->modify('-1 day');
            $remainingHours = $this->periodService->getRemainingHoursInMonth($today);
        } else {
            //future
            $remainingHours = $this->periodService->getTotalHoursInMonth($period);
        }


        if ($period->isSameMonthAndYear($userStartDate)) {

            $balance = $this->getMonthBalance($user, $userStartDate, $period);
            //if we are in the month the user started in
            if ($period->isSameMonthAndYear($this->referenceDate)) {
                $runningBalance = $balance;
            } else {
                $runningBalance = $this->getRunningBalance($user);
            }

            return [
                'monthTotalWorkedHours' => $this->getMonthTotalWorked($user, $userStartDate, $period),
                'monthTotalHours'       => $this->periodService->getTotalHoursFromDateToEndOfMonth($userStartDate),
                'monthBalance'          => $balance,
                'runningBalance'        => $runningBalance,
                'monthRemainingHours'   => $remainingHours,
                'balanceForward'        => $this->getBalanceForward($user),
            ];
        }



        return [
            'monthTotalWorkedHours' => $this->getMonthTotalWorked($user, $userStartDate, $period),
            'monthTotalHours'       => $this->periodService->getTotalHoursInMonth($period),
            'monthBalance'          => $this->getMonthBalance($user, $userStartDate, $period),
            'runningBalance'        => $this->getRunningBalance($user),
            'monthRemainingHours'   => $remainingHours,
            'balanceForward'        => $this->getBalanceForward($user),
        ];
    }

    /**
     * PRIVATE
     *
     * @param UserInterface $user
     * @return float
     */
    public function getRunningBalance(UserInterface $user)
    {
        $balance             = $this->getBalanceForward($user);
        $totalHoursThisMonth = $this->periodService->getTotalHoursFromBeginningOfMonthToDate($this->referenceDate);
        $bookedThisMonth     = $this->bookingRepository->getMonthBookedToDateTotalByUser($user, $this->referenceDate);
        $monthBalance        = $bookedThisMonth - $totalHoursThisMonth;
        $balance             += $monthBalance;

        return floatval(number_format($balance, 2));
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
