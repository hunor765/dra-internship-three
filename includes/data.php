<?php
/**
 * Dummy data store for the "Thread & Stitch" fashion shop.
 *
 * Everything here is fictional. No database is used — this file is the
 * single source of truth for categories, products and product variants.
 *
 * Product shape follows the GA4 ecommerce "item" convention so it maps
 * cleanly onto the dataLayer (item_id, item_name, item_brand, price, etc.).
 */

$STORE = [
    'name'     => 'Thread & Stitch',
    'currency' => 'USD',
];

/* ---------------------------------------------------------------------------
 * Categories
 * ------------------------------------------------------------------------ */
$CATEGORIES = [
    'womens-apparel' => [
        'id'    => 'womens-apparel',
        'name'  => "Women's Apparel",
        'blurb' => 'Dresses, blouses and knits — considered pieces for every day.',
        'color' => '#b56576',
        'icon'  => '👗',
    ],
    'mens-apparel' => [
        'id'    => 'mens-apparel',
        'name'  => "Men's Apparel",
        'blurb' => 'Shirts, trousers and tees cut clean and built to last.',
        'color' => '#34495e',
        'icon'  => '👔',
    ],
    'footwear' => [
        'id'    => 'footwear',
        'name'  => 'Footwear',
        'blurb' => 'Sneakers, boots and sandals that finish the look and go the distance.',
        'color' => '#6d4c41',
        'icon'  => '👟',
    ],
    'accessories' => [
        'id'    => 'accessories',
        'name'  => 'Accessories',
        'blurb' => 'Bags, belts and scarves — the small things that pull an outfit together.',
        'color' => '#b8860b',
        'icon'  => '👜',
    ],
];

/* ---------------------------------------------------------------------------
 * Products
 *
 * "variants" is an optional list of selectable options. When present the
 * product page renders a variant selector and the chosen variant name is
 * sent to GA4 as item_variant.
 * ------------------------------------------------------------------------ */
$PRODUCTS = [
    /* ---- Women's Apparel ---------------------------------------------- */
    'SKU-DRS-001' => [
        'id'       => 'SKU-DRS-001',
        'name'     => 'Linen Wrap Dress',
        'brand'    => 'Thread & Stitch',
        'category' => 'womens-apparel',
        'price'    => 68.00,
        'rating'   => 4.7,
        'desc'     => 'A breezy, mid-length wrap dress in stonewashed linen with a flattering tie waist. Made-up fabric, entirely fictional thread count.',
        'variants' => [
            ['id' => 'xs', 'name' => 'XS'],
            ['id' => 's',  'name' => 'S'],
            ['id' => 'm',  'name' => 'M'],
            ['id' => 'l',  'name' => 'L'],
            ['id' => 'xl', 'name' => 'XL'],
        ],
    ],
    'SKU-BLS-002' => [
        'id'       => 'SKU-BLS-002',
        'name'     => 'Silk Button Blouse',
        'brand'    => 'Thread & Stitch',
        'category' => 'womens-apparel',
        'price'    => 52.00,
        'rating'   => 4.6,
        'desc'     => 'A fluid, relaxed-fit blouse in imaginary mulberry silk with mother-of-pearl buttons. Dresses up or down without complaint.',
        'variants' => [
            ['id' => 'ivory', 'name' => 'Ivory'],
            ['id' => 'blush', 'name' => 'Blush'],
            ['id' => 'black', 'name' => 'Black'],
        ],
    ],
    'SKU-KNT-003' => [
        'id'       => 'SKU-KNT-003',
        'name'     => 'Merino Knit Sweater',
        'brand'    => 'Thread & Stitch',
        'category' => 'womens-apparel',
        'price'    => 74.00,
        'rating'   => 4.8,
        'desc'     => 'A fine-gauge crewneck in soft (pretend) merino that layers under everything. Warm, lightweight and pleasantly itch-free.',
        'variants' => [
            ['id' => 's', 'name' => 'S'],
            ['id' => 'm', 'name' => 'M'],
            ['id' => 'l', 'name' => 'L'],
        ],
    ],

    /* ---- Men's Apparel ------------------------------------------------ */
    'SKU-OXF-004' => [
        'id'       => 'SKU-OXF-004',
        'name'     => 'Oxford Cotton Shirt',
        'brand'    => 'Oakline',
        'category' => 'mens-apparel',
        'price'    => 49.00,
        'rating'   => 4.6,
        'desc'     => 'A tailored button-down in breathable oxford cotton with a hidden button-down collar. The workhorse of an imaginary wardrobe.',
        'variants' => [
            ['id' => 's',  'name' => 'S'],
            ['id' => 'm',  'name' => 'M'],
            ['id' => 'l',  'name' => 'L'],
            ['id' => 'xl', 'name' => 'XL'],
        ],
    ],
    'SKU-CHN-005' => [
        'id'       => 'SKU-CHN-005',
        'name'     => 'Slim Chino Trousers',
        'brand'    => 'Oakline',
        'category' => 'mens-apparel',
        'price'    => 58.00,
        'rating'   => 4.5,
        'desc'     => 'A slim-but-not-skinny chino with a touch of stretch for all-day comfort. Holds a crease, forgives a big lunch.',
        'variants' => [
            ['id' => '30', 'name' => 'W30'],
            ['id' => '32', 'name' => 'W32'],
            ['id' => '34', 'name' => 'W34'],
            ['id' => '36', 'name' => 'W36'],
        ],
    ],
    'SKU-TEE-006' => [
        'id'       => 'SKU-TEE-006',
        'name'     => 'Heavyweight Cotton Tee',
        'brand'    => 'Oakline',
        'category' => 'mens-apparel',
        'price'    => 28.00,
        'rating'   => 4.4,
        'desc'     => 'A structured 240gsm tee that keeps its shape wash after (make-believe) wash. Boxy, modern fit with a clean neckline.',
        'variants' => [
            ['id' => 'black', 'name' => 'Black'],
            ['id' => 'white', 'name' => 'White'],
            ['id' => 'navy',  'name' => 'Navy'],
        ],
    ],

    /* ---- Footwear ----------------------------------------------------- */
    'SKU-SNK-007' => [
        'id'       => 'SKU-SNK-007',
        'name'     => 'Retro Court Sneakers',
        'brand'    => 'Stride',
        'category' => 'footwear',
        'price'    => 89.00,
        'rating'   => 4.7,
        'desc'     => 'A low-profile court sneaker in imaginary full-grain leather with a cushioned footbed. Goes with jeans, chinos, everything.',
        'variants' => [
            ['id' => '7',  'name' => 'US 7'],
            ['id' => '8',  'name' => 'US 8'],
            ['id' => '9',  'name' => 'US 9'],
            ['id' => '10', 'name' => 'US 10'],
            ['id' => '11', 'name' => 'US 11'],
        ],
    ],
    'SKU-BOO-008' => [
        'id'       => 'SKU-BOO-008',
        'name'     => 'Leather Chelsea Boots',
        'brand'    => 'Stride',
        'category' => 'footwear',
        'price'    => 139.00,
        'rating'   => 4.8,
        'desc'     => 'A sleek elastic-gusset Chelsea boot on a lightly lugged sole. Pretend-resoleable, endlessly wearable.',
        'variants' => [
            ['id' => '8',  'name' => 'US 8'],
            ['id' => '9',  'name' => 'US 9'],
            ['id' => '10', 'name' => 'US 10'],
            ['id' => '11', 'name' => 'US 11'],
        ],
    ],
    'SKU-SND-009' => [
        'id'       => 'SKU-SND-009',
        'name'     => 'Woven Slide Sandals',
        'brand'    => 'Stride',
        'category' => 'footwear',
        'price'    => 42.00,
        'rating'   => 4.3,
        'desc'     => 'A minimalist slide with a woven (fictional) leather strap and a contoured cork footbed. Summer, sorted.',
        'variants' => [],
    ],

    /* ---- Accessories -------------------------------------------------- */
    'SKU-BAG-010' => [
        'id'       => 'SKU-BAG-010',
        'name'     => 'Canvas Tote Bag',
        'brand'    => 'Maison Faux',
        'category' => 'accessories',
        'price'    => 38.00,
        'rating'   => 4.5,
        'desc'     => 'A structured heavyweight-canvas tote with a leather-trim handle and an interior pocket. Carries a laptop and a life.',
        'variants' => [
            ['id' => 'natural',  'name' => 'Natural'],
            ['id' => 'charcoal', 'name' => 'Charcoal'],
        ],
    ],
    'SKU-BLT-011' => [
        'id'       => 'SKU-BLT-011',
        'name'     => 'Full-Grain Leather Belt',
        'brand'    => 'Maison Faux',
        'category' => 'accessories',
        'price'    => 34.00,
        'rating'   => 4.6,
        'desc'     => 'A 35mm belt in imaginary full-grain leather with a brushed matte buckle. Ages into something better.',
        'variants' => [
            ['id' => 's', 'name' => 'S'],
            ['id' => 'm', 'name' => 'M'],
            ['id' => 'l', 'name' => 'L'],
        ],
    ],
    'SKU-SCF-012' => [
        'id'       => 'SKU-SCF-012',
        'name'     => 'Wool Blend Scarf',
        'brand'    => 'Maison Faux',
        'category' => 'accessories',
        'price'    => 29.00,
        'rating'   => 4.4,
        'desc'     => 'An oversized, brushed wool-blend scarf that doubles as a travel blanket. Warm without the weight.',
        'variants' => [],
    ],
];

/* ---------------------------------------------------------------------------
 * Promotions (used for view_promotion / select_promotion events)
 * ------------------------------------------------------------------------ */
$PROMOTIONS = [
    [
        'promotion_id'   => 'PROMO_SUMMER25',
        'promotion_name' => 'Summer Style Edit',
        'creative_name'  => 'hero_banner',
        'creative_slot'  => 'homepage_hero',
    ],
];
