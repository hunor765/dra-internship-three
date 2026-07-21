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
    'icon'     => '👗',
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
        'options' => [
            ['id' => 'monogram', 'label' => 'Monogram', 'choices' => [
                ['id' => 'none',     'name' => 'No monogram', 'price' => 0],
                ['id' => 'initials', 'name' => 'Add initials', 'price' => 6.00],
            ]],
            ['id' => 'wrap', 'label' => 'Gift wrapping', 'choices' => [
                ['id' => 'none', 'name' => 'No wrapping',   'price' => 0],
                ['id' => 'box',  'name' => '+ Gift box',     'price' => 5.00],
            ]],
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
        'options' => [
            ['id' => 'monogram', 'label' => 'Monogram', 'choices' => [
                ['id' => 'none',     'name' => 'No monogram',   'price' => 0],
                ['id' => 'initials', 'name' => 'Cuff initials',  'price' => 7.00],
            ]],
            ['id' => 'giftbox', 'label' => 'Gift box', 'choices' => [
                ['id' => 'none', 'name' => 'No gift box',  'price' => 0],
                ['id' => 'box',  'name' => '+ Gift box',    'price' => 5.00],
            ]],
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
        'options' => [
            ['id' => 'hemming', 'label' => 'Hemming', 'choices' => [
                ['id' => 'standard', 'name' => 'Standard length',  'price' => 0],
                ['id' => 'tailored', 'name' => 'Tailored hem',     'price' => 8.00],
            ]],
            ['id' => 'wrap', 'label' => 'Gift wrapping', 'choices' => [
                ['id' => 'none', 'name' => 'No wrapping', 'price' => 0],
                ['id' => 'box',  'name' => '+ Gift box',   'price' => 5.00],
            ]],
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
        'options' => [
            ['id' => 'laces', 'label' => 'Extra laces', 'choices' => [
                ['id' => 'none', 'name' => 'No extra laces',  'price' => 0],
                ['id' => 'pair', 'name' => '+ 2 spare pairs',  'price' => 6.00],
            ]],
            ['id' => 'protect', 'label' => 'Protection', 'choices' => [
                ['id' => 'none',  'name' => 'No spray',            'price' => 0],
                ['id' => 'spray', 'name' => '+ Waterproof spray',   'price' => 8.00],
            ]],
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
        'options' => [
            ['id' => 'monogram', 'label' => 'Monogram', 'choices' => [
                ['id' => 'none',     'name' => 'No monogram', 'price' => 0],
                ['id' => 'initials', 'name' => 'Add initials', 'price' => 6.00],
            ]],
            ['id' => 'pouch', 'label' => 'Inner pouch', 'choices' => [
                ['id' => 'none', 'name' => 'No pouch',        'price' => 0],
                ['id' => 'zip',  'name' => '+ Zip pouch',      'price' => 9.00],
            ]],
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
 * Coupon / promo codes (advanced ecommerce).
 *
 * Applied on the cart; the code + discount ride through begin_checkout,
 * add_shipping_info, add_payment_info and purchase as the GA4 `coupon` param,
 * and the discount reduces the order value.
 * ------------------------------------------------------------------------ */
$COUPONS = [
    'THREAD10' => ['code' => 'THREAD10', 'type' => 'percent', 'amount' => 10,    'label' => '10% off your order'],
    'STYLE15'  => ['code' => 'STYLE15',  'type' => 'fixed',   'amount' => 15.00, 'label' => '$15 off your order'],
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
 * Product personalization quiz (quiz.php)
 *
 * Four questions; on completion the client picks a random handful of products
 * from the catalog as "recommendations" and fires quiz_complete +
 * view_item_list. The answers themselves don't filter — the match is random.
 * ------------------------------------------------------------------------ */
$QUIZ = [
    'id'               => 'style_match',
    'nav_label'        => 'Style quiz',
    'title'            => 'Find your style match',
    'intro'            => 'Answer four quick questions and we\'ll pull a few pieces from the shop that could be a good fit.',
    'result_list_id'   => 'quiz_recommendations',
    'result_list_name' => 'Quiz Recommendations',
    'result_count'     => 3,
    'questions'        => [
        ['id' => 'occasion', 'q' => 'What are you shopping for?', 'a' => ['Everyday basics', 'A special occasion', 'Workwear', 'A wardrobe refresh']],
        ['id' => 'style',    'q' => 'Your style leans…',          'a' => ['Classic & timeless', 'Relaxed & casual', 'Bold & trend-led', 'Minimal & clean']],
        ['id' => 'fabric',   'q' => 'Which fabrics do you love?', 'a' => ['Natural & breathable', 'Cosy knits', 'Structured & crisp', 'Whatever feels soft']],
        ['id' => 'palette',  'q' => 'Your go-to palette?',        'a' => ['Neutrals', 'Earth tones', 'Monochrome', 'Pops of colour']],
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

/* ---------------------------------------------------------------------------
 * DOWNLOADABLE DOCUMENTS
 *
 * Each entry is rendered to a real PDF on demand by download.php (see
 * includes/pdf.php) — nothing binary is committed. A document either belongs
 * to a product ('product' => SKU) and shows on that product page, or is
 * site-wide ('product' => null) and shows only in the resource centre.
 *
 * 'type' and 'label' ride along on the file_download event so downloads can be
 * segmented in GA4 by what kind of document was taken.
 * ------------------------------------------------------------------------- */
$DOCUMENTS = [
    'fabric-care-linen-dress' => [
        'id'      => 'fabric-care-linen-dress',
        'title'   => 'Linen Wrap Dress — Fabric & Care Guide',
        'label'   => 'Fabric & care guide',
        'type'    => 'care_guide',
        'product' => 'SKU-DRS-001',
        'file'    => 'linen-wrap-dress-care.pdf',
        'summary' => 'Fibre composition, washing temperatures, drying, pressing and how linen is meant to age.',
        'sections' => [
            ['heading' => 'Composition', 'body' => [
                '- Shell: 100% European flax linen, 185gsm, garment-washed.',
                '- Thread: cotton-wrapped polyester for seam strength.',
                'The fabric is washed before making up, so the finished garment has already taken most of its shrinkage. Expect no more than 1% further movement in the first wash.',
            ]],
            ['heading' => 'Washing', 'body' => [
                'Machine wash at 30C on a gentle cycle, inside out, with similar colours. Use a mild detergent without optical brighteners; brighteners flatten the natural slub that gives linen its surface.',
                '- Do not use fabric softener. It coats the fibre and stops linen softening naturally.',
                '- Do not bleach, including oxygen bleach on the dyed colourways.',
                '- Wash separately for the first two washes. Garment-dyed linen releases a little colour early on.',
            ]],
            ['heading' => 'Drying and pressing', 'body' => [
                'Line dry in shade. Direct sun will fade the dye unevenly along the fold lines. Tumble drying on low is possible but will shorten the life of the fibre noticeably over a season.',
                'Press while slightly damp on a medium-hot iron, or hang in a steamy bathroom and let the creases drop. Linen is not supposed to look pressed flat; a soft crease is the intended finish.',
            ]],
            ['heading' => 'How it should age', 'body' => [
                'Linen softens and lightens with every wash for roughly the first twenty, then stabilises. Slubs and small irregularities in the weave are characteristic of flax and are not faults.',
                'Wear will show first at the wrap tie and the underarm seam. Both are reinforced, and both can be repaired by any tailor without disturbing the drape.',
            ]],
        ],
    ],

    'size-guide-oxford-shirt' => [
        'id'      => 'size-guide-oxford-shirt',
        'title'   => 'Oxford Cotton Shirt — Size & Fit Guide',
        'label'   => 'Size & fit guide',
        'type'    => 'size_guide',
        'product' => 'SKU-OXF-004',
        'file'    => 'oxford-cotton-shirt-size-guide.pdf',
        'summary' => 'Body measurements, garment measurements, how the fit runs and what changes after washing.',
        'sections' => [
            ['heading' => 'How to measure yourself', 'body' => [
                'Measure over a thin layer, not bare skin, and keep the tape level.',
                '- Chest: around the fullest part, under the arms, arms down.',
                '- Neck: around the base of the neck, one finger under the tape.',
                '- Sleeve: from the centre back of the neck, over the shoulder, to the wrist bone.',
            ]],
            ['heading' => 'Body measurements by size (cm)', 'body' => [
                '- S: chest 92-97, neck 38, sleeve 84',
                '- M: chest 98-103, neck 40, sleeve 86',
                '- L: chest 104-110, neck 42, sleeve 88',
                '- XL: chest 111-117, neck 44, sleeve 90',
                '- XXL: chest 118-124, neck 46, sleeve 92',
            ]],
            ['heading' => 'Garment measurements, laid flat (cm)', 'body' => [
                'These are the shirt itself, not the body it fits. Chest is measured across, one inch below the armhole, and doubles to give the full circumference.',
                '- S: chest 55, length 74, shoulder 44',
                '- M: chest 58, length 76, shoulder 46',
                '- L: chest 61, length 78, shoulder 48',
                '- XL: chest 64, length 80, shoulder 50',
                '- XXL: chest 67, length 82, shoulder 52',
            ]],
            ['heading' => 'Fit notes', 'body' => [
                'The cut is a classic fit — room through the chest and a straight body, meant to be worn tucked or loose. If you prefer a close fit, size down; the shoulder is the seam that will tell you whether you have gone too far.',
                'Oxford cotton shrinks about 2% in length on the first hot wash and effectively nothing thereafter. Sleeve and body length are cut with that allowance already included, so wash at 30C and the sizing above holds.',
            ]],
        ],
    ],

    'materials-chelsea-boots' => [
        'id'      => 'materials-chelsea-boots',
        'title'   => 'Leather Chelsea Boots — Materials & Construction',
        'label'   => 'Materials & construction',
        'type'    => 'technical_spec',
        'product' => 'SKU-BOO-008',
        'file'    => 'leather-chelsea-boots-construction.pdf',
        'summary' => 'Leather grade, sole construction, resoling, break-in and long-term care.',
        'sections' => [
            ['heading' => 'Materials', 'body' => [
                '- Upper: full-grain calf leather, 1.4-1.6mm, vegetable tanned.',
                '- Lining: unlined vamp, calf leather quarter lining.',
                '- Elastic: twin-gore natural rubber and polyester panel.',
                '- Sole: leather midsole with a 6mm rubber outsole.',
                'Full-grain means the outermost layer of the hide is intact rather than sanded and re-embossed. It marks more easily at first and wears far better over years.',
            ]],
            ['heading' => 'Construction', 'body' => [
                'Goodyear welted. The upper, welt and midsole are stitched together, and the outsole is stitched to the welt — so the outsole can be removed and replaced without disturbing the upper.',
                'A welted boot can be resoled many times. Take them to a cobbler when the rubber has worn to within 2mm of the stitching; leaving it later risks damage to the welt itself, which is a much bigger repair.',
            ]],
            ['heading' => 'Break-in', 'body' => [
                'Expect 2 to 3 weeks of regular wear. The unlined vamp will mould to the top of your foot and the heel will soften and stop slipping. A small amount of heel lift when new is normal and correct.',
                'Do not try to speed this up with heat or water. Both will dry the leather and set it in a shape that has nothing to do with your foot.',
            ]],
            ['heading' => 'Care', 'body' => [
                'Brush after wearing. Condition every 20 to 30 wears with a neutral cream, more often in winter if you walk in salt.',
                'Use cedar trees between wears. They hold the shape and pull moisture out of the lining, which is the single thing that most extends the life of a leather boot.',
                'Dry wet boots at room temperature, away from radiators, with the trees in.',
            ]],
        ],
    ],

    'size-chart-full' => [
        'id'      => 'size-chart-full',
        'title'   => 'Full Size Chart — All Categories',
        'label'   => 'Full size chart',
        'type'    => 'size_guide',
        'product' => null,
        'file'    => 'thread-and-stitch-size-chart.pdf',
        'summary' => 'Every size across dresses, tops, knitwear, trousers and footwear, with international conversions.',
        'sections' => [
            ['heading' => 'Dresses, blouses and knitwear (cm)', 'body' => [
                '- XS: bust 82, waist 64, hip 90',
                '- S: bust 86, waist 68, hip 94',
                '- M: bust 90, waist 72, hip 98',
                '- L: bust 96, waist 78, hip 104',
                '- XL: bust 102, waist 84, hip 110',
            ]],
            ['heading' => 'Shirts and trousers (cm)', 'body' => [
                '- S: chest 92-97, waist 76-81',
                '- M: chest 98-103, waist 82-87',
                '- L: chest 104-110, waist 88-94',
                '- XL: chest 111-117, waist 95-101',
                'Chino trousers are cut with a 2cm allowance at the waistband so they can be taken in without disturbing the pocket bags.',
            ]],
            ['heading' => 'Footwear conversions', 'body' => [
                '- EU 40 = UK 6.5 = US 7.5 = 25.5cm',
                '- EU 41 = UK 7.5 = US 8.5 = 26.0cm',
                '- EU 42 = UK 8 = US 9 = 26.5cm',
                '- EU 43 = UK 9 = US 10 = 27.5cm',
                '- EU 44 = UK 9.5 = US 10.5 = 28.0cm',
                'Measure your foot in the evening, standing, heel against a wall. Feet swell through the day and a boot fitted in the morning will feel tight by six.',
            ]],
            ['heading' => 'Between sizes?', 'body' => [
                'Size up for knitwear and outerwear, size down for anything with elastic or a wrap tie. Our returns are free, so ordering two sizes and sending one back is a perfectly reasonable way to settle it.',
            ]],
        ],
    ],

    'catalogue-current' => [
        'id'      => 'catalogue-current',
        'title'   => 'Thread & Stitch — Product Catalogue',
        'label'   => 'Product catalogue',
        'type'    => 'catalogue',
        'product' => null,
        'file'    => 'thread-and-stitch-catalogue.pdf',
        'summary' => 'The full range with prices, in a printable list.',
        'sections' => [],   // built from the live catalogue by download.php
    ],
];
