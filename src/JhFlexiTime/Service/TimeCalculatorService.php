<?php

namespace JhFlexiTime\Service;

use JhFlexiTime\Service\BalanceServiceInterface;
use JhFlexiTime\Repository\BookingRepositoryInterface;
use ZfcUser\Entity\UserInterface;
use JhFlexiTime\Repository\BookingRepository;
use JhFlexiTime\Options\ModuleOptions;

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
     * @var BalanceServiceInterface
     */
    protected $balanceService;

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
     * @param BalanceServiceInterface $balanceService
     * @param PeriodServiceInterface $periodService
     * @param \DateTime $date
     */
    public function __construct(
        ModuleOptions $options,
        BookingRepositoryInterface $bookingRepository,
        BalanceServiceInterface $balanceService,
        PeriodServiceInterface $periodService,
        \DateTime $date
    ) {
        $this->options              = $options;
        $this->bookingRepository    = $bookingRepository;
        $this->balanceService       = $balanceService;
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
    public function getTotals(UserInterface $user, \DateTime $period)
    {
        return [
            'monthTotalWorkedHours' => $this->getMonthTotalWorked($user, $period),
            'monthTotalHours'       => $this->periodService->getTotalHoursInMonth($period),
            'monthBalance'          => $this->getMonthBalance($user, $period),
            'runningBalance'        => $this->getRunningBalance($user),
        ];
    }

    /**
     * @param UserInterface $user
     * @return float
     */
    public function getRunningBalance(UserInterface $user)
    {
        $runningBalance         = $this->balanceService->getRunningBalance($user);
        $balance                = $runningBalance->getBalance();
        $remainingHoursInMonth  = $this->periodService->getRemainingHoursInMonth($this->referenceDate);
        $balance                += $remainingHoursInMonth;
        //ignore today so add on 7.5 hours
        //$balance                += $this->options->getHoursInDay();
        $balance                -= $this->bookingRepository->getTotalBookedAfter($user, $this->referenceDate);

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
