<?php

declare(strict_types=1);

namespace builder\Database\Schema;

class Expression 
{
    /**
     * The raw SQL expression.
     *
     * @var string
     */
    protected $value;

    /**
     * Create a new raw expression instance.
     *
     * @param string $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Get the raw SQL expression.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the raw SQL expression.
     * 
     * @param string $value
     *
     * @return string
     */
    public function setValue($value)
    {
        return $this->value = $value;
    }

}