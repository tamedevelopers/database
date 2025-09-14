<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Migrations\Traits;

use Exception;

trait SchemaConfigurationTrait{

    /**
     * Collate Allowed Type
     * @var array
     */ 
    protected $collateTypes = [
        'varchar', 
        'enum', 
        'text', 
        'mediumText', 
        'longText',
    ];

    /**
     * Unsigned allowed type
     * @var array
     */ 
    protected $unsignedTypes = [
        'int', 
        'bigint', 
        'tinyint', 
        'smallint', 
        'mediumint',
        'decimal',
        'float',
        'double'
    ];

    /**
     * Length allowed type
     * @var array
     */ 
    protected $legnthTypes = [
        'string', 
        'char', 
        'binary',
    ];

    /**
     * Length allowed type
     * @var array
     */ 
    protected $legnth_255_Types = [
        'binary', 
        'char', 
    ];

    /**
     * Adding to columns
     * @param string $name 
     * @param string $type
     * @param int|null $length
     * 
     * @return $this
    */
    protected function addColumn($name, $type, $length = null)
    {
        $column = [
            'name' => trim((string) $name), 
            'type' => trim((string) $type)
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
     * @return string
     */
    protected function createColumnDefinition(?array $options)
    {
        // array merge
        $options = array_merge($options, [
            'name'      => '',
            'type'      => '',
            'length'    => null,
            'default'   => null,
            'nullable'  => false,
        ], $options);
        
        // create default string
        $type       = $this->getColumnType($options['type']);
        $unsigned   = $this->getUnsigned($options['type']);
        $columnDef  = "`{$options['name']}` {$type}";
        
        // Query for Type and Length 
        $columnDef .= $this->queryForType_and_Length($options);

        // add unsigned
        $columnDef .= $this->queryForUnsigned($options, $type, $unsigned);

        // add collate
        $columnDef .= $this->queryForCollate($type);

        // add for nullable
        $columnDef .= $this->queryForNullable($options);
        
        // add for default values
        $columnDef .= $this->queryForDefault($options);

        return $columnDef;
    }

    /**
     * Creating Query String for Data type and Length
     * @param array $options
     * 
     * @return string
     */
    protected function queryForType_and_Length(?array $options = [])
    {
        $columnDef = "";
        
        // for enum|set
        if(isset($options['values'])){
            array_walk($options['values'], function (&$value, $key){
                $value = "\'{$value}\'";
            });
            $values = implode(', ', $options['values']);
            $columnDef .= "({$values})";
        }

        // decimal|double|float
        elseif(isset($options['places'])){
            $columnDef .= "({$options['total']},{$options['places']})";
        }

        // add for legnth
        else{
            $getLength = $this->getColumnLength($options['type'], $options['length']);
            if (!is_null($getLength)) {
                $columnDef .= "({$getLength})";
            }
        }

        return $columnDef;
    }

    /**
     * Creating Query String for Collate
     * @param string|null $getType
     * 
     * @return string
     */
    protected function queryForCollate($getType = null)
    {
        $columnDef = "";
        if(in_array($getType, $this->collateTypes)){
            if(!empty($this->collation)){
                $columnDef .= " COLLATE {$this->collation}";
            }
        }

        return $columnDef;
    }

    /**
     * Creating Query String for Unsigned
     * @param array $options
     * @param string|null $getType
     * @param string|null $unsigned
     * 
     * @return string
     */
    protected function queryForUnsigned(?array $options = [], $getType = null, $unsigned = null)
    {
        $columnDef = "";
        if(isset($options['unsigned'])){
            if($options['unsigned'] && in_array($getType, $this->unsignedTypes)){
                $columnDef .= " UNSIGNED";
            }
        }
        elseif(!is_null($unsigned)){
            $columnDef .= " {$unsigned}";
        }

        return $columnDef;
    }

    /**
     * Creating Query String for Nullable
     * @param array $options
     * 
     * @return string
     */
    protected function queryForNullable(?array $options = [])
    {
        $columnDef = "";
        if (isset($options['nullable']) && $options['nullable']) {
            $columnDef .= ' NULL';
        } else {
            $columnDef .= ' NOT NULL';
        }

        return $columnDef;
    }

    /**
     * Creating Query String for Default
     * @param array $options
     * 
     * @return string
     */
    protected function queryForDefault(?array $options = [])
    {
        $columnDef = "";
        if (!is_null($options['default'])) {
            // for enum|set
            if(isset($options['values'])){
                $columnDef .= " DEFAULT \'{$options['default']}\'";
            }else{
                if(is_string($options['default'])){
                    $columnDef .= " DEFAULT \'{$options['default']}\'";
                } else{
                    $columnDef .= " DEFAULT {$options['default']}";
                }
            }
        }

        return $columnDef;
    }

    /**
     * Get the corresponding column length for a given type string
     * @param string $type 
     * @param int|null $length 
     * 
     * @return string
     */
    protected function getColumnLength($type, ?int $length = null)
    {
        // if global length is defined
        if( defined('ORM_MAX_STRING_LENGTH') ){
            // check to change length
            if(in_array($type, $this->legnthTypes)){
                // for columns types with max legnth of 255 
                if(in_array($type, $this->legnth_255_Types) && $length > 255){
                    $length = 255;
                } else{
                    if(is_int($length) && $length > ORM_MAX_STRING_LENGTH){
                        $length = ORM_MAX_STRING_LENGTH;
                    }
                }
            }
        } 

        $defaultLengths = [
            'string'                => $this->lengthDefault($length),
            'char'                  => $this->lengthDefault($length),
            'binary'                => $this->lengthDefault($length),
            'text'                  => null,
            'text'                  => null,
            'boolean'               => null,
            'integer'               => 11,
            'tinyInteger'           => 6,
            'bigInteger'            => 20,
            'bigIncrements'         => 20,
            'increments'            => 11,
            'unsignedInteger'       => 10,
            'unsignedBigInteger'    => 20,
            'unsignedTinyInteger'   => 3,
            'unsignedSmallInteger'  => 5,
            'unsignedMediumInteger' => 9,
            'unsignedDecimal'       => null,
            'float'                 => null,
            'double'                => null,
            'decimal'               => '10, 0',
            'date'                  => null,
            'dateTime'              => null,
            'time'                  => null,
            'timestamp'             => null,
            'timestamps'            => null,
            'json'                  => null,
            'uuid'                  => 16,
            'ipAddress'             => 45,
            'macAddress'            => 17,
            'year'                  => 4,
            'enum'                  => null,
            'set'                   => null,
        ];

        return $length ?? $defaultLengths[$type] ?? null;
    }

    /**
     * Get the corresponding column type for a given type string
     * @param string $type 
     * 
     * @return string
     */
    protected function getColumnType($type)
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
            'time'                  => 'time',
            'timestamp'             => 'timestamp',
            'timestamps'            => 'timestamp',
            'softDeletes'           => 'timestamp',
            'json'                  => 'json',
            'uuid'                  => 'binary',
            'ipAddress'             => 'varchar',
            'macAddress'            => 'varchar',
            'year'                  => 'year',
            'enum'                  => 'enum',
            'set'                   => 'set',
        ];

        return $typeMap[$type] ?? $type;
    }

    /**
     * Get the corresponding Unassigned string
     * @param string $type 
     * 
     * @return string
     */
    protected function getUnsigned($type)
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
     * 
     * @param string|null $name
     * @return string
     */
    protected function genericIdentifier($name = null)
    {
        // Always reference the last added column in the collection
        $lastIndex = array_key_last($this->columns);
        $current = $this->columns[$lastIndex] ?? [];
        $unique = (new Exception)->getTrace()[1]['function'] ?? '__';
        
        // for foreign keys
        if(($current['type'] ?? null) === 'foreign'){
            $name = "{$this->tableName}_" . ($current['name'] ?? 'column') . "_" . ($current['type'] ?? 'foreign');
        } else{
            // create unique name
            if(empty($name)){
                $name = "{$this->tableName}_" . ($current['name'] ?? 'column') . "_{$unique}";
            } else{
                $name = "{$this->tableName}_{$name}";
            }
        }

        return $name;
    }

    /**
     * Get Default Length for Allowed \legnthTypes
     * @param int $length 
     * 
     * @return int
     */
    protected function lengthDefault(?int $length = 255)
    {
       return $length ?? 255;
    }

}