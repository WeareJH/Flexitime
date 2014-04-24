<?php

namespace JhFlexiTime\Controller;

use JhFlexiTime\Validator\UniqueBooking;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Validator\Date as DateValidator;

/**
 * Class BookingController
 * @package JhFlexiTime\Controller
 * @author Aydin Hassan <aydin@wearejh.com>s
 */
class BookingController extends AbstractActionController
{

    /**
     * @var \JhFlexiTime\Form\BookingForm
     */
    protected $bookingForm;

    /**
     * @var \JhFlexiTime\Service\BookingService
     */
    protected $bookingService;

    /**
     * @var \JhFlexiTime\Service\TimeCalculatorService
     */
    protected $timeCalculatorService;

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
     * @return ViewModel
     */
    public function listAction()
    {

        $month  = (string) $this->params()->fromQuery('m');
        $year   = (string) $this->params()->fromQuery('y');

        $validator  = new DateValidator(array('format' => 'M Y'));
        $period = new \DateTime();
        if ($validator->isValid(sprintf("%s %s", $month, $year))) {
            $period = new \DateTime(sprintf('last day of %s %s 23:59:59', $month, $year));
        }

        $user           = $this->zfcUserAuthentication()->getIdentity();
        $records        = $this->getBookingService()->getUserBookingsForMonth($user, $period);
        $pagination     = $this->getBookingService()->getPagination($period);
        $totals         = $this->getTimeCalculatorService()->getTotals($user, $period);

        $viewModel = $this->acceptableViewModelSelector($this->acceptCriteria);
        $viewModel->setVariables(array(
            'bookings' => array(
                'records'       => $records,
                'totals'        => $totals,
                'user'          => $user,
            ),
            'pagination' => $pagination,
            'date'       => $period,
            'today'      => new \DateTime("today"),
        ));

        if ($viewModel instanceof ViewModel) {
            $viewModel->setVariable('form', $this->getBookingForm());
        }

        return $viewModel;
    }

    /**
     * @return \JhFlexiTime\Form\BookingForm
     */
    protected function getBookingForm()
    {
        if (!$this->bookingForm) {
            $sl = $this->getServiceLocator();
            $this->bookingForm = $sl->get('FormElementManager')
                ->get('JhFlexiTime\Form\BookingForm');

        }
        return $this->bookingForm;
    }

    /**
     * @return \JhFlexiTime\Service\BookingService
     */
    protected function getBookingService()
    {
        if (!$this->bookingService) {
            $this->bookingService = $this->getServiceLocator()->get('JhFlexiTime\Service\BookingService');
        }
        return $this->bookingService;
    }

    /**
     * @return \JhFlexiTime\Service\TimeCalculatorService
     */
    protected function getTimeCalculatorService()
    {
        if (!$this->timeCalculatorService) {
            $this->timeCalculatorService = $this->getServiceLocator()->get('JhFlexiTime\Service\TimeCalculatorService');
        }
        return $this->timeCalculatorService;
    }
}
