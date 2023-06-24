<?php

declare(strict_types=1);

namespace builder\Database\Schema\Traits;


use builder\Database\Schema\Expression;

trait ExpressionTrait{
    
    /**
     * Create a raw SQL expression.
     *
     * @param string $expression
     * 
     * @return builder\Database\Schema\Expression
     */
    public static function raw($expression)
    {
        return new Expression($expression);
    }

}
