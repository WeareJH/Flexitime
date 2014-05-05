<?php

namespace JhFlexiTime\Service;

use JhFlexiTime\Repository\BookingRepositoryInterface;
use ZfcUser\Entity\UserInterface;
use JhFlexiTime\Repository\BookingRepository;
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
     * @var \DateTime
     */
    protected $referenceDate;

    /**
     * @param ModuleOptions $options
     * @param BookingRepositoryInterface $bookingRepository
     * @param BalanceRepositoryInterface $balanceRepository
     * @param PeriodServiceInterface $periodService
     * @param \DateTime $date
     */
    public function __construct(
        ModuleOptions $options,
        BookingRepositoryInterface $bookingRepository,
        BalanceRepositoryInterface $balanceRepository,
        PeriodServiceInterface $periodService,
        \DateTime $date
    ) {
        $this->options              = $options;
        $this->bookingRepository    = $bookingRepository;
        $this->balanceRepository    = $balanceRepository;
        $this->periodService        = $periodService;
        $this->referenceDate        = $date;
    }

    /**
     * @param UserInterface $user
     * @param \DateTime $period
     * @return float
     */
    public function getMonthBalance(UserInterface $user, \DateTime $period)
    {
        $firstDayOfMonth = clone $this->referenceDate;
        $firstDayOfMonth->modify('first day of this month');

        if ($period < $firstDayOfMonth) {

            $totalWorkedHours       = $this->bookingRepository->getMonthBookedTotalByUser($user, $period);
            $hoursAvailableToDate   = $this->periodService->getTotalHoursInMonth($period);
            $balance                = ($totalWorkedHours - $hoursAvailableToDate);

        } elseif ($period->format('m-Y') === $firstDayOfMonth->format('m-Y')) {

            //get the amount of hours booked between the first day of the month
            //and the current date
            $totalWorkedHours       = $this->bookingRepository->getMonthBookedToDateTotalByUser($user, $this->referenceDate);
            $hoursAvailableToDate   = $this->periodService->getTotalHoursToDateInMonth($this->referenceDate);
            $balance                = ($totalWorkedHours - $hoursAvailableToDate);
        } else {
            $balance = 0;
        }

        return $balance;
    }

    /**
     * @param UserInterface $user
     * @param \DateTime $period
     * @return float
     */
    public function getMonthTotalWorked(UserInterface $user, \DateTime $period)
    {
        $firstDayOfMonth = clone $this->referenceDate;
        $firstDayOfMonth->modify('first day of this month');

        if ($period < $firstDayOfMonth) {
            $totalWorkedHours = $this->bookingRepository->getMonthBookedTotalByUser($user, $period);
        } else {
            $totalWorkedHours = $this->bookingRepository->getMonthBookedToDateTotalByUser($user, $period);
        }

        return $totalWorkedHours;
    }

    /**
     * @param UserInterface $user
     * @param \DateTime $period
     * @return array
     */
    public function getWeekTotals(UserInterface $user, \DateTime $period)
    {
        $week           = $this->periodService->getFirstAndLastDayOfWeek($period);
        $totalWorked    = $this->bookingRepository->getTotalBookedBetweenByUser($user, $week['firstDay'], $week['lastDay']);
        $totalHours     = $this->periodService->getNumWorkingDaysInWeek($period) * $this->options->getHoursInDay();

        return [
            'weekTotalWorkedHours'  => $totalWorked,
            'weekTotalHours'        => $totalHours,
            'balance'               => $totalWorked - $totalHours,
        ];
    }


    /**
     * @param UserInterface $user
     * @param \DateTime $period
     * @return array
     */
    public function getTotals(UserInterface $user, \DateTime $period)
    {
        return [
            'monthTotalWorkedHours' => $this->getMonthTotalWorked($user, $period),
            'monthTotalHours'       => $this->periodService->getTotalHoursInMonth($period),
            'monthBalance'          => $this->getMonthBalance($user, $period),
            'runningBalance'        => $this->getRunningBalance($user),
            'monthRemainingHours'   => $this->periodService->getRemainingHoursInMonth($this->referenceDate),
        ];
    }

    /**
     * @param UserInterface $user
     * @return float
     */
    public function getRunningBalance(UserInterface $user)
    {

        $balanceEntity = $this->balanceRepository->findByUser($user);

        if($balanceEntity) {
            $balance = $balanceEntity->getBalance();
        } else {
            $balance = 0;
        }

        $totalHoursThisMonth    = $this->periodService->getTotalHoursToDateInMonth($this->referenceDate);
        $bookedThisMonth        = $this->bookingRepository->getMonthBookedToDateTotalByUser($user, $this->referenceDate);
        $monthBalance           = $bookedThisMonth - $totalHoursThisMonth;
        $balance                += $monthBalance;

        return floatval(number_format($balance, 2));
    }

    /**
     * @param BookingRepository $bookingRepository
     */
    public function setBookingRepository(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    /**
     * @return BookingRepository
     */
    public function getBookingRepository()
    {
        return $this->bookingRepository;
    }
}
