<?php
namespace KarlRoos\Bank;

/**
 * Transaction
 * @author Karl Laurentius Roos <karlroos93@gmail.com>
 */
class Transaction
{
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
     * Date
     * @var string
     */
    public $date;

    /**
     * Constructor
     * @param string $name
     * @param float $amount
     * @param string $date
     */
    public function __construct($name = null, $amount = null, $date = null)
    {
        if ($name !== null) {
            $this->setName($name);
        }

        if ($amount !== null) {
            $this->setAmount($amount);
        }

        if ($date !== null) {
            $this->setDate($date);
        }
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

    /**
     * Set date
     * @param string $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * Get date
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }
}