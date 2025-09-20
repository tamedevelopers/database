<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Traits;

use PDO;
use Tamedevelopers\Support\Str;
use Tamedevelopers\Support\Zip;
use Tamedevelopers\Database\Constant;
use Tamedevelopers\Support\Capsule\File;

/**
 * Trait DBExportTrait
 *
 * Contains helper methods for DBExport functionality.
 */
trait DBExportTrait
{

    /**
     * Check Database connection 
     * 
     * @return bool
    */
    private function dbConnect()
    {
        return $this->db['status'] == Constant::STATUS_200;
    }
    
    /**
     * Write the SQL header to the file handle.
     *
     * @param resource $fh File handle
     * @param string $dbName Database name
     * @param string $timestamp Backup timestamp
     */
    protected function writeHeader($fh, string $dbName, string $timestamp): void
    {
        $config = $this->db['config'];
        $pdo = $this->db['pdo'];

        // Get MySQL/MariaDB server version dynamically
        $serverVersion = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);

        // Get client version
        $clientVersion = $pdo->getAttribute(PDO::ATTR_CLIENT_VERSION);

        // PHP version
        $phpVersion    = PHP_VERSION;

        // Fallback values if not available
        $serverVersion  = $serverVersion ?: 'Unknown';
        $phpVersion     = $phpVersion ?: 'Unknown';
        $parentClass    = $this::class;

        fwrite($fh, "-- {$parentClass}\n");
        fwrite($fh, "-- Database: `{$dbName}`\n");
        fwrite($fh, "-- Date: {$timestamp}\n\n");

        fwrite($fh, "SET FOREIGN_KEY_CHECKS=0;\n");
        fwrite($fh, "SET AUTOCOMMIT=0;\n");

        fwrite($fh, "-- phpMyAdmin SQL Dump\n");
        fwrite($fh, "-- version 5.2.0\n");
        fwrite($fh, "-- https://www.phpmyadmin.net/\n--\n");
        fwrite($fh, "-- Host: {$config['host']}\n");
        fwrite($fh, "-- Generation Time: {$timestamp}\n");
        fwrite($fh, "-- Server version: {$serverVersion}\n");
        fwrite($fh, "-- PHP Version: {$phpVersion}\n\n");

        fwrite($fh, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
        fwrite($fh, "START TRANSACTION;\n");
        fwrite($fh, "SET time_zone = \"+00:00\";\n\n");

        fwrite($fh, "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n");
        fwrite($fh, "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n");
        fwrite($fh, "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n");
        fwrite($fh, "/*!40101 SET NAMES utf8mb4 */;\n\n");

        fwrite($fh, "--\n");
        fwrite($fh, "-- Database: `{$dbName}`\n");
        fwrite($fh, "--\n\n");

        fwrite($fh, "-- --------------------------------------------------------\n");
    }

    /**
     * Write the SQL footer to the file handle.
     *
     * @param resource $fh File handle
     */
    protected function writeFooter($fh): void
    {
        fwrite($fh, "COMMIT;\n");
        fwrite($fh, "SET FOREIGN_KEY_CHECKS=1;\n");
    }

    /**
     * Retrieve all tables in the database.
     *
     * @param string $dbName Database name
     * @return array List of table names
     */
    protected function getTables(string $dbName): array
    {
        $rows = $this->conn->select('SHOW TABLES');

        // The key is "tables_in_{$dbName}"
        $key = 'tables_in_' . $dbName;

        return tcollect($rows)
                ->map(fn($row) => $row->$key)
                ->values()
                ->all();
    }

    /**
     * Return the CREATE TABLE statement for a given table.
     *
     * @param string $table Table name
     * @return string CREATE TABLE statement
     */
    protected function getCreateTableStatement(string $table): string
    {
        $row = $this->conn->selectOne("SHOW CREATE TABLE `{$table}`");
        
        // SHOW CREATE TABLE returns two columns: Table, Create Table
        // but their keys differ by PDO driver; let's be defensive:
        $values = $row->toArray();

        // PDO driver differences: some return 'Create Table', some 'create table'
        $createTableSql = $values['Create Table'] ?? $values['create table'] ?? '';
        
        return $createTableSql;
    }

    /**
     * Dump all rows of a table to the file handle as batched INSERTs.
     * Uses UTF-8 (utf8mb4) normalization and PDO::quote for safe escaping.
     *
     * @param resource $fh   File handle to write the SQL
     * @param string   $table Table name
     * @return void
     */
    protected function dumpTableData($fh, string $table): void
    {
        // 1) Get total row count
        $count = (int) $this->conn->table($table)->count();
        if ($count === 0) {
            return; // nothing to dump
        }

        // 2) Get column names (case-insensitive + driver-agnostic)
        $columns = $this->conn->query("DESCRIBE `{$table}`")->get()->toArray();

        // Pick only field names
        $fields = tcollect($columns)
                    ->map(fn ($row) => $row['field'])
                    ->values()
                    ->all();

        // Prepare column list for INSERT statement
        $columnListSql = '`' . implode('`, `', $fields) . '`';

        // Write section header
        fwrite($fh, "\n--\n-- Dumping data for table `{$table}`\n--\n\n");

        // 3) Fetch rows in batches to avoid memory issues
        $batchSize = $this->insertBatchSize;
        $offset = 0;

        do {
            $rows = $this->conn->table($table)
                            ->query("SELECT * FROM `{$table}` LIMIT {$offset}, {$batchSize}")
                            ->get()
                            ->toArray();

            if (empty($rows)) {
                break;
            }

            $batch = [];
            foreach ($rows as $row) {
                $rowArray = (array) $row; // ensure associative access by column name
                $values = [];

                // Ensure values are ordered to match $fields/columnListSql
                foreach ($fields as $field) {
                    $value = $rowArray[$field] ?? null;

                    $values[] = $this->toSqlValue($value);
                }

                $batch[] = '(' . implode(', ', $values) . ')';
            }

            if (!empty($batch)) {
                $insertSql = "INSERT INTO `{$table}` ({$columnListSql}) VALUES\n"
                    . implode(",\n", $batch)
                    . ";\n";

                // Write as UTF-8 (no BOM). fwrite writes bytes as-is.
                fwrite($fh, $insertSql);
            }

            $offset += $batchSize;
        } while (count($rows) === $batchSize);

        // Finalize the section
        fwrite($fh, "\n-- --------------------------------------------------------\n");
    }

    /**
     * Convert a PHP value into a SQL literal.
     *
     * @param mixed $value
     * @return string
     */
    protected function toSqlValue($value): string
    {
        $pdo = $this->db['pdo'];

        if ($value === null) {
            return 'NULL';
        } elseif (is_int($value) || is_float($value)) {
            return (string) $value;
        } elseif (is_bool($value)) {
            return $value ? '1' : '0';
        } elseif (is_resource($value)) {
            return $pdo->quote(stream_get_contents($value));
        } else {
            // Normalize and sanitize Unicode to avoid ambiguous characters and invalid sequences
            $string = (string) $value;

            // Replace non-breaking space with regular space
            $string = str_replace("\xC2\xA0", ' ', $string);

            // Strip zero-width and directional marks (BOM, ZWSP, ZWNJ, ZWJ, WORD JOINER, SHY, bidi controls)
            $string = preg_replace('/[\x{FEFF}\x{200B}\x{200C}\x{200D}\x{2060}\x{00AD}\x{200E}\x{200F}\x{202A}-\x{202E}\x{2066}-\x{2069}]/u', '', $string);

            // Unicode normalization (if intl extension is available)
            if (class_exists('Normalizer')) {
                $string = \Normalizer::normalize($string, \Normalizer::FORM_C) ?: $string;
            }

            // Ensure valid UTF-8 (drop invalid sequences quietly)
            $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $string);
            if ($converted !== false) {
                $string = $converted;
            }

            // Quote as utf8mb4 literal to guarantee correct interpretation by MySQL
            return $pdo->quote($string);
        }
    }

    /**
     * Save Exported Files into a Zip compressed folder
     *
     * @param [type] $path
     * @return void
     */
    protected function saveFileAs($path)
    {
        $storagePath = Str::replace('.sql', "", dirname($path));
        $storageFilePath = "{$storagePath}.{$this->saveAsFileType}";

        // save to the instance
        $this->fileStoragePath = $storageFilePath;

        // Create a zip file if Type is not null
        if(!is_null($this->saveAsFileType)) {
            Zip::zip($storagePath, $storageFilePath);
        }
    }

    /**
     * Remove old backups exceeding the retention period.
     *
     * @param string $directory Backup directory
     * @param int $days Retention period in days
     */
    protected function cleanupOldBackups(string $directory, int $days): void
    {
        $files = scandir($directory);
        $now = new \DateTimeImmutable();

        foreach ($files as $file) {
            if (!preg_match('/\.sql(\.(gz|zip|rar))?$/i', $file)) {
                continue;
            }

            $filePath = $directory . DIRECTORY_SEPARATOR . $file;
            $lastModified = (new \DateTimeImmutable())->setTimestamp(filemtime($filePath));
            $interval = $now->diff($lastModified)->days;

            if ($interval >= $days) {
                File::delete($filePath);
            }
        }
    }
}
