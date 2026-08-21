<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class RegenerateAssetTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hsbit:regenerate-tags {--start=} {--output= : info|warn|error|all} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tạo lại toàn bộ thẻ tài sản. Lệnh này có thể phá hủy dữ liệu, cần dùng thận trọng.';

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
        if ($this->confirm('Thao tác này sẽ tạo lại toàn bộ thẻ tài sản trong hệ thống. Đây là thao tác có thể phá hủy dữ liệu, cần dùng thận trọng. Bạn có muốn tiếp tục?')) {
            $output['info'] = [];
            $output['warn'] = [];
            $output['error'] = [];
            $settings = Setting::getSettings();

            $start_tag = ($this->option('start')) ? $this->option('start') : (($settings->next_auto_tag_base) ? Setting::getSettings()->next_auto_tag_base : 1);

            $this->info('Bắt đầu từ '.$start_tag);

            $total_assets = Asset::orderBy('id', 'asc')->get();
            $bar = $this->output->createProgressBar(count($total_assets));

            try {
                Artisan::call('backup:run');
            } catch (\Exception $e) {
                $output['error'][] = $e;
            }

            foreach ($total_assets as $asset) {

                $output['info'][] = 'Asset tag:'.$asset->asset_tag;
                $asset->asset_tag = $settings->auto_increment_prefix.$settings->auto_increment_prefix.$start_tag;

                if ($settings->zerofill_count > 0) {
                    $asset->asset_tag = $settings->auto_increment_prefix.Asset::zerofill($start_tag, $settings->zerofill_count);
                }

                $output['info'][] = 'New Asset tag:'.$asset->asset_tag;

                // Use forceSave here to override model level validation
                $asset->forceSave();
                $start_tag++;
                if ($bar) {
                    $bar->advance();
                }
            }

            $settings->next_auto_tag_base = Asset::zerofill($start_tag, $settings->zerofill_count);
            $settings->save();

            $bar->finish();
            $this->info("\n");

            if (($this->option('output') == 'all') || ($this->option('output') == 'info')) {
                foreach ($output['info'] as $key => $output_text) {
                    $this->info($output_text);
                }
            }
            if (($this->option('output') == 'all') || ($this->option('output') == 'warn')) {
                foreach ($output['warn'] as $key => $output_text) {
                    $this->warn($output_text);
                }
            }
            if (($this->option('output') == 'all') || ($this->option('output') == 'error')) {
                foreach ($output['error'] as $key => $output_text) {
                    $this->error($output_text);
                }
            }
        }
    }
}
