<?php

declare(strict_types=1);

namespace builder\Database\Migrations\Traits;

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
    public function primary(?string $name, $autoIncrement = true, $unsigned = true)
    {
        return $this->addColumn($name, 'bigInteger', [
            'primary'           => "PRIMARY", 
            'unsigned'          => $unsigned, 
            'auto_increment'    => $autoIncrement,
        ]);
    }

    /**
     * Creating Indexs
     * @param string $name 
     * 
     * @return $this
     */
    public function index(?string $name = null)
    {
        $this->columns[count($this->columns) - 1]['index'] = $this->generix_name($name);

        return $this;
    }

    /**
     * Creating Indexs
     * @param string $name 
     * 
     * @return $this
     */
    public function unique(?string $name = null)
    {
        $this->columns[count($this->columns) - 1]['unique'] = $this->generix_name($name);
        
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
     * @param string $table
     * @param string $column
     * - [optional] Default is `id`
     * 
     * @return $this
     */
    public function constrained(?string $table = null, $column = 'id')
    {
        // we try to use defined table name, if no name is given to the method
        $tableName = explode('_', $this->tableName);
        
        return $this->references($column)->on($table ?? $tableName[0] ?? '');
    }

    /**
     * Creating Constraints Property
     * @param string $referencedColumn 
     * <code> - Parent Table References Column name </code>
     * 
     * @return $this
     */
    public function references($referencedColumn)
    {
        $this->columns[count($this->columns) - 1]['references'] = $referencedColumn;
        $this->columns[count($this->columns) - 1]['generix'] = $this->generix_name($referencedColumn);
        return $this;
    }

    /**
     * Creating Constraints Property
     * @param string $referencedTable 
     * - Table name you're referencing to
     * 
     * @return $this
     */
    public function on($referencedTable)
    {
        $this->columns[count($this->columns) - 1]['on'] = $referencedTable;
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
