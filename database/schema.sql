CREATE TABLE books (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    author TEXT NOT NULL,
    category TEXT NOT NULL DEFAULT '',
    publisher TEXT NOT NULL DEFAULT '',
    isbn TEXT NOT NULL DEFAULT '',
    condition TEXT NOT NULL,
    location TEXT NOT NULL DEFAULT '',
    notes TEXT NOT NULL DEFAULT '',
    price_cents INTEGER NOT NULL CHECK(price_cents >= 0),
    quantity INTEGER NOT NULL DEFAULT 0 CHECK(quantity >= 0 AND quantity <= 1000000),
    minimum_stock INTEGER NOT NULL DEFAULT 1 CHECK(minimum_stock >= 0),
    archived INTEGER NOT NULL DEFAULT 0 CHECK(archived IN (0, 1)),
    created_at TEXT NOT NULL,
    updated_at TEXT NOT NULL
);
CREATE TABLE movements (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    book_id INTEGER NOT NULL REFERENCES books(id),
    type TEXT NOT NULL CHECK(type IN ('entrada', 'saida')),
    quantity INTEGER NOT NULL CHECK(quantity > 0),
    balance_after INTEGER NOT NULL CHECK(balance_after >= 0),
    reason TEXT NOT NULL,
    note TEXT NOT NULL DEFAULT '',
    created_at TEXT NOT NULL
);
CREATE INDEX movements_book ON movements(book_id, id DESC);
PRAGMA user_version = 1;
