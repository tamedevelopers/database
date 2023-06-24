<?php

namespace builder\Database\Connectors;

interface ConnectorInterface
{
    /**
     * Establish a database connection.
     *
     * @param  array  $config
     * @return array
     */
    public static function connect(array $config);
}
