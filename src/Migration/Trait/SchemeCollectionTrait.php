<?php

declare(strict_types=1);

namespace UltimateOrmDatabase\Migration\Trait;

use Exception;


trait SchemeCollectionTrait{
    
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
     * @param bool $autoIncrement \Default is true
     * @param bool $unsigned \Default is false
     * 
     * @return object|string|null\id
     */
    public function id($name = 'id', $autoIncrement = true, $unsigned = false)
    {
        return $this->addColumn($name, 'integer', [
            'primary'           => "PRIMARY", 
            'unsigned'          => $unsigned, 
            'auto_increment'    => $autoIncrement,
        ]);
    }

    /**
     * Creating Indexs
     * @param string $name 
     * 
     * @return object\primary
     */
    public function primary(?string $name = null, $unsigned = false, $autoIncrement = true)
    {
        $this->columns[count($this->columns) - 1]['primary'] = "PRIMARY";
        $this->columns[count($this->columns) - 1]['unsigned'] = $unsigned;
        $this->columns[count($this->columns) - 1]['auto_increment'] = $autoIncrement;

        return $this;
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
     * 
     * @return object\foreign
     */
    public function foreign($column)
    {
        return $this->addColumn($column, 'foreign');
    }

    /**
     * Creating Constraints Property
     * @param string $referencedColumn 
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

    /**
     * Adding to columns
     * @param string $name 
     * @param string $type
     * @param int|null $length
     * 
     * @return object\addColumn
    */
    public function addColumn($name, $type, $length = null)
    {
        $column = [
            'name' => $name, 
            'type' => $type
        ];

        // add legnth
        if(is_int($length)){
            $column['length'] = $length;
        }elseif(is_array($length)){
            $column = array_merge($column, $length);
        }

        $this->columns[] = $column;

        return $this;
    }

    /**
     * Create a column definition with optional length and default value
     * @param array $options 
     * 
     * @return string\createColumnDefinition
     */
    public function createColumnDefinition(?array $options)
    {
        $default = [
            'name'      => $options['name']     ?? '',
            'type'      => $options['type']     ?? '',
            'length'    => $options['length']   ?? null,
            'default'   => $options['default']  ?? null,
            'nullable'  => $options['nullable'] ?? false,
        ];

        // create default string
        $getType    = $this->getColumnType($default['type']);
        $unassigned = $this->getUnAssigned($default['type']);
        $columnDef  = "`{$default['name']}` {$getType}";

        // for enum
        if(isset($options['values'])){
            array_walk($options['values'], function (&$value, $key){
                $value = "\'{$value}\'";
            });
            $values = implode(', ', $options['values']);
            $columnDef .= "({$values})";
        }
        // decimal|double
        elseif(isset($options['places'])){
            $columnDef .= "({$options['total']}, {$options['places']})";
        }
        // add for legnth
        else{
            $getLength = $this->getColumnLength($default['type'], $default['length']);
            if (!is_null($getLength)) {
                $columnDef .= "({$getLength})";
            }
        }

        // unassigned is set
        if(isset($options['unsigned'])){
            if($options['unsigned']){
                $columnDef .= " UNSIGNED";
            }
        }
        elseif(!is_null($unassigned)){
            $columnDef .= " {$unassigned}";
        }

        // add collate
        if(in_array($getType, ['varchar', 'enum', 'text', 'mediumText', 'longText'])){
            if(!empty($this->collation)){
                $columnDef .= " COLLATE {$this->collation}";
            }
        }

        // add for nullable
        if ($default['nullable']) {
            $columnDef .= ' NULL';
        } else {
            $columnDef .= ' NOT NULL';
        }

        // add for default values
        if (!is_null($default['default'])) {

            // for enum
            if(isset($options['values'])){
                $columnDef .= " DEFAULT '{$default['default']}'";
            }else{
                $columnDef .= " DEFAULT {$default['default']}";
            }
        }

        return $columnDef;
    }

    /**
     * Get the corresponding column type for a given type string
     * @param string $type 
     * 
     * @return string\getColumnType
     */
    protected function getColumnType(?string $type)
    {
        $typeMap = [
            'increments'            => 'int',
            'bigIncrements'         => 'bigint',
            'string'                => 'varchar',
            'text'                  => 'text',
            'boolean'               => 'boolean',
            'integer'               => 'int',
            'tinyInteger'           => 'tinyint',
            'bigInteger'            => 'bigint',
            'unsignedInteger'       => 'int',
            'unsignedBigInteger'    => 'bigint',
            'unsignedTinyInteger'   => 'tinyint',
            'unsignedSmallInteger'  => 'smallint',
            'unsignedMediumInteger' => 'mediumint',
            'unsignedDecimal'       => 'decimal',
            'float'                 => 'float',
            'double'                => 'double',
            'decimal'               => 'decimal',
            'char'                  => 'char',
            'binary'                => 'binary',
            'date'                  => 'date',
            'dateTime'              => 'datetime',
            'time'                   => 'time',
            'timestamp'             => 'timestamp',
            'timestamps'            => 'timestamp',
            'rememberToken'         => 'varchar',
            'json'                  => 'json',
            'jsonb'                 => 'json',
            'uuid'                  => 'char',
            'ipAddress'             => 'varchar',
            'macAddress'            => 'varchar',
            'year'                  => 'year',
            'enum'                  => 'enum',
            'set'                   => 'set',
        ];

        return $typeMap[$type] ?? $type;
    }

    /**
     * Get the corresponding column length for a given type string
     * @param string $type 
     * 
     * @return string\getColumnLength
     */
    protected function getColumnLength(string $type, ?int $length = null)
    {
        $defaultLengths = [
            'increments'            => 11,
            'bigIncrements'         => 20,
            'string'                => ($type === 'text') ? null : $length ?? 255,
            'text'                  => null,
            'boolean'               => null,
            'integer'               => 11,
            'tinyInteger'           => 6,
            'bigInteger'            => 20,
            'unsignedBigInteger'    => 20,
            'unsignedInteger'       => 10,
            'unsignedTinyInteger'   => 3,
            'unsignedSmallInteger'  => 5,
            'unsignedMediumInteger' => 9,
            'unsignedDecimal'       => null,
            'float'                 => null,
            'double'                => null,
            'decimal'               => '10, 0',
            'char'                  => 255,
            'binary'                => null,
            'date'                  => null,
            'dateTime'              => null,
            'time'                  => null,
            'timestamp'             => null,
            'timestamps'            => null,
            'rememberToken'         => null,
            'json'                  => null,
            'jsonb'                 => null,
            'uuid'                  => 36,
            'ipAddress'             => 45,
            'macAddress'            => 17,
            'year'                  => 4,
            'enum'                  => null,
            'set'                   => null,
        ];

        return $length ?? $defaultLengths[$type] ?? null;
    }

    /**
     * Get the corresponding Unassigned string
     * @param string $type 
     * 
     * @return string\getUnAssigned
     */
    protected function getUnAssigned(?string $type)
    {
        $typeUnassigned = [
            'increments'            => 'UNSIGNED',
            'bigIncrements'         => 'UNSIGNED',
            'unsignedInteger'       => 'UNSIGNED',
            'unsignedBigInteger'    => 'UNSIGNED',
            'unsignedTinyInteger'   => 'UNSIGNED',
            'unsignedSmallInteger'  => 'UNSIGNED',
            'unsignedMediumInteger' => 'UNSIGNED',
            'unsignedDecimal'       => 'UNSIGNED',
        ];

        return $typeUnassigned[$type] ?? null;
    }

    /**
     * Create generix identifier name
     * @param string $name
     * 
     * @return string\generix_name
     */
    protected function generix_name(?string $name = null)
    {
        $column = $this->columns[count($this->columns) - 1];
        $unique = (new Exception)->getTrace()[1]['function'] ?? '__';
        
        // for foreign keys
        if($column['type'] == 'foreign'){
            $name = "{$column['name']}_{$column['references']}_{$column['type']}";
        }else{
            // create unique name
            if(is_null($name)){
                $name = "{$this->tableName}_{$column['name']}_{$unique}";
            }else{
                $name = "{$this->tableName}_{$name}";
            }
        }

        return $name;
    }
    

}


