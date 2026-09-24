-- Mira Cafe Digital Menu | MySQL 5.7+ / MariaDB 10.3+
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS login_attempts;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS admins;

CREATE TABLE admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_login_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name_fa VARCHAR(100) NOT NULL,
  name_en VARCHAR(100) NULL,
  slug VARCHAR(100) NOT NULL UNIQUE,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_categories_active_sort (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE products (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  category_id INT UNSIGNED NOT NULL,
  name_fa VARCHAR(150) NOT NULL,
  name_en VARCHAR(150) NULL,
  description VARCHAR(500) NULL,
  ingredients VARCHAR(700) NULL,
  price INT UNSIGNED NULL,
  image VARCHAR(100) NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  badge_vegan TINYINT(1) NOT NULL DEFAULT 0,
  badge_sugar_free TINYINT(1) NOT NULL DEFAULT 0,
  badge_diet TINYINT(1) NOT NULL DEFAULT 0,
  caffeine_level TINYINT UNSIGNED NOT NULL DEFAULT 0,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  is_visible TINYINT(1) NOT NULL DEFAULT 1,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON UPDATE CASCADE ON DELETE RESTRICT,
  INDEX idx_products_menu (category_id, is_visible, is_available, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  setting_key VARCHAR(100) NOT NULL UNIQUE,
  setting_value TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE login_attempts (
  client_key CHAR(64) PRIMARY KEY,
  attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  locked_until DATETIME NULL,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_login_cleanup (updated_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE analytics_daily (
  event_date DATE NOT NULL, event_type ENUM('view','product') NOT NULL,
  product_id INT UNSIGNED NOT NULL DEFAULT 0, event_count INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY(event_date,event_type,product_id), INDEX idx_analytics_date(event_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categories (id,name_fa,name_en,slug,sort_order,is_active) VALUES
(1,'شیک‌ها','Shakes','shakes',10,1),(2,'اسموتی‌ها','Smoothies','smoothies',20,1),
(3,'کافی بار','Coffee Bar','coffee-bar',30,1),(4,'نوشیدنی‌های گرم','Hot Drinks','hot-drinks',40,1),
(5,'ماکتیل','Mocktails','mocktails',50,1),(6,'بار سرد','Cold Bar','cold-bar',60,1),
(7,'میان وعده','Snacks','snacks',70,1),(8,'کیک و دسر','Cake & Dessert','cake-dessert',80,1);

-- Prices are stored as full Tomans. Unknown prices intentionally remain NULL.
INSERT INTO products (category_id,name_fa,name_en,description,price,image,is_available,is_visible,sort_order) VALUES
(1,'شیک انبه',NULL,NULL,385000,NULL,1,1,10),(1,'شیک توت فرنگی',NULL,NULL,385000,NULL,1,1,20),
(1,'شیک نوتلا',NULL,NULL,385000,NULL,1,1,30),(1,'شیک موز و کره بادام زمینی',NULL,NULL,420000,NULL,1,1,40),
(1,'شیک لوتوس',NULL,NULL,410000,NULL,1,1,50),
(2,'اسموتی سانی منگو',NULL,NULL,435000,NULL,1,1,10),(2,'اسموتی منگو بری',NULL,NULL,435000,NULL,1,1,20),
(2,'اسموتی تروپیکال',NULL,NULL,480000,NULL,1,1,30),
(3,'موکا',NULL,NULL,295000,NULL,1,1,10),(3,'لاته','Latte',NULL,230000,'a3f5d9c80b2741e6aa91d8f0435c72be.webp',1,1,20),
(3,'کاپوچینو',NULL,NULL,220000,NULL,1,1,30),(3,'اسپرسو دبل (۵۰/۵۰)',NULL,NULL,125000,NULL,1,1,40),
(3,'اسپرسو سینگل (۵۰/۵۰)',NULL,NULL,100000,NULL,1,1,50),(3,'اسپرسو دوبل (۱۰۰٪ روبوستا)',NULL,NULL,105000,NULL,1,1,60),
(3,'اسپرسو سینگل (۱۰۰٪ روبوستا)',NULL,NULL,85000,NULL,1,1,70),(3,'ایروپرس',NULL,NULL,280000,NULL,1,1,80),
(3,'V60','V60',NULL,340000,NULL,1,1,90),(3,'کمکس',NULL,NULL,350000,NULL,1,1,100),
(3,'قهوه ترک',NULL,NULL,125000,NULL,1,1,110),(3,'آمریکانو',NULL,NULL,140000,NULL,1,1,120),
(4,'کوکو هات',NULL,NULL,235000,NULL,1,1,10),(4,'وایت چاکلت',NULL,NULL,235000,NULL,1,1,20),
(4,'هات چاکلت',NULL,NULL,235000,NULL,1,1,30),(4,'کرک',NULL,NULL,235000,NULL,1,1,40),
(4,'ماسالا',NULL,NULL,235000,NULL,1,1,50),(4,'چای ساده',NULL,NULL,150000,NULL,1,1,60),
(4,'چای زعفران',NULL,NULL,180000,NULL,1,1,70),(4,'چای زنجبیل',NULL,NULL,180000,NULL,1,1,80),
(4,'چای دارچین',NULL,NULL,180000,NULL,1,1,90),(4,'چای هل',NULL,NULL,180000,NULL,1,1,100),
(4,'دمنوش جینسینگ',NULL,NULL,160000,NULL,1,1,110),(4,'دمنوش نعنا لیمو',NULL,NULL,160000,NULL,1,1,120),
(5,'کوکو فرش',NULL,NULL,235000,NULL,1,1,10),(5,'رد موهیتو',NULL,NULL,270000,NULL,1,1,20),
(5,'پرتقال توت فرنگی',NULL,NULL,265000,NULL,1,1,30),(5,'لیموناد',NULL,NULL,200000,NULL,1,1,40),
(5,'موهیتو',NULL,NULL,210000,NULL,1,1,50),
(6,'آفوگاتو کره بادام زمینی',NULL,NULL,350000,NULL,1,1,10),(6,'آفوگاتو لوتوس',NULL,NULL,350000,NULL,1,1,20),
(6,'آفوگاتو',NULL,NULL,285000,NULL,1,1,30),(6,'آیس اورنج کافی',NULL,NULL,295000,NULL,1,1,40),
(6,'آیس رومانو',NULL,NULL,160000,NULL,1,1,50),(6,'آیس آمریکانو',NULL,NULL,150000,NULL,1,1,60),
(6,'آیس لاته',NULL,NULL,230000,NULL,1,1,70),(6,'آیس لاته سیروپ',NULL,NULL,290000,NULL,1,1,80),
(6,'کلد برو',NULL,NULL,270000,NULL,1,1,90),(6,'آیس موکا',NULL,NULL,295000,NULL,1,1,100),
(7,'سیب چدار',NULL,NULL,345000,NULL,1,1,10),(7,'کروسان نوتلا',NULL,NULL,NULL,NULL,1,1,20),
(7,'تست ژامبون',NULL,NULL,320000,NULL,1,1,30),(7,'تست کره بادام زمینی',NULL,NULL,NULL,NULL,1,1,40),
(7,'املت مخصوص',NULL,NULL,450000,NULL,1,1,50),(7,'سوسیس تخم مرغ',NULL,NULL,355000,NULL,1,1,60),
(7,'املت گوجه ایرانی',NULL,NULL,280000,NULL,1,1,70),(7,'سیب و فیله سوخاری',NULL,NULL,395000,NULL,1,1,80),
(7,'سیب ناگت',NULL,NULL,345000,NULL,1,1,90),
(8,'جار کیک سه شیر',NULL,NULL,190000,NULL,1,1,10),(8,'جار کیک رد ولوت',NULL,NULL,175000,NULL,1,1,20),
(8,'جار کیک دوبی چاکلت',NULL,NULL,195000,NULL,1,1,30),(8,'جار کیک شکلاتی',NULL,NULL,175000,NULL,1,1,40),
(8,'پاپسیکل',NULL,NULL,140000,NULL,1,1,50),(8,'کوکی',NULL,NULL,NULL,NULL,1,1,60),
(8,'چیز کیک اسنیکرز',NULL,NULL,195000,NULL,1,1,70),(8,'تیرامیسو',NULL,NULL,175000,NULL,1,1,80);

INSERT INTO settings (setting_key,setting_value) VALUES
('cafe_name','میرا کافه'),('page_title','منوی میرا کافه'),
('meta_description','منوی دیجیتال میرا کافه؛ قهوه، نوشیدنی، شیک، اسموتی، میان‌وعده و دسر.'),
('instagram',''),('phone',''),('address',''),('footer_text','با عشق برای شما ☕️'),
('currency','تومان'),('show_unavailable','1');

-- A few polished examples; every field remains editable from the admin panel.
UPDATE products SET ingredients='اسپرسو، شیر تازه و فوم لطیف شیر', is_featured=1, caffeine_level=2 WHERE id=10;
UPDATE products SET name_en='Nutella Shake', ingredients='نوتلا، بستنی وانیلی و شیر', is_featured=1 WHERE id=3;
UPDATE products SET name_en='Mojito', ingredients='لیموی تازه، نعنا، سودا و یخ', badge_vegan=1, badge_sugar_free=1 WHERE id=37;
UPDATE products SET name_en='Tiramisu', ingredients='ماسکارپونه، قهوه، بیسکویت و پودر کاکائو', is_featured=1 WHERE id=64;

SET FOREIGN_KEY_CHECKS = 1;
