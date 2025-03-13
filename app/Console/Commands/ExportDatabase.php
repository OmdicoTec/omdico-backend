<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
class ExportDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'app:export-database';
    protected $signature = 'export:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export data from all tables in the database to JSON files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tables = $this->getTables();

        foreach ($tables as $table) {
            $this->exportTable($table);
        }

        $this->info('Database export completed.');
    }

    /**
     * Get all tables in the database.
     *
     * @return array
     */
    private function getTables()
    {
        // Get the list of all tables in the database
        $tables = DB::connection('sqlite')->getDoctrineSchemaManager()->listTableNames();

        return $tables;
    }
    /**
     * Export the given table to a JSON file.
     *
     * @param  string  $table
     * @return void
     */
    private function exportTable($table)
    {
        $data = DB::connection('sqlite')->table($table)->get()->toArray();

        // Convert the data to JSON
        $jsonData = json_encode($data, JSON_PRETTY_PRINT);

        // Save the JSON data to a file
        $jsonFilePath = database_path("seeders/{$table}.json");
        File::put($jsonFilePath, $jsonData);

        $this->info("Data from table '{$table}' saved to JSON file: {$jsonFilePath}");
    }
}
