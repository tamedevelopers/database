<?php

declare(strict_types=1);

namespace builder\Database\Migrations\Traits;


trait SchemaTrait{

    /**
     * Creating column
     * @param string $name 
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function increments($name)
    {
        return $this->addColumn($name, 'increments');
    }

    /**
     * Creating column
     * @param string $name 
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function bigIncrements($name)
    {
        return $this->addColumn($name, 'bigIncrements');
    }

    /**
     * Creating column
     * @param string $name 
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
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
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function tinyInteger($name, ?int $length = 4)
    {
        return $this->addColumn($name, 'tinyInteger', compact('length'));
    }

    /**
     * Creating column
     * @param string $name 
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function bigInteger($name)
    {
        return $this->addColumn($name, 'bigInteger');
    }

    /**
     * Creating column
     * @param string $name 
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function unsignedBigInteger($name)
    {
        return $this->addColumn($name, 'unsignedBigInteger');
    }

    /**
     * Creating a column
     * @param string $name 
     * @param int $total
     * @param int $places
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
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
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function decimal($name, $total = 8, $places = 2)
    {
        return $this->addColumn($name, 'decimal', ['total' => $total, 'places' => $places]);
    }

    /**
     * Creating a column
     *
     * @param string $name
     * @param int|null $total
     * @param int|null $places
     *
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function float($name, $total = null, $places = null)
    {
        return $this->addColumn($name, 'float', compact('total', 'places'));
    }

    /**
     * Creating column
     * @param string $name 
     * @param int $length 
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function string($name, ?int $length = 255)
    {
        return $this->addColumn($name, 'string', $length);
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
     * Creating column
     * @param string $name 
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function text($name)
    {
        return $this->addColumn($name, 'text');
    }

    /**
     * Creating column
     * @param string $name 
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function longText($name)
    {
        return $this->addColumn($name, 'longText');
    }

    /**
     * Creating column
     * @param string $name 
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function mediumText($name)
    {
        return $this->addColumn($name, 'mediumText');
    }

    /**
     * Creating a column
     * @param string $name 
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function blob($name)
    {
        return $this->addColumn($name, 'blob');
    }

    /**
     * Creating a column
     * @param string $name 
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function tinyBlob($name)
    {
        return $this->addColumn($name, 'tinyblob');
    }

    /**
     * Creating a column
     * @param string $name 
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function mediumBlob($name)
    {
        return $this->addColumn($name, 'mediumblob');
    }

    /**
     * Creating a column
     * @param string $name 
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function longBlob($name)
    {
        return $this->addColumn($name, 'longblob');
    }

    /**
     * Creating a column
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function rememberToken()
    {
        return $this->addColumn('remember_token', 'string', ['nullable' => true, 'length' => 100]);
    }

    /**
     * Creating a column
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function softDeletes()
    {
        return $this->addColumn('deleted_at', 'timestamp', ['nullable' => true]);
    }

    /**
     * Creating a column
     * @param string $name 
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function year($name)
    {
        return $this->addColumn($name, 'year');
    }

    /**
     * Creating a column
     * @param string $name 
     * @param int $length
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function binary($name, $length = 255)
    {
        return $this->addColumn($name, 'binary', compact('length'));
    }

    /**
     * Creating a column
     * @param string $name 
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function json($name)
    {
        return $this->addColumn($name, 'json');
    }

    /**
     * Creating column
     * @param string $name 
     * @param array $values 
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function enum($name, array $values)
    {
        return $this->addColumn($name, 'enum', compact('values'));
    }

    /**
     * Creating a "set" column.
     *
     * @param  string  $name
     * @param  array  $values
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function set($name, array $values)
    {
        return $this->addColumn($name, 'set', compact('values'));
    }

    /**
     * Creating column
     * @param string $name 
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function boolean($name)
    {
        return $this->addColumn($name, 'boolean');
    }

    /**
     * Creating a column with UUID data type.
     *
     * @param string $name
     *
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function uuid($name)
    {
        return $this->addColumn($name, 'uuid', ['length' => 16, 'default' => '(UUID())']);
    }

    /**
     * Creating a column with IP Address data type.
     *
     * @param string $name
     *
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function ipAddress($name)
    {
        return $this->addColumn($name, 'ipAddress');
    }

    /**
     * Creating a column with MAC Address data type.
     *
     * @param string $name
     *
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function macAddress($name)
    {
        return $this->binary($name, 6);
    }

    /**
     * Creating a column
     * @param string $name 
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function date($name)
    {
        return $this->addColumn($name, 'date');
    }

    /**
     * Creating a column
     * @param string $name 
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function dateTime($name)
    {
        return $this->addColumn($name, 'dateTime');
    }

    /**
     * Creating a column
     * @param string $name 
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function time($name)
    {
        return $this->addColumn($name, 'time');
    }

    /**
     * Creating a column
     * @param string $name 
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
     */
    public function timestamp($name)
    {
        return $this->addColumn($name, 'timestamp', ['nullable' => true, 'default' => null]);
    }

    /**
     * Creating a column
     * 
     * @return \builder\Database\Migrations\Traits\addColumn
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

}