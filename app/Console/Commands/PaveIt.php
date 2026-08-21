<?php

namespace App\Console\Commands;

use App\Models\CustomField;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PaveIt extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hsbit:pave  {--force : Skip the interactive yes/no prompt for confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dọn dữ liệu trong các bảng, giữ lại migration, cấu trúc bảng và người dùng đầu tiên. Chủ yếu dùng nhanh cho môi trường phát triển. Muốn xóa cả bảng, dùng php artisan db:wipe.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        if (! $this->option('force')) {
            $confirmation = $this->confirm("\n****************************************************\nTHAO TÁC NÀY SẼ XÓA TOÀN BỘ DỮ LIỆU TRONG CƠ SỞ DỮ LIỆU. \nKhông thể hoàn tác. Toàn bộ dữ liệu sẽ bị xóa, \nBAO GỒM cả các bảng không thuộc HSB-IT trong cơ sở dữ liệu này. \n****************************************************\n\nBạn có muốn tiếp tục? Không thể hoàn tác! ");
            if (! $confirmation) {
                $this->error('ABORTING');
                exit(-1);
            }
        }

        // List all the tables in the database so we don't have to worry about missing some as the app grows
        $tables = Schema::getTables();
        $except_tables = [
            'oauth_access_tokens',
            'oauth_clients',
            'oauth_personal_access_clients',
            'migrations',
            'settings',
            'users',
            'telescope_entries',
            'telescope_entries_tags',
            'telescope_monitoring',
        ];

        // We only need to find out what these are so we can nuke these columns on the assets table.
        $custom_fields = CustomField::get();
        foreach ($custom_fields as $custom_field) {
            $this->info('DROP the '.$custom_field->db_column.' column from assets as well.');

            if (Schema::hasColumn('assets', $custom_field->db_column)) {
                Schema::table('assets', function ($table) use ($custom_field) {
                    $table->dropColumn($custom_field->db_column);
                });
            }
        }

        foreach ($tables as $table_obj) {
            $table = $table_obj['name'];
            if (in_array($table, $except_tables)) {
                $this->info($table.' is SKIPPED.');
            } else {
                \DB::statement('truncate '.$table);
                $this->info($table.' is TRUNCATED.');
            }
        }

        // Leave in the demo oauth keys so we don't have to reset them every day in the demos
        DB::statement('delete from oauth_clients WHERE id > 2');
        DB::statement('delete from oauth_access_tokens WHERE user_id > 2');

    }
}
