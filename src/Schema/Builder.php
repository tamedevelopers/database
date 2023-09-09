<?php

declare(strict_types=1);

namespace builder\Database\Schema;

use PDO;
use Closure;
use Exception;
use PDOException;
use DateTimeInterface;
use builder\Database\Constant;
use builder\Database\Capsule\Forge;
use builder\Database\Schema\Pagination\Paginator;
use builder\Database\Collections\Collection;
use builder\Database\Schema\Traits\BuilderTrait;
use builder\Database\Schema\Traits\ExpressionTrait;
use builder\Database\Schema\Traits\MySqlProperties;

/**
 * @property $manager \builder\Database\Capsule\Manager
 * @property $dbManager \builder\Database\DatabaseManager
 * 
 * @see \builder\Database\Schema\Traits\MySqlProperties
 * @see \builder\Database\Capsule\Manager
 * @see \builder\Database\DatabaseManager
*/
class Builder  {
    
    use BuilderTrait, 
        MySqlProperties,
        ExpressionTrait;
    

    /**
     * Set the table which the query is targeting.
     *
     * @param  string  $table
     * @param  string|null  $as
     * @return $this
     */
    public function from($table, $as = null)
    {
        $this->from = $as ? "{$table} as {$as}" : $table;

        return $this;
    }

    /**
     * Direct Query Expression
     * 
     * @param string $query
     * @return $this
     */ 
    public function query(string $query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Columns to be selected.
     * 
     * @param mixed $columns
     * [array] or [string] if single selection
     * 
     * @return $this
     */ 
    public function select(mixed $columns = [])
    {
        return $this->buildSelect(
            is_array($columns) ? $columns : func_get_args()
        );
    }

    /**
     * Add a Raw select expression to the query.
     * 
     * @param mixed $expression
     * 
     * @param array $bindings
     * [optional] data to bind in the expression if found
     * 
     * @return $this
     */ 
    public function selectRaw(mixed $expression, $bindings = [])
    {
        return $this->buildSelectRaw($expression, $bindings);
    }

    /**
     * Add an "order by" clause to the query.
     * 
     * @param string $column
     * 
     * @param string|null $direction
     * [optional] Default direction is `asc`
     * 
     * @return $this
     */ 
    public function orderBy($column, $direction = 'asc')
    {
        $direction = strtolower($direction);

        if (! in_array($direction, ['asc', 'desc'], true)) {
            throw new Exception('Order direction must be "asc" or "desc".');
        }

        $this->orders[] = compact('column', 'direction');

        return $this;
    }

    /**
     * Add a raw "order by" clause to the query.
     * 
     * @param string $sql
     * 
     * @return $this
     */ 
    public function orderByRaw(string $sql)
    {
        $type = 'Raw';

        $this->orders[] = compact('type', 'sql');

        return $this;
    }

    /**
     * Add a "latest" clause to the query.
     * 
     * @param string $column
     * [optional] Default column name is `id`
     *
     * @return $this
     */
    public function latest(string $column = 'id')
    {
        $this->orderBy($column, 'desc');

        return $this;
    }

    /**
     * Add a "oldest" clause to the query.
     * 
     * @param string $column
     * [optional] Default column name is `id`
     *
     * @return $this
     */
    public function oldest(string $column = 'id')
    {
        $this->orderBy($column);

        return $this;
    }

    /**
     * Query's results in random order.
     * 
     * @return $this
     */ 
    public function inRandomOrder()
    {
        $driver = self::$connection['config']['driver'] ?? 'mysql';
        if($driver === 'mysql'){
            $sql = "RAND()";
        } elseif(in_array($driver, ['pgsql', 'sqlite'])){
            $sql = "RANDOM()";
        }

        $this->orderByRaw($sql);

        return $this;
    }

    /**
     * Query's results in random order.
     * 
     * @return $this
     */ 
    public function random()
    {
        $this->inRandomOrder();
        
        return $this;
    }

    /**
     * Set limits
     * 
     * @param int $value
     * @return $this
     */ 
    public function limit($value)
    {
        if ($value >= 0) {
            $this->limit = !is_null($value) ? (int) $value : null;
        }

        return $this;
    }

    /**
     * Set the "offset" value of the query.
     * 
     * @param  int  $value
     * @return $this
     */ 
    public function offset($value)
    {
        $this->offset = max(0, (int) $value);
        
        return $this;
    }

    /**
     * Add a join clause to the query.
     * 
     * @param  string  $table
     * @param  string  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * @param  string  $type
     * @param  bool  $where
     * 
     * @return $this
     */ 
    public function join($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
    {
        $join = $this->newJoinClause($this, $type, $table);

        // If the first "column" of the join is really a Closure instance the developer
        // is trying to build a join with a complex "on" clause containing more than
        // one condition, so we'll add the join and call a Closure with the query.
        if ($this->isClosure($first)) {
            $first($join);

            $this->joins[] = $join;

            $this->addBinding($join->getBindings(), 'join');
        }

        // If the column is simply a string, we can assume the join simply has a basic
        // "on" clause with a single condition. So we will just build the join with
        // this simple join clauses attached to it. There is not a join callback.
        else {
            $method = $where ? 'where' : 'on';

            $this->joins[] = $join->$method($first, $operator, $second);

            $this->addBinding($join->getBindings(), 'join');
        }

        return $this;
    }

    /**
     * Add a "join where" clause to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $table
     * @param  \Closure|string  $first
     * @param  string  $operator
     * @param  string  $second
     * @param  string  $type
     * @return $this
     */
    public function joinWhere($table, $first, $operator, $second, $type = 'inner')
    {
        return $this->join($table, $first, $operator, $second, $type, true);
    }

    /**
     *  Add a left Join clause to the query.
     * 
     * @param  string  $table
     * @param  string  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * 
     * @return $this
     */ 
    public function leftJoin($table, $first = null, $operator = null, $second = null)
    {
        $this->join($table, $first, $operator, $second, 'left');

        return $this;
    }

    
    /**
     * Add a "join where" clause to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $table
     * @param  \Closure|string  $first
     * @param  string  $operator
     * @param  string  $second
     * @return $this
     */
    public function leftJoinWhere($table, $first, $operator, $second)
    {
        return $this->joinWhere($table, $first, $operator, $second, 'left');
    }

    /**
     *  Add a right Join clause to the query.
     * 
     * @param  string  $table
     * @param  string  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * 
     * @return $this
     */ 
    public function rightJoin($table, $first = null, $operator = null, $second = null)
    {
        $this->join($table, $first, $operator, $second, 'right');

        return $this;
    }

    /**
     * Add a "right join where" clause to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $table
     * @param  \Closure|string  $first
     * @param  string  $operator
     * @param  string  $second
     * @return $this
     */
    public function rightJoinWhere($table, $first, $operator, $second)
    {
        return $this->joinWhere($table, $first, $operator, $second, 'right');
    }

    /**
     *  Add a cross Join clause to the query.
     * 
     * @param  string  $table
     * @param  string  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * 
     * @return $this
     */ 
    public function crossJoin($table, $first = null, $operator = null, $second = null)
    {
        if ($first) {
            return $this->join($table, $first, $operator, $second, 'cross');
        }

        $this->joins[] = $this->newJoinClause($this, 'cross', $table);

        return $this;
    }

    /**
     * Add a "group by" clause to the query.
     *
     * @param  array|string ...$groups
     * @return $this
     */ 
    public function groupBy(...$groups)
    {
        foreach ($groups as $group) {
            $this->groups = array_merge(
                (array) $this->groups,
                Forge::wrap($group)
            );
        }

        return $this;
    }

    /**
     * Add a raw groupBy clause to the query.
     *
     * @param  string  $sql
     * @param  array  $bindings
     * @return $this
     */
    public function groupByRaw($sql, array $bindings = [])
    {
        $this->groups[] = self::raw($sql);

        $this->addBinding($bindings, 'groupBy');

        return $this;
    }

    /**
     * Add a basic where clause to the query.
     * 
     * @param  \Closure|string|array|\builder\Database\Schema\Expression  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * 
     * @return $this
     */ 
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($this->isExpression($column)) {
            $type = 'Expression';

            $this->wheres[] = compact('type', 'column', 'boolean');

            return $this;
        }

        // If the column is an array, we will assume it is an array of key-value pairs
        // and can add them each as a where clause. We will maintain the boolean we
        // received when the method was called and pass it into the nested where.
        // [ ['email_verified', 1], ['balanace', '>', 1] ]
        if (is_array($column)) {
            return $this->addArrayOfWheres($column, $boolean);
        }

        // If only 2 values are passed to the method, then operator is default by equals sign
        // Otherwise, we'll require the operator to be passed in.
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        // If the column is an actual Closure (callablle method) instance, we will assume the developer
        // we will begine nested query, adding Closure to the query and return back immediately.
        if ($this->isClosure($column) && is_null($operator)) {
            return $this->whereNested($column, $boolean);
        }

        // If the given operator is not found in the list of valid operators
        // we revert back to the default operator of '='
        if ($this->invalidOperator($operator)) {
            [$value, $operator] = [$value, '='];
        }

        // If the value is "null", we assume its a where null method. 
        // So, we will allow a short-cut here for convenience.
        if (is_null($value)) {
            return $this->whereNull($column, $boolean, $operator !== '=');
        }

        $type = 'Basic';

        // Now that we are working with just a simple query we can put the elements
        // in our array and add the query binding to our array of bindings that
        // will be bound to each SQL statements when it is finally executed.
        $this->wheres[] = compact(
            'type', 'column', 'operator', 'value', 'boolean'
        );
        
        if(!$this->isExpressionContract($value)) {
            $this->addBinding($this->flattenValue($value), 'where');
        }

        return $this;
    }

    /**
     * PDO orWhere clause. Expects three params (only two mandatory)
     * By default if you provide two param (operator becomes =) equals to. And Value becomes 2nd param
     * If you provide three values then, operator must be the middle param
     * 
     * @param \Closure|string|array|\builder\Database\Schema\Expression  $column
     * @param string $operator
     * @param string $value
     * 
     * @return object
     */ 
    public function orWhere($column, $operator = null, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->where($column, $operator, $value, 'or');
    }

    /**
     * Add a basic "where not" clause to the query.
     *
     * @param  \Closure|string|array|\builder\Database\Schema\Expression  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * @return $this
     */
    public function whereNot($column, $operator = null, $value = null, $boolean = 'and')
    {
        if (is_array($column)) {
            return $this->whereNested(function ($query) use ($column, $operator, $value, $boolean) {
                $query->where($column, $operator, $value, $boolean);
            }, $boolean.' not');
        }

        return $this->where($column, $operator, $value, $boolean.' not');
    }

    /**
     * Add an "or where not" clause to the query.
     *
     * @param  \Closure|string|array|\builder\Database\Schema\Expression  $column
     * @param  mixed  $operator
     * @param  mixed  $value
     * @return $this
     */
    public function orWhereNot($column, $operator = null, $value = null)
    {
        return $this->whereNot($column, $operator, $value, 'or');
    }

    /**
     * Add a "where null" clause to the query.
     *
     * @param  string|array|\builder\Database\Schema\Expression  $columns
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function whereNull($columns, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotNull' : 'Null';

        foreach (Forge::wrap($columns) as $column) {
            $this->wheres[] = compact('type', 'column', 'boolean');
        }

        return $this;
    }

    /**
     * Add an "or where null" clause to the query.
     *
     * @param  string|array|\builder\Database\Schema\Expression  $column
     * @return $this
     */
    public function orWhereNull($column)
    {
        return $this->whereNull($column, 'or');
    }

    /**
     * Add a "where not null" clause to the query.
     *
     * @param  string|array|\builder\Database\Schema\Expression  $columns
     * @param  string  $boolean
     * @return $this
     */
    public function whereNotNull($columns, $boolean = 'and')
    {
        return $this->whereNull($columns, $boolean, true);
    }

    /**
     * Add an "or where not null" clause to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @return $this
     */
    public function orWhereNotNull($column)
    {
        return $this->whereNotNull($column, 'or');
    }

    /**
     * Add a "where" clause comparing two columns to the query.
     *
     * @param  string|array  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * @param  string|null  $boolean
     * @return $this
     */
    public function whereColumn($first, $operator = null, $second = null, $boolean = 'and')
    {
        // If the column is an array, we will assume it is an array of key-value pairs
        // and can add them each as a where clause. We will maintain the boolean we
        // received when the method was called and pass it into the nested where.
        if (is_array($first)) {
            return $this->addArrayOfWheres($first, $boolean, 'whereColumn');
        }

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            [$second, $operator] = [$operator, '='];
        }

        // Finally, we will add this where clause into this array of clauses that we
        // are building for the query. All of them will be compiled via a grammar
        // once the query is about to be executed and run against the database.
        $type = 'Column';

        $this->wheres[] = compact(
            'type', 'first', 'operator', 'second', 'boolean'
        );

        return $this;
    }

    /**
     * Add an "or where" clause comparing two columns to the query.
     *
     * @param  string|array  $first
     * @param  string|null  $operator
     * @param  string|null  $second
     * @return $this
     */
    public function orWhereColumn($first, $operator = null, $second = null)
    {
        return $this->whereColumn($first, $operator, $second, 'or');
    }

    /**
     * Add a raw where clause to the query.
     *
     * @param  string  $sql
     * @param  mixed  $bindings
     * @param  string  $boolean
     * @return $this
     */
    public function whereRaw($sql, $bindings = [], $boolean = 'and')
    {
        $this->wheres[] = ['type' => 'raw', 'sql' => $sql, 'boolean' => $boolean];

        $this->addBinding((array) $bindings, 'where');

        return $this;
    }

    /**
     * Add a raw or where clause to the query.
     *
     * @param  string  $sql
     * @param  mixed  $bindings
     * @return $this
     */
    public function orWhereRaw($sql, $bindings = [])
    {
        return $this->whereRaw($sql, $bindings, 'or');
    }

    /**
     * Add a "where in" clause to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @param  mixed  $values
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotIn' : 'In';

        // Next, if the value is Arrayable we need to cast it to its raw array form so we
        // have the underlying array value instead of an Arrayable object which is not
        // able to be added as a binding, etc. We will then add to the wheres array.
        if ($this->isCollection($values)) {
            $values = $values->toArray();
        }

        $this->wheres[] = compact('type', 'column', 'values', 'boolean');

        if (count($values) !== count(Forge::flattenValue($values, 1))) {
            throw new Exception('Nested arrays may not be passed to whereIn method.');
        }

        // Finally, we'll add a binding for each value unless that value is an expression
        // in which case we will just skip over it since it will be the query as a raw
        // string and not as a parameterized place-holder to be replaced by the PDO.
        $this->addBinding($this->cleanBindings($values), 'where');

        return $this;
    }

    /**
     * Add an "or where in" clause to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @param  mixed  $values
     * @return $this
     */
    public function orWhereIn($column, $values)
    {
        return $this->whereIn($column, $values, 'or');
    }

    /**
     * Add a "where not in" clause to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @param  mixed  $values
     * @param  string  $boolean
     * @return $this
     */
    public function whereNotIn($column, $values, $boolean = 'and')
    {
        return $this->whereIn($column, $values, $boolean, true);
    }

    /**
     * Add an "or where not in" clause to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @param  mixed  $values
     * @return $this
     */
    public function orWhereNotIn($column, $values)
    {
        return $this->whereNotIn($column, $values, 'or');
    }

    /**
     * Add a where between statement to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @param  mixed  $values
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function whereBetween($column, iterable $values, $boolean = 'and', $not = false)
    {
        $type = 'between';

        if ($this->isCarbonPeriod($values)) {
            $values = [$values->start, $values->end];
        }

        $this->wheres[] = compact('type', 'column', 'values', 'boolean', 'not');

        $this->addBinding(array_slice($this->cleanBindings(Forge::flattenValue($values)), 0, 2), 'where');

        return $this;
    }

    /**
     * Add an or where between statement to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @param  iterable  $values
     * @return $this
     */
    public function orWhereBetween($column, iterable $values)
    {
        return $this->whereBetween($column, $values, 'or');
    }

    /**
     * Add a where not between statement to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @param  iterable  $values
     * @param  string  $boolean
     * @return $this
     */
    public function whereNotBetween($column, iterable $values, $boolean = 'and')
    {
        return $this->whereBetween($column, $values, $boolean, true);
    }

    /**
     * Add an or where not between statement to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @param  iterable  $values
     * @return $this
     */
    public function orWhereNotBetween($column, iterable $values)
    {
        return $this->whereNotBetween($column, $values, 'or');
    }

    /**
     * Add a where between statement using columns to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @param  array  $values
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function whereBetweenColumns($column, array $values, $boolean = 'and', $not = false)
    {
        $type = 'betweenColumns';

        $this->wheres[] = compact('type', 'column', 'values', 'boolean', 'not');

        return $this;
    }

    /**
     * Add an or where between statement using columns to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @param  array  $values
     * @return $this
     */
    public function orWhereBetweenColumns($column, array $values)
    {
        return $this->whereBetweenColumns($column, $values, 'or');
    }

    /**
     * Add a where not between statement using columns to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @param  array  $values
     * @param  string  $boolean
     * @return $this
     */
    public function whereNotBetweenColumns($column, array $values, $boolean = 'and')
    {
        return $this->whereBetweenColumns($column, $values, $boolean, true);
    }

    /**
     * Add an or where not between statement using columns to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @param  array  $values
     * @return $this
     */
    public function orWhereNotBetweenColumns($column, array $values)
    {
        return $this->whereNotBetweenColumns($column, $values, 'or');
    }

    /**
     * Add a "where date" statement to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|null  $value
     * @param  string  $boolean
     * @return $this
     */
    public function whereDate($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $value = $this->flattenValue($value);

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y-m-d');
        }

        return $this->addDateBasedWhere('Date', $column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where date" statement to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|null  $value
     * @return $this
     */
    public function orWhereDate($column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->whereDate($column, $operator, $value, 'or');
    }

    /**
     * Add a "where time" statement to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|null  $value
     * @param  string  $boolean
     * @return $this
     */
    public function whereTime($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $value = $this->flattenValue($value);

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('H:i:s');
        }

        return $this->addDateBasedWhere('Time', $column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where time" statement to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|null  $value
     * @return $this
     */
    public function orWhereTime($column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->whereTime($column, $operator, $value, 'or');
    }

    /**
     * Add a "where day" statement to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|int|null  $value
     * @param  string  $boolean
     * @return $this
     */
    public function whereDay($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $value = $this->flattenValue($value);

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('d');
        }

        if (! $this->isExpressionContract($value)) {
            $value = sprintf('%02d', $value);
        }

        return $this->addDateBasedWhere('Day', $column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where day" statement to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|int|null  $value
     * @return $this
     */
    public function orWhereDay($column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->whereDay($column, $operator, $value, 'or');
    }

    /**
     * Add a "where month" statement to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|int|null  $value
     * @param  string  $boolean
     * @return $this
     */
    public function whereMonth($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $value = $this->flattenValue($value);

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('m');
        }

        if (! $this->isExpressionContract($value)) {
            $value = sprintf('%02d', $value);
        }

        return $this->addDateBasedWhere('Month', $column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where month" statement to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|int|null  $value
     * @return $this
     */
    public function orWhereMonth($column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->whereMonth($column, $operator, $value, 'or');
    }

    /**
     * Add a "where year" statement to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|int|null  $value
     * @param  string  $boolean
     * @return $this
     */
    public function whereYear($column, $operator, $value = null, $boolean = 'and')
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        $value = $this->flattenValue($value);

        if ($value instanceof DateTimeInterface) {
            $value = $value->format('Y');
        }

        return $this->addDateBasedWhere('Year', $column, $operator, $value, $boolean);
    }

    /**
     * Add an "or where year" statement to the query.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @param  string  $operator
     * @param  \DateTimeInterface|string|int|null  $value
     * @return $this
     */
    public function orWhereYear($column, $operator, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->whereYear($column, $operator, $value, 'or');
    }

    /**
     * Add a "having" clause to the query.
     *
     * @param  \builder\Database\Schema\Expression|\Closure|string  $column
     * @param  string|int|float|null  $operator
     * @param  string|int|float|null  $value
     * @param  string  $boolean
     * @return $this
     */
    public function having($column, $operator = null, $value = null, $boolean = 'and')
    {
        $type = 'Basic';

        if ($this->isExpression($column)) {
            $type = 'Expression';

            $this->havings[] = compact('type', 'column', 'boolean');

            return $this;
        }

        // Here we will make some assumptions about the operator. If only 2 values are
        // passed to the method, we will assume that the operator is an equals sign
        // and keep going. Otherwise, we'll require the operator to be passed in.
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        if ($column instanceof Closure && is_null($operator)) {
            return $this->havingNested($column, $boolean);
        }

        // If the given operator is not found in the list of valid operators we will
        // assume that the developer is just short-cutting the '=' operators and
        // we will set the operators to '=' and set the values appropriately.
        if ($this->invalidOperator($operator)) {
            [$value, $operator] = [$value, '='];
        }

        $this->havings[] = compact('type', 'column', 'operator', 'value', 'boolean');

        if (! $this->isExpressionContract($value)) {
            $this->addBinding($this->flattenValue($value), 'having');
        }

        return $this;
    }

    /**
     * Add an "or having" clause to the query.
     *
     * @param  \builder\Database\Schema\Expression|\Closure|string  $column
     * @param  string|int|float|null  $operator
     * @param  string|int|float|null  $value
     * @return $this
     */
    public function orHaving($column, $operator = null, $value = null)
    {
        [$value, $operator] = $this->prepareValueAndOperator(
            $value, $operator, func_num_args() === 2
        );

        return $this->having($column, $operator, $value, 'or');
    }

    /**
     * Add a nested having statement to the query.
     *
     * @param  \Closure  $callback
     * @param  string  $boolean
     * @return $this
     */
    public function havingNested(Closure $callback, $boolean = 'and')
    {
        $callback($query = $this->forNestedWhere());

        return $this->addNestedHavingQuery($query, $boolean);
    }

    /**
     * Add another query builder as a nested having to the query builder.
     *
     * @param  \builder\Database\Schema\Query\Builder  $query
     * @param  string  $boolean
     * @return $this
     */
    public function addNestedHavingQuery($query, $boolean = 'and')
    {
        if (count($query->havings)) {
            $type = 'Nested';

            $this->havings[] = compact('type', 'query', 'boolean');

            $this->addBinding($query->getRawBindings()['having'], 'having');
        }

        return $this;
    }

    /**
     * Add a "having null" clause to the query.
     *
     * @param  string|array  $columns
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function havingNull($columns, $boolean = 'and', $not = false)
    {
        $type = $not ? 'NotNull' : 'Null';

        foreach (Forge::wrap($columns) as $column) {
            $this->havings[] = compact('type', 'column', 'boolean');
        }

        return $this;
    }

    /**
     * Add an "or having null" clause to the query.
     *
     * @param  string  $column
     * @return $this
     */
    public function orHavingNull($column)
    {
        return $this->havingNull($column, 'or');
    }

    /**
     * Add a "having not null" clause to the query.
     *
     * @param  string|array  $columns
     * @param  string  $boolean
     * @return $this
     */
    public function havingNotNull($columns, $boolean = 'and')
    {
        return $this->havingNull($columns, $boolean, true);
    }

    /**
     * Add an "or having not null" clause to the query.
     *
     * @param  string  $column
     * @return $this
     */
    public function orHavingNotNull($column)
    {
        return $this->havingNotNull($column, 'or');
    }

    /**
     * Add a "having between " clause to the query.
     *
     * @param  string  $column
     * @param  mixed  $values
     * @param  string  $boolean
     * @param  bool  $not
     * @return $this
     */
    public function havingBetween($column, iterable $values, $boolean = 'and', $not = false)
    {
        $type = 'between';

        if ($this->isCarbonPeriod($values)) {
            $values = [$values->start, $values->end];
        }

        $this->havings[] = compact('type', 'column', 'values', 'boolean', 'not');

        $this->addBinding(array_slice($this->cleanBindings(Forge::flattenValue($values)), 0, 2), 'having');

        return $this;
    }

    /**
     * Add a raw having clause to the query.
     *
     * @param  string  $sql
     * @param  array  $bindings
     * @param  string  $boolean
     * @return $this
     */
    public function havingRaw($sql, array $bindings = [], $boolean = 'and')
    {
        $type = 'Raw';

        $this->havings[] = compact('type', 'sql', 'boolean');

        $this->addBinding($bindings, 'having');

        return $this;
    }

    /**
     * Add a raw or having clause to the query.
     *
     * @param  string  $sql
     * @param  array  $bindings
     * @return $this
     */
    public function orHavingRaw($sql, array $bindings = [])
    {
        return $this->havingRaw($sql, $bindings, 'or');
    }

    /**
     * Check if table exists
     * 
     * @param string $table
     * 
     * @return bool
     */
    public function tableExists(?string $table)
    {
        try{
            $pdo = $this->connection->pdo;

            // check if Database connection has been established 
            if(!$this->isPDO($pdo)){
                return false;
            }

            $pdo->query("
                select exists (select 1 from `{$table}` limit 1) as 'exists'
            ")->execute();

            $this->close();
            
            return true;
        }catch (PDOException $e){
            return false;
        }
    }

    /**
     * Determine if any rows exist for the current query.
     *
     * @return bool
     */
    public function exists()
    {
        $this->applyBeforeQueryCallbacks();

        $results = $this->statement(
            $this->compile()->compileExists($this),
            $this->getBindings()
        );

        // save data before closing queries
        $data = $results->fetch(PDO::FETCH_COLUMN);

        $this->close();

        // since we're usinfg fetch column, then result will return the value of the fetched column
        // instead of returning the arrays where the columns is
        return $data >= Constant::ONE ? true : false;
    }

    /**
     * Determine if no rows exist for the current query.
     *
     * @return bool
     */
    public function doesntExist()
    {
        return ! $this->exists();
    }

    /**
     * Get the SQL representation of the query.
     *
     * @return string
     */
    public function toSql()
    {
        $this->applyBeforeQueryCallbacks();

        return $this->compile()->compileSelect($this);
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  int $limit
     * @return \builder\Database\Collections\Collection
     */
    public function get($limit = null)
    {
        // only allow get limit parameter
        // if limit has not been set and if not empty
        if(empty($this->limit) && !empty($limit)){
            $this->limit($limit);
        }

        return $this->getBuilder(true, false, __FUNCTION__);
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param  int|string $perPage
     * - Supporting numeric string values, which will be internally converted to `int`
     * 
     * @param  string $pageParam
     * [optional] parameter name on url
     * 
     * @return \builder\Database\Collections\Collection
     */
    public function paginate($perPage = 15, $pageParam = 'page')
    {
        // total page count from query builder
        // passing false to the builder, means we do not want to close connection
        // and queries after the count as aggregate
        $totalCount = $this->countBuilder('*', false);

        // new paginator class
        $paginator = new Paginator($pageParam);

        $this->setMethod(__FUNCTION__);

        // paginator data
        $results =  $paginator->getPagination($totalCount, $perPage, $this);
        
        return new Collection($results['data'], $results['builder']);
    }

    /**
     * Retrieve the "count" result of the query.
     * @return int
     */
    public function count()
    {
        return $this->countBuilder();
    }

    /**
     * Find data by given value
     * 
     * @param int $value
     * [default column name is `id`]
     *
     * @return null\builder\Database\Collections\Collection
     */
    public function find(int $value)
    {
        return $this->where('id', $value)->first();
    }

    /**
     * Execute the query and get the first result.
     * @return null\builder\Database\Collections\Collection
     */
    public function first()
    {
        return $this->firstOrIgnoreBuilder(__FUNCTION__);
    }

    /**
     * Execute the query and get the first result or throw an exception.
     * @return null\builder\Database\Collections\Collection
     */
    public function firstOrIgnore()
    {
        return $this->firstOrIgnoreBuilder(__FUNCTION__, true, true);
    }

    /**
     * Execute the query and get the first result or throw an exception.
     * @return void\builder\Database\Collections\Collection
     */
    public function firstOrFail()
    {
        if (! is_null($results = $this->first())) {
            return $results;
        }

        $this->manager->setHeaders();
    }

    /**
     * Get the first related record matching the attributes or create it.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return \builder\Database\Collections\Collection
     */
    public function firstOrCreate(array $attributes = [], array $values = [])
    {
        // we run a query to the where method to get data where columns 
        // is an array, and this is expected to return a Collection of data
        // We also add limit by 1 to query
        $results = $this->limit(1)->where($attributes)->getBuilder(false);

        // if there's an item in collection then, we only get the index of 0
        // as expected, either Collection is empty or always return only one item
        // since we had already limit query by 1
        $results =  $results->count() > 0 ? $results[0] : null;

        if (is_null($instance = $results)) {

            // close query instance and passing false will allow the table name to remain
            // since we're still going to run an insert method, hereafter
            $this->close(false);

            // since insert returns a collection, then we convert to an array
            $instance = $this->insert(array_merge($attributes, $values));

            // since insert returns a collection, then we convert to an array.
            // if the insert is succesful, then we convert data to an array 
            // so we can only return a collection of data once
            if($this->isCollection($instance)){
                $instance = $instance->toArray();
            }
        }

        $this->setMethod(__FUNCTION__);

        return new Collection($instance);
    }

    /**
     * Insert new records into the database.
     *
     * @param  array  $values
     * @return \builder\Database\Collections\Collection
     */
    public function insert(array $values)
    {
        return $this->insertBuilder($values);
    }

    /**
     * Insert new records into the database while ignoring errors.
     *
     * @param  array  $values
     * @return null\builder\Database\Collections\Collection
     */
    public function insertOrIgnore(array $values)
    {
        return $this->insertBuilder($values, true);
    }

    /**
     * Update records in the database.
     *
     * @param  array  $values
     * @return int
     */
    public function update(array $values)
    {
        return $this->updateBuilder($values);
    }

    /**
     * Update or ignore query data
     * 
     * @param array $values
     * @return int
     */ 
    public function updateOrIgnore(array $values)
    {
        return $this->updateBuilder($values, true);
    }

    /**
     * Delete records from the database.
     *
     * @return int
     */
    public function delete()
    {
        $this->applyBeforeQueryCallbacks();

        $sql = $this->compile()->compileDelete($this);

        // replace all quoteation to backticks
        $sql = str_replace(['`', "'"], "`", $sql);

        // delete query
        $delete = $this->statement($sql, $this->cleanBindings(
            $this->compile()->prepareBindingsForDelete($this->bindings)
        ));

        return $delete->rowCount();
    }

    /**
     * Destroy records from the database.
     * [performing where clause under the hood]
     *
     * @param mixed $id
     *
     * @param string $column
     * [default column name is 'id']
     * 
     * @return int
     */
    public function destroy($id, $column = 'id')
    {
        return $this->where($column, $id)->delete();
    }

    /**
     * Increment a column's value by a given amount.
     *
     * @param  string  $column
     * @param  int|float|array $count
     * @param  array  $extra
     * @return int
     *
     * @throws \Exception
     */
    public function increment(string $column, $count = 1, array $extra = [])
    {
        [$column, $count, $extra] = $this->prepareIncrementParams(
            $column, $count, $extra, ((func_num_args() === 1 || func_num_args() === 2) && !is_array($count))
        );

        return $this->incrementEach([$column => $count], $extra);
    }

    /**
     * Decrement a column's value by a given amount.
     *
     * @param  string  $column
     * @param  int|float|array $count
     * @param  array  $extra
     * @return int
     *
     * @throws \Exception
     */
    public function decrement(string $column, $count = 1, array $extra = [])
    {
        [$column, $count, $extra] = $this->prepareIncrementParams(
            $column, $count, $extra, ((func_num_args() === 1 || func_num_args() === 2) && !is_array($count))
        );

        return $this->decrementEach([$column => $count], $extra);
    }

    /**
     * Retrieve the minimum value of a given column.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @return mixed
     */
    public function min($column)
    {
        return $this->aggregate(__FUNCTION__, [$column]);
    }

    /**
     * Retrieve the maximum value of a given column.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @return mixed
     */
    public function max($column)
    {
        return $this->aggregate(__FUNCTION__, [$column]);
    }

    /**
     * Retrieve the sum of the values of a given column.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @return mixed
     */
    public function sum($column)
    {
        $result = $this->aggregate(__FUNCTION__, [$column]);

        return $result ?? 0;
    }

    /**
     * Retrieve the average of the values of a given column.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @return mixed
     */
    public function avg($column)
    {
        return $this->aggregate(__FUNCTION__, [$column]);
    }

    /**
     * Alias for the "avg" method.
     *
     * @param  \builder\Database\Schema\Expression|string  $column
     * @return mixed
     */
    public function average($column)
    {
        return $this->avg($column);
    }

    /**
     * Dump the current SQL and bindings.
     *
     * @return $this
     */
    public function dump()
    {
        dump($this->toSql(), $this->getBindings());

        $this->totalQueryDuration();

        return $this;
    }

    /**
     * Die and dump the current SQL and bindings.
     *
     * @return never
     */
    public function dd()
    {
        $this->totalQueryDuration();

        dd($this->toSql(), $this->getBindings(), $this);
    }

}
