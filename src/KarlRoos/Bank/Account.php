<?php
namespace KarlRoos\Bank;

/**
 * Account
 * @author Karl Laurentius Roos <karlroos93@gmail.com>
 */
class Account
{
    /**
     * Identifier
     * @var string
     */
    public $identifier;

    /**
     * Name
     * @var string
     */
    public $name;

    /**
     * Amount
     * @var float
     */
    public $amount = 0;

    /**
     * Constructor
     * @param string $identifier
     * @param stringf $name
     * @param float $amount
     */
    public function __construct($identifier = null, $name = null, $amount = null)
    {
        if ($identifier !== null) {
            $this->setIdentifier($identifier);
        }

        if ($name !== null) {
            $this->setName($name);
        }

        if ($amount !== null) {
            $this->setAmount($amount);
        }
    }

    /**
     * Set identifier
     * @param string $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Get identifier
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set name
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set amount
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * Get amount
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }
}