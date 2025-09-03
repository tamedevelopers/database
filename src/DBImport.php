<?php

declare(strict_types=1);

namespace Tamedevelopers\Database;

use PDOException;
use Tamedevelopers\Database\DB;
use Tamedevelopers\Support\Str;
use Tamedevelopers\Support\Server;
use Tamedevelopers\Support\Capsule\File;
use Tamedevelopers\Database\Traits\DBImportTrait;


class DBImport{

    use DBImportTrait;
    
    /**
     * Realpath to database file
     *
     * @var mixed
     */
    private $realpath;

    /**
     * Error status
     *
     * @var int
     */
    public $error;

    /**
     * Message body
     *
     * @var mixed
     */
    public $message;
    
    /**
     * Instance of Database Object
     *
     * @var array
     */
    private $db;

    /**
     * Instance of Database Object
     *
     * @var \Tamedevelopers\Database\Connectors\Connector
     */
    protected $conn;
    
    /**
     * Path to sql file
     *
     * @var string|null
     */
    private $path;

       
    /**
     * Construct Instance of Database
     * 
     * @param string|null $path
     * @param string|null $connection
     * @return void
     */
    public function __construct($path = null, $connection = null) 
    {
        $this->error    = Constant::STATUS_404;
        $this->path     = $path;

        $this->conn = DB::connection($connection);
        $this->db   = $this->conn->dbConnection();
    }
    
    /**
     * Alias for import() method.
     * 
     * @return object
     * [status, message]
     */ 
    public function run()
    {
        return $this->import();
    }

    /**
     * Run the database import process.
     * @param string|null path
     * 
     * @return object
     * [status, message]
     */
    public function import($path = null)
    {
        // use the provided path or fall back to the instance's path [for older version support]
        $path = empty($path) ? $this->path : $path;
        $path = Str::replace(Server::formatWithBaseDirectory(), '', $path);

        $this->realpath = Server::formatWithBaseDirectory($path);

        /**
         * If SQL file does'nt exists
         */
        if(!File::exists($this->realpath)){
            $this->message = sprintf("Failed to open stream: [`%s`] does'nt exist.", $this->realpath);
        } else{

            // read a file into an array
            $readFile = file($this->realpath);

            // is readable
            if(!$this->isReadable($readFile)){
                $this->message = sprintf("Failed to read file or empty data. [`%s`]", $path);
            } else{

                // check if connection test is okay
                if($this->dbConnect()){
                    try{
                        // connection driver
                        $pdo = $this->db['pdo'];

                        // get content
                        $sql = File::get($this->realpath);

                        // Replace Creation of tables
                        $sql = str_replace("CREATE TABLE", "CREATE TABLE IF NOT EXISTS", $sql);

                        // Replace Insert into
                        $sql = str_replace("INSERT INTO", "INSERT IGNORE INTO", $sql);

                        // Replace Creation of triggers
                        $sql = str_replace("CREATE TRIGGER", "CREATE TRIGGER IF NOT EXISTS", $sql);

                        // Remove delimiter
                        $sql = str_replace(['DELIMITER', '$$'], "", $sql);

                        // Check if table exists and remove ALTER TABLE queries
                        $matches = [];
                        preg_match_all('/ALTER TABLE `(\w+)`/i', $sql, $matches);
                        $tableNames = $matches[1];

                        // loop through to check if table exist already and ignore ALTER queries
                        foreach ($tableNames as $tableName) {
                            $tableExistsQuery = "SHOW TABLES LIKE '{$tableName}'";
                            $tableExists = $pdo->query($tableExistsQuery)->rowCount() > 0;
                        
                            if ($tableExists) {
                                $sql = preg_replace("/ALTER TABLE `{$tableName}`.*?;/is", "", $sql);
                            }
                        }

                        // Split SQL into individual queries safely (respect quotes and comments)
                        $statements = $this->splitSqlStatements($sql);
                        $allSuccess = true;
                        $errors = [];

                        foreach ($statements as $statement) {
                            $statement = trim($statement);
                            if ($statement === '') {
                                continue;
                            }
                            try {
                                $pdo->exec($statement);
                            } catch (\PDOException $e) {
                                $allSuccess = false;
                                $errors[] = $e->getMessage();
                            }
                        }

                        // Set status based on execution
                        if ($allSuccess) {
                            $this->error   = Constant::STATUS_200;
                            $this->message = "- Database has been imported successfully.";
                        } else {
                            $this->error   = Constant::STATUS_400;
                            $this->message = "- Database import completed with errors:\n" . implode("\n", $errors);
                        }
                    } catch(PDOException $e){
                        $this->message  = "- Performing query: <strong style='color: #000'>{$e->getMessage()}</strong>";
                        $this->error    = Constant::STATUS_400;
                    }
                } else{
                    $this->message  = $this->db['message'];
                }
            }
        }
        
        /*
        | ----------------------------------------------------------------------------
        | Database importation use. Below are the status code
        | ----------------------------------------------------------------------------
        |   if ->status === 404 (Failed to read file or File does'nt exists
        |   if ->status === 400 (Query to database error
        |   if ->status === 200 (Success importing to database
        */ 
        return (object) [
            'status'    => $this->error, 
            'message'   => is_array($this->message) 
                            ? implode('\n<br>', $this->message)
                            : $this->message
        ];
    }
    
    /**
     * Check Database connection 
     * 
     * @return bool
    */
    private function dbConnect()
    {
        return $this->db['status'] == Constant::STATUS_200;
    }
    
}