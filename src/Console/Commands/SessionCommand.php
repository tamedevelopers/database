<?php

declare(strict_types=1);

namespace Tamedevelopers\Database\Console\Commands;


use Tamedevelopers\Database\Constant;
use Tamedevelopers\Database\AutoLoader;
use Tamedevelopers\Support\Capsule\File;
use Tamedevelopers\Support\Capsule\Logger;
use Tamedevelopers\Database\Migrations\Migration;
use Tamedevelopers\Support\Capsule\CommandHelper;


class SessionCommand extends CommandHelper
{

    /**
     * Default entry when running commands.
     *
     * @return void
     */
    public function handle()
    {
        $this->handleHeader('session');
        Logger::writeln(' session:clean');
        Logger::writeln(' session:config');
        Logger::writeln(' session:table');
        Logger::writeln('');
    }

    /**
     * Clean expired session files
     */
    public function clean()
    {
        $force = $this->force();

        $clean = $this->CleanFile(
            storage_path('sessions'),
            $force
        );

        if(count($clean) <= 0){
            $this->error("No session file has been deleted.");
            return 0;
        }
        
        $this->info(sprintf(
            "[%s] Session file%s has been deleted.", 
            count($clean),
            count($clean) > 1 ? 's' : ''
        ));
        return 1;
    }

    /**
     * Create a Session configuration file
     */
    public function config()
    {
        $realPath = realpath(__DIR__ . '/../../');
        $autoloader = new AutoLoader();

        $paths = $autoloader->getPathsData($realPath);

        $sessionPath = $paths['session']['path'];

        // Check if session config file already exists
        if(File::exists($sessionPath)) {
            $this->error(sprintf(
                "Session configuration file already exists at: [%s]", 
                config_path('session.php')
            ));
            return 0;
        }

        $autoloader->createSession($paths);

        $this->info(sprintf(
            "Session configuration file created at: [%s]", 
            config_path('session.php')
        ));
        return 1;
    }

    /**
     * Publish a migration table for session database
     */
    public function table()
    {
        $migration = new Migration();

        $response = $migration->create('sessions', 'sessions');

        if($response['status'] != Constant::STATUS_200){
            $this->error($response['message']);
            return 0;
        }
        
        $this->info($response['message']);
        return 1;
    }

    /**
     * Execute the console command.
     * 
     * @param  mixed $path
     * @param  bool $cleanAll
     * @return array
     */
    protected function CleanFile($path = null, $cleanAll = false)
    {
        $check = [];
        
        // Get a list of all files in the session directory
        $files = array_diff(scandir($path), ['.', '..']);

        foreach ($files as $file) {

            // Get the full path of the session file
            $filePath = "{$path}/$file";

            if($cleanAll){
                $check[] = $filePath;

                // Delete the expired session file
                @unlink("{$filePath}");
            } else{
                // Check if the session file is expired
                if ($this->isExpired($filePath)){
                    $check[] = $filePath;
        
                    // Delete the expired session file
                    @unlink("{$filePath}");
                }
            }
        }

        return $check;
    }

    /**
     * Check if a session file is expired based on its creation time.
     *
     * @param string $filePath
     * @return bool
     */
    protected function isExpired($filePath)
    {
        // Get the session lifetime from configuration (in minutes)
        $expirationTime = config('session.lifetime');

        // Get the last modification time of the file
        $lastModifiedTime = filemtime($filePath);

        // Get the current time
        $currentTime = time();

        // Check if the session file is expired
        return ($lastModifiedTime + $expirationTime) < $currentTime;
    }

}