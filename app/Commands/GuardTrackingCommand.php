<?php

namespace App\Commands;

use App\Classes\HMAC;
use App\Exceptions\UnprotectedDirectoryException;
use App\Models\Directory;
use App\Services\FileManagementService;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class GuardTrackingCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'guard:tracking {dir : The directory that will be guarded (required)}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Tracking of the specified directory';

    /**
     * Execute the console command.
     *
     * @return bool
     * @throws UnprotectedDirectoryException
     */
    public function handle()
    {
        $dir = $this->argument('dir');

        if (!file_exists($dir)) {
            throw new DirectoryNotFoundException();
        }

        $directory = Directory::query()
            ->where('absolute_path', realpath($dir))
            ->first();
        if (is_null($directory)) {
            throw new UnprotectedDirectoryException();
        }

        $hmac = new HMAC();
        $files = FileManagementService::through($hmac, $dir);
        FileManagementService::tracking($files);

        return true;
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
