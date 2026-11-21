<?php

namespace App\Livewire\Shared\Layout;

use App\Models\Battery\BatteryProduct;
use App\Models\Retail\RetailProduct;
use App\Models\Shared\UserActivityLog;
use Livewire\Component;

class NotificationBell extends Component
{
    public string $module = 'retail';

    public string $tab = 'stock';

    public function setTab(string $tab): void
    {
        $this->tab = in_array($tab, ['stock', 'activity'], true) ? $tab : 'stock';
    }

    public function render()
    {
        $isBattery = $this->module === 'battery';

        $lowStockItems = $isBattery
            ? BatteryProduct::query()
                ->whereRaw('CAST(COALESCE(quantity, 0) AS SIGNED) < 10')
                ->orderByRaw('CAST(COALESCE(quantity, 0) AS SIGNED) ASC')
                ->limit(10)
                ->get(['id', 'product_name', 'quantity'])
            : RetailProduct::query()
                ->whereRaw('CAST(COALESCE(quantity, 0) AS SIGNED) < 10')
                ->orderByRaw('CAST(COALESCE(quantity, 0) AS SIGNED) ASC')
                ->limit(10)
                ->get(['id', 'product_name', 'quantity']);

        $lowStockCount = $isBattery
            ? (int) BatteryProduct::whereRaw('CAST(COALESCE(quantity, 0) AS SIGNED) < 10')->count()
            : (int) RetailProduct::whereRaw('CAST(COALESCE(quantity, 0) AS SIGNED) < 10')->count();

        $canSeeActivity = auth()->user()?->isDeveloper() ?? false;
        $recentLogs = $canSeeActivity
            ? UserActivityLog::query()->orderByDesc('id')->limit(8)->get()
            : collect();

        return view('livewire.shared.layout.notification-bell', [
            'isBattery' => $isBattery,
            'lowStockItems' => $lowStockItems,
            'lowStockCount' => $lowStockCount,
            'canSeeActivity' => $canSeeActivity,
            'recentLogs' => $recentLogs,
        ]);
    }
}
