<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bridge\Doctrine\Tests\Fixtures\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entry
 *
 * @ORM\Table(name="entry")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EntryRepository")
 */
class Entry
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
     * @ORM\ManyToOne(targetEntity="Company")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="EntryType")
     * @ORM\JoinColumn(name="entry_type_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $entryType;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @var ArrayCollection alibapassgroup
     *
     * @ORM\ManyToMany(targetEntity="AlibapassGroup", cascade={"persist"})
     * @ORM\JoinTable(name="entry_alibapassgroup",
     *  joinColumns={@ORM\JoinColumn(name="entry_id", referencedColumnName="id", onDelete="CASCADE")},
     *  inverseJoinColumns={@ORM\JoinColumn(name="alibapassgroup_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    private $alibapassgroup;

    /**
     * @var int
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="userfullname", type="string", length=255)
     */
    private $userfullname;

    /**
     * @var int
     *
     * @ORM\Column(name="rev", type="integer")
     */
    private $rev;

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
     * Set company
     *
     * @param \AppBundle\Entity\Company $company
     *
     * @return Entry
     */
    public function setCompany(Company $company = NULL)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return \AppBundle\Entity\Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set entryType
     *
     * @param \AppBundle\Entity\EntryType $entryType
     *
     * @return Entry
     */
    public function setEntryType(EntryType $entryType = NULL)
    {
        $this->entryType = $entryType;

        return $this;
    }

    /**
     * Get entryType
     *
     * @return \AppBundle\Entity\EntryType
     */
    public function getEntryType()
    {
        return $this->entryType;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Entry
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Get groups
     */
    public function __construct() {
        $this->alibapassgroup = new ArrayCollection();
    }

    public function getAlibapassgroup() {
        return $this->alibapassgroup;
    }

    /**
     * Add groups
     */
    public function addAlibapassgroup(AlibapassGroup $alibapassgroup) {
        $this->alibapassgroup[] = $alibapassgroup;
    }

    /**
     * Remove Groups
     */
    public function removeAlibapassgroup(AlibapassGroup $alibapassgroup) {
        $this->alibapassgroup->removeElement($alibapassgroup);
    }

    /**
     * Set user
     *
     * @param integer $user
     *
     * @return Entry
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get rev
     *
     * @return int
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set userfullname
     *
     * @param string $userfullname
     *
     * @return Entry
     */
    public function setUserfullname($userfullname)
    {
        $this->userfullname = $userfullname;

        return $this;
    }

    /**
     * Get userfullname
     *
     * @return string
     */
    public function getUserfullname()
    {
        return $this->userfullname;
    }

    /**
     * Set rev
     *
     * @param integer $rev
     *
     * @return Entry
     */
    public function setRev($rev)
    {
        $this->rev = $rev;

        return $this;
    }

    /**
     * Get rev
     *
     * @return int
     */
    public function getRev()
    {
        return $this->rev;
    }

    /**
     * Set datetimets
     *
     * @param integer $datetimets
     *
     * @return Entry
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

