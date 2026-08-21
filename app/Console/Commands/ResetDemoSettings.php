<?php

namespace App\Console\Commands;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class ResetDemoSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hsbit:demo-settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Đặt lại cấu hình demo HSB-IT về mặc định.';

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

        $this->info('Dang dat lai cau hinh demo.');
        $settings = Setting::first();
        $settings->per_page = 20;
        $settings->site_name = 'Demo quan ly tai san HSB-IT';
        $settings->auto_increment_assets = 1;
        $settings->logo = 'hsb-logo.png';
        $settings->alert_email = 'service@hsb-it.io';
        $settings->login_note = "Dung mot trong cac tai khoan sau de dang nhap ban demo:\n\n- `admin` / `password`\n- `assets` / `password`\n- `testuser` / `password`";
        $settings->header_color = '#3c8dbc';
        $settings->link_dark_color = '#5fa4cc';
        $settings->link_light_color = '#296282;';
        $settings->nav_link_color = '#FFFFFF';
        $settings->label2_2d_type = 'QRCODE';
        $settings->default_currency = 'VND';
        $settings->brand = 2;
        $settings->ldap_enabled = 0;
        $settings->full_multiple_companies_support = 0;
        $settings->label2_1d_type = 'C128';
        $settings->email_domain = 'hsb.edu.vn';
        $settings->email_format = 'filastname';
        $settings->username_format = 'filastname';
        $settings->date_display_format = 'D M d, Y';
        $settings->time_display_format = 'g:iA';
        $settings->thumbnail_max_h = '30';
        $settings->locale = 'vi-VN';
        $settings->version_footer = 'on';
        $settings->support_footer = 'on';
        $settings->saml_enabled = '0';
        $settings->saml_sp_x509cert = null;
        $settings->saml_idp_metadata = null;
        $settings->saml_attr_mapping_username = null;
        $settings->saml_forcelogin = '0';
        $settings->saml_slo = null;
        $settings->saml_custom_settings = null;
        $settings->default_avatar = 'default.png';

        $settings->save();

        User::where('username', 'admin')->update([
            'password' => Hash::make('password'),
            'locale' => 'vi-VN',
        ]);

        $this->info('Hoan tat dat lai demo HSB-IT.');
    }
}