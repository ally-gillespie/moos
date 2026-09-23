<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title("Archive — Moo's")]
class Archive extends Component
{
    public string $period = 'day';

    public function deleteDay(string $date): void
    {
        abort_unless(preg_match('/^\d{4}-\d{2}-\d{2}$/', $date), 400);
        DB::table('orders')->where('trading_date', $date)->delete();
    }

    public function deleteAll(): void
    {
        DB::table('orders')->delete();
    }

    public function render()
    {
        $days = DB::table('orders')
            ->where('payment_status', 'paid')
            ->select(
                'trading_date',
                DB::raw('SUM(total_pence) as total_pence'),
                DB::raw('COUNT(*) as order_count')
            )
            ->groupBy('trading_date')
            ->orderByDesc('trading_date')
            ->get();

        $itemRows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.payment_status', 'paid')
            ->select(
                'orders.trading_date',
                'order_items.item_name',
                DB::raw('SUM(order_items.quantity) as qty'),
                DB::raw('SUM(order_items.quantity * order_items.unit_price_pence) as item_total_pence')
            )
            ->groupBy('orders.trading_date', 'order_items.item_name')
            ->orderBy('orders.trading_date', 'desc')
            ->orderByDesc(DB::raw('SUM(order_items.quantity)'))
            ->get();

        $groups = match ($this->period) {
            'week'  => $this->groupByWeek($days, $itemRows),
            'month' => $this->groupByMonth($days, $itemRows),
            default => $this->groupByDay($days, $itemRows),
        };

        return view('livewire.archive', [
            'groups'            => $groups,
            'allTimeTotalPence' => $days->sum('total_pence'),
        ]);
    }

    private function groupByDay($days, $itemRows): \Illuminate\Support\Collection
    {
        $itemsByDate = $itemRows->groupBy('trading_date');

        return $days->map(fn ($d) => [
            'key'         => $d->trading_date,
            'label'       => Carbon::parse($d->trading_date)->format('l j F Y'),
            'total_pence' => $d->total_pence,
            'order_count' => $d->order_count,
            'items'       => $itemsByDate->get($d->trading_date, collect()),
            'deletable'   => $d->trading_date,
        ]);
    }

    private function groupByWeek($days, $itemRows): \Illuminate\Support\Collection
    {
        $weekKey = fn ($date) => Carbon::parse($date)->startOfWeek(Carbon::MONDAY)->format('Y-m-d');

        $itemsByWeek = $itemRows
            ->groupBy(fn ($r) => $weekKey($r->trading_date))
            ->map(fn ($rows) => $rows
                ->groupBy('item_name')
                ->map(fn ($g, $name) => (object) [
                    'item_name'        => $name,
                    'qty'              => $g->sum('qty'),
                    'item_total_pence' => $g->sum('item_total_pence'),
                ])
                ->sortByDesc('qty')
                ->values()
            );

        return $days
            ->groupBy(fn ($d) => $weekKey($d->trading_date))
            ->map(function ($weekDays, $weekStart) use ($itemsByWeek) {
                $weekEnd = Carbon::parse($weekStart)->addDays(6);
                return [
                    'key'         => $weekStart,
                    'label'       => Carbon::parse($weekStart)->format('j M') . ' – ' . $weekEnd->format('j M Y'),
                    'total_pence' => $weekDays->sum('total_pence'),
                    'order_count' => $weekDays->sum('order_count'),
                    'items'       => $itemsByWeek->get($weekStart, collect()),
                    'deletable'   => null,
                ];
            })
            ->sortKeysDesc()
            ->values();
    }

    private function groupByMonth($days, $itemRows): \Illuminate\Support\Collection
    {
        $monthKey = fn ($date) => Carbon::parse($date)->format('Y-m');

        $itemsByMonth = $itemRows
            ->groupBy(fn ($r) => $monthKey($r->trading_date))
            ->map(fn ($rows) => $rows
                ->groupBy('item_name')
                ->map(fn ($g, $name) => (object) [
                    'item_name'        => $name,
                    'qty'              => $g->sum('qty'),
                    'item_total_pence' => $g->sum('item_total_pence'),
                ])
                ->sortByDesc('qty')
                ->values()
            );

        return $days
            ->groupBy(fn ($d) => $monthKey($d->trading_date))
            ->map(function ($monthDays, $yearMonth) use ($itemsByMonth) {
                return [
                    'key'         => $yearMonth,
                    'label'       => Carbon::parse($yearMonth . '-01')->format('F Y'),
                    'total_pence' => $monthDays->sum('total_pence'),
                    'order_count' => $monthDays->sum('order_count'),
                    'items'       => $itemsByMonth->get($yearMonth, collect()),
                    'deletable'   => null,
                ];
            })
            ->sortKeysDesc()
            ->values();
    }
}
