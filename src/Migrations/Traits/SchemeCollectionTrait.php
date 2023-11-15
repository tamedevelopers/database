<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Migrations\Traits;

trait SchemaCollectionTrait{

    /**
     * Creating column
     * 
     * @return $this
     */
    public function unsigned()
    {
        $this->columns[count($this->columns) - 1]['unsigned'] = true;
        return $this;
    }

    /**
     * Creating Default value
     * @param string $value 
     * 
     * @return $this
     */
    public function default($value)
    {
        $this->columns[count($this->columns) - 1]['default'] = $value;
        return $this;
    }

    /**
     * Creating Nullable value
     * @param string $value 
     * 
     * @return $this
     */
    public function nullable()
    {
        $this->columns[count($this->columns) - 1]['nullable'] = true;
        return $this;
    }

    /**
     * Creating Indexs
     * @param string $name 
     * 
     * @return $this
     */
    public function id($name = 'id')
    {
        return $this->addColumn($name, 'bigInteger', [
            'primary'           => "PRIMARY", 
            'unsigned'          => true, 
            'auto_increment'    => true,
        ]);
    }

    /**
     * Creating Indexs
     * @param string $name 
     * @param bool $autoIncrement \Default is true
     * @param bool $unsigned \Default is true
     * 
     * @return $this
     */
    public function primary($name, ?bool $autoIncrement = true, ?bool $unsigned = true)
    {
        return $this->addColumn($name, 'bigInteger', [
            'primary'           => "PRIMARY", 
            'unsigned'          => $unsigned, 
            'auto_increment'    => $autoIncrement,
        ]);
    }

    /**
     * Creating Indexs
     * @param string|null $name 
     * 
     * @return $this
     */
    public function index($name = null)
    {
        $this->columns[count($this->columns) - 1]['index'] = $this->genericIdentifier($name);

        return $this;
    }

    /**
     * Creating Indexs
     * @param string|null $name 
     * 
     * @return $this
     */
    public function unique($name = null)
    {
        $this->columns[count($this->columns) - 1]['unique'] = $this->genericIdentifier($name);
        
        return $this;
    }

    /**
     * Creating Constraints Property
     * @param string $column
     * - Child column name
     * 
     * @return $this
     */
    public function foreign($column)
    {
        return $this->addColumn($column, 'foreign');
    }

    /**
     * Creating Constraints Property
     * @param string $column
     * - Child column name
     * 
     * @return $this
     */
    public function foreignId($column)
    {
        $this->bigInteger($column)->unsigned();

        return $this->foreign($column);
    }

    /**
     * Create a foreign key constraint on this column referencing the "id" column of the conventionally related table.
     *
     * @param string|null $table
     * @param string $column
     * - [optional] Default is `id`
     * 
     * @param string|null $indexName
     * 
     * @return $this
     */
    public function constrained($table = null, $column = 'id', $indexName = null)
    {
        // we try to use defined table name, if no name is given to the method
        if(empty($table)){
            $table = explode('_', $this->tableName)[0] ?? '';
        }
        
        return $this->references($column, $indexName)->on($table);
    }

    /**
     * Creating Constraints Property
     * 
     * @param string $columns 
     * <code> - Parent Table References Column name </code>
     * 
     * @param string|null $indexName
     * 
     * @return $this
     */
    public function references($columns, $indexName = null)
    {
        $this->columns[count($this->columns) - 1]['references'] = $columns;
        $this->columns[count($this->columns) - 1]['generix'] = $this->genericIdentifier($indexName ?? $columns);

        return $this;
    }

    /**
     * Creating Constraints Property
     * 
     * @param string $table 
     * - Table name you're referencing to
     * 
     * @return $this
     */
    public function on($table)
    {
        $this->columns[count($this->columns) - 1]['on'] = $table;
        return $this;
    }

    /**
     * Creating Constraints Property
     * @param string $action 
     * 
     * @return $this
     */
    public function onDelete($action)
    {
        $this->columns[count($this->columns) - 1]['onDelete'] = $action;
        return $this;
    }

    /**
     * Creating Constraints Property
     * @param string $action 
     * 
     * @return $this
     */
    public function onUpdate($action)
    {
        $this->columns[count($this->columns) - 1]['onUpdate'] = $action;
        return $this;
    }
    
} 
