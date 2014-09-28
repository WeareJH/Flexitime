<?php
 
namespace JhFlexiTime\Repository;
 
use Doctrine\Common\Persistence\ObjectRepository;
use ZfcUser\Entity\UserInterface;

/**
 * Class UserSettingsRepository
 * @package JhFlexiTime\Repository
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 */
class UserSettingsRepository implements UserSettingsRepositoryInterface
{
 
    /**
     * @var \Doctrine\Common\Persistence\ObjectRepository
     */
    protected $userSettingsRepository;

    /**
     * @param ObjectRepository $userSettingsRepository
     */
    public function __construct(ObjectRepository $userSettingsRepository)
    {
        $this->userSettingsRepository = $userSettingsRepository;
    }

    /**
     * @param UserInterface $user
     * @return \JhFlexiTime\Entity\UserSettings
     */
    public function findOneByUser(UserInterface $user)
    {
        return $this->userSettingsRepository->findOneBy(['user' => $user]);
    }
}
