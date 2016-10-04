<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Archive
 *
 * @ORM\Table(name="archive")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ArchiveRepository")
 */
class Archive
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
     * @ORM\Column(name="site", type="string", length=255)
     */
    private $site;

    /**
     * @var string
     *
     * @ORM\Column(name="groups", type="text")
     */
    private $groups;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="credentials", type="text")
     */
    private $credentials;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="text")
     */
    private $comment;

    /**
     * @var string
     *
     * @ORM\Column(name="userfullname", type="string", length=255)
     */
    private $userfullname;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datetimeutc", type="datetime")
     */
    private $datetimeutc;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=255)
     */
    private $action;

    /**
     * @var int
     *
     * @ORM\Column(name="id_entry", type="integer")
     */
    private $entry;

    /**
     * @var int
     *
     * @ORM\Column(name="revision", type="integer")
     */
    private $revision;


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
     * Set site
     *
     * @param string $site
     *
     * @return Archive
     */
    public function setSite($site)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site
     *
     * @return string
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Set groups
     *
     * @param string $groups
     *
     * @return Archive
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * Get groups
     *
     * @return string
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Archive
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set credentials
     *
     * @param string $credentials
     *
     * @return Archive
     */
    public function setCredentials($credentials)
    {
        $this->credentials = $credentials;

        return $this;
    }

    /**
     * Get credentials
     *
     * @return string
     */
    public function getCredentials()
    {
        return $this->credentials;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Archive
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
     * Set userfullname
     *
     * @param string $userfullname
     *
     * @return Archive
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
     * Set datetimeutc
     *
     * @param \DateTime $datetimeutc
     *
     * @return Archive
     */
    public function setDatetimeutc($datetimeutc)
    {
        $this->datetimeutc = $datetimeutc;

        return $this;
    }

    /**
     * Get datetimeutc
     *
     * @return \DateTime
     */
    public function getDatetimeutc()
    {
        return $this->datetimeutc;
    }

    /**
     * Set action
     *
     * @param string $action
     *
     * @return Archive
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set entry
     *
     * @param integer $entry
     *
     * @return Archive
     */
    public function setEntry($entry)
    {
        $this->entry = $entry;

        return $this;
    }

    /**
     * Get entry
     *
     * @return int
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * Set revision
     *
     * @param integer $revision
     *
     * @return Archive
     */
    public function setRevision($revision)
    {
        $this->revision = $revision;

        return $this;
    }

    /**
     * Get revision
     *
     * @return int
     */
    public function getRevision()
    {
        return $this->revision;
    }
}

