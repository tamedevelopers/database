<?php

declare(strict_types=1);

namespace builder\Database\Capsule;

use builder\Database\Constants;
use builder\Database\Capsule\Manager;


class AppManager extends Constants{
    
    static private $year;

    /**
     * initialize
     */
    public function __construct() {
        self::$year = date('Y', time());
    }

    /**
     * Sample copy of env file
     * 
     * @return string
     */
    static public function envDummy()
    {
        return preg_replace("/^[ \t]+|[ \t]+$/m", "", 'APP_NAME="ORM Model"
            APP_ENV=local
            APP_KEY='. self::generateAppKey() .'
            APP_DEBUG=true
            SITE_EMAIL=
            
            DRIVER_NAME=mysql
            DB_HOST="localhost"
            DB_PORT=3306
            DB_USERNAME="root"
            DB_PASSWORD=
            DB_DATABASE=

            DB_CHARSET=utf8mb4
            DB_COLLATION=utf8mb4_general_ci

            MAIL_MAILER=smtp
            MAIL_HOST=
            MAIL_PORT=465
            MAIL_USERNAME=
            MAIL_PASSWORD=
            MAIL_ENCRYPTION=tls
            MAIL_FROM_ADDRESS="${MAIL_USERNAME}"
            MAIL_FROM_NAME="${APP_NAME}"

            AWS_ACCESS_KEY_ID=
            AWS_SECRET_ACCESS_KEY=
            AWS_DEFAULT_REGION=us-east-1
            AWS_BUCKET=
            AWS_USE_PATH_STYLE_ENDPOINT=false

            PUSHER_APP_ID=
            PUSHER_APP_KEY=
            PUSHER_APP_SECRET=
            PUSHER_HOST=
            PUSHER_PORT=443
            PUSHER_SCHEME=https
            PUSHER_APP_CLUSTER=mt1
            
            #©Copyright '. self::$year .'
            APP_DEVELOPER=
            APP_DEVELOPER_EMAIL=
        ');
    }

    /**
     * Generates an app KEY
     * 
     * @return string
     */
    static public function generateAppKey($length = 32)
    {
        $randomBytes = random_bytes($length);
        $appKey = 'base64:' . rtrim(strtr(base64_encode($randomBytes), '+/', '-_'), '=');
        $appKey = str_replace('+', '-', $appKey);
        $appKey = str_replace('/', '_', $appKey);

        // Generate a random position to insert '/'
        $randomPosition = random_int(0, strlen($appKey));
        $appKey         = substr_replace($appKey, '/', $randomPosition, 0);

        $appKey .= '=';

        return $appKey;
    }

}