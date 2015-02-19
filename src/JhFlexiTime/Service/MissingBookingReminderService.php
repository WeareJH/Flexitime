<?php

namespace JhFlexiTime\Service;

use JhFlexiTime\Notification\MissingBookingsNotification;
use JhFlexiTime\Options\NotificationOptions;
use JhFlexiTime\Repository\BookingRepositoryInterface;
use JhFlexiTime\Repository\UserSettingsRepositoryInterface;
use JhHubBase\Notification\NotificationService;
use JhUser\Repository\UserRepositoryInterface;
use Zend\View\Model\ViewModel;
use ZfcUser\Entity\UserInterface;

/**
 * Class MissingBookingReminderService
 * @package JhFlexiTime\Service
 * @author  Aydin Hassan <aydin@hotmail.co.uk>
 */
class MissingBookingReminderService
{
    /**
     * @var NotificationService
     */
    private $notificationService;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var UserSettingsRepositoryInterface
     */
    private $userSettingsRepository;

    /**
     * @var BookingRepositoryInterface
     */
    private $bookingRepository;

    /**
     * @var NotificationOptions
     */
    private $options;

    /**
     * @param NotificationService               $notificationService
     * @param UserRepositoryInterface           $userRepository
     * @param UserSettingsRepositoryInterface   $userSettingsRepository
     * @param BookingRepositoryInterface        $bookingRepository
     * @param NotificationOptions               $options
     */
    public function __construct(
        NotificationService $notificationService,
        UserRepositoryInterface $userRepository,
        UserSettingsRepositoryInterface $userSettingsRepository,
        BookingRepositoryInterface $bookingRepository,
        NotificationOptions $options
    ) {
        $this->notificationService = $notificationService;
        $this->userRepository = $userRepository;
        $this->userSettingsRepository = $userSettingsRepository;
        $this->bookingRepository = $bookingRepository;
        $this->options = $options;
    }

    /**
     * Find And Notify Users of any missing bookings
     */
    public function findAndNotifyMissingBookings()
    {
        $period = $this->options->getRemindPeriod();
        foreach ($this->userRepository->findAll(true) as $user) {
            $missingBookings = $this->findMissingBookingsForUser($user, $period);

            if (count($missingBookings) > 0) {
                $notification = new MissingBookingsNotification($period, $missingBookings);
                $this->notificationService->notify($notification, $user);
            }
        }
    }

    /**
     * @param UserInterface $user
     * @param array         $period
     *
     * @return array
     */
    public function findMissingBookingsForUser(UserInterface $user, array $period)
    {
        $missingBookings = [];
        foreach ($period as $date) {
            $booking = $this->bookingRepository->findOneBy(['user' => $user, 'date' => $date]);

            if (null === $booking) {
                $missingBookings[] = $date;
            }
        }

        return $missingBookings;
    }
}
