<?php

namespace App\Commands;

use App\Models\Setting;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class SettingKeyCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'setting:key {key : New key to HMAC (required)}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Defines the new key to HMAC algorithm';

    /**
     * Execute the console command.
     *
     * @return bool
     */
    public function handle()
    {
        $key = $this->argument('key');

        Setting::query()
            ->updateOrCreate([
                'name' => 'hmac_key',
            ], [
                'value' => $key,
            ]);

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
