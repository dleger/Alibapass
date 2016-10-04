<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Firewall
 *
 * @ORM\Table(name="firewall")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FirewallRepository")
 */
class Firewall
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=255)
     */
    private $ip;

    /**
     * @var int
     *
     * @ORM\Column(name="type", type="integer")
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="datetimets", type="integer")
     */
    private $datetimets;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set ip
     *
     * @param string $ip
     *
     * @return Firewall
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * Set type
     *
     * @param integer $type
     *
     * @return Firewall
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set datetimets
     *
     * @param integer $datetimets
     *
     * @return Firewall
     */
    public function setDatetimets($datetimets)
    {
        $this->datetimets = $datetimets;

        return $this;
    }

    /**
     * Get datetimets
     *
     * @return int
     */
    public function getDatetimets()
    {
        return $this->datetimets;
    }
}

