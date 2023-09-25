<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Schema\Traits;


use Tamedevelopers\Database\Schema\Expression;

trait ExpressionTrait{
    
    /**
     * Create a raw SQL expression.
     *
     * @param string $expression
     * 
     * @return Tamedevelopers\Database\Schema\Expression
     */
    public static function raw($expression)
    {
        return new Expression($expression);
    }

}
