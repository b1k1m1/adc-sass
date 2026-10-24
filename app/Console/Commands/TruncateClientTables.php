<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TruncateClientTables extends Command
{
    protected $signature = 'client:truncate-data {--force-run : Actually truncate the tables}';

    protected $description = 'Truncate selected client data tables while keeping setup/dropdown/master tables';

    public function handle()
    {
        /*
         * ONLY put tables here that should be emptied
         * when preparing database for a new client.
         */
        $tables = [
            'members',
            'partattendence',
            'partbp',
            'partadl',
            'appointment',
            'daycare_member_meds',
            'member_coverage_history',
            'shiftaccount',
        ];

        /*
         * These tables should never be truncated by this command.
         */
        $protectedTables = [
            'users',
            'roles',
            'permissions',
            'model_has_roles',
            'model_has_permissions',
            'role_has_permissions',
            'organizations',
            'menus',
            'menu_items',
            'dropdowns',
            'hmodropdown',
        ];

        foreach ($tables as $table) {
            if (in_array($table, $protectedTables)) {
                $this->error("Protected table detected: {$table}. Remove it from truncate list.");
                return Command::FAILURE;
            }
        }

        $currentDatabase = DB::selectOne('select database() as db')->db ?? 'UNKNOWN';

        $this->warn('Current connected database: ' . $currentDatabase);
        $this->newLine();

        $this->warn('The following tables are selected for truncate:');

        foreach ($tables as $table) {
            $this->line(' - ' . $table);
        }

        $this->newLine();

        if (!$this->option('force-run')) {
            $this->info('Dry run only. No tables were truncated.');
            $this->warn('To actually truncate, run:');
            $this->line('php artisan client:truncate-data --force-run');

            return Command::SUCCESS;
        }

        if (!$this->confirm('This will permanently delete all rows from the selected tables in database [' . $currentDatabase . ']. Continue?')) {
            $this->info('Cancelled. No tables were truncated.');
            return Command::SUCCESS;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            foreach ($tables as $table) {
                if (!Schema::hasTable($table)) {
                    $this->warn("Skipped: {$table} table does not exist.");
                    continue;
                }

                DB::table($table)->truncate();

                $this->info("Truncated: {$table}");
            }
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $this->newLine();
        $this->info('Selected client data tables truncated successfully.');

        return Command::SUCCESS;
    }
}
