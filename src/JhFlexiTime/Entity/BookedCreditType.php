<?php

namespace JhFlexiTime\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class BookedCreditType
 * @package JhFlexiTime\Entity
 * @author Aydin Hassan <aydin@hotmail.co.uk>
 *
 * @ORM\Entity
 * @ORM\Table(name="booked_credit_type")
 */
class BookedCreditType
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
     * @var string
     *
     * @ORM\Column(type="string", name="short_name", nullable=true)
     */
    protected $shortName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="label", nullable=true)
     */
    protected $label;
}
