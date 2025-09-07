<?php

declare(strict_types=1);

namespace Tamedevelopers\Database;


class Constant{

    const STATUS_400  = 400;
    const STATUS_404  = 404;
    const STATUS_200  = 200;
    const COUNT       = 0;
    const ONE         = 1;


    /**
     * Format Migration Table Name
     *
     * @param string $migration
     * 
     * @return string <create_{$migration}_table.php>
     */
    public static function formatMigrationTableName($migration)
    {
        // "%s_%s_%s"
        return sprintf("%s_%s", 
            date('Y_m_d'), 
            // substr((string) time(), 4),
            "create_{$migration}_table.php");
    }
}
