<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class MakeSeederFromTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:table-seeder {table}';

    protected $description = 'Generate a seeder from an existing table';

    public function handle()
    {
        $table = $this->argument('table');

        if (! Schema::hasTable($table)) {
            $this->error("Table {$table} does not exist!");

            return 1;
        }

        $rows = DB::table($table)->get();

        if ($rows->isEmpty()) {
            $this->warn("Table {$table} is empty!");

            return 0;
        }

        $seederName = Str::studly($table).'Seeder';
        $filePath = database_path("seeders/{$seederName}.php");

        $dataArray = $rows->map(fn ($row) => (array) $row)->toArray();

        $export = var_export($dataArray, true);

        $stub = <<<PHP
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class {$seederName} extends Seeder
{
    public function run(): void
    {
        DB::table('{$table}')->insert({$export});
    }
}

PHP;

        File::put($filePath, $stub);

        $this->info("Seeder created: {$filePath}");

        return 0;
    }
}
