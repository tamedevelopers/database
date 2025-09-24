<?php

declare(strict_types=1);

namespace Tamedevelopers\Database;

use Tamedevelopers\Support\Str;
use Tamedevelopers\Database\Constant;
use Tamedevelopers\Support\Capsule\File;
use Tamedevelopers\Database\Traits\DBExportTrait;
use Tamedevelopers\Support\Collections\Collection;

/**
 * Class DBExport
 *
 * Provides database backup functionality.
 */
class DBExport
{
    use DBExportTrait;

    /**
     * Number of rows per INSERT batch.
     *
     * @var int
     */
    protected $insertBatchSize = 100;

    /**
     * File storage path to [zip|rar]
     *
     * @var int
     */
    protected $fileStoragePath;

    /**
     * File Types supported by this class
     *
     * @var array
     */
    protected $fileTypes = [
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed'
    ];

    /**
     * Whether to save the backup in zip|rar format.
     *
     * @var string|null
     */
    protected $saveAsFileType;

    /**
     * Number of days to retain backups.
     *
     * @var int
     */
    protected $retentionDays;

    /**
     * Error status
     *
     * @var int
     */
    protected $error;

    /**
     * Message body
     *
     * @var mixed
     */
    protected $message;
    
    /**
     * Instance of Database Object
     *
     * @var mixed
     */
    public $db;

    /**
     * Instance of Database Object
     *
     * @var \Tamedevelopers\Database\Connectors\Connector
     */
    public $conn;

    /**
     * DBExport constructor.
     *  
     * @param string|null $connection
     * @param string|null $saveAsFileType Save the backup file as [zip|rar]
     * @param int $retentionDays Days to keep backups before deletion
     */
    public function __construct($connection = null, $saveAsFileType = null, ?int $retentionDays = 5)
    {
        $saveAsFileType = Str::lower($saveAsFileType);

        // Check if filetype is valid and set it
        if(in_array($saveAsFileType, array_keys($this->fileTypes))){
            $this->saveAsFileType = $saveAsFileType;
        }

        $this->retentionDays = $retentionDays;
        $this->error = Constant::STATUS_400;

        $this->conn = DB::connection($connection);
        $this->db = $this->conn->dbConnection();

        // since we're exporting from a live/database server
        // we need to temporarily disable prefixing, by setting its value to empty string
        $this->conn->changeTablePrefix('');
    }

    /**
     * Run the database backup process.
     *
     * @param string $backupDir Directory to store the backup
     * 
     * @return \Tamedevelopers\Support\Collections\Collection|mixed {status, message, path}
     * 
     * @throws \RuntimeException
     */
    public function run($backupDir = null)
    {
        $backupDir  = empty($backupDir) ? 'backups' : $backupDir;
        $dbInfo     = $this->db['config'];
        $dbName     = $dbInfo['database'];

        $timestamp  = (new \DateTimeImmutable())->format('Y-m-d_H-i-s');
        $backupDir  = storage_path($backupDir);
        $filename   = "{$dbName}_backup_{$timestamp}.sql";
        $path       = "{$backupDir}/{$filename}";

        // make required directory
        if (!File::isDirectory($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        // Cleanup old backups if expired
        if ($this->retentionDays >= 0) {
            $this->cleanupOldBackups($backupDir, $this->retentionDays);
        }

        $fh = fopen($path, 'w');
        if (!$fh) {
            $this->message = sprintf("Cannot open file for writing: [`%s`]", $path);
        } else {
            // check if connection test is okay
            if($this->dbConnect()){
                try {
                    $this->writeHeader($fh, $dbName, $timestamp);
        
                    $tables = $this->getTables($dbName);
                    foreach ($tables as $table) {
                        if (empty($table)) {
                            continue;
                        }
                        // Drop + Create
                        $createStmt = $this->getCreateTableStatement($table);
    
                        // Write table structure
                        fwrite($fh, "\n--\n-- Table structure for table `{$table}`\n--\n\n");
                        fwrite($fh, "{$createStmt};\n\n");
        
                        $this->dumpTableData($fh, $table);
                    }
                    
                    // Write the footer comment
                    $this->writeFooter($fh);
                    
                    // Close the file handle
                    fclose($fh);
        
                    // Compress the backup using
                    $this->saveFileAs($path);
        
                    $this->error   = Constant::STATUS_200;
                    $this->message = "- Database has been exported successfully.";
                } catch (\Throwable $e) {
                    if (is_resource($fh)) {
                        fclose($fh);
                    }

                    $this->message  = "- Performing query: <strong style='color: #000'>{$e->getMessage()}</strong>";
                    $this->error    = Constant::STATUS_400;
                }
            } else{
                $this->message  = $this->db['message'];
            }
        }
        
        return $this->makeResponse();
    }

    /**
     * Create API Response
     * @return \Tamedevelopers\Support\Collections\Collection
     */
    protected function makeResponse()
    {
        /*
        | ----------------------------------------------------------------------------
        | Database importation use. Below are the status code
        | ----------------------------------------------------------------------------
        |   if ->status === 400 (Error Status
        |   if ->status === 200 (Success importing to database
        */ 
        $storagePath = is_null($this->saveAsFileType) ? '' : $this->fileStoragePath;
        $message = is_array($this->message) ? implode('\n<br>', $this->message) : $this->message;

        return new Collection([
            'status'    => $this->error, 
            'path'      => $storagePath, 
            'message'   => $message
        ]);
    }

}
