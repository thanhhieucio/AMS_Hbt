<?php

namespace App\Console\Commands;

use App\Models\SamlNonce;
use Illuminate\Console\Command;

class SamlClearExpiredNonces extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saml:clear_expired_nonces';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xóa các xác nhận SAML đã hết hạn khỏi bảng saml_nonces.';

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
     * @return int
     */
    public function handle()
    {
        SamlNonce::where('not_valid_after', '<=', now())->delete();

        return 0;
    }
}
