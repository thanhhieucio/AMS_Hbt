<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;

class DisableLDAP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hsbit:ldap-disable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lệnh cứu hộ để tắt cấu hình LDAP khi cấu hình sai khiến bạn không đăng nhập được.';

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
        if ($this->confirm("\n****************************************************\nThao tác này sẽ tắt LDAP. Bạn sẽ không thể \nđăng nhập bằng tài khoản chưa tồn tại \ntrong cơ sở dữ liệu cục bộ của HSB-IT. \n****************************************************\n\nBạn có muốn tiếp tục? [y|N]")) {
            $setting = Setting::getSettings();
            $setting->ldap_enabled = 0;
            if ($setting->save()) {
                $this->info('LDAP has been set to disabled.');
            } else {
                $this->info('Unable to disable LDAP.');
            }
        } else {
            $this->info('Canceled. No actions taken.');
        }
    }
}
