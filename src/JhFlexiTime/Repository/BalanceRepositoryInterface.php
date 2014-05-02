<?php

namespace JhFlexiTime\Repository;

use ZfcUser\Entity\UserInterface;

/**
 * Interface BalanceRepositoryInterface
 * @package JhFlexiTime\Repository
 * @author Aydin Hassan <aydin@wearejh.com>
 */
interface BalanceRepositoryInterface
{
    /**
     * @param UserInterface $user
     * @return \JhFlexiTime\Entity\RunningBalance
     */
    public function findByUser(UserInterface $user);

    /**
     * @param array $criteria
     * @return \JhFlexiTime\Entity\RunningBalance
     */
    public function findOneBy(array $criteria);
}
