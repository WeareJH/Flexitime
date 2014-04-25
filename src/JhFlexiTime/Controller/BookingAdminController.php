<?php

namespace JhFlexiTime\Controller;

use JhFlexiTime\Service\BookingService;
use JhFlexiTime\Service\TimeCalculatorService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Helper\Gravatar;
use Zend\View\Model\JsonModel;
use Zend\Validator\Date as DateValidator;
use JhUser\Repository\UserRepositoryInterface;

class BookingAdminController extends AbstractActionController
{

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
     * @var Gravatar
     */
    protected $gravaterHelper;

    /**
     * @param BookingService $bookingService
     * @param TimeCalculatorService $timeCalculatorService
     * @param UserRepositoryInterface $userRepository
     * @param Gravatar $gravatarHelper
     */
    public function __construct(
        BookingService $bookingService,
        TimeCalculatorService $timeCalculatorService,
        UserRepositoryInterface $userRepository,
        Gravatar $gravatarHelper
    ) {
        $this->timeCalculatorService    = $timeCalculatorService;
        $this->bookingService           = $bookingService;
        $this->userRepository           = $userRepository;
        $this->gravaterHelper           = $gravatarHelper;
    }

    /**
     * Accepted Types
     *
     * @var array
     */
    protected $acceptCriteria = array(
        'Zend\View\Model\JsonModel' => array(
            'application/json',
        ),
        'Zend\View\Model\ViewModel' => array(
            'text/html',
        ),
    );

    /**
     * Get All Users
     * TODO: Cleanup this code, create a service to get the Gravatar URL's
     * @return JsonModel
     */
    public function usersAction()
    {
        $users = $this->userRepository->findAll(false);

        $gravatarImages = [];
        foreach ($users as $user) {
            $url = $this->gravaterHelper->__invoke($user->getEmail(),  array('img_size' => '40'))->__toString();
            preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $url, $result);
            $url = array_pop($result);
            $gravatarImages[$user->getEmail()] = $url;
        }

        return new JsonModel(array(
           'users'  => $users,
           'images' => $gravatarImages,
        ));
    }

    /**
     * Just renders template for Angular when text/html
     * Returns records and user if json request
     */
    public function viewAction()
    {
        $viewModel = $this->acceptableViewModelSelector($this->acceptCriteria);

        if ($viewModel instanceof JsonModel) {
            $userId = $this->params()->fromRoute('id', 0);

            $month  = (string) $this->params()->fromQuery('m');
            $year   = (string) $this->params()->fromQuery('y');

            $validator  = new DateValidator(array('format' => 'M Y'));
            $period = new \DateTime();
            if ($validator->isValid(sprintf("%s %s", $month, $year))) {
                $period = new \DateTime(sprintf('last day of %s %s 23:59:59', $month, $year));
            }

            $user = $this->userRepository->find($userId);
            if (!$user) {
                return $viewModel->setVariables(array(
                    'success' => false,
                    'message' => 'User does not exist',
                ));
            }

            $records        = $this->bookingService->getUserBookingsForMonth($user, $period);
            $pagination     = $this->bookingService->getPagination($period);
            $totals         = $this->timeCalculatorService->getTotals($user, $period);

            $viewModel = $this->acceptableViewModelSelector($this->acceptCriteria);
            $viewModel->setVariables(array(
                'time' => array(
                    'records'       => $records,
                    'totals'        => $totals,
                    'user'          => $user,
                ),
                'pagination' => $pagination,
                'date'       => $period,
                'today'      => new \DateTime("today"),
            ));
        }

        return $viewModel;
    }
}