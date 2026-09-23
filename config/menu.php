<?php

// Simple static menu config. Move this to a database table later if you
// want staff to edit prices without a deploy — this is fine for v1.

return [
    'burgers' => [
        ['key' => 'classic', 'name' => 'Classic Burger', 'price_pence' => 650],
        ['key' => 'cheese', 'name' => 'Cheeseburger', 'price_pence' => 700],
        ['key' => 'double', 'name' => 'Double Stack', 'price_pence' => 950],
        ['key' => 'veggie', 'name' => 'Veggie Burger', 'price_pence' => 650],
    ],

    'sides' => [
        [
            'key'         => 'loaded_fries',
            'name'        => 'Loaded Fries',
            'price_pence' => 350,   // TODO: set actual price
            'extras'      => [
                ['key' => 'fries_cheese', 'name' => 'Cheese', 'price_pence' => 0],
                ['key' => 'fries_ham',    'name' => 'Ham',    'price_pence' => 0],
            ],
        ],
    ],

    // Discount applied once per qualifying burger+side pair in the same order
    'meal_deals' => [
        ['label' => 'Meal Deal', 'side_key' => 'loaded_fries', 'discount_pence' => 100],
    ],

    // Ingredients that come as standard and can be removed for free
    'standard_ingredients' => [
        'lettuce', 'tomato', 'onion', 'pickles', 'ketchup', 'mustard', 'mayo',
    ],

    // Extras that cost more if added
    'paid_extras' => [
        ['key' => 'bacon', 'name' => 'Bacon', 'price_pence' => 100],
        ['key' => 'extra_cheese', 'name' => 'Extra Cheese', 'price_pence' => 80],
        ['key' => 'fried_egg', 'name' => 'Fried Egg', 'price_pence' => 90],
        ['key' => 'jalapenos', 'name' => 'Jalapeños', 'price_pence' => 50],
    ],
];
