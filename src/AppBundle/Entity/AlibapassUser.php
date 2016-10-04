<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AlibapassUser
 *
 * @ORM\Table(name="alibapass_user")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AlibapassUserRepository")
 */
class AlibapassUser
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
     * @ORM\Column(name="firstname", type="string", length=255)
     *
     * @Assert\NotBlank()
     * @Assert\Length(
     *  min = 1, max = 255
     * )
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255)
     *
     * @Assert\NotBlank()
     * @Assert\Length(
     *  min = 1, max = 255
     * )
     */
    private $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, unique=true)
     *
     * @Assert\NotBlank()
     * @Assert\Length(
     *  min = 5, max = 255
     * )
     * @Assert\Email
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="username", type="string", length=255, unique=true)
     *
     * @Assert\NotBlank()
     * @Assert\Length(
     *  min = 2, max = 255
     * )
     */
    private $username;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=true)
     */
    private $active;

    /**
     * @var bool
     *
     * @ORM\Column(name="admin", type="boolean", nullable=true)
     */
    private $admin;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=255)
     */
    private $password;

    /**
     * @var ArrayCollection alibapassgroup
     *
     * @ORM\ManyToMany(targetEntity="AlibapassGroup")
     * @ORM\JoinTable(name="alibapassuser_alibapassgroup",
     *  joinColumns={@ORM\JoinColumn(name="alibapassuser_id", referencedColumnName="id", onDelete="CASCADE")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="alibapassgroup_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    private $alibapassgroup;

    public function __construct() {
        $this->alibapassgroup = new ArrayCollection();
    }

    public function getAlibapassgroup() {
        return $this->alibapassgroup;
    }

    public function addAlibapassgroup(AlibapassGroup $alibapassgroup) {
        $this->alibapassgroup[] = $alibapassgroup;
    }

    public function removeAlibapassgroup(AlibapassGroup $alibapassgroup) {
        $this->alibapassgroup->removeElement($alibapassgroup);
    }

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
     * Set firstname
     *
     * @param string $firstname
     *
     * @return AlibapassUser
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set lastname
     *
     * @param string $lastname
     *
     * @return AlibapassUser
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get lastname
     *
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return AlibapassUser
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return AlibapassUser
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return AlibapassUser
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set admin
     *
     * @param boolean $admin
     *
     * @return AlibapassUser
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * Get admin
     *
     * @return bool
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return AlibapassUser
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }
}

