<?php

namespace JhFlexiTime\Service;

use JhFlexiTime\Entity\RunningBalance;
use JhFlexiTime\Repository\BalanceRepositoryInterface;
use JhFlexiTime\Repository\BookingRepositoryInterface;
use JhUser\Repository\UserRepositoryInterface;
use JhFlexiTime\Repository\UserSettingsRepositoryInterface;
use Zend\EventManager\EventManagerAwareInterface;
use ZfcUser\Entity\UserInterface;
use Doctrine\Common\Persistence\ObjectManager;
use JhFlexiTime\DateTime\DateTime;
use Zend\EventManager\EventManagerAwareTrait;

/**
 * Class RunningBalanceService
 * @package JhFlexiTime\Service
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class RunningBalanceService implements EventManagerAwareInterface
{

    use EventManagerAwareTrait;

    /**
     * @var \DateTime
     */
    protected $date;

    /**
     * @var \JhUser\Repository\UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var \JhFlexiTime\Repository\UserSettingRepositoryInterface
     */
    protected $userSettingsRepository;

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
     * @param UserSettingsRepositoryInterface $userSettingsRepository
     * @param BookingRepositoryInterface $bookingRepository
     * @param BalanceRepositoryInterface $balanceRepository
     * @param PeriodServiceInterface $periodService
     * @param ObjectManager $objectManager
     * @param DateTime $date
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        UserSettingsRepositoryInterface $userSettingsRepository,
        BookingRepositoryInterface $bookingRepository,
        BalanceRepositoryInterface $balanceRepository,
        PeriodServiceInterface $periodService,
        ObjectManager $objectManager,
        DateTime $date
    ) {
        $this->userRepository           = $userRepository;
        $this->userSettingsRepository   = $userSettingsRepository;
        $this->bookingRepository        = $bookingRepository;
        $this->balanceRepository        = $balanceRepository;
        $this->periodService            = $periodService;
        $this->objectManager            = $objectManager;
        $this->date                     = $date;

        $this->lastMonth = clone $this->date;
        $this->lastMonth->modify('first day of previous month 00:00');
    }

    /**
     * Calculate the previous month balance for all users
     * and add it to their running balance
     */
    public function indexPreviousMonthBalance()
    {
        foreach ($this->userRepository->findAll(false) as $user) {
            $runningBalance = $this->balanceRepository->findOneByUser($user);
            $userSettings   = $this->userSettingsRepository->findOneByUser($user);

            $date = $this->lastMonth;
            //if user started this month use that as from date instead
            if ($userSettings->getFlexStartDate() > $date) {
                $date = $userSettings->getFlexStartDate();
            }

            $this->addMonthBalance(
                $user,
                $runningBalance,
                [
                    'start' => $date,
                    'end' => $this->lastMonth->endOfMonth()
                ]
            );
        }

        $this->objectManager->flush();
    }

    /**
     * Recalculate all user's running balance
     */
    public function reIndexAllUsersRunningBalance()
    {
        foreach ($this->userRepository->findAll(false) as $user) {
            $runningBalance = $this->balanceRepository->findOneByUser($user);
            $userSettings   = $this->userSettingsRepository->findOneByUser($user);

            $monthRanges = $this->getMonthRange(
                $userSettings->getFlexStartDate(),
                $this->lastMonth->endOfMonth()
            );

            $this->reIndexRunningBalance(
                $user,
                $runningBalance,
                $monthRanges,
                $userSettings->getStartingBalance()
            );
        }

        $this->objectManager->flush();
    }

    /**
     * Recaulculate Individual user's running balance
     *
     * @param UserInterface $user
     */
    public function reIndexIndividualUserRunningBalance(UserInterface $user)
    {
        $runningBalance = $this->balanceRepository->findOneByUser($user);
        $userSettings   = $this->userSettingsRepository->findOneByUser($user);

        $monthRanges = $this->getMonthRange(
            $userSettings->getFlexStartDate(),
            $this->lastMonth->endOfMonth()
        );

        $this->reIndexRunningBalance(
            $user,
            $runningBalance,
            $monthRanges,
            $userSettings->getStartingBalance()
        );

        $this->objectManager->flush();
    }

    /**
     * @param UserInterface $user
     * @param float $balance
     */
    public function setUserStartingBalance(UserInterface $user, $balance)
    {
        $settings = $this->userSettingsRepository->findOneByUser($user);
        $settings->setStartingBalance($balance);
        $this->objectManager->flush();
    }

    /**
     * @param UserInterface $user
     * @param RunningBalance $runningBalance
     * @param array $months
     * @param int $initialBalance
     */
    private function reIndexRunningBalance(
        UserInterface $user,
        RunningBalance $runningBalance,
        array $months,
        $initialBalance
    ) {

        $runningBalance->setBalance($initialBalance);

        foreach ($months as $month) {
            $this->addMonthBalance($user, $runningBalance, $month);
        }
    }

    /**
     * @param UserInterface $user
     * @param RunningBalance $runningBalance
     * @param $month
     */
    private function addMonthBalance(UserInterface $user, RunningBalance $runningBalance, array $month)
    {
        $this->getEventManager()->trigger('addMonthBalance.pre', null, ['runningBalance' => $runningBalance]);

        $runningBalance->addBalance(
            $this->calculateBalance(
                $user,
                $month['start'],
                $month['end']
            )
        );

        $this->getEventManager()->trigger('addMonthBalance.post', null, ['runningBalance' => $runningBalance]);
    }

    /**
     * Calculate the balance between the two dates
     *
     * @param UserInterface $user
     * @param DateTime $start
     * @param DateTime $end
     * @return float
     */
    private function calculateBalance(UserInterface $user, DateTime $start, DateTime $end)
    {
        $monthTotalHours    = $this->periodService->getTotalHoursBetweenDates($start, $end);
        $totalBookedHours   = $this->bookingRepository->getTotalBookedBetweenByUser($user, $start, $end);
        $balance            = $totalBookedHours - $monthTotalHours;
        return $balance;
    }

    /**
     * @param DateTime $start
     * @param DateTime $end
     * @return array
     */
    private function getMonthRange(DateTime $start, DateTime $end)
    {
        $months = $start->getMonthsBetween($end);
        $monthRanges = [];
        foreach ($months as $key => $month) {
            $monthRanges[] = [
                'start' => $month->startOfMonth(),
                'end'   => $month->endOfMonth()
            ];

            if ($key === 0) {
                $monthRanges[$key]['start'] = $start;
            }
        }
        return $monthRanges;
    }
}
