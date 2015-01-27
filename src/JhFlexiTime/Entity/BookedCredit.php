<?php

namespace JhFlexiTime\Entity;

use JhFlexiTime\DateTime\DateTime;
use ZfcUser\Entity\UserInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class BookedCredit
 * @package JhFlexiTime\Entity
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @ORM\Entity
 * @ORM\Table(name="booked_credit")
 */
class BookedCredit
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var UserInterface
     *
     * @ORM\ManyToOne(targetEntity="JhUser\Entity\User")
     */
    protected $user = null;

    /**
     * @var DateTime
     *
     * @ORM\Column(type="date", name="date", nullable=false)
     */
    protected $date;

    /**
     * @var float
     *
     * @ORM\Column(type="float", name="amount", nullable=false)
     */
    protected $amount;

    /**
     * @var BookedCreditType
     *
     * @ORM\ManyToOne(targetEntity="JhFlexiTime\Entity\BookedCreditType")
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="notes", nullable=true)
     */
    protected $notes;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return BookedCreditType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param BookedCreditType $type
     */
    public function setType(BookedCreditType $type)
    {
        $this->type = $type;
    }

    /**
     * @param string $notes
     * @return \JhFlexiTime\Entity\Booking
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
        return $this;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }
}
