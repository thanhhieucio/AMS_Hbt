<?php

namespace App\Console\Commands;

use App\Helpers\Helper;
use Illuminate\Console\Command;

class TestLocationsFMCS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hsbit:test-locations-fmcs {--location_id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kiểm tra sai lệch ID công ty khi dùng FullMultipleCompanySupport với địa điểm theo phạm vi.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Script này kiểm tra sai lệch ID công ty khi dùng Full Multiple Company Support với địa điểm theo phạm vi.');
        $this->info('Quá trình này có thể mất một lúc nếu tập dữ liệu rất lớn.');
        $this->newLine();

        // if parameter location_id is set, only test this location
        $location_id = null;
        if ($this->option('location_id')) {
            $location_id = $this->option('location_id');
        }

        $start = microtime(true);
        $mismatched = Helper::test_locations_fmcs(true, $location_id);
        $elapsed = microtime(true) - $start;

        $this->warn(trans_choice('admin/settings/message.location_scoping.mismatch', count($mismatched)));
        $this->newLine();
        $this->info('Edit your locations to associate them with the correct company.');

        $header = ['Type', 'ID', 'Name', 'Checkout Type',  'Company ID', 'Item Company', 'Item Location', 'Location Company', 'Location Company ID'];
        sort($mismatched);

        $this->table($header, $mismatched);

        $this->newLine();
        $this->info(sprintf('Completed in %.2f seconds.', $elapsed));
    }
}
