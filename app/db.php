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
        require_once __DIR__ . '/seed.php';
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
    if (!isset($data['created_at']) && in_array($table, ['orders', 'service_requests', 'messages', 'admins', 'products', 'customers', 'reviews', 'posts', 'subscribers', 'installment_requests'], true)) {
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

/** إضافة عمود لجدول موجود (للتحديث من نسخة قديمة) */
function add_column(PDO $pdo, string $table, string $column, string $def): void
{
    $cols = array_column($pdo->query("PRAGMA table_info($table)")->fetchAll(), 'name');
    if (!in_array($column, $cols, true)) $pdo->exec("ALTER TABLE $table ADD COLUMN $column $def");
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
    parent_id   INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    name        TEXT NOT NULL,
    slug        TEXT NOT NULL UNIQUE,
    description TEXT,
    image       TEXT,
    sort_order  INTEGER DEFAULT 0,
    is_active   INTEGER DEFAULT 1
);
CREATE TABLE IF NOT EXISTS brands (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    name        TEXT NOT NULL,
    slug        TEXT NOT NULL UNIQUE,
    logo        TEXT,
    description TEXT,
    sort_order  INTEGER DEFAULT 0,
    is_active   INTEGER DEFAULT 1
);
CREATE TABLE IF NOT EXISTS products (
    id                 INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id        INTEGER REFERENCES categories(id) ON DELETE SET NULL,
    brand_id           INTEGER REFERENCES brands(id) ON DELETE SET NULL,
    name               TEXT NOT NULL,
    slug               TEXT NOT NULL UNIQUE,
    short_description  TEXT,
    description        TEXT,
    specs              TEXT,
    faqs               TEXT,
    price              REAL NOT NULL DEFAULT 0,
    old_price          REAL,
    stock              INTEGER NOT NULL DEFAULT 0,
    image              TEXT,
    gallery            TEXT,
    badge              TEXT,
    warranty           TEXT,
    installment_months INTEGER DEFAULT 0,
    is_express         INTEGER DEFAULT 1,
    is_featured        INTEGER DEFAULT 0,
    is_active          INTEGER DEFAULT 1,
    sales_count        INTEGER DEFAULT 0,
    views              INTEGER DEFAULT 0,
    rating_avg         REAL DEFAULT 0,
    rating_count       INTEGER DEFAULT 0,
    created_at         TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at         TEXT DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS customers (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    name          TEXT NOT NULL,
    phone         TEXT NOT NULL UNIQUE,
    email         TEXT,
    password_hash TEXT NOT NULL,
    governorate   TEXT,
    city          TEXT,
    address       TEXT,
    last_login    TEXT,
    created_at    TEXT DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS wishlist (
    customer_id INTEGER NOT NULL REFERENCES customers(id) ON DELETE CASCADE,
    product_id  INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    created_at  TEXT DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (customer_id, product_id)
);
CREATE TABLE IF NOT EXISTS reviews (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    product_id  INTEGER NOT NULL REFERENCES products(id) ON DELETE CASCADE,
    customer_id INTEGER REFERENCES customers(id) ON DELETE SET NULL,
    name        TEXT NOT NULL,
    rating      INTEGER NOT NULL DEFAULT 5,
    comment     TEXT,
    is_approved INTEGER DEFAULT 0,
    created_at  TEXT DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS orders (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    code           TEXT NOT NULL UNIQUE,
    customer_id    INTEGER REFERENCES customers(id) ON DELETE SET NULL,
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
CREATE TABLE IF NOT EXISTS installment_requests (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    name         TEXT NOT NULL,
    phone        TEXT NOT NULL,
    governorate  TEXT,
    job          TEXT,
    product_id   INTEGER REFERENCES products(id) ON DELETE SET NULL,
    product_name TEXT,
    months       INTEGER,
    notes        TEXT,
    status       TEXT DEFAULT 'new',
    created_at   TEXT DEFAULT CURRENT_TIMESTAMP
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
CREATE TABLE IF NOT EXISTS subscribers (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    email      TEXT NOT NULL UNIQUE,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS slides (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    title       TEXT,
    subtitle    TEXT,
    button_text TEXT,
    button_link TEXT,
    image       TEXT,
    sort_order  INTEGER DEFAULT 0,
    is_active   INTEGER DEFAULT 1
);
CREATE TABLE IF NOT EXISTS banners (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    position    TEXT NOT NULL,
    title       TEXT,
    subtitle    TEXT,
    button_text TEXT,
    link        TEXT,
    image       TEXT,
    bg_color    TEXT DEFAULT '#2B04B5',
    sort_order  INTEGER DEFAULT 0,
    is_active   INTEGER DEFAULT 1
);
CREATE TABLE IF NOT EXISTS posts (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    title        TEXT NOT NULL,
    slug         TEXT NOT NULL UNIQUE,
    excerpt      TEXT,
    content      TEXT,
    image        TEXT,
    is_published INTEGER DEFAULT 1,
    views        INTEGER DEFAULT 0,
    created_at   TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at   TEXT DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS pages (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    slug       TEXT NOT NULL UNIQUE,
    title      TEXT NOT NULL,
    content    TEXT,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP
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
SQL);

    // ترقية قواعد بيانات النسخة الأولى
    add_column($pdo, 'categories', 'parent_id', 'INTEGER REFERENCES categories(id) ON DELETE SET NULL');
    foreach ([
        'brand_id' => 'INTEGER REFERENCES brands(id) ON DELETE SET NULL', 'faqs' => 'TEXT',
        'installment_months' => 'INTEGER DEFAULT 0', 'is_express' => 'INTEGER DEFAULT 1',
        'rating_avg' => 'REAL DEFAULT 0', 'rating_count' => 'INTEGER DEFAULT 0',
    ] as $c => $d) add_column($pdo, 'products', $c, $d);
    add_column($pdo, 'orders', 'customer_id', 'INTEGER REFERENCES customers(id) ON DELETE SET NULL');

    $pdo->exec(<<<SQL
CREATE INDEX IF NOT EXISTS idx_products_category ON products(category_id);
CREATE INDEX IF NOT EXISTS idx_products_brand ON products(brand_id);
CREATE INDEX IF NOT EXISTS idx_categories_parent ON categories(parent_id);
CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(status);
CREATE INDEX IF NOT EXISTS idx_orders_created ON orders(created_at);
CREATE INDEX IF NOT EXISTS idx_orders_customer ON orders(customer_id);
CREATE INDEX IF NOT EXISTS idx_reviews_product ON reviews(product_id);
SQL);
}

/** إعادة حساب متوسط التقييم لمنتج */
function refresh_rating(int $productId): void
{
    $r = row('SELECT COUNT(*) c, COALESCE(AVG(rating), 0) a FROM reviews WHERE product_id = ? AND is_approved = 1', [$productId]);
    q('UPDATE products SET rating_avg = ?, rating_count = ? WHERE id = ?', [round((float)$r['a'], 1), (int)$r['c'], $productId]);
}
