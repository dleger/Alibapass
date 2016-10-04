<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EntryField
 *
 * @ORM\Table(name="entry_field")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EntryFieldRepository")
 */
class EntryField
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
     * @ORM\ManyToOne(targetEntity="Entry")
     * @ORM\JoinColumn(name="id_entry", referencedColumnName="id", onDelete="CASCADE")
     */
    private $entry;

    /**
     * @ORM\ManyToOne(targetEntity="Field")
     * @ORM\JoinColumn(name="id_field", referencedColumnName="id", onDelete="CASCADE")
     */
    private $field;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", nullable=true)
     */
    private $value;


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
     * Set entry
     *
     * @param \AppBundle\Entity\Entry $entry
     *
     * @return EntryField
     */
    public function setEntry(Entry $entry = NULL)
    {
        $this->entry = $entry;

        return $this;
    }

    /**
     * Get entry
     *
     * @return \AppBundle\Entity\Entry
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * Set field
     *
     * @param \AppBundle\Entity\Field $field
     *
     * @return EntryField
     */
    public function setField(Field $field = NULL)
    {
        $this->field = $field;

        return $this;
    }

    /**
     * Get field
     *
     * @return \AppBundle\Entity\Field
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Set value
     *
     * @param string $value
     *
     * @return EntryField
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }
}

