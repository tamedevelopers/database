<?php

declare(strict_types=1);

namespace builder\Database\Migrations\Trait;


trait SchemaTrait{

    /**
     * Creating column
     * @param string $name 
     * @param array $values 
     * 
     * @return object|null\enum
     */
    public function enum($name, ?array $values)
    {
        return $this->addColumn($name, 'enum', compact('values'));
    }

    /**
     * Creating column
     * @param string $name 
     * 
     * @return object\increments
     */
    public function increments($name)
    {
        return $this->addColumn($name, 'increments');
    }

    /**
     * Creating column
     * @param string $name 
     * 
     * @return object\bigIncrements
     */
    public function bigIncrements($name)
    {
        return $this->addColumn($name, 'bigIncrements');
    }

    /**
     * Creating column
     * @param string $name 
     * 
     * @return object\integer
     */
    public function integer($name)
    {
        return $this->addColumn($name, 'integer');
    }

    /**
     * Creating column
     * @param string $name 
     * @param int $length 
     * 
     * @return object\tinyInteger
     */
    public function tinyInteger($name, ?int $length = 4)
    {
        return $this->addColumn($name, 'tinyInteger', compact('length'));
    }

    /**
     * Creating column
     * @param string $name 
     * 
     * @return object\bigInteger
     */
    public function bigInteger($name)
    {
        return $this->addColumn($name, 'bigInteger');
    }

    /**
     * Creating column
     * @param string $name 
     * 
     * @return object\unsignedBigInteger
     */
    public function unsignedBigInteger($name)
    {
        return $this->addColumn($name, 'unsignedBigInteger');
    }

    /**
     * Creating column
     * 
     * @return object\unsigned
     */
    public function unsigned()
    {
        $this->columns[count($this->columns) - 1]['unsigned'] = true;
        return $this;
    }

    /**
     * Creating column
     * @param string $name 
     * 
     * @return object\boolean
     */
    public function boolean($name)
    {
        return $this->addColumn($name, 'boolean');
    }

    /**
     * Creating column
     * @param string $name 
     * @param int $length 
     * 
     * @return object\string
     */
    public function string($name, ?int $length = 255)
    {
        return $this->addColumn($name, 'string', $length);
    }
    
    /**
     * Creating column
     * @param string $name 
     * 
     * @return object\text
     */
    public function text($name)
    {
        return $this->addColumn($name, 'text');
    }

    /**
     * Creating column
     * @param string $name 
     * 
     * @return object|\longText
     */
    public function longText($name)
    {
        return $this->addColumn($name, 'longText');
    }

    /**
     * Creating column
     * @param string $name 
     * 
     * @return object\mediumText
     */
    public function mediumText($name)
    {
        return $this->addColumn($name, 'mediumText');
    }

    /**
     * Creating a column
     * @param string $name 
     * 
     * @return object\json
     */
    public function json($name)
    {
        return $this->addColumn($name, 'json');
    }

    /**
     * Creating a column
     * @param string $name 
     * 
     * @return object\blob
     */
    public function blob($name)
    {
        return $this->addColumn($name, 'blob');
    }

    /**
     * Creating a column
     * 
     * @return object\softDeletes
     */
    public function softDeletes()
    {
        return $this->addColumn('deleted_at', 'softDeletes');
    }

    /**
     * Creating a column
     * @param string $name 
     * 
     * @return object\date
     */
    public function date($name)
    {
        return $this->addColumn($name, 'date');
    }

    /**
     * Creating a column
     * @param string $name 
     * 
     * @return object\dateTime
     */
    public function dateTime($name)
    {
        return $this->addColumn($name, 'dateTime');
    }

    /**
     * Creating a column
     * @param string $name 
     * 
     * @return object\time
     */
    public function time($name)
    {
        return $this->addColumn($name, 'time');
    }

    /**
     * Creating a column
     * @param string $name 
     * 
     * @return object\time
     */
    public function timestamp($name)
    {
        return $this->addColumn($name, 'timestamp', ['nullable' => true, 'default' => null]);
    }

    /**
     * Creating a column
     * 
     * @return object\timestamps
     */
    public function timestamps()
    {
        return $this->addColumn('created_at', 'timestamps', [
                        'nullable'  => true,
                        'index'     => $this->generix_name('created_at_index')
                    ])
                    ->addColumn('updated_at', 'timestamps', [
                        'nullable'  => true,
                        'index'     => $this->generix_name('updated_at_index')
                    ]);
    }

    /**
     * Creating a column
     * 
     * @return object\rememberToken
     */
    public function rememberToken()
    {
        return $this->addColumn('remember_token', 'rememberToken');
    }

    /**
     * Creating a column
     * @param string $name 
     * @param int $total
     * @param int $places
     * 
     * @return object\double
     */
    public function double($name, $total = 8, $places = 2)
    {
        return $this->addColumn($name, 'double', ['total' => $total, 'places' => $places]);
    }

    /**
     * Creating a column
     * @param string $name 
     * @param int $total
     * @param int $places
     * 
     * @return object\decimal
     */
    public function decimal($name, $total = 8, $places = 2)
    {
        return $this->addColumn($name, 'decimal', ['total' => $total, 'places' => $places]);
    }

    /**
     * Creating a column
     * @param string $name 
     * @param int $length
     * 
     * @return object\char
     */
    public function char($name, $length = 255)
    {
        return $this->addColumn($name, 'char', compact('length'));
    }

    /**
     * Creating a column
     * @param string $name 
     * @param int $length
     * 
     * @return object\binary
     */
    public function binary($name, $length = 255)
    {
        return $this->addColumn($name, 'binary', compact('length'));
    }

}