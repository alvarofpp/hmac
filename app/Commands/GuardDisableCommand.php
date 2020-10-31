<?php

namespace App\Commands;

use App\Exceptions\UnprotectedDirectoryException;
use App\Models\Directory;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class GuardDisableCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'guard:disable {dir : The directory that will be guarded (required)}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Disables the guard of specified directory';

    /**
     * Execute the console command.
     *
     * @return mixed
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

        $directory->delete();
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
