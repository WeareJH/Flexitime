<?php

namespace JhFlexiTime\Repository;

use ZfcUser\Entity\UserInterface;

/**
 * Interface UserSettingsRepositoryInterface
 * @package JhFlexiTime\Repository
 * @author Aydin Hassan <aydin@wearejh.com>
 */
interface UserSettingsRepositoryInterface
{
    /**
     * @param UserInterface $user
     * @return \JhFlexiTime\Entity\UserSettings
     */
    public function findOneByUser(UserInterface $user);
}
