<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Field
 *
 * @ORM\Table(name="field")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FieldRepository")
 */
class Field
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
     * @ORM\Column(name="value", type="string", length=255)
     */
    private $value;

    /**
     * @var string
     *
     * @ORM\Column(name="default_value", type="string", length=255, nullable=true)
     */
    private $defaultValue;

    /**
     * @var string
     *
     * @ORM\Column(name="placeholder", type="string", length=255, nullable=true)
     */
    private $placeholder;

    /**
     * @var int
     *
     * @ORM\Column(name="field_order", type="integer", nullable=true)
     */
    private $fieldOrder;

    /**
     * @ORM\ManyToOne(targetEntity="EntryType")
     * @ORM\JoinColumn(name="entry_type_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $entryType;

    /**
     * @ORM\ManyToOne(targetEntity="FieldType")
     * @ORM\JoinColumn(name="field_type_id", referencedColumnName="id")
     */
    private $fieldType;

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
     * Set value
     *
     * @param string $value
     *
     * @return Field
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

    /**
     * Set defaultValue
     *
     * @param string $defaultValue
     *
     * @return Field
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    /**
     * Get defaultValue
     *
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Set placeholder
     *
     * @param string $placeholder
     *
     * @return Field
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Get placeholder
     *
     * @return string
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * Set fieldOrder
     *
     * @param integer $fieldOrder
     *
     * @return Field
     */
    public function setFieldOrder($fieldOrder)
    {
        $this->fieldOrder = $fieldOrder;

        return $this;
    }

    /**
     * Get fieldOrder
     *
     * @return int
     */
    public function getFieldOrder()
    {
        return $this->fieldOrder;
    }

    /**
     * Set entryType
     *
     * @param \AppBundle\Entity\EntryType $entryType
     *
     * @return Field
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
     * Set fieldType
     *
     * @param \AppBundle\Entity\FieldType $fieldType
     *
     * @return Field
     */
    public function setFieldType(FieldType $fieldType = NULL)
    {
        $this->fieldType = $fieldType;

        return $this;
    }

    /**
     * Get fieldType
     *
     * @return \AppBundle\Entity\FieldType
     */
    public function getFieldType()
    {
        return $this->fieldType;
    }

    public function __toString() {
        return $this->getValue();
    }
}

