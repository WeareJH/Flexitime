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
    /**
     * Just renders template for Angular when text/html
     */
    public function indexAction()
    {
    }
}
