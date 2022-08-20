<?php

namespace RachidLaasri\LaravelInstaller\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class Uninstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'installer:uninstall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (app()->environment() !== 'local') {
            if (!$this->confirm("App is running in non-local environment. Are you sure you want to uninstall?", true)) {
                $this->info("Process terminated by user");
                return 0;
            }
        }

        try {
            $this->info("cleaning up database.");

            Schema::dropAllTables();
        } catch (QueryException $e) {
        }

        $this->info("removing .env file.");

        if (file_exists(base_path('.env')))
            unlink(base_path('.env'));

        $this->call('optimize:clear');

        return 0;
    }
}
