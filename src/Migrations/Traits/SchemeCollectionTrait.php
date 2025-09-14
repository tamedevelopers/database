<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Migrations\Traits;

trait SchemaCollectionTrait{
    
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
     * Creating Indexes
     * 
     * @return $this
     */
    public function primary()
    {
        $lastIndex = array_key_last($this->columns);
        $name = $this->columns[$lastIndex]['name'] ?? 'id';
        $type = $this->columns[$lastIndex]['type'] ?? 'integer';

        // unset the element in columns
        // since we're trying to create an auto incrementing primary key
        // for the column in the schema collection.
        unset($this->columns[$lastIndex]);

        return $this->addColumn($name, $type, [
            'primary'           => "PRIMARY", 
            'auto_increment'    => true,
        ]);
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
        $lastIndex = array_key_last($this->columns);
        $this->columns[$lastIndex]['references'] = $columns;
        $this->columns[$lastIndex]['generix'] = $this->genericIdentifier($indexName);

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
        $lastIndex = array_key_last($this->columns);
        $this->columns[$lastIndex]['on'] = $table;
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
        $lastIndex = array_key_last($this->columns);
        $this->columns[$lastIndex]['onDelete'] = $action;
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
        $lastIndex = array_key_last($this->columns);
        $this->columns[$lastIndex]['onUpdate'] = $action;
        return $this;
    }

    /**
     * Creating column
     * 
     * @return $this
     */
    public function unsigned()
    {
        $lastIndex = array_key_last($this->columns);
        $this->columns[$lastIndex]['unsigned'] = true;
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
        $lastIndex = array_key_last($this->columns);
        $this->columns[$lastIndex]['default'] = $value;
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
        $lastIndex = array_key_last($this->columns);
        $this->columns[$lastIndex]['nullable'] = true;

        return $this;
    }

    /**
     * Creating Indexs
     * @param string|null $name 
     * 
     * @return $this
     */
    public function index($name = null)
    {
        $lastIndex = array_key_last($this->columns);
        $this->columns[$lastIndex]['index'] = $this->genericIdentifier($name);

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
        $lastIndex = array_key_last($this->columns);
        $this->columns[$lastIndex]['unique'] = $this->genericIdentifier($name);
        
        return $this;
    }
    
} 
