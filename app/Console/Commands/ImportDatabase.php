<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import data from JSON files in the seeders directory into the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $jsonFiles = $this->getJsonFiles();

        foreach ($jsonFiles as $jsonFile) {
            $tableName = pathinfo($jsonFile, PATHINFO_FILENAME);

            if ($this->confirm("Do you want to import data into table '{$tableName}'?")) {
                $this->importFile($jsonFile, $tableName);
            } else {
                $this->info("Skipping import for table '{$tableName}'.");
            }
        }

        $this->info('Database import completed.');
    }

    private function getJsonFiles()
    {
        // Get all JSON files in the seeders directory
        $jsonFiles = glob(database_path('seeders/*.json'));

        return $jsonFiles;
    }

    /**
     * Import the given JSON file into
     * the database
     * @param string $jsonFile
     * @return boolean
     * @return
     */
    private function importFile($jsonFile, $tableName)
    {
        $jsonData = File::get($jsonFile);
        $data = json_decode($jsonData, true);

        DB::table($tableName)->insert($data);

        $this->info("Data from JSON file '{$jsonFile}' imported into table '{$tableName}'.");
    }
}
