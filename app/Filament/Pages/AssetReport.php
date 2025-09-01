<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Navigation\NavigationGroup;
use App\Models\OtherAsset;
use UnitEnum;

class AssetReport extends Page
{
    protected static string | UnitEnum | null $navigationGroup = 'Report & Audit';
    protected static ?int $navigationSort = 4;
    protected string $view = 'filament.pages.asset-report';

    public $fixedAssets;
    public $movingAssets;
    public $arrears;
    public $nonMovingAssets;
    public $totalAssets;

    public function mount()
    {
        // Dummy logic
        $this->fixedAssets     = OtherAsset::where('value', '>', 10000000)->get();   // dianggap aset tetap
        $this->movingAssets    = OtherAsset::where('value', '<=', 10000000)->get();  // dianggap aset bergerak
        $this->arrears         = OtherAsset::where('description', 'like', '%arrear%')->get(); // dummy utang/piutang
        $this->nonMovingAssets = OtherAsset::where('description', 'like', '%non-move%')->get(); // dummy non moving

        // Total semua aset
        $this->totalAssets = OtherAsset::sum('value');
    }
}





