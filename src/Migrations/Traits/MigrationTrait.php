<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Migrations\Traits;

use Tamedevelopers\Support\Env;
use Tamedevelopers\Support\Str;
use Tamedevelopers\Database\Constant;
use Tamedevelopers\Support\Capsule\File;
use Tamedevelopers\Support\Process\HttpRequest;
use Tamedevelopers\Support\Collections\Collection;

/**
 * 
 * @property mixed $style
 */
trait MigrationTrait{

    private static $database;
    private static $migrations;
    private static $seeders;
    private static $error;
    private static $message;
    private static $storagePath;


    /**
     * Normalize folser structure for migrations and seeders
     *
     * @return void
     */
    private static function normalizeFolderStructure()
    {
        // collection of migration and seeders path
        self::$database     = Env::getServers('server') . "database/";
        self::$migrations   = self::$database . "migrations/";
        self::$seeders      = self::$database . "seeders/";
    }

    /**
     * Get Dummy real path data
     *
     * @return array
     */
    private static function getDummyParts()
    {
        // real path
        $realPath = Str::replace('\\', '/', rtrim(realpath(__DIR__), "/\\"));

        return [
            'default'   => "{$realPath}/../../Dummy/dummyMigration.dum",
            'job'       => "{$realPath}/../../Dummy/dummyJobsMigration.dum",
            'session'   => "{$realPath}/../../Dummy/dummySessionsMigration.dum",
        ];
    }
    
    /**
     * Run Migrations
     *
     * @param  string $table_name
     * @param  string|null $type
     * @return \Tamedevelopers\Support\Collections\Collection
     */
    private static function runMigrationCreateTable($table_name, $type = null) 
    {
        // table name
        $table  = Str::snake($table_name ?? '');
        $type   = Str::lower($type);
        $style  = self::$style;

        // Date convert
        $fileName = Constant::formatMigrationTableName($table);

        // path
        $path = self::getDummyParts();

        // get directory
        $dummyPath = match ($type) {
            !empty($type) && in_array($type, ['job', 'jobs']) => $path['job'],
            !empty($type) && in_array($type, ['session', 'sessions']) => $path['session'],
            default => $path['default'],
        };

        // dummy content
        $dummyContent = Str::replace('{{TABLE}}', $table, File::get($dummyPath));

        // absolute path
        self::$storagePath = self::$migrations . $fileName;

        // browser break
        $isConsole = HttpRequest::runningInConsole();
        $message = [
            'console_error' => "Migration <b>[%s]</b> already exists.",
            'console_success' => "Migration <b>[%s]</b> created successfully.",
            'browser_error' => "<span style='background: #ee0707; {$style}'>Migration %s already exists.</span><br>",
            'browser_success' => "<span style='background: #027b02; {$style}'>Migration %s created successfully.</span><br>",
        ];
        
        if(File::exists(self::$storagePath)){
            self::$error = Constant::STATUS_400;
            self::$message = sprintf(
                $isConsole ? $message['console_error'] : $message['browser_error'],
                self::$storagePath
            );
            return self::makeResponse();
        }

        // start writting
        // Write the contents to the new files
        File::put(self::$storagePath, $dummyContent);

        self::$error = Constant::STATUS_200;
        self::$message = sprintf(
            $isConsole ? $message['console_success'] : $message['browser_success'], 
            self::$storagePath
        );

        return self::makeResponse();
    }
    
    /**
     * Creating Managers
     * 
     * @return mixed
     */
    private static function initBaseDirectory() 
    {
        self::normalizeFolderStructure();

        // check if database folder not exist
        if(!File::isDirectory(self::$database)){
            @File::makeDirectory(self::$database, 0777);

            // gitignore fle path
            $gitignore = sprintf("%s.gitignore", self::$database);

            // create file if not exist
            if (!File::exists($gitignore) && !is_dir($gitignore)) {
                // Write the contents to the new file
                File::put($gitignore, preg_replace(
                    '/^[ \t]+|[ \t]+$/m', '', 
                    ".
                    /database
                    .env"
                ));
            }
        }

        // if migrations folder not found
        if(!File::isDirectory(self::$migrations)){
            File::makeDirectory(self::$migrations, 0777);
        }

        // if seeders folder not found
        if(!File::isDirectory(self::$seeders)){
            File::makeDirectory(self::$seeders, 0777);
        }

        if(!File::isDirectory(self::$migrations)){
            throw new \Exception(
                sprintf("Path to dabatase[dir] not found ---> `%s`", self::$migrations) 
            );
        } 
    }

    /**
     * Getting all files in directory
     * @param string $directory 
     * 
     * @return array|string
     */
    private static function scanDirectoryFiles($directory)
    {
        // read file inside folders
        $files = scandir($directory);

        unset($files[0]);
        unset($files[1]);

        // change value to absolute path to file
        array_walk($files, function(&$value, $index) use($directory) {
            $value = rtrim($directory, '/') . "/{$value}";
        });

        // Sort with enhanced dependency-aware ordering
        return self::sortParentFiles($files);
    }

    /**
     * Getting all files in directory (descending dependency order)
     * For dropping tables safely: ensures children are processed before parents.
     * @param string $directory
     * @return array|string
     */
    private static function scanDirectoryFilesDesc($directory)
    {
        // Ascending, dependency-aware order (parents before children)
        $asc = self::scanDirectoryFiles($directory);

        // Reverse to get children before parents
        return array_reverse($asc);
    }

    /**
     * Sort parent files according to their names and detected foreign key dependencies
     *
     * Ensures parent/base tables (e.g., "blogs_categories") are executed before
     * child tables that reference them (e.g., "blogs" referencing blogs_categories),
     * while also keeping base-before-derivative ordering (e.g., ads before ads_data/info).
     *
     * @param array $files Full paths to migration files
     * @return array
     */
    private static function sortParentFiles(array $files)
    {
        // Helper: parse name cues from filename
        $parseNameFromFilename = function (string $path): array {
            $name = basename($path);
            if (preg_match('/create_(.+)_table\.php$/', $name, $m)) {
                $full = $m[1];
                $parts = explode('_', $full);
                return [
                    'file'      => $path,
                    'name'      => $name,
                    'full'      => $full,          // e.g., blogs, blogs_categories
                    'parts'     => $parts,
                    'base'      => $parts[0] ?? $full,
                    'is_parent' => count($parts) === 1,
                ];
            }
            return [
                'file'      => $path,
                'name'      => $name,
                'full'      => $name,
                'parts'     => [$name],
                'base'      => $name,
                'is_parent' => true,
            ];
        };

        // Helper: extract created table and referenced tables from file content
        $parseMigrationContent = function (string $path) use ($parseNameFromFilename): array {
            $info = $parseNameFromFilename($path);
            try {
                $content = File::get($path);
            } catch (\Throwable $e) {
                $content = '';
            }

            // Detect created table: Schema::create('table', ...) or Schema::create("table", ...)
            $creates = null;
            if (preg_match("/Schema::create\(['\"]([a-zA-Z0-9_]+)['\"]/", $content, $m)) {
                $creates = $m[1];
            } else {
                // fallback to filename cue
                $creates = $info['full'];
            }

            // Detect referenced tables via ->on('table') or ->on("table")
            $refs = [];
            if (preg_match_all("/->on\(['\"]([a-zA-Z0-9_]+)['\"]\)/", $content, $mm)) {
                $refs = array_values(array_unique($mm[1]));
            }

            return [
                'file'    => $path,
                'name'    => $info['name'],
                'base'    => $info['base'],
                'full'    => $info['full'],
                'parts'   => $info['parts'],
                'is_parent' => $info['is_parent'],
                'creates' => $creates,
                'refs'    => $refs,
            ];
        };

        // Parse all migrations
        $nodes = [];
        foreach ($files as $f) {
            // only consider php files
            if (!is_string($f)) continue;
            if (substr($f, -4) !== '.php') continue;
            $nodes[] = $parseMigrationContent($f);
        }

        // Map created table -> node index
        $creatorIndexByTable = [];
        foreach ($nodes as $i => $n) {
            if (!empty($n['creates'])) {
                $creatorIndexByTable[$n['creates']] = $i;
            }
        }

        // Build dependency graph (Kahn): edge creator -> dependent
        $adj = array_fill(0, count($nodes), []);
        $inDegree = array_fill(0, count($nodes), 0);

        foreach ($nodes as $i => $n) {
            foreach ($n['refs'] as $rt) {
                if (isset($creatorIndexByTable[$rt])) {
                    $p = $creatorIndexByTable[$rt]; // parent index
                    if ($p !== $i) {
                        $adj[$p][] = $i;
                        $inDegree[$i]++;
                    }
                }
            }
        }

        // Prepare initial queue (in-degree 0). Use tie-breaker to keep base/parent-first preference
        $queue = [];
        foreach ($nodes as $i => $n) {
            if ($inDegree[$i] === 0) {
                $queue[] = $i;
            }
        }

        $tieBreaker = function (int $a, int $b) use ($nodes) {
            $na = $nodes[$a];
            $nb = $nodes[$b];
            // Same base: parent-first, fewer parts first, then alpha by full
            if ($na['base'] === $nb['base']) {
                if ($na['is_parent'] !== $nb['is_parent']) {
                    return $na['is_parent'] ? -1 : 1;
                }
                $cmpParts = count($na['parts']) <=> count($nb['parts']);
                if ($cmpParts !== 0) return $cmpParts;
                return strcmp($na['full'], $nb['full']);
            }
            // Different bases: alphabetical by created table name fallback
            return strcmp($na['creates'] ?? $na['name'], $nb['creates'] ?? $nb['name']);
        };

        usort($queue, $tieBreaker);

        $ordered = [];
        while (!empty($queue)) {
            $curr = array_shift($queue);
            $ordered[] = $curr;
            foreach ($adj[$curr] as $v) {
                $inDegree[$v]--;
                if ($inDegree[$v] === 0) {
                    $queue[] = $v;
                }
            }
            // keep queue stable
            if (!empty($queue)) {
                usort($queue, $tieBreaker);
            }
        }

        // If there is a cycle or unresolved deps, append remaining in a safe order
        if (count($ordered) < count($nodes)) {
            $remaining = [];
            foreach ($nodes as $i => $_) {
                if (!in_array($i, $ordered, true)) {
                    $remaining[] = $i;
                }
            }
            usort($remaining, $tieBreaker);
            $ordered = array_merge($ordered, $remaining);
        }

        // Map back to file paths in computed order
        $result = [];
        foreach ($ordered as $idx) {
            $result[] = $nodes[$idx]['file'];
        }

        // Fallback: if for some reason result is empty, use filename-based ordering
        if (empty($result)) {
            $result = $files; // as-is
        }

        return $result;
    }

    /**
     * Create API Response
     * @return \Tamedevelopers\Support\Collections\Collection
     */
    protected static function makeResponse()
    {
        /*
        | ----------------------------------------------------------------------------
        | Database importation use. Below are the status code
        | ----------------------------------------------------------------------------
        |   if ->status === 400 (Error Status
        |   if ->status === 200 (Success importing to database
        */ 
        return new Collection([
            'status'    => self::$error,
            'path'      => self::$storagePath, 
            'message'   => self::$message
        ]);
    }

}