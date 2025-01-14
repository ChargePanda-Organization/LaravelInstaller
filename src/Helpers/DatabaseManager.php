<?php

namespace RachidLaasri\LaravelInstaller\Helpers;

use Exception;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Console\Output\BufferedOutput;

class DatabaseManager
{
    /**
     * Migrate and seed the database.
     *
     * @return array
     */
    public function migrateAndSeed()
    {
        $outputLog = new BufferedOutput;

        $this->sqlite($outputLog);

        return $this->migrate($outputLog);
    }

    /**
     * Run the migration and call the seeder.
     *
     * @param collection $outputLog
     * @return collection
     */
    private function migrate($outputLog)
    {
        try {
            DB::unprepared(file_get_contents(database_path('schema/mysql-schema.dump')));

            $countries = Storage::disk('local')->get('countries.sql');
            DB::unprepared($countries);

            $states = Storage::disk('local')->get('states.sql');
            DB::unprepared($states);
            Artisan::call('migrate', ["--force" => true], $outputLog);
        } catch (Exception $e) {
            throw ValidationException::withMessages([
                'message' => $outputLog
            ]);
        }

        return $this->seed($outputLog);
    }

    /**
     * Seed the database.
     *
     * @param collection $outputLog
     * @return array
     */
    private function seed($outputLog)
    {
        try{
            Artisan::call('db:seed', ['--force' => true], $outputLog);
        }
        catch(Exception $e){
            throw ValidationException::withMessages([
                'message' => $outputLog
            ]);
        }
    }

    /**
     * Return a formatted error messages.
     *
     * @param $message
     * @param string $status
     * @param collection $outputLog
     * @return array
     */
    private function response($message, $status = 'danger', $outputLog)
    {
        return [
            'status' => $status,
            'message' => $message,
            'dbOutputLog' => $outputLog->fetch()
        ];
    }

    /**
     * check database type. If SQLite, then create the database file.
     *
     * @param collection $outputLog
     */
    private function sqlite($outputLog)
    {
        if(DB::connection() instanceof SQLiteConnection) {
            $database = DB::connection()->getDatabaseName();
            if(!file_exists($database)) {
                touch($database);
                DB::reconnect(Config::get('database.default'));
            }
            $outputLog->write('Using SqlLite database: ' . $database, 1);
        }
    }
}
