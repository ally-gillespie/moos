<?php

namespace App\Livewire;

use App\Services\MenuService;
use App\Services\OrderBuilder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app', params: ['bodyClass' => 'bg-neutral-50 min-h-screen'])]
#[Title("POS — Moo's")]
class PosForm extends Component
{
    public array $cart = [];
    public string $customerName = '';
    public string $phoneNumber = '';
    public bool $smsOptIn = false;
    public string $paymentMethod = 'card';
    public ?int $confirmedOrderNumber = null;

    #[Computed(persist: true)]
    public function menu(): array
    {
        return app(MenuService::class)->getMenu();
    }

    public function mount(): void
    {
        $this->addItem();
    }

    public function addItem(): void
    {
        $menu = $this->menu();
        $firstCategory = $menu['categories'][0] ?? null;
        $firstItem = $firstCategory['items'][0] ?? null;

        $this->cart[] = [
            'key'              => $firstItem['key'] ?? '',
            'name'             => $firstItem['name'] ?? '',
            'unit_price_pence' => $firstItem['price_pence'] ?? 0,
            'category'         => $firstCategory['key'] ?? '',
            'quantity'         => 1,
            'removed'          => [],
            'added'            => [],
        ];
    }

    public function removeItem(int $index): void
    {
        array_splice($this->cart, $index, 1);
    }

    public function changeItem(int $index, string $key): void
    {
        $menu = $this->menu();
        foreach ($menu['categories'] as $cat) {
            foreach ($cat['items'] as $item) {
                if ($item['key'] === $key) {
                    $this->cart[$index]['key']              = $item['key'];
                    $this->cart[$index]['name']             = $item['name'];
                    $this->cart[$index]['unit_price_pence'] = $item['price_pence'];
                    $this->cart[$index]['category']         = $cat['key'];
                    $this->cart[$index]['removed']          = [];
                    $this->cart[$index]['added']            = [];
                    return;
                }
            }
        }
    }

    public function toggleRemoved(int $index, string $ingredient): void
    {
        $removed = $this->cart[$index]['removed'] ?? [];
        $pos = array_search($ingredient, $removed, true);

        if ($pos !== false) {
            array_splice($removed, $pos, 1);
        } else {
            $removed[] = $ingredient;
        }

        $this->cart[$index]['removed'] = array_values($removed);
    }

    public function toggleExtra(int $index, string $extraKey, int $extraPrice): void
    {
        $added = $this->cart[$index]['added'] ?? [];

        foreach ($added as $i => $a) {
            if ($a['ingredient'] === $extraKey) {
                array_splice($added, $i, 1);
                $this->cart[$index]['added'] = array_values($added);
                return;
            }
        }

        $added[] = [
            'ingredient'        => $extraKey,
            'extra_price_pence' => $extraPrice,
        ];
        $this->cart[$index]['added'] = $added;
    }

    public function getItemExtras(string $itemKey): array
    {
        $menu = $this->menu();
        foreach ($menu['categories'] as $cat) {
            foreach ($cat['items'] as $item) {
                if ($item['key'] === $itemKey) {
                    return $item['extras'] ?? [];
                }
            }
        }
        return [];
    }

    public function getTotal(): int
    {
        $menu = $this->menu();
        $burgerKeys = array_column($menu['burgers'], 'key');
        $itemKeys   = array_column($this->cart, 'key');

        $total = 0;
        foreach ($this->cart as $item) {
            $lineTotal = ($item['unit_price_pence'] * $item['quantity']);
            foreach ($item['added'] ?? [] as $extra) {
                $lineTotal += ($extra['extra_price_pence'] ?? 0) * $item['quantity'];
            }
            $total += $lineTotal;
        }

        $hasBurger = (bool) array_intersect($burgerKeys, $itemKeys);
        foreach ($menu['meal_deals'] as $deal) {
            if ($hasBurger && in_array($deal['side_key'], $itemKeys)) {
                $total -= $deal['discount_pence'];
            }
        }

        return max(0, $total);
    }

    public function getMealDealSaving(): int
    {
        $menu = $this->menu();
        $burgerKeys = array_column($menu['burgers'], 'key');
        $itemKeys   = array_column($this->cart, 'key');

        $saving = 0;
        $hasBurger = (bool) array_intersect($burgerKeys, $itemKeys);
        foreach ($menu['meal_deals'] as $deal) {
            if ($hasBurger && in_array($deal['side_key'], $itemKeys)) {
                $saving += $deal['discount_pence'];
            }
        }

        return $saving;
    }

    public function submit(OrderBuilder $builder): void
    {
        if (empty($this->cart)) {
            return;
        }

        $order = $builder->build(
            $this->cart,
            'pos',
            session('staff_id'),
            $this->customerName ?: null,
            $this->phoneNumber ?: null,
            $this->smsOptIn,
        );

        $builder->markPaidAndQueue($order);

        $this->confirmedOrderNumber = $order->order_number;
        $this->cart = [];
        $this->customerName = '';
        $this->phoneNumber = '';
        $this->smsOptIn = false;
        $this->paymentMethod = 'card';
        $this->addItem();
    }

    public function render()
    {
        $menu = $this->menu();

        $extrasMap = [];
        foreach ($menu['categories'] as $cat) {
            foreach ($cat['items'] as $item) {
                $extrasMap[$item['key']] = $item['extras'] ?? [];
            }
        }

        return view('livewire.pos-form', [
            'menu'      => $menu,
            'extrasMap' => $extrasMap,
        ]);
    }
}
