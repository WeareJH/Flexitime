<?php

namespace JhFlexiTime\Entity;

use JhFlexiTime\DateTime\DateTime;
use ZfcUser\Entity\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class CappedCredit
 * @package JhFlexiTime\Entity
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @ORM\Entity
 * @ORM\Table(name="capped_credit")
 */
class CappedCredit
{
    /**
     * @var UserInterface
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="JhUser\Entity\User")
     */
    protected $user = null;

    /**
     * @var DateTime
     *
     * @ORM\Id
     * @ORM\Column(type="date", name="date", nullable=false)
     */
    protected $date;

    /**
     * @var float
     *
     * @ORM\Column(type="float", name="capped_credit", nullable=false)
     */
    protected $cappedCredit;

    /**
     * @return string
     */
    public function getId()
    {
        if (!$this->user) {
            throw new \RuntimeException("No User is set. Needed to generate ID");
        }

        return $this->date->format('U') . "-" . $this->user->getId();
    }

    /**
     *
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     */
    public function setDate(DateTime $date)
    {
        $this->date = $date;
    }

    /**
     * @return float
     */
    public function getCappedCredit()
    {
        return $this->cappedCredit;
    }

    /**
     * @param float $cappedCredit
     */
    public function setCappedCredit($cappedCredit)
    {
        $this->cappedCredit = $cappedCredit;
    }

    /**
     * @param float $credit
     */
    public function subtractCredit($credit)
    {
        $this->cappedCredit -= $credit;
    }
}
