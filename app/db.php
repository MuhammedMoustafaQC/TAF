<?php
/**
 * الاتصال بقاعدة البيانات + إنشاء الجداول + البيانات المبدئية
 */

function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;

    $path = config('db_path');
    $isNew = !file_exists($path);
    if (!is_dir(dirname($path))) mkdir(dirname($path), 0775, true);

    $pdo = new PDO('sqlite:' . $path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA journal_mode = WAL');

    migrate($pdo);
    if ($isNew || !(int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn()) {
        seed($pdo);
    }
    return $pdo;
}

function q(string $sql, array $params = []): PDOStatement
{
    $st = db()->prepare($sql);
    $st->execute($params);
    return $st;
}

function row(string $sql, array $params = []): ?array
{
    $r = q($sql, $params)->fetch();
    return $r ?: null;
}

function rows(string $sql, array $params = []): array
{
    return q($sql, $params)->fetchAll();
}

function val(string $sql, array $params = [])
{
    return q($sql, $params)->fetchColumn();
}

function insert(string $table, array $data): int
{
    if (!isset($data['created_at']) && in_array($table, ['orders', 'service_requests', 'messages', 'admins', 'products'], true)) {
        $data['created_at'] = date('Y-m-d H:i:s'); // بتوقيت القاهرة
    }
    $cols = array_keys($data);
    $sql = sprintf(
        'INSERT INTO %s (%s) VALUES (%s)',
        $table,
        implode(',', $cols),
        implode(',', array_map(fn($c) => ':' . $c, $cols))
    );
    q($sql, $data);
    return (int)db()->lastInsertId();
}

function update(string $table, int $id, array $data): void
{
    $set = implode(',', array_map(fn($c) => "$c = :$c", array_keys($data)));
    $data['__id'] = $id;
    q("UPDATE $table SET $set WHERE id = :__id", $data);
}

function migrate(PDO $pdo): void
{
    $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS settings (
    key   TEXT PRIMARY KEY,
    value TEXT
);
CREATE TABLE IF NOT EXISTS admins (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    name          TEXT NOT NULL,
    username      TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    last_login    TEXT,
    created_at    TEXT DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS categories (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT NOT NULL,
    slug        TEXT NOT NULL UNIQUE,
    description TEXT,
    image       TEXT,
    sort_order  INTEGER DEFAULT 0,
    is_active   INTEGER DEFAULT 1
);
CREATE TABLE IF NOT EXISTS products (
    id                INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id       INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    name              TEXT NOT NULL,
    slug              TEXT NOT NULL UNIQUE,
    short_description TEXT,
    description       TEXT,
    specs             TEXT,
    price             REAL NOT NULL DEFAULT 0,
    old_price         REAL,
    stock             INTEGER NOT NULL DEFAULT 0,
    image             TEXT,
    gallery           TEXT,
    badge             TEXT,
    warranty          TEXT,
    is_featured       INTEGER DEFAULT 0,
    is_active         INTEGER DEFAULT 1,
    sales_count       INTEGER DEFAULT 0,
    views             INTEGER DEFAULT 0,
    created_at        TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at        TEXT DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS orders (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    code           TEXT NOT NULL UNIQUE,
    customer_name  TEXT NOT NULL,
    phone          TEXT NOT NULL,
    phone2         TEXT,
    governorate    TEXT,
    city           TEXT,
    address        TEXT,
    notes          TEXT,
    payment_method TEXT DEFAULT 'cod',
    subtotal       REAL DEFAULT 0,
    shipping       REAL DEFAULT 0,
    total          REAL DEFAULT 0,
    status         TEXT DEFAULT 'new',
    admin_notes    TEXT,
    created_at     TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at     TEXT DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS order_items (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id     INTEGER NOT NULL REFERENCES orders(id) ON DELETE CASCADE,
    product_id   INTEGER,
    product_name TEXT NOT NULL,
    price        REAL NOT NULL,
    qty          INTEGER NOT NULL,
    total        REAL NOT NULL
);
CREATE TABLE IF NOT EXISTS service_requests (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    name           TEXT NOT NULL,
    phone          TEXT NOT NULL,
    governorate    TEXT,
    address        TEXT,
    service_type   TEXT,
    preferred_date TEXT,
    notes          TEXT,
    status         TEXT DEFAULT 'new',
    created_at     TEXT DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS messages (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT NOT NULL,
    phone      TEXT,
    email      TEXT,
    subject    TEXT,
    message    TEXT NOT NULL,
    is_read    INTEGER DEFAULT 0,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS slides (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    title       TEXT NOT NULL,
    subtitle    TEXT,
    button_text TEXT,
    button_link TEXT,
    image       TEXT,
    sort_order  INTEGER DEFAULT 0,
    is_active   INTEGER DEFAULT 1
);
CREATE TABLE IF NOT EXISTS testimonials (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    name       TEXT NOT NULL,
    city       TEXT,
    content    TEXT NOT NULL,
    rating     INTEGER DEFAULT 5,
    sort_order INTEGER DEFAULT 0,
    is_active  INTEGER DEFAULT 1
);
CREATE TABLE IF NOT EXISTS faqs (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    question   TEXT NOT NULL,
    answer     TEXT NOT NULL,
    sort_order INTEGER DEFAULT 0,
    is_active  INTEGER DEFAULT 1
);
CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id);
CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status);
CREATE INDEX IF NOT EXISTS idx_orders_created ON orders(created_at);
SQL);
}

function seed(PDO $pdo): void
{
    $pdo->beginTransaction();

    // ---------- الإعدادات ----------
    $settings = [
        'site_name'          => 'قطرات الندى',
        'site_tagline'       => 'لأنظمة معالجة المياه',
        'meta_description'   => 'قطرات الندى لأنظمة معالجة المياه — فلاتر مياه 7 مراحل و5 مراحل، شمعات وقطع غيار، فلاتر المنزل بالكامل ومحطات التحلية، مع تركيب وصيانة في كل محافظات مصر.',
        'announcement'       => 'تركيب مجاني وضمان حقيقي على كل فلاتر الـ 7 مراحل — اطلب دلوقتي والدفع عند الاستلام',
        'phone'              => '01000000000',
        'phone2'             => '',
        'whatsapp'           => '201000000000',
        'email'              => 'info@qataratelnada.com',
        'address'            => 'القاهرة، جمهورية مصر العربية',
        'working_hours'      => 'يومياً من 10 صباحاً حتى 10 مساءً',
        'facebook'           => '#',
        'instagram'          => '#',
        'tiktok'             => '#',
        'youtube'            => '#',
        'shipping_fee'       => '50',
        'free_shipping_min'  => '3000',
        'wallet_number'      => '01000000000',
        'enable_wallet'      => '1',
        'about_text'         => "قطرات الندى شركة مصرية متخصصة في أنظمة معالجة وتنقية المياه للمنازل والشركات والمصانع.\nبنوفّر فلاتر مياه بأعلى جودة، وقطع غيار أصلية، وفريق فني محترف للتركيب والصيانة الدورية في كل محافظات مصر.\nهدفنا إن كل بيت مصري يشرب مياه نقية وصحية بسعر مناسب وخدمة ما بعد البيع يعتمد عليها.",
        'stat_years'         => '10',
        'stat_customers'     => '15000',
        'stat_governorates'  => '27',
        'stat_technicians'   => '40',
    ];
    $st = $pdo->prepare('INSERT OR REPLACE INTO settings (key, value) VALUES (?, ?)');
    foreach ($settings as $k => $v) $st->execute([$k, $v]);

    // ---------- المدير ----------
    $a = config('default_admin');
    $pdo->prepare('INSERT OR IGNORE INTO admins (name, username, password_hash) VALUES (?, ?, ?)')
        ->execute([$a['name'], $a['username'], password_hash($a['password'], PASSWORD_DEFAULT)]);

    if ((int)$pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn() > 0) {
        $pdo->commit();
        return;
    }

    // ---------- الأقسام ----------
    $cats = [
        ['فلاتر 7 مراحل',            'filters-7-stages',  'فلاتر أوزموسيس عكسي 7 مراحل لمياه نقية وصحية بطعم ممتاز.', 'assets/img/products/ro-7.svg'],
        ['فلاتر 5 مراحل',            'filters-5-stages',  'فلاتر 5 مراحل اقتصادية بكفاءة تنقية عالية.',               'assets/img/products/ro-5.svg'],
        ['شمعات وقطع غيار',          'cartridges',        'شمعات أصلية وممبرين وقطع غيار لكل أنواع الفلاتر.',          'assets/img/products/cartridges-set.svg'],
        ['فلاتر المنزل بالكامل',     'whole-house',       'فلاتر جامبو وسوفتنر لحماية كل مواسير وأجهزة البيت.',        'assets/img/products/jumbo.svg'],
        ['مبردات مياه',              'water-coolers',     'مبردات مياه ساخن وبارد بفلتر مدمج.',                         'assets/img/products/cooler.svg'],
        ['محطات تحلية وتنقية',       'ro-plants',         'محطات تنقية وتحلية للشركات والمصانع والمدارس.',             'assets/img/products/ro-plant.svg'],
    ];
    $catIds = [];
    $st = $pdo->prepare('INSERT INTO categories (name, slug, description, image, sort_order) VALUES (?, ?, ?, ?, ?)');
    foreach ($cats as $i => $c) {
        $st->execute([$c[0], $c[1], $c[2], $c[3], $i + 1]);
        $catIds[$c[1]] = (int)$pdo->lastInsertId();
    }

    // ---------- المنتجات ----------
    $p = fn($cat, $name, $slug, $short, $price, $old, $stock, $img, $badge, $featured, $sales, $warranty, $specs, $desc) => compact(
        'cat', 'name', 'slug', 'short', 'price', 'old', 'stock', 'img', 'badge', 'featured', 'sales', 'warranty', 'specs', 'desc'
    );
    $ro7desc = "فلتر 7 مراحل بتقنية التناضح العكسي (Reverse Osmosis) بيشيل لحد 99% من الأملاح الذائبة والرواسب والكلور والمعادن الثقيلة والبكتيريا.\nمناسب لمياه الشرب والطبخ، وبيحافظ على طعم المياه ورائحتها.\nالسعر شامل التركيب المجاني ومتابعة فنية بعد التركيب.";
    $products = [
        $p('filters-7-stages', 'فلتر قطرات الندى 7 مراحل تايواني', 'ro-7-taiwan', 'فلتر أوزموسيس عكسي 7 مراحل بمكونات تايوانية أصلية وخزان 12 لتر.', 3950, 4600, 25, 'ro-7.svg', 'الأكثر مبيعاً', 1, 184, 'ضمان 5 سنوات على الهيكل', "عدد المراحل: 7 مراحل\nبلد المنشأ: تايوان\nسعة الممبرين: 75 جالون/يوم\nسعة الخزان: 12 لتر\nنسبة إزالة الأملاح: حتى 99%\nالتركيب: مجاني", $ro7desc),
        $p('filters-7-stages', 'فلتر 7 مراحل بالموتور لضعف الضغط', 'ro-7-pump', 'فلتر 7 مراحل مزود بموتور رفع ضغط مناسب للأدوار العالية وضعف المياه.', 4850, 5400, 14, 'ro-pump.svg', 'جديد', 1, 96, 'ضمان سنتين على الموتور', "عدد المراحل: 7 مراحل\nموتور رفع ضغط: 24 فولت\nسعة الممبرين: 75 جالون/يوم\nسعة الخزان: 12 لتر\nمناسب لـ: ضعف الضغط والأدوار العالية", $ro7desc),
        $p('filters-7-stages', 'فلتر 7 مراحل ألكالاين (قلوي)', 'ro-7-alkaline', 'مرحلة ألكالاين لرفع الـ pH وإضافة معادن مفيدة للجسم.', 4450, 5000, 18, 'ro-7.svg', 'خصم', 1, 120, 'ضمان 5 سنوات على الهيكل', "عدد المراحل: 7 مراحل\nمرحلة إضافية: ألكالاين + معادن\nسعة الممبرين: 75 جالون/يوم\nسعة الخزان: 12 لتر", $ro7desc),
        $p('filters-5-stages', 'فلتر 5 مراحل أوزموسيس عكسي', 'ro-5-classic', 'فلتر 5 مراحل اقتصادي بكفاءة تنقية عالية وخزان 8 لتر.', 3200, 3700, 30, 'ro-5.svg', '', 1, 143, 'ضمان 3 سنوات على الهيكل', "عدد المراحل: 5 مراحل\nسعة الممبرين: 50 جالون/يوم\nسعة الخزان: 8 لتر\nالتركيب: مجاني", "فلتر 5 مراحل مناسب للأسر الصغيرة، بيوفر مياه نقية بسعر اقتصادي.\nكل الشمعات متوفرة عندنا بأسعار مناسبة."),
        $p('filters-5-stages', 'فلتر 5 مراحل كوري', 'ro-5-korean', 'مكونات كورية بجودة عالية وتصميم موفر للمساحة تحت الحوض.', 3600, null, 12, 'ro-5.svg', '', 0, 58, 'ضمان 3 سنوات على الهيكل', "عدد المراحل: 5 مراحل\nبلد المنشأ: كوريا\nسعة الممبرين: 75 جالون/يوم\nسعة الخزان: 8 لتر", "فلتر كوري 5 مراحل بخامات عالية الجودة وعمر افتراضي طويل للشمعات."),
        $p('cartridges', 'طقم شمعات المراحل الأولى (3 شمعات)', 'cartridges-set-3', 'طقم تغيير دوري للمراحل 1 و2 و3 — يتغير كل 3 إلى 6 شهور.', 450, 550, 120, 'cartridges-set.svg', 'عرض', 1, 410, 'شمعات أصلية', "يحتوي على: شمعة PP + شمعة كربون حبيبي + شمعة كربون بلوك\nالمقاس: 10 بوصة\nالتغيير: كل 3–6 شهور", "تغيير الشمعات في معادها بيحافظ على الممبرين وعلى جودة المياه.\nمتوافقة مع كل الفلاتر القياسية 10 بوصة."),
        $p('cartridges', 'ممبرين 75 جالون', 'membrane-75', 'ممبرين أوزموسيس عكسي أصلي بكفاءة إزالة أملاح عالية.', 650, 750, 60, 'membrane.svg', '', 0, 165, 'أصلي', "السعة: 75 جالون/يوم\nنسبة إزالة الأملاح: 96–99%\nالتغيير: كل 18–24 شهر", "قلب الفلتر — الممبرين هو المسؤول عن إزالة الأملاح والمعادن الثقيلة."),
        $p('cartridges', 'شمعة بولي بروبلين 10 بوصة', 'pp-sediment-10', 'شمعة الترسيب الأولى لإزالة الرمل والصدأ والشوائب.', 90, null, 300, 'sediment.svg', '', 0, 520, '', "النوع: PP Sediment\nالدقة: 5 ميكرون\nالمقاس: 10 بوصة", "شمعة المرحلة الأولى، بتحمي باقي المراحل من الرواسب."),
        $p('cartridges', 'حنفية فلتر استانلس', 'faucet-stainless', 'حنفية رقبة طويلة استانلس ستيل لا تصدأ.', 280, 350, 45, 'faucet.svg', '', 0, 77, 'ضمان سنة', "الخامة: استانلس ستيل 304\nالتوصيل: 1/4 بوصة", "حنفية أنيقة مناسبة لكل أنواع الفلاتر."),
        $p('cartridges', 'جهاز قياس الأملاح TDS', 'tds-meter', 'قيس نسبة الأملاح الذائبة في المياه في ثواني.', 250, 300, 40, 'tds.svg', '', 0, 64, '', "المدى: 0–9990 جزء في المليون\nالتشغيل: بطارية", "أداة بسيطة تعرف بيها كفاءة الفلتر ومعاد تغيير الشمعات."),
        $p('whole-house', 'فلتر جامبو 20 بوصة للمنزل بالكامل', 'jumbo-20-single', 'فلتر مدخل رئيسي بيحمي كل مواسير وأجهزة البيت من الرواسب.', 1350, 1600, 20, 'jumbo.svg', '', 0, 49, 'ضمان سنتين', "المقاس: 20 بوصة جامبو\nالتوصيل: 1 بوصة\nالمراحل: مرحلة واحدة", "بيتركب على المدخل الرئيسي للمياه وبيحمي السخانات والغسالات والحنفيات."),
        $p('whole-house', 'فلتر جامبو 3 مراحل للمنزل بالكامل', 'jumbo-20-triple', 'تنقية شاملة لكل مياه البيت: رواسب + كربون + كلور.', 3900, 4400, 10, 'jumbo.svg', 'جديد', 1, 37, 'ضمان سنتين', "المقاس: 20 بوصة جامبو\nالمراحل: 3 مراحل\nالتوصيل: 1 بوصة", "حل متكامل لمياه البيت كله — ممتاز للفلل والشقق الكبيرة."),
        $p('whole-house', 'سوفتنر لإزالة عسر المياه', 'water-softener', 'بيشيل الجير والعسر ويحمي السخانات والأجهزة من التكلسات.', 9500, 10500, 6, 'softener.svg', '', 0, 21, 'ضمان سنتين', "السعة: 1 قدم مكعب ريزن\nالتحكم: محبس أوتوماتيك\nالتجديد: ملح طعام", "السوفتنر بيقلل عسر المياه وبيحافظ على الأجهزة والشعر والبشرة."),
        $p('water-coolers', 'مبرد مياه ساخن وبارد بالفلتر', 'cooler-hot-cold', 'مبرد مياه بفلتر مدمج — ساخن وبارد — مناسب للمكاتب والبيوت.', 7800, 8500, 8, 'cooler.svg', '', 1, 33, 'ضمان سنة', "الحرارة: ساخن / بارد\nفلتر مدمج: 3 مراحل\nالخزان: 5 لتر بارد", "مبرد عملي بيوفر مياه نقية ساخنة وباردة طول اليوم."),
        $p('ro-plants', 'محطة تنقية 400 جالون/يوم', 'ro-plant-400', 'محطة تنقية للمكاتب والمطاعم والعيادات.', 28000, null, 3, 'ro-plant.svg', '', 0, 9, 'ضمان سنة + صيانة', "الإنتاج: 400 جالون/يوم\nعدد الممبرين: 2\nموتور: رفع ضغط", "محطة متكاملة مع تركيب وتشغيل وتدريب."),
        $p('ro-plants', 'محطة تحلية 1000 جالون/يوم', 'ro-plant-1000', 'محطة تحلية صناعية للمصانع والمدارس والمستشفيات.', 55000, null, 2, 'ro-plant.svg', 'حسب الطلب', 0, 4, 'ضمان سنة + صيانة', "الإنتاج: 1000 جالون/يوم\nعدد الممبرين: 4\nلوحة تحكم: أوتوماتيك", "بنعمل دراسة للمياه وتصميم للمحطة حسب احتياجك."),
    ];
    $st = $pdo->prepare('INSERT INTO products (category_id, name, slug, short_description, description, specs, price, old_price, stock, image, badge, warranty, is_featured, sales_count) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
    foreach ($products as $x) {
        $st->execute([
            $catIds[$x['cat']], $x['name'], $x['slug'], $x['short'], $x['desc'], $x['specs'],
            $x['price'], $x['old'], $x['stock'], 'assets/img/products/' . $x['img'],
            $x['badge'], $x['warranty'], $x['featured'], $x['sales'],
        ]);
    }

    // ---------- السلايدر ----------
    $slides = [
        ['مياه نقية لكل بيت مصري', 'فلاتر 7 مراحل بتقنية التناضح العكسي — تركيب مجاني وضمان حقيقي والدفع عند الاستلام.', 'تسوق الفلاتر', '/category/filters-7-stages', 'assets/img/hero/hero-1.svg'],
        ['صيانة وتغيير شمعات في بيتك', 'فنيين متخصصين يوصلولك في كل المحافظات. احجز زيارتك في دقيقة.', 'احجز صيانة', '/maintenance', 'assets/img/hero/hero-2.svg'],
        ['جودة مضمونة وخدمة ما بعد البيع', 'قطع غيار أصلية ومتابعة دورية — لأن صحتك تستاهل.', 'شوف العروض', '/offers', 'assets/img/hero/hero-3.svg'],
    ];
    $st = $pdo->prepare('INSERT INTO slides (title, subtitle, button_text, button_link, image, sort_order) VALUES (?,?,?,?,?,?)');
    foreach ($slides as $i => $s) $st->execute([...$s, $i + 1]);

    // ---------- آراء العملاء ----------
    $tests = [
        ['أحمد محمود', 'القاهرة', 'الفلتر ممتاز والمياه طعمها اتغير خالص. الفني جه في معاده وركّب بسرعة ونضافة.', 5],
        ['منى عبد الرحمن', 'الإسكندرية', 'خدمة عملاء محترمة جداً، وبيكلموني لوحدهم لما ييجي معاد تغيير الشمعات.', 5],
        ['محمد السيد', 'المنصورة', 'جبت فلتر 7 مراحل بالموتور عشان الضغط عندنا ضعيف وشغال تمام. أنصح بيهم.', 5],
        ['سارة إبراهيم', 'الجيزة', 'الأسعار مناسبة جداً والتوصيل كان سريع. الدفع عند الاستلام ريّحني.', 4],
    ];
    $st = $pdo->prepare('INSERT INTO testimonials (name, city, content, rating, sort_order) VALUES (?,?,?,?,?)');
    foreach ($tests as $i => $t) $st->execute([...$t, $i + 1]);

    // ---------- الأسئلة الشائعة ----------
    $faqs = [
        ['هل التركيب مجاني؟', 'أيوه، التركيب مجاني لكل فلاتر الـ 5 والـ 7 مراحل داخل نطاق الخدمة، والفني بيشرحلك طريقة الاستخدام والصيانة.'],
        ['إمتى لازم أغير الشمعات؟', 'المراحل الأولى (1، 2، 3) كل 3 إلى 6 شهور حسب جودة المياه، والممبرين كل 18 إلى 24 شهر، والمراحل الأخيرة كل سنة تقريباً.'],
        ['طرق الدفع المتاحة إيه؟', 'الدفع عند الاستلام كاش، أو تحويل على فودافون كاش / إنستاباي.'],
        ['بتوصلوا لكل المحافظات؟', 'أيوه بنوصل لكل محافظات مصر، ومدة التوصيل من 1 إلى 4 أيام عمل حسب المحافظة.'],
        ['إيه الفرق بين فلتر 5 مراحل و7 مراحل؟', 'الـ 7 مراحل فيه مراحل إضافية بعد الممبرين بتحسن الطعم وبتضيف معادن مفيدة وبتظبط الـ pH، أما الـ 5 مراحل فاختيار اقتصادي ممتاز.'],
        ['هل في ضمان؟', 'كل الفلاتر عليها ضمان مكتوب على الهيكل والموتور حسب نوع المنتج، بالإضافة لمتابعة فنية بعد التركيب.'],
    ];
    $st = $pdo->prepare('INSERT INTO faqs (question, answer, sort_order) VALUES (?,?,?)');
    foreach ($faqs as $i => $f) $st->execute([...$f, $i + 1]);

    $pdo->commit();
}
