<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArchiveController extends Controller
{
    public function index()
    {
        // Use DB::table for both queries so trading_date is always a plain string,
        // ensuring the groupBy key lookup in the view matches without any casting.
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

        $itemsByDate = $itemRows->groupBy('trading_date');

        return view('archive.index', [
            'days'        => $days,
            'itemsByDate' => $itemsByDate,
        ]);
    }

    public function destroyDay(string $date)
    {
        abort_unless(preg_match('/^\d{4}-\d{2}-\d{2}$/', $date), 400);

        DB::table('orders')->where('trading_date', $date)->delete();

        return redirect()->route('archive.index');
    }

    public function destroyAll()
    {
        DB::table('orders')->delete();

        return redirect()->route('archive.index');
    }
}
