<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Tests\Fixtures\User;

/**
 * LostCredential
 *
 * @ORM\Table(name="lost_credential")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LostCredentialRepository")
 */
class LostCredential
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
     * @ORM\ManyToOne(targetEntity="AlibapassUser")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255)
     */
    private $token;

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
     * Set user
     *
     * @param \AppBundle\Entity\AlibapassUser $user
     *
     * @return LostCredential
     */
    public function setUser(AlibapassUser $user = NULL)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\AlibapassUser
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set token
     *
     * @param string $token
     *
     * @return LostCredential
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set datetimets
     *
     * @param integer $datetimets
     *
     * @return LostCredential
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

