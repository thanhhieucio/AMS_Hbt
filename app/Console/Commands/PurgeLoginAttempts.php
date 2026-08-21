<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PurgeLoginAttempts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hsbit:purge-logins';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xóa bảng login_attempts.';

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
        if ($this->confirm("\n****************************************************\nTHAO TÁC NÀY SẼ XÓA TOÀN BỘ BẢN GHI THỬ ĐĂNG NHẬP. \nKhông thể hoàn tác! \n****************************************************\n\nBạn có muốn tiếp tục? Không thể hoàn tác! [y|N]")) {
            \DB::statement('delete from login_attempts');
        }
    }
}
