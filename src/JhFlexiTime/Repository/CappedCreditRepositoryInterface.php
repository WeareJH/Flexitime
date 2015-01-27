<?php

namespace JhFlexiTime\Repository;

use ZfcUser\Entity\UserInterface;
use JhFlexiTime\Entity\CappedCredit;

/**
 * Class CappedCreditRepository
 * @package JhFlexiTime\Repository
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
interface CappedCreditRepositoryInterface
{
    /**
     * Proxy to Doctrine Repo
     *
     * @param array $criteria
     * @return \JhFlexiTime\Entity\RunningBalance
     */
    public function findOneBy(array $criteria);

    /**
     * @param UserInterface $user
     * @return CappedCredit[]
     */
    public function findAllByUser(UserInterface $user);

    /**
     * @param UserInterface $user
     */
    public function getTotalCappedCreditByUser(UserInterface $user);

    /**
     * Delete all records by user
     *
     * @param UserInterface $user
     */
    public function deleteAllByUser(UserInterface $user);

    /**
     * Delete all Records in table
     */
    public function deleteAll();
}