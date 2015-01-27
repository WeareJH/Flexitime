<?php

namespace JhFlexiTime\Service;

use Doctrine\Common\Persistence\ObjectManager;
use JhFlexiTime\DateTime\DateTime;
use JhFlexiTime\Entity\CappedCredit;
use JhFlexiTime\Repository\CappedCreditRepositoryInterface;
use ZfcUser\Entity\UserInterface;

/**
 * Class CappedCreditService
 * @package JhFlexiTime\Service
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class CappedCreditService
{
    /**
     * @var CappedCreditRepositoryInterface
     */
    protected $cappedCreditRepository;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param CappedCreditRepositoryInterface $cappedCreditRepository
     * @param ObjectManager $objectManager
     */
    public function __construct(CappedCreditRepositoryInterface $cappedCreditRepository, ObjectManager $objectManager)
    {
        $this->cappedCreditRepository = $cappedCreditRepository;
        $this->objectManager = $objectManager;
    }

    /**
     * @param UserInterface $user
     * @param float $creditAmount
     * @param DateTime $date
     */
    public function create(UserInterface $user, $creditAmount, DateTime $date)
    {
        $cappedCredit = new CappedCredit;
        $cappedCredit->setUser($user);
        $cappedCredit->setDate($date->endOfMonth());
        $cappedCredit->setCappedCredit($creditAmount);
        $this->save($cappedCredit);
    }

    /**
     * @param CappedCredit $cappedCredit
     */
    public function save(CappedCredit $cappedCredit)
    {
        $this->objectManager->persist($cappedCredit);
        $this->objectManager->flush();
    }
}
