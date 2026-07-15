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

/* ---------------------------------------------------------------------------
 * Contact details (contact.php)
 * ------------------------------------------------------------------------ */
$CONTACT = [
    'email'    => 'hello@threadandstitch.example',
    'phone'    => '+44 20 7946 0577',
    'hours'    => 'Mon–Sat, 9am–6pm (GMT)',
    'address'  => "Thread & Stitch Ltd\n17 Weavers Row\nHallowmere HM2 4TS\nUnited Kingdom",
    'subjects' => [
        'Order enquiry',
        'Sizing & fit advice',
        'Returns and exchanges',
        'Alterations & repairs',
        'Something else',
    ],
];

/* ---------------------------------------------------------------------------
 * Newsletter block (footer + inline placements)
 * ------------------------------------------------------------------------ */
$NEWSLETTER = [
    'heading' => 'New arrivals and honest fit notes',
    'blurb'   => 'What just landed, what runs small, and when the good sizes are about to disappear. One email a fortnight.',
];

/* ---------------------------------------------------------------------------
 * Blog
 * ------------------------------------------------------------------------ */
$BLOG_META = [
    'title' => 'The Cutting Room',
    'intro' => 'Fabric, fit and the unglamorous craft behind clothes that last. Written by people who have unpicked a lot of bad seams.',
];

$BLOG_POSTS = [
    'how-to-read-a-care-label' => [
        'slug'      => 'how-to-read-a-care-label',
        'title'     => 'How to read a care label like a tailor',
        'excerpt'   => 'The composition tag tells you more about how a garment will age than the price tag ever will. Here is how to decode it in ten seconds.',
        'author'    => 'Isolde Marchetti',
        'date'      => '10 March 2026',
        'category'  => 'Fabric',
        'read_time' => 6,
        'icon'      => '🧵',
        'color'     => '#b56576',
        'body'      => [
            ['type' => 'p',  'text' => 'Turn the garment inside out before you look at the price. The little tag at the side seam is the only part of the label that cannot market to you — it is a legally required list of what the thing is actually made of.'],
            ['type' => 'h2', 'text' => 'Percentages are ranked, and the order matters'],
            ['type' => 'p',  'text' => 'Fibres are listed by weight, heaviest first. "95% polyester, 5% wool" is a polyester garment wearing a wool hat. That 5% is there so the word wool can appear on a swing tag, and it will do almost nothing for warmth or breathability.'],
            ['type' => 'h2', 'text' => 'A little elastane is good; a lot is a countdown'],
            ['type' => 'p',  'text' => 'Two to three percent elastane gives you recovery — the garment springs back rather than bagging at the knees and elbows. Above about eight percent, the elastane becomes structural, and elastane is the fibre that degrades first. When it goes, the garment goes with it.'],
            ['type' => 'quote', 'text' => 'Natural fibres age. Blends with heavy elastane expire. There is a real difference, and it is printed on a tag most people never read.'],
            ['type' => 'h2', 'text' => 'The washing symbols are a floor, not a ceiling'],
            ['type' => 'p',  'text' => 'A 40°C symbol means the garment survives 40°C. It does not mean it needs it. Almost everything you own would be happier, and last considerably longer, at 30°C or cold — and your knitwear will thank you for being kept out of the machine altogether.'],
            ['type' => 'h2', 'text' => 'One test in the shop'],
            ['type' => 'p',  'text' => 'Scrunch a section of the fabric in your fist for five seconds and let go. If it stays creased and crumpled, it will crease and crumple on you at 10am and stay that way all day. This tells you more than any amount of description on a website.'],
        ],
    ],
    'capsule-wardrobe' => [
        'slug'      => 'capsule-wardrobe',
        'title'     => 'The capsule wardrobe, minus the sanctimony',
        'excerpt'   => 'You do not need exactly 33 items. You need to notice which ten things you actually reach for, and stop buying the eleventh.',
        'author'    => 'Callum Reyes',
        'date'      => '24 February 2026',
        'category'  => 'Styling',
        'read_time' => 5,
        'icon'      => '👔',
        'color'     => '#34495e',
        'body'      => [
            ['type' => 'p',  'text' => 'Capsule wardrobe advice has become strangely moralistic — as though owning a fourth shirt is a character flaw. It is not. But most of us do own a great many clothes we never wear, and it is worth understanding why.'],
            ['type' => 'h2', 'text' => 'Run the hanger test for one month'],
            ['type' => 'p',  'text' => 'Turn every hanger in your wardrobe to face backwards. When you wear something and hang it back up, turn its hanger the right way round. After a month, look at what is still backwards. That is your answer, and it is usually humbling.'],
            ['type' => 'h2', 'text' => 'The unworn things have a reason'],
            ['type' => 'p',  'text' => 'It is almost never that the garment is ugly. It is that it needs ironing, or it only works with one other item, or the neckline sits wrong and you notice it all day. Write the actual reason on a note. The list will start repeating itself, and the repeats are your real buying criteria.'],
            ['type' => 'quote', 'text' => 'Nobody has ever stood in front of a full wardrobe and thought: my problem is that I do not own enough clothes.'],
            ['type' => 'h2', 'text' => 'Buy for the life you have'],
            ['type' => 'p',  'text' => 'The most common wardrobe mistake is dressing for an imagined life — the formal dinners, the beach holidays, the version of you who irons on a Tuesday. Buy for the Wednesday you will actually have. The aspirational purchases are the ones that stay backwards on the rail.'],
        ],
    ],
    'denim-care' => [
        'slug'      => 'denim-care',
        'title'     => 'Stop washing your denim so much',
        'excerpt'   => 'Every wash strips indigo and weakens cotton. Here is how often you actually need to, and what to do instead.',
        'author'    => 'Isolde Marchetti',
        'date'      => '8 February 2026',
        'category'  => 'Care',
        'read_time' => 4,
        'icon'      => '👖',
        'color'     => '#6d4c41',
        'body'      => [
            ['type' => 'p',  'text' => 'Denim is the most over-washed garment most people own, and it is also the one where washing does the most visible damage. Those two facts are not a coincidence.'],
            ['type' => 'h2', 'text' => 'What a wash actually does'],
            ['type' => 'p',  'text' => 'Indigo sits on the surface of the cotton fibre rather than penetrating it. Every wash cycle abrades that surface, which is why jeans fade uniformly in the machine instead of developing the wear patterns that follow how you actually move.'],
            ['type' => 'h2', 'text' => 'A realistic schedule'],
            ['type' => 'p',  'text' => 'Every ten wears is plenty for most people. Spot-clean marks as they happen. Air them overnight — the smell people are usually washing away is bacteria, and a night on a hanger by an open window handles it entirely.'],
            ['type' => 'p',  'text' => 'And ignore the freezer advice. It has been tested repeatedly and it does not kill the bacteria; it just chills them until they warm back up on your legs.'],
            ['type' => 'quote', 'text' => 'Wash them inside out, cold, and hang them to dry. The tumble dryer is where denim goes to die.'],
            ['type' => 'h2', 'text' => 'The one exception'],
            ['type' => 'p',  'text' => 'Raw, unsanforised denim will shrink on first contact with hot water, sometimes dramatically. If your jeans are raw, follow whatever the maker tells you, because they know exactly how much their fabric will move and you do not.'],
        ],
    ],
    'shoe-fit-guide' => [
        'slug'      => 'shoe-fit-guide',
        'title'     => 'Why your shoes hurt (it is probably not the size)',
        'excerpt'   => 'Length is the number everyone checks and the measurement that matters least. Width and volume are doing the damage.',
        'author'    => 'Callum Reyes',
        'date'      => '19 January 2026',
        'category'  => 'Fit',
        'read_time' => 5,
        'icon'      => '👟',
        'color'     => '#b8860b',
        'body'      => [
            ['type' => 'p',  'text' => 'Someone comes in with a size 9 that hurts and asks for a size 10. The 10 is too long, so it slips at the heel, so they lace it tighter, so it hurts in a new place. The problem was never the length.'],
            ['type' => 'h2', 'text' => 'Three measurements, not one'],
            ['type' => 'p',  'text' => 'A shoe has to fit your length, your width across the ball of the foot, and your volume — the depth from the sole to the top of your instep. Shoe sizing gives you one number for the first of those and quietly assumes the other two.'],
            ['type' => 'h2', 'text' => 'Where it hurts tells you which one is wrong'],
            ['type' => 'p',  'text' => 'Pain across the widest part of the foot is a width problem. Pressure over the top of the foot, or a feeling of being clamped down, is a volume problem. Toes hitting the front, or a heel that lifts as you walk, is genuinely a length problem — and that one is the least common of the three.'],
            ['type' => 'quote', 'text' => 'Buy shoes in the late afternoon. Your feet swell across the day, and a shoe fitted at 9am is a shoe that hurts by 5pm.'],
            ['type' => 'h2', 'text' => 'Leather moves; synthetics do not'],
            ['type' => 'p',  'text' => 'A leather shoe that is snug across the width will relax to your foot within a fortnight. A synthetic upper that is snug will be exactly as snug in a year. Be honest with yourself about which material you are buying before you tell yourself it will break in.'],
        ],
    ],
];

/* ---------------------------------------------------------------------------
 * Store locations
 * ------------------------------------------------------------------------ */
$LOCATIONS = [
    'hallowmere-flagship' => [
        'id'          => 'hallowmere-flagship',
        'name'        => 'Hallowmere Flagship',
        'city'        => 'Hallowmere',
        'country'     => 'United Kingdom',
        'address'     => '17 Weavers Row',
        'phone'       => '+44 20 7946 0577',
        'hours_short' => 'Open daily · 10am–7pm',
        'flagship'    => true,
        'icon'        => '🧵',
        'color'       => '#b56576',
        'blurb'       => 'Our largest shop, with an in-house alterations room on the first floor. Anything you buy here can be taken in, let out or shortened, usually within the week.',
        'services'    => [
            'In-house alterations and tailoring',
            'Personal fitting appointments',
            'Free repairs for the life of the garment',
            'Full size range carried in store',
            'Click & collect within two hours',
        ],
        'hours'       => [
            'Monday'    => '10:00 – 19:00',
            'Tuesday'   => '10:00 – 19:00',
            'Wednesday' => '10:00 – 19:00',
            'Thursday'  => '10:00 – 20:00',
            'Friday'    => '10:00 – 20:00',
            'Saturday'  => '9:30 – 19:00',
            'Sunday'    => '11:00 – 17:00',
        ],
    ],
    'lintwick-lane' => [
        'id'          => 'lintwick-lane',
        'name'        => 'Lintwick Lane',
        'city'        => 'Lintwick',
        'country'     => 'United Kingdom',
        'address'     => '4 Bobbin Court',
        'phone'       => '+44 113 496 0233',
        'hours_short' => 'Mon–Sat · 9am–6pm',
        'flagship'    => false,
        'icon'        => '👗',
        'color'       => '#b8860b',
        'blurb'       => 'A smaller shop with an unusually deep footwear range and staff who will measure your feet properly rather than guessing from your last pair.',
        'services'    => [
            'Full foot measurement — width and volume',
            'Extended footwear sizing',
            'Shoe repair and resoling service',
            'Same-day delivery within the city',
        ],
        'hours'       => [
            'Monday'    => '9:00 – 18:00',
            'Tuesday'   => '9:00 – 18:00',
            'Wednesday' => '9:00 – 18:00',
            'Thursday'  => '9:00 – 18:00',
            'Friday'    => '9:00 – 18:00',
            'Saturday'  => '9:00 – 18:00',
            'Sunday'    => 'Closed',
        ],
    ],
    'selby-wharf-outlet' => [
        'id'          => 'selby-wharf-outlet',
        'name'        => 'Selby Wharf Outlet',
        'city'        => 'Selby Wharf',
        'country'     => 'United Kingdom',
        'address'     => 'Unit 9, The Old Mill',
        'phone'       => '+44 1482 555 019',
        'hours_short' => 'Thu–Sun · 10am–6pm',
        'flagship'    => false,
        'icon'        => '👜',
        'color'       => '#34495e',
        'blurb'       => 'End-of-season stock, sample pieces and the occasional beautiful mistake from the cutting room. Open four days a week, and worth the trip.',
        'services'    => [
            'End-of-season and sample stock',
            'Reduced pieces with minor faults, clearly marked',
            'No alterations on outlet items',
            'Cash and card, no reservations',
        ],
        'hours'       => [
            'Monday'    => 'Closed',
            'Tuesday'   => 'Closed',
            'Wednesday' => 'Closed',
            'Thursday'  => '10:00 – 18:00',
            'Friday'    => '10:00 – 18:00',
            'Saturday'  => '9:30 – 18:00',
            'Sunday'    => '11:00 – 16:00',
        ],
    ],
];
