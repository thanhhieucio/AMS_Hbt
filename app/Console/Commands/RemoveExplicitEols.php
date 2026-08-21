<?php

namespace App\Console\Commands;

use App\Models\Asset;
use App\Models\AssetModel;
use Illuminate\Console\Command;

class RemoveExplicitEols extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hsbit:remove-explicit-eols {--model_name= : The name of the asset model to update (use "all" to update all models)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xóa EOL đặt trực tiếp trên tài sản thuộc model đã chọn để tài sản kế thừa EOL từ model.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);

        if ($this->option('model_name') == 'all') {
            $assets = Asset::all();
            $this->updateAssets($assets);
        } else {
            $assetModel = AssetModel::where('name', '=', $this->option('model_name'))->first();

            if ($assetModel) {
                $assets = Asset::where('model_id', '=', $assetModel->id)->get();
                $this->updateAssets($assets);
            } else {
                $this->error('Asset model not found');
            }
        }
        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime);
        $this->info('Command executed in '.round($executionTime, 2).' seconds.');
    }

    private function updateAssets($assets)
    {
        foreach ($assets as $asset) {
            $asset->eol_explicit = 0;
            $asset->asset_eol_date = null;
            $asset->save();
        }

        $this->info($assets->count().' Assets updated successfully');
    }
}
