<?php

namespace JhFlexiTime\Controller;

use JhFlexiTime\Repository\UserSettingsRepositoryInterface;
use JhUser\Repository\UserRepositoryInterface;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\Validator\Date as DateValidator;
use Zend\View\Model\JsonModel;
use JhFlexiTime\Service\TimeCalculatorService;
use JhFlexiTime\Service\BookingService;

/**
 * Class BookingRestController
 * @package JhFlexiTime\Controller
 * @author Aydin Hassan <aydin@wearejh.com>
 */
class BookingRestController extends AbstractRestfulController
{
    use GetSetDateTrait;

    /**
     * @var \JhFlexiTime\Service\BookingService
     */
    protected $bookingService;

    /**
     * @var \JhFlexiTime\Service\TimeCalculatorService
     */
    protected $timeCalculatorService;

    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var UserSettingsRepositoryInterface
     */
    protected $userSettingsRepository;

    /**
     * @param BookingService $bookingService
     * @param TimeCalculatorService $timeCalculatorService
     * @param UserRepositoryInterface $userRepository
     * @param UserSettingsRepositoryInterface $userSettingsRepository
     */
    public function __construct(
        BookingService $bookingService,
        TimeCalculatorService $timeCalculatorService,
        UserRepositoryInterface $userRepository,
        UserSettingsRepositoryInterface $userSettingsRepository
    ) {
        $this->bookingService           = $bookingService;
        $this->timeCalculatorService    = $timeCalculatorService;
        $this->userRepository           = $userRepository;
        $this->userSettingsRepository   = $userSettingsRepository;
    }

    /**
     * @return JsonModel
     */
    public function getList()
    {
        $month  = $this->params()->fromQuery('m', false);
        $year   = $this->params()->fromQuery('y', false);
        $userId = $this->params()->fromQuery('user', false);
        $period = $this->getDate($month, $year);

        if ($userId && $this->isGranted('flexi-time.readOthers')) {

            $user = $this->userRepository->find($userId);
            if (!$user) {
                return new JsonModel([
                    'success' => false,
                    'message' => 'User does not exist',
                ]);
            }
        } else {
            $user = $this->zfcUserAuthentication()->getIdentity();
        }

        $userSettings   = $this->userSettingsRepository->findOneByUser($user);
        $records        = $this->bookingService->getUserBookingsForMonth($user, $period);
        $pagination     = $this->bookingService->getPagination($period);
        $totals         = $this->timeCalculatorService->getTotals($user, $userSettings->getFlexStartDate(), $period);

        return new JsonModel([
            'bookings' => [
                'records'   => $records,
                'totals'    => $totals,
                'user'      => $user,
            ],
            'pagination' => $pagination,
            'date'       => $period,
            'today'      => new \DateTime("today"),
        ]);
    }

    /**
     * @param int $id
     * @return JsonModel
     */
    public function get($id)
    {
        $id   = $this->parseIdCriteria($id);
        return new JsonModel([
            'booking' => $this->bookingService->getBookingByUserAndDate($id['user'], $id['date']),
        ]);
    }

    /**
     * @param array $data
     * @return JsonModel
     */
    public function create($data)
    {
        $booking = $this->bookingService->create($data);

        if (is_array($booking)) {
            $booking['success'] = false;
            return new JsonModel($booking);
        }

        $userSettings = $this->userSettingsRepository->findOneByUser($booking->getUser());
        $monthTotals  = $this->timeCalculatorService->getTotals(
            $booking->getUser(),
            $userSettings->getFlexStartDate(),
            $booking->getDate()
        );
        return new JsonModel([
            'success'       => true,
            'booking'       => $booking,
            'monthTotals'   => $monthTotals,
            'weekTotals'    => $this->timeCalculatorService->getWeekTotals($booking->getUser(), $booking->getDate())
        ]);
    }

    /**
     * @param int $id
     * @param array $data
     * @return JsonModel
     */
    public function update($id, $data)
    {
        $id         = $this->parseIdCriteria($id);
        $booking    = $this->bookingService->update($id['user'], $id['date'], $data);

        if (is_array($booking)) {
            $booking['success'] = false;
            return new JsonModel($booking);
        }

        $userSettings = $this->userSettingsRepository->findOneByUser($booking->getUser());
        $monthTotals  = $this->timeCalculatorService->getTotals(
            $booking->getUser(),
            $userSettings->getFlexStartDate(),
            $booking->getDate()
        );
        return new JsonModel([
            'success'       => true,
            'booking'       => $booking,
            'monthTotals'   => $monthTotals,
            'weekTotals'    => $this->timeCalculatorService->getWeekTotals($booking->getUser(), $booking->getDate())
        ]);
    }

    /**
     * @param int $id
     * @return JsonModel
     */
    public function delete($id)
    {
        $id         = $this->parseIdCriteria($id);
        $booking    = $this->bookingService->delete($id['user'], $id['date']);
        if (is_array($booking)) {
            $booking['success'] = false;
            return new JsonModel($booking);
        }

        $userSettings = $this->userSettingsRepository->findOneByUser($booking->getUser());
        $monthTotals  = $this->timeCalculatorService->getTotals(
            $booking->getUser(),
            $userSettings->getFlexStartDate(),
            $booking->getDate()
        );
        return new JsonModel([
            'success'       => true,
            'monthTotals'   => $monthTotals,
            'weekTotals'    => $this->timeCalculatorService->getWeekTotals($booking->getUser(), $booking->getDate())
        ]);
    }

    /**
     * @param $id
     * @return array
     */
    public function parseIdCriteria($id)
    {
        $idParts    = explode("-", $id);
        $date       = new \JhFlexiTime\DateTime\DateTime();
        $date->setTimestamp($idParts[0]);
        return ['date' => $date,'user' => $idParts[1]];
    }
}
