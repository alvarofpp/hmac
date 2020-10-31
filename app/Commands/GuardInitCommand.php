<?php

namespace App\Commands;

use App\Classes\HMAC;
use App\Exceptions\DirectoryAlwaysGuardedException;
use App\Models\Directory;
use App\Models\File;
use App\Services\FileManagementService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;

class GuardInitCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'guard:init {dir : The directory that will be guarded (required)}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Starts the guard of specified directory';

    /**
     * Execute the console command.
     *
     * @return bool
     * @throws DirectoryAlwaysGuardedException
     */
    public function handle()
    {
        $dir = $this->argument('dir');
        $hmac = new HMAC();

        if (!file_exists($dir)) {
            throw new DirectoryNotFoundException();
        }

        $directoryExists = Directory::query()
            ->where('absolute_path', realpath($dir))
            ->exists();
        if ($directoryExists) {
            throw new DirectoryAlwaysGuardedException();
        }

        $files = FileManagementService::through($hmac, $dir);

        DB::beginTransaction();
        try {
            // Directories
            $dirs = $files->pluck('dir', 'dir_absolute_path')
                ->unique();

            foreach ($dirs as $absolutePath => $dirname) {
                $directory = Directory::query()
                    ->updateOrCreate([
                        'absolute_path' => $absolutePath,
                    ], [
                        'name' => $dirname,
                    ]);

                // Updates files
                $files = $files->where('dir_absolute_path', $absolutePath)
                    ->transform(function ($file) use ($directory) {

                        return [
                            'name' => $file['filename'],
                            'hmac' => $file['hmac'],
                            'directory_id' => $directory->id,
                        ];
                    });
            }

            // Files
            $files = $files->toArray();
            File::query()
                ->insert($files);

            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * Define the command's schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
