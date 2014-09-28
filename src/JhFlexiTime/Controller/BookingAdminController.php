<?php

namespace JhFlexiTime\Controller;

use JhFlexiTime\Service\BookingService;
use JhFlexiTime\Service\TimeCalculatorService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Helper\Gravatar;
use Zend\View\Model\JsonModel;
use JhUser\Repository\UserRepositoryInterface;

class BookingAdminController extends AbstractActionController
{
    use GetSetDateTrait;

    /**
     * @var BookingService
     */
    protected $bookingService;

    /**
     * @var TimeCalculatorService
     */
    protected $timeCalculatorService;

    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @param BookingService $bookingService
     * @param TimeCalculatorService $timeCalculatorService
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(
        BookingService $bookingService,
        TimeCalculatorService $timeCalculatorService,
        UserRepositoryInterface $userRepository
    ) {
        $this->timeCalculatorService    = $timeCalculatorService;
        $this->bookingService           = $bookingService;
        $this->userRepository           = $userRepository;
    }

    /**
     * Accepted Types
     *
     * @var array
     */
    protected $acceptCriteria = [
        'Zend\View\Model\JsonModel' => [
            'application/json',
        ],
        'Zend\View\Model\ViewModel' => [
            'text/html',
        ],
    ];

    /**
     * Get All Users
     * TODO: Cleanup this code, create a service to get the Gravatar URL's
     * TODO: Extend Gravatar helper to return method with just URL
     * @return JsonModel
     */
    public function usersAction()
    {
        $users = $this->userRepository->findAll(false);

        $gravatarImages = [];
        foreach ($users as $user) {
            $url = 'http://www.gravatar.com/avatar/' . md5($user->getEmail()) . '/?s=40&d=mm&r=r';
            $gravatarImages[$user->getEmail()] = $url;
        }

        return new JsonModel([
           'users'  => $users,
           'images' => $gravatarImages,
        ]);
    }

    /**
     * Just renders template for Angular when text/html
     * Returns records and user if json request
     */
    public function listAction()
    {
        $viewModel = $this->acceptableViewModelSelector($this->acceptCriteria);

        if ($viewModel instanceof JsonModel) {
            $userId = $this->params()->fromRoute('id', 0);

            $month  = (string) $this->params()->fromQuery('m');
            $year   = (string) $this->params()->fromQuery('y');
            $period = $this->getDate($month, $year);

            $user = $this->userRepository->find($userId);
            if (!$user) {
                return $viewModel->setVariables([
                    'success' => false,
                    'message' => 'User does not exist',
                ]);
            }

            $records        = $this->bookingService->getUserBookingsForMonth($user, $period);
            $pagination     = $this->bookingService->getPagination($period);
            $totals         = $this->timeCalculatorService->getTotals($user, $period);

            $viewModel = $this->acceptableViewModelSelector($this->acceptCriteria);
            $viewModel->setVariables([
                'bookings' => [
                    'records'       => $records,
                    'totals'        => $totals,
                    'user'          => $user,
                ],
                'pagination' => $pagination,
                'date'       => $period,
                'today'      => new \DateTime("today"),
            ]);
        }

        return $viewModel;
    }
}
