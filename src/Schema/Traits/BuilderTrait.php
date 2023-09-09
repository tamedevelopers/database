<?php

declare(strict_types=1);

namespace builder\Database\Schema\Traits;

use PDO;
use Closure;
use DateTime;
use Exception;
use HTMLPurifier;
use DateTimeInterface;
use builder\Database\Capsule\Forge;
use builder\Database\Schema\Builder;
use builder\Database\Schema\Expression;
use builder\Database\Schema\JoinClause;
use builder\Database\Schema\Pagination\Paginator;
use builder\Database\Collections\Collection;
use builder\Database\Schema\BuilderCompiler;


/**
 * @property mixed $connection
 * 
 * @see \builder\Database\Schema\Builder
 * @see \builder\Database\Schema\BuilderCompiler
 */
trait BuilderTrait{

    /**
     * Builder Compiler Instance
     * 
     * @return string
     */
    public function tableName()
    {
        return "{$this->connection->tablePrefix}{$this->from}";
    }

    /**
     * Set callback method
     * @return void
     */
    protected function setMethod($method = null)
    {
        $this->method = $method;
    }

    /**
     * Builder Compiler Instance
     * 
     * @return \builder\Database\Schema\BuilderCompiler
     */
    protected function compile()
    {
        $builder = new BuilderCompiler;
        $builder->tablePrefix = $this->connection->tablePrefix;

        return $builder;
    }

    /**
     * Get the singleton instance of new query builder.
     *
     * @return \builder\Database\Schema\Builder
     */
    protected function newQuery()
    {
        $instance = new static();
        $instance->connection = $this->connection;
        $instance->dbManager = $this->dbManager;
        $instance->manager = $this->manager;

        return $instance;
    }

    /**
     * Get a new join clause.
     *
     * @param  \builder\Database\Schema\Builder  $parentQuery
     * @param  string  $type
     * @param  string  $table
     * @return \builder\Database\Schema\Query\JoinClause
     */
    protected function newJoinClause(self $parentQuery, $type, $table)
    {
        return new JoinClause($parentQuery, $type, $table);
    }

    /**
     * Get the current query value bindings in a flattened array.
     *
     * @return array
     */
    protected function getBindings()
    {
        return Forge::flattenValue($this->bindings);
    }

    /**
     * Get the raw array of bindings.
     *
     * @return array
     */
    protected function getRawBindings()
    {
        return $this->bindings;
    }
    
    /**
     * Check if instance of Raw Expression Class
     *
     * @param mixed $value
     * 
     * @return bool
     */
    protected function isExpression(mixed $value = null)
    {
        return $value instanceof Expression;
    }

    /**
     * Check if instance of Raw Expression Class
     *
     * @param mixed $value
     * 
     * @return bool
     */
    protected function isExpressionContract(mixed $value = null)
    {
        return ($value instanceof Expression || is_array($value));
    }
    
    /**
     * Check if instance of Collection class
     *
     * @param mixed $value
     * 
     * @return bool
     */
    protected function isCollection(mixed $value = null)
    {
        return $value instanceof Collection;
    }
    
    /**
     * Check if instance of Closure class
     *
     * @param mixed $value
     * 
     * @return bool
     */
    protected function isClosure(mixed $value = null)
    {
        return $value instanceof Closure;
    }

    /**
     * Check if instance of PDO class
     *
     * @param mixed $value
     * 
     * @return bool
     */
    protected function isPDO(mixed $value = null)
    {
        return $value instanceof PDO;
    }

    /**
     * Check if a value has a format similar to CarbonPeriod.
     *
     * @param  mixed  $value
     * @return bool
     */
    protected function isCarbonPeriod($value)
    {
        // Check if the value is an array
        // the  automatically value is not a Carbon Period Data
        if (is_array($value) || !is_object($value)) {
            return false;
        }

        // If value is an object and CarbonPeriod class exists
        if(is_object($value) && (class_exists('\Carbon\CarbonPeriod'))){
            return true;
        }

        // Check if both start and end values are instances of DateTimeInterface
        // Extract the start and end values from the array
        if (is_array($value) || count($value) === 2) {
            [$start, $end] = $value;

            if (!($start instanceof DateTimeInterface) || !($end instanceof DateTimeInterface)) {
                return true;
            }
        }

        // If all checks pass, consider the not to be similar to CarbonPeriod
        return false;
    }

    /**
     * Add a new select column to the query.
     *
     * @param  array|mixed  $columns
     * 
     * @param array $bindings
     * [optional] data to bind in the expression if found
     * 
     * @return $this
     */
    protected function addSelect($columns, $bindings = [])
    {
        if($this->isExpression($columns)){
            $this->columns[] = $columns;
        } else{
            if(is_array($columns)){
                foreach ($columns as $as => $column) {
                    if($this->isExpression($column)){
                        $this->addSelect($column);
                    } else{
                        $this->columns[] = $column;
                    }
                }
            }
        }

        if($bindings){
            $this->addBinding($bindings, 'select');
        }

        return $this;
    }

    /**
     * Add a binding to the query.
     *
     * @param  mixed  $value
     * @param  string  $type
     * @return $this
     *
     * @throws \Exception
     */
    protected function addBinding($value, $type = 'where')
    {
        if (! array_key_exists($type, $this->bindings)) {
            throw new Exception("Invalid binding type: {$type}.");
        }

        if (is_array($value)) {
            $this->bindings[$type] = array_merge($this->bindings[$type], $value);
        } else {
            $this->bindings[$type][] = $value;
        }

        return $this;
    }
    
    /**
     * Add a select expression to the query.
     *
     * @param mixed $columns
     * 
     * @return $this
     */
    protected function buildSelect(mixed $columns = [])
    {
        $this->addSelect($columns);

        return $this;
    }

    /**
     * Add a select expression to the query.
     *
     * @param mixed $expression
     * 
     * @param array $bindings
     * [optional] data to bind in the expression if found
     * 
     * @return $this
     */
    protected function buildSelectRaw(mixed $expression, $bindings = [])
    {
        $expression = $this->isExpression($expression) 
                        ? $expression 
                        : self::raw($expression);
        
        $this->addSelect($expression, $bindings);

        return $this;
    }

    /**
     * Process the expression by binding the data to the placeholders.
     *
     * @param string $expression
     * @param array $bindings
     * @return string
     */
    protected function processExpression($expression, $bindings = [])
    {
        // Replace each placeholder '?' in the expression with '%s'
        $formattedExpression = $expression;
        $expression = str_replace('?', '%s', $expression);

        // Format the expression by passing the bindings array using the spread operator
        if($bindings){
            $formattedExpression = sprintf($expression, ...$bindings);
        }
        
        return $formattedExpression;
    }

    /**
     * Prepare the value and operator for a where clause.
     *
     * @param  string  $value
     * @param  string  $operator
     * @param  bool  $useDefault
     * @return array
     *
     * @throws \Exception
     */
    protected function prepareValueAndOperator($value, $operator, $useDefault = false)
    {
        if ($useDefault) {
            return [$operator, '='];
        } elseif ($this->invalidOperatorAndValue($operator, $value)) {
            throw new Exception("Illegal operator and value combination. `{$operator}`");
        }

        return [$value, $operator];
    }

    /**
     * Prepare the value and operator for a where clause.
     *
     * @param  string  $column
     * @param  int|float|array $count
     * @param  array  $extra
     * @param  bool  $useDefault
     * @return array
     *
     * @throws \Exception
     */
    protected function prepareIncrementParams($column, $count = 1, array $extra = [], $useDefault = false)
    {
        // if arguments is 1|2 and $count is not an array
        // then we assume no extra param is given, and also we want to perform a direct increment
        // [column, count]. if no count given then count has already been set to `1` by default
        if($useDefault){
            [$column => $count];
        } 
        
        // if count is an array, then automatically say the developer wants to use 1 as default value
        // and then we will set the [extra] data as the value for count.
        // now count will be seen as data we will use as update
        elseif(is_array($count)){
            $extra = $count;
            [$column => $count] = [$column => 1];
        }

        if (! is_numeric($count)) {
            throw new Exception('Non-numeric value passed to increment method.');
        }

        return [$column, $count, $extra];
    }

    /**
     * Determine if the given operator and value combination is legal.
     *
     * Prevents using Null values with invalid operators.
     *
     * @param  string  $operator
     * @param  mixed  $value
     * @return bool
     */
    protected function invalidOperatorAndValue($operator, $value)
    {
        return is_null($value) && in_array($operator, $this->operators) &&
             ! in_array($operator, ['=', '<>', '!=']);
    }

    /**
     * Determine if the given operator is supported.
     *
     * @param  string  $operator
     * @return bool
     */
    protected function invalidOperator($operator)
    {
        return ! is_string($operator) || (! in_array(strtolower($operator), $this->operators, true) &&
               ! in_array(strtolower($operator), $this->operators, true));
    }

    /**
     * Add an array of where clauses to the query.
     *
     * @param array $column An array of where clauses.
     * @param string $boolean The boolean operator to use for combining the where clauses. Default is 'and'.
     * @param  string  $method
     * @return $this
     */
    protected function addArrayOfWheres(array $column, $boolean = 'and', $method = 'where')
    {
        return $this->whereNested(function ($query) use ($column, $boolean, $method) {
            foreach ($column as $key => $value) {
                if (is_numeric($key) && is_array($value)) {
                    // Handle array values as [column, operator, value]
                    $query->{$method}(...array_values($value));
                } else {
                    // Default operator is '='
                    $query->{$method}($key, '=', $value, $boolean);
                }
            }
        }, $boolean);
    }

    /**
     * Add a nested where statement to the query.
     *
     * @param  \Closure  $callback
     * @param  string  $boolean
     * @return $this
     */
    protected function whereNested(Closure $callback, $boolean = 'and')
    {
        $callback($query = $this->newQuery());

        return $this->addNestedWhereQuery($query, $boolean);
    }

    /**
     * Add another query builder as a nested where to the query builder.
     *
     * @param  \builder\Database\Schema\Builder  $query
     * @param  string  $boolean
     * @return $this
     */
    protected function addNestedWhereQuery($query, $boolean = 'and')
    {
        if (count($query->wheres)) {
            $type = 'Nested';

            $this->wheres[] = compact('type', 'query', 'boolean');

            $this->addBinding($query->bindings['where'], 'where');
        }

        return $this;
    }

    /**
     * Add a date based (year, month, day, time) statement to the query.
     *
     * @param  string  $type
     * @param  \builder\Database\Schema\Expression|string  $column
     * @param  string  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * @return $this
     */
    protected function addDateBasedWhere($type, $column, $operator, $value, $boolean = 'and')
    {
        $this->wheres[] = compact('column', 'type', 'boolean', 'operator', 'value');

        if (! $this->isExpressionContract($value)) {
            $this->addBinding($value, 'where');
        }

        return $this;
    }

    /**
     * Compile the "select *" portion of the query.
     *
     * @param  \builder\Database\Schema\Builder  $query
     * @param  array  $columns
     * @return string|null
     */
    protected function compileColumns(Builder $query, $columns)
    {
        // If the query is actually performing an aggregating select, we will let that
        // compiler handle the building of the select clauses, as it will need some
        // more syntax that is best handled by that function to keep things neat.
        if (! is_null($query->aggregate)) {
            return;
        }

        if ($query->distinct) {
            $select = 'select distinct ';
        } else {
            $select = 'select ';
        }

        return $select.$this->columnize($columns);
    }

    /**
     * Create a new query instance for nested where condition.
     *
     * @return \builder\Database\Schema\Builder
     */
    protected function forNestedWhere()
    {
        return $this->newQuery()->from($this->from);
    }

    /**
     * Get a scalar type value from an unknown type of input.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function flattenValue($value)
    {
        return is_array($value) ? Forge::head( Forge::flattenValue($value) ) : $value;
    }

    /**
     * Determine if the value is a query builder instance or a Closure.
     *
     * @param  mixed  $value
     * @return bool
     */
    protected function isQueryable($value)
    {
        return $value instanceof self || $value instanceof Closure;
    }

    /**
     * Clone the query.
     *
     * @return static
     */
    protected function clone()
    {
        return clone $this;
    }

    /**
     * Trim empty strings from an array value
     * 
     * @param array $param
     * @param bool $indent
     * 
     * @return array
     */ 
    protected function arrayWalkerTrim(?array $param = [], ?bool $indent = false)
    {
        array_walk($param, function(&$value, $index) use($indent){
            if(!empty($value) && is_string($value)){
                $value = trim($value);
                // if indentation of value is allowed
                if($indent){
                    $value = "`$value`";
                }
            }
        });

        return $param;
    }

    /**
     * Wrap a table in keyword identifiers.
     *
     * @param  \builder\Database\Schema\Expression|string  $table
     * @return string
     */
    protected function wrapTable($table)
    {
        if (! $this->isExpression($table)) {
            return $this->wrap($this->tablePrefix.$table, true);
        }

        return $this->getValue($table);
    }

    /**
     * Wrap the given value as a column value.
     *
     * @param  string  $value
     * @return string
     */
    protected function wrapValue(string $value): string
    {
        // Add the necessary logic to wrap the value as a column value
        // For example, you can use single quotes (') for string values
        if ($value !== '*') {
            return "`{$value}`";
        }

        return $value;
    }

    /**
     * Wrap a value in keyword identifiers.
     *
     * @param  \builder\Database\Schema\Expression|string  $value
     * @param  bool  $prefixAlias
     * @return string
     */
    protected function wrap($value, $prefixAlias = false)
    {
        if ($this->isExpression($value)) {
            return $this->getValue($value);
        }

        // If the value being wrapped has a column alias we will need to separate out
        // the pieces so we can wrap each of the segments of the expression on its
        // own, and then join these both back together using the "as" connector.
        if (stripos($value, ' as ') !== false) {
            return $this->wrapAliasedValue($value, $prefixAlias);
        }
        
        return $this->wrapSegments(explode('.', $value));
    }

    /**
     * Wrap an array of values.
     *
     * @param  array  $values
     * @return array
     */
    protected function wrapArray(array $values)
    {
        return array_map([$this, 'wrap'], $values);
    }

    /**
     * Wrap a value that has an alias.
     *
     * @param  string  $value
     * @param  bool  $prefixAlias
     * @return string
     */
    protected function wrapAliasedValue($value, $prefixAlias = false)
    {
        $segments = preg_split('/\s+as\s+/i', $value);

        // If we are wrapping a table we need to prefix the alias with the table prefix
        // as well in order to generate proper syntax. If this is a column of course
        // no prefix is necessary. The condition will be true when from wrapTable.
        if ($prefixAlias) {
            $segments[1] = $this->tablePrefix.$segments[1];
        }

        return $this->wrap($segments[0]).' as '.$this->wrapValue($segments[1]);
    }

    /**
     * Wrap the given value segments using a custom approach.
     *
     * @param  array  $segments
     * @param  bool  $prefixAlias
     * @return string
     */
    protected function wrapSegments(array $segments, $prefixAlias = false): string
    {
        $count = count($segments);
        $wrappedSegments = [];

        foreach ($segments as $key => $segment) {
            if ($key === 0 && $count > 1) {
                $wrappedSegments[] = $this->wrapTable($segment);
            } else {
                $wrappedSegments[] = $this->wrapValue($segment);
            }
        }

        // This will trigger for the join queries only
        // any future possible code will go in here
        if(count($wrappedSegments) === 2){
            // 
        }

        return implode('.', $wrappedSegments);
    }

    /**
     * Remove all of the expressions from a list of bindings.
     *
     * @param array $bindings
     * @return array
     */
    public function cleanBindings(array $bindings)
    {
        $cleanedBindings = [];

        foreach ($bindings as $binding) {
            if (!$this->isExpressionContract($binding)) {
                $cleanedBindings[] = $this->castBinding($binding);
            }
        }

        return $cleanedBindings;
    }

    /**
     * Cast the given binding value.
     *
     * @param mixed $value
     * @return mixed
     */
    public function castBinding($value)
    {
        if ($this->isExpression($value)) {
            return $value->getValue();
        }

        return $value;
    }

    /**
     * Transforms expressions to their scalar types.
     *
     * @param  \builder\Database\Schema\Expression|string|int|float  $value
     * @return string|int|float
     */
    protected function getValue($value)
    {
        if ($this->isExpression($value)) {
            return $this->getValue($value->getValue($this));
        }

        return $value;
    }

    /**
     * Get all of the query builder's columns in a text-only array with all expressions evaluated.
     *
     * @return array
     */
    protected function getColumns()
    {
        if (is_null($this->columns)) {
            return [];
        }
        
        return array_map(function ($column) {
            return $this->getValue($column);
        }, $this->columns);
    }

    /**
     * Build select columns to the query.
     *
     * @param mixed $columns
     * 
     * @return string
     */
    protected function buildSelectColumns(mixed $columns = [])
    {
        array_walk($columns, function(&$value, $index){
            if($this->isExpression()){
                $value = trim($value->getValue());
            }
        });

        return implode(', ', $this->arrayWalkerTrim($columns));
    }

    /**
     * Get the format for database stored dates.
     *
     * @return string
     */
    protected function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * Concatenate an array of segments, removing empties.
     *
     * @param  array  $segments
     * @return string
     */
    protected function concatenate($segments)
    {
        return implode(' ', array_filter($segments, function ($value) {
            return (string) $value !== '';
        }));
    }

    /**
     * Remove the leading boolean from a statement.
     *
     * @param  string  $value
     * @return string
     */
    protected function removeLeadingBoolean($value)
    {
        return preg_replace('/and |or /i', '', $value, 1);
    }

    /**
     * Register a closure to be invoked before the query is executed.
     *
     * @param  callable  $callback
     * @return $this
     */
    protected function beforeQuery(callable $callback)
    {
        $this->beforeQueryCallbacks[] = $callback;

        return $this;
    }

    /**
     * Invoke the "before query" modification callbacks.
     *
     * @return void
     */
    protected function applyBeforeQueryCallbacks()
    {
        foreach ($this->beforeQueryCallbacks as $callback) {
            $callback($this);
        }

        $this->beforeQueryCallbacks = [];
    }

    /**
     * Convert an array of column names into a delimited string.
     *
     * @param  array  $columns
     * @return string
     */
    protected function columnize(array $columns)
    {
        return implode(', ', array_map([$this, 'wrap'], $columns));
    }

    /**
     * Create query parameter place-holders for an array.
     *
     * @param  array  $values
     * @return string
     */
    protected function parameterize(array $values)
    {
        return implode(', ', array_map([$this, 'parameter'], $values));
    }

    /**
     * Get the appropriate query parameter place-holder for a value.
     *
     * @param  mixed  $value
     * @return string
     */
    protected function parameter($value)
    {
        return $this->isExpression($value) ? $this->getValue($value) : '?';
    }

    /**
     * Quote the given string literal.
     *
     * @param  string|array  $value
     * @return string
     */
    protected function quoteString($value)
    {
        if (is_array($value)) {
            return implode(', ', array_map([$this, __FUNCTION__], $value));
        }

        return "'$value'";
    }

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param  string  $query
     * @param  array  $bindings
     * @param  bool  $ignore
     * @return $this
     */
    protected function statement($query, $bindings = [], $ignore = false)
    {
        $pdo = $this->connection->pdo;

        try {
            // checking if global query is used
            if(!empty($this->query)){
                $query = $this->query;
            }

            $statement = $pdo->prepare($query);
            $this->bindValues(
                $statement, 
                Forge::flattenValue($bindings)
            );
            $statement->execute();
        } catch (\PDOException $e) {
            if(!$ignore){
                return $this->errorException($e);
            }
        }

        $this->connection->statement = $statement ?? null;
        $this->query = null;
        
        return $this;
    }

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param  \builder\Database\Schema\Builder  $query
     * @param  \builder\Database\Schema\Pagination\Paginator  $paginator
     * @return $this
     */
    protected function paginateStatement(Builder $query, Paginator $paginator)
    {
        $pdo = $query->connection->pdo;
        $sql = $query->limit($paginator->pagination->limit)
                        ->offset($paginator->pagination->offset)
                        ->compile()
                        ->compileSelect($query);

        try {
            $statement = $pdo->prepare($sql);
            $this->bindValues(
                $statement, 
                Forge::flattenValue($query->getBindings())
            );
            $statement->execute();

            return $statement->fetchAll();
        } catch (\PDOException $e) {
            return $this->errorException($e);
        }
    }

    /**
     * Get last insert ID
     *
     * @return int
     */
    protected function rowCount()
    {
        return  $this->connection->statement 
                ? $this->connection->statement->rowCount()
                : 0;
    }

    /**
     * Get last insert ID
     *
     * @return int
     */
    protected function lastInsertId()
    {
        return  $this->connection->pdo
                ? $this->connection->pdo->lastInsertId()
                : 0;
    }

    /**
     * Bind values to their parameters in the given statement.
     *
     * @param  \PDOStatement  $statement
     * @param  array  $bindings
     * @return void
     */
    protected function bindValues($statement, $bindings)
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1,
                $value,
                match (true) {
                    is_int($value) => PDO::PARAM_INT,
                    is_resource($value) => PDO::PARAM_LOB,
                    default => PDO::PARAM_STR
                },
            );
        }
    }

    /**
     * Increment the given column's values by the given amounts.
     *
     * @param  array<string, float|int|numeric-string>  $columns
     * @param  array<string, mixed>  $extra
     * @param  string $sign\Default is `+`
     * @return int
     *
     * @throws \Exception
     */
    protected function incrementEach(array $columns, array $extra = [], $sign = '+')
    {
        foreach ($columns as $column => $amount) {
            if (! is_numeric($amount)) {
                throw new Exception('Non-assohciative array passed to incrementEach method.');
            } elseif (! is_string($column)) {
                throw new Exception('Non-associative array passed to incrementEach method.');
            }

            $columns[$column] = $this->raw("{$this->wrap($column)} {$sign} {$amount}");
        }

        return $this->update(array_merge($columns, $extra));
    }

    /**
     * Decrement the given column's values by the given amounts.
     *
     * @param  array<string, float|int|numeric-string>  $columns
     * @param  array<string, mixed>  $extra
     * @return int
     *
     * @throws \Exception
     */
    protected function decrementEach(array $columns, array $extra = [])
    {
        return $this->incrementEach($columns, $extra, '-');
    }

    /**
     * Update records in the database.
     *
     * @param  array  $values
     * @param  bool  $ignore
     * 
     * @return int
     */
    protected function insertBuilder(array $values, $ignore = false)
    {
        // Since every insert gets treated like a batch insert, we will make sure the
        // bindings are structured in a way that is convenient when building these
        // inserts statements by verifying these elements are actually an array.
        if (empty($values)) {
            return true;
        }

        // check if timestamps is available in table
        // if yes then add time stamdps to columns
        // with this helper, developers dont need to worry adding them
        $stampData = $this->timeStampData();

        if (! is_array(reset($values))) {
            $values = [array_merge($values, $stampData)];
        }

        // Here, we will sort the insert keys for every record so that each insert is
        // in the same order for the record. We need to make sure this is the case
        // so there are not any errors or problems when inserting these records.
        else {
            foreach ($values as $key => $value) {
                ksort($value);

                $values[$key] = array_merge($values, $stampData);
            }
        }

        $this->applyBeforeQueryCallbacks();

        $sql = $this->compile()->compileInsert($this, $values);

        // insert ingore method checker
        if($ignore){
            $sql = $this->connection->driver->compileInsertOrIgnore($sql);
        }

        // insert query
        $this->statement($sql, $this->cleanBindings(
            $this->cleanBindings(Forge::flattenValue($values, 1))
        ));

        // save data before closing queries
        $this->close(false);

        // get primary key and lastinsertID
        [$column, $col_value] = $this->connection->driver->describeColumn($this);

        // get data using the first or ignore method
        // with this no error is expected to be returned
        // by default its impossible for an error to occur
        // as the driver `describeColumn` method ensure to return the
        // PRIMARY KEY column name and lastinsertID
        return $this->select(['*'])->where($column, $col_value)->firstOrIgnore();
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  string  $method
     * @param  bool  $close \By default we close queries 
     * @param  bool  $ignore
     * 
     * @return $this
     */
    protected function firstOrIgnoreBuilder($method = null, $close = true, $ignore = false)
    {
        $results = $this->limit(1)->getBuilder($close, $ignore, $method, false);

        return  count($results) > 0
                ? new Collection($results[0], $this)
                : null;
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  bool  $close \By default we close queries 
     * [optional] Either to close query after execution
     * 
     * @param  bool  $ignore
     * [optional] Either to ignore Exception error or not
     * 
     * @param  string $method
     * [optional] Method it's being called from
     * 
     * @param  bool $collection
     * [optional] By default method returns a collection.
     * So we can ignore and return direct data
     * 
     * @return $this
     */
    protected function getBuilder($close = true, $ignore = false, $method = null, $collection = true)
    {
        $this->applyBeforeQueryCallbacks();

        $results = $this->statement(
            $this->compile()->compileSelect($this),
            $this->getBindings(),
            $ignore
        );

        // save data before closing queries
        $data = $results->fetchAll();

        // only close when allowed
        // this will help us in Pagination, so we can count the data and returned total count
        // before starting the [paginaion] request
        if($close){
            $this->close();
        }

        $this->setMethod($method);

        return $collection ? new Collection($data, $this) : $data;
    }

    /**
     * Update records in the database.
     *
     * @param  array  $values
     * @param  bool  $ignore
     * @return int
     */
    protected function updateBuilder(array $values, $ignore = false)
    {
        $this->applyBeforeQueryCallbacks();

        $sql = $this->compile()->compileUpdate($this, $values);

        // update ingore method checker
        if($ignore){
            $sql = $this->connection->driver->compileUpdateOrIgnore($sql);
        }

        // update query
        $update = $this->statement($sql, $this->cleanBindings(
            $this->compile()->prepareBindingsForUpdate($this->bindings, $values)
        ), $ignore);

        // save data before closing queries
        $this->close();

        return $update->rowCount();
    }

    /**
     * Retrieve the "count" result of the query.
     *
     * @param  array|string  $columns
     * @param  bool  $close \By default we close queries 
     * 
     * @return int
     */
    protected function countBuilder($columns = ['*'], $close = true)
    {
        $this->applyBeforeQueryCallbacks();
        $this->selectRaw('count' . '(' . $this->columnize(Forge::wrap($columns)) . ') as aggregate');
        
        $results = $this->statement(
            $this->compile()->compileSelect($this),
            $this->getBindings(),
            false
        );

        // save data before closing queries
        $data = $results->fetchAll();

        // only close when allowed
        // this will help us in Pagination, so we can count the data and returned total count
        // before starting the [paginaion] request
        if($close){
            $this->close();
        }

        return (int) $data[0]['aggregate'] ?? 0;
    }

    /**
     * Execute an aggregate function on the database.
     *
     * @param  string  $function
     * @param  array|string  $columns
     * For any method that extends the `aggregate`
     * 
     * @return mixed
     */
    protected function aggregate($function, $columns = ['*'], $close = true)
    {
        $this->selectRaw($function . '(' . $this->columnize($columns) . ') as aggregate');
        $results = $this->getBuilder($close, false, $function);

        if (! $results->isEmpty()) {
            $result = (array) $results[0];
            return array_change_key_case($result)['aggregate'];
        }
    }

    /**
     * Return timestamp data
     * @param bool $mode \Default is true
     * - Meaning both columns will be returned
     * 
     * @return array
     */ 
    protected function timeStampData($mode = true)
    {
        // check if timestamps is available in table
        // if yes then add time stamdps to columns
        // with this helper, developers dont need to worry adding them
        if($this->isTimeStampsReady()){
            $timeStamp = date($this->getDateFormat());
            // for insert query
            if($mode){
                return ['created_at' => $timeStamp, 'updated_at' => $timeStamp];
            } 

            // only for update query
            else{
                return ['updated_at' => $timeStamp];
            }
        }

        return [];
    }

    /**
     * Perform time stamp query to determine if 
     * `created_at` and `updated_at` columns exists in a given table
     * 
     * @return bool
     */ 
    protected function isTimeStampsReady()
    {
        try {
            $pdo    = $this->connection->pdo;
            $stmt   = $pdo->query("
                show columns from `{$this->tableName()}` where field in ('created_at', 'updated_at')
            ");

            // execute query
            $stmt->execute();

            // get results data
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if(is_array($result) && count($result) === 2){
                return true;
            }
            return false;
        } catch (\PDOException $th) {
            return false;
        }
    }

    /**
     * Fetch Request 
     * @param int $mode\Default is PDO::FETCH_ASSOC
     * - [optional] PDO MySQL CONSTANTs
     * 
     * @return mixed
     */
    protected function fetch(int $mode = PDO::FETCH_ASSOC)
    {
        try {
            $statement = $this->connection->statement;
            return  $statement
                    ? $statement->fetch($mode)
                    : [];
        } catch (\PDOException $e) {
            return $this->errorException($e);
        }
    }

    /**
     * Fetch All Request
     * @param int $mode\Default is PDO::FETCH_ASSOC
     * - [optional] PDO MySQL CONSTANTs
     *
     * @return mixed
     */
    protected function fetchAll(int $mode = PDO::FETCH_ASSOC)
    {
        try {
            $statement = $this->connection->statement;
            return  $statement
                    ? $statement->fetchAll($mode)
                    : [];
        } catch (\PDOException $e) {
            return $this->errorException($e);
        }
    }

    /**
     * Close all queries and restore back to default
     * @param bool $table \Default is set to true
     * - Since sometimes we want to reset all connection 
     * leaving the table name alone
     *
     * @return void
     */
    protected function close($table = true)
    {
        $this->bindings = [
            'select'    => [],
            'from'      => [],
            'join'      => [],
            'where'     => [],
            'groupBy'   => [],
            'having'    => [],
            'order'     => [],
        ];
        $this->wheres   = [];
        $this->columns  = null;
        $this->method   = null;
        $this->query    = null;
        $this->joins    = null;
        $this->orders   = null;
        $this->havings  = null;
        $this->groups   = null;
        $this->limit    = null;
        $this->offset   = null;
        $this->runtime  = 0.00;
        if($table){
            $this->from = null;
        }
    }

    /**
     * Get total query execution time
     * 
     * @return void
     */ 
    protected function totalQueryDuration()
    {
        $start  = $this->connection->timer;
        $end    = new DateTime();

        // time difference
        if(is_object($start)){
            $diff = $start->diff($end);
            
            // runtime  
            $this->runtime = $diff->format('%s.%f');
        }

        // round to 2 decimal
        $this->runtime = round((float) $this->runtime, 2);
    }

    /**
     * Handle Errors
     * 
     * @param mixed $exception
     * - \Instance of Throwable or PDOException
     * 
     * @return mixed
     */ 
    protected function errorException(mixed $exception)
    {
        if($this->manager::AppDebug()){
            ORMDebugManager->handleException(
                new \PDOException($exception->getMessage(), (int) $exception->getCode(), $exception),
            );
        } 
        exit(1);
    }
    
} 

