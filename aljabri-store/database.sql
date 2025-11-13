-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS aljabri_store;
USE aljabri_store;

-- جدول المنتجات
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    price_yer DECIMAL(10,2) NOT NULL,
    price_sar DECIMAL(10,2) NOT NULL,
    price_usd DECIMAL(10,2) NOT NULL,
    main_image VARCHAR(255),
    featured BOOLEAN DEFAULT FALSE,
    category_id INT,
    stock_quantity INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- جدول صور المنتج
CREATE TABLE product_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT,
    image_path VARCHAR(255),
    is_main BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- جدول الطلبات
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_address TEXT NOT NULL,
    payment_method ENUM('كريمي جوال', 'جيب', 'ون كاش', 'فلوسك', 'جوالي', 'كاش') NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول تفاصيل الطلب
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- جدول إعدادات المتجر
CREATE TABLE store_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(255) UNIQUE NOT NULL,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- إضافة إعدادات افتراضية
INSERT INTO store_settings (setting_key, setting_value) VALUES
('store_name', 'الجابري ستور'),
('primary_color', '#FF6B35'),
('secondary_color', '#2C3E50'),
('announcement_text', '🔥 عروض خاصة - تخفيضات تصل إلى 50% 🔥'),
('store_address', 'اليمن - صنعاء - الصياح - امم محطة براش'),
('store_phone', '+967782090454'),
('store_whatsapp', '+967782090454');

-- إنشاء مسؤول للنظام
CREATE TABLE admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- إضافة مسؤول افتراضي (كلمة المرور: admin123)
INSERT INTO admin_users (username, password_hash, full_name) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'مدير النظام');
-- تحديث جدول إعدادات المتجر
INSERT INTO store_settings (setting_key, setting_value) VALUES
('store_logo', ''),
('welcome_message', 'مرحباً بكم في متجر الجابري ستور - أفضل المنتجات بأفضل الأسعار');

-- تحديث جدول المنتجات لإضافة الفئات
ALTER TABLE products ADD COLUMN category_id INT DEFAULT 1 AFTER featured;

-- إنشاء جدول الفئات
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- إضافة فئات افتراضية
INSERT INTO categories (name, description) VALUES
('إلكترونيات', 'الأجهزة الإلكترونية والكهربائية'),
('ملابس', 'ملابس رجالية ونسائية وأطفال'),
('منتجات منزلية', 'أدوات ومنتجات للاستخدام المنزلي');