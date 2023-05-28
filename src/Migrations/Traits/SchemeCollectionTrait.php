<?php

declare(strict_types=1);

namespace builder\Database\Migrations\Traits;

trait SchemaCollectionTrait{

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
     * Creating Default value
     * @param string $value 
     * 
     * @return object\default
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
     * @return object\nullable
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
     * @return object\id
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
     * @return object\primary
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
     * @return object\index
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
     * @return object\unique
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
     * @return object\foreign
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
     * @return object\foreignId
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
     * @return object\constrained
     */
    public function constrained(?string $table = null, $column = 'id')
    {
        // we try to use defined tabled name, if no name is given to the method
        $tableName = explode('_', $this->tableName);
        
        return $this->references($column)->on($table ?? $tableName[0] ?? '');
    }

    /**
     * Creating Constraints Property
     * @param string $referencedColumn 
     * <code> - Parent Table References Column name </code>
     * 
     * @return object\references
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
     * @return object\on
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
     * @return object\onDelete
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
     * @return object\onUpdate
     */
    public function onUpdate($action)
    {
        $this->columns[count($this->columns) - 1]['onUpdate'] = $action;
        return $this;
    }
    
} 
