-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 12 نوفمبر 2025 الساعة 21:15
-- إصدار الخادم: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aljabri_store`
--

-- --------------------------------------------------------

--
-- بنية الجدول `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password_hash`, `full_name`, `created_at`) VALUES
(2, 'admin', '$2y$10$v.Y1pLo1i3Oxf.xaekyZEO7yUv7D0u2CUVEC5NDB4/e3X7Cugg/bC', 'مدير النظام', '2025-11-05 18:28:34');

-- --------------------------------------------------------

--
-- بنية الجدول `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'إلكترونيات', 'الأجهزة الإلكترونية والكهربائية', '2025-11-05 18:28:30'),
(2, 'ملابس', 'ملابس رجالية ونسائية وأطفال', '2025-11-05 18:28:31'),
(3, 'منتجات منزلية', 'أدوات ومنتجات للاستخدام المنزلي', '2025-11-05 18:28:31'),
(4, 'هواتف وأجهزة لوحية', 'الهواتف الذكية والأجهزة اللوحية وملحقاتها', '2025-11-05 18:28:31'),
(5, 'أجهزة الكمبيوتر', 'لابتوبات، أجهزة مكتبية، وملحقاتها', '2025-11-05 18:28:32');

-- --------------------------------------------------------

--
-- بنية الجدول `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(20) DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `customer_address` text NOT NULL,
  `order_notes` text DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `shipping` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `currency` varchar(10) DEFAULT 'YER',
  `payment_method` enum('كريمي جوال','جيب','ون كاش','فلوسك','جوالي','كاش') NOT NULL,
  `payment_status` varchar(20) DEFAULT 'pending',
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','shipped','delivered','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `customer_city` varchar(100) DEFAULT NULL,
  `customer_area` varchar(100) DEFAULT NULL,
  `customer_street` varchar(200) DEFAULT NULL,
  `customer_building` varchar(50) DEFAULT NULL,
  `customer_apartment` varchar(50) DEFAULT NULL,
  `customer_notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `customer_name`, `customer_phone`, `customer_email`, `customer_address`, `order_notes`, `subtotal`, `shipping`, `total`, `currency`, `payment_method`, `payment_status`, `total_amount`, `status`, `created_at`, `updated_at`, `customer_city`, `customer_area`, `customer_street`, `customer_building`, `customer_apartment`, `customer_notes`) VALUES
(3, 'JAB2025110002', 'اختبار العميل', '771234567', 'test@example.com', 'عنوان اختباري', 'ملاحظات اختبار', 50000.00, 5000.00, 55000.00, 'YER', '', 'pending', 0.00, 'shipped', '2025-11-06 20:15:06', '2025-11-11 18:48:53', NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'JAB2025110002', 'Ali', '0775975551', 'aliabdulkafy646@gmail.com', 'SANAA', '', 26500.00, 5000.00, 31500.00, 'YER', '', 'pending', 0.00, 'shipped', '2025-11-06 20:15:45', '2025-11-11 20:22:55', NULL, NULL, NULL, NULL, NULL, NULL),
(8, 'ORD2025110003', 'Ali', '0775975551', 'aliabdulkafy646@gmail.com', 'اليمن عمران المحافظه', '', 26750.00, 5000.00, 31750.00, 'YER', '', 'pending', 0.00, 'shipped', '2025-11-08 18:40:51', '2025-11-12 19:52:12', NULL, NULL, NULL, NULL, NULL, NULL),
(9, 'ORD2025110004', 'Ali', '0775975551', 'aliabdulkafy646@gmail.com', 'صنعاء حزيز', '', 26750.00, 5000.00, 31750.00, 'YER', '', 'pending', 0.00, 'shipped', '2025-11-08 19:16:42', '2025-11-11 20:53:09', NULL, NULL, NULL, NULL, NULL, NULL),
(11, 'ORD2025110006', 'Ali', '0775975551', 'aliabdulkafy646@gmail.com', 'صنعاء شرتون', '', 26750.00, 5000.00, 31750.00, 'YER', '', 'pending', 0.00, 'confirmed', '2025-11-08 19:51:39', '2025-11-11 19:33:59', NULL, NULL, NULL, NULL, NULL, NULL),
(13, 'ORD2025110008', 'Ali', '0775975551', 'aliabdulkafy646@gmail.com', 'تبييؤلرؤءلا', '', 26750.00, 5000.00, 31750.00, 'YER', '', 'pending', 0.00, 'confirmed', '2025-11-08 20:08:30', '2025-11-11 19:20:54', NULL, NULL, NULL, NULL, NULL, NULL),
(15, 'JAB2025110003', 'Ali', '0775975551', NULL, 'Yemen / sanaa / sanaa/00967', NULL, 0.00, 0.00, 26750.00, 'YER', 'كريمي جوال', 'pending', 0.00, 'shipped', '2025-11-09 17:53:17', '2025-11-11 20:23:50', NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'JAB2025110004', 'Ali', '0775975551', NULL, 'صنعاء شرتون', NULL, 0.00, 0.00, 26750.00, 'YER', 'كريمي جوال', 'pending', 0.00, 'shipped', '2025-11-09 18:11:18', '2025-11-11 19:24:09', NULL, NULL, NULL, NULL, NULL, NULL),
(17, 'JAB2025110005', 'Ali', '0775975551', NULL, 'صنعاء', NULL, 0.00, 0.00, 26500.00, 'YER', 'كريمي جوال', 'pending', 0.00, 'delivered', '2025-11-09 18:48:19', '2025-11-11 18:33:18', NULL, NULL, NULL, NULL, NULL, NULL),
(18, NULL, 'Ali', '0775975551', 'aliabdulkafy646@gmail.com', 'ذمار المدينه جوار العزاني', '', 321000.00, 0.00, 321000.00, 'YER', '', 'pending', 0.00, 'shipped', '2025-11-11 18:36:45', '2025-11-11 18:48:44', NULL, NULL, NULL, NULL, NULL, NULL),
(19, NULL, 'Ali', '0775975551', 'aliabdulkafy646@gmail.com', 'تعز الحوبان', '', 321000.00, 0.00, 321000.00, 'YER', '', 'pending', 0.00, 'delivered', '2025-11-11 19:54:49', '2025-11-11 20:45:37', NULL, NULL, NULL, NULL, NULL, NULL),
(20, NULL, 'Ali', '077597555', 'aliabdulkafy646@gmail.com', '', '', 18000.00, 5000.00, 23000.00, 'YER', '', 'pending', 0.00, 'delivered', '2025-11-11 20:10:13', '2025-11-11 20:45:22', NULL, NULL, NULL, NULL, NULL, NULL),
(21, NULL, 'Ali', '077597555', 'aliabdulkafy646@gmail.com', '', '', 18000.00, 5000.00, 23000.00, 'YER', '', 'pending', 0.00, 'shipped', '2025-11-11 20:13:24', '2025-11-11 20:23:03', NULL, NULL, NULL, NULL, NULL, NULL),
(22, NULL, 'Ali', '077597555', 'aliabdulkafy646@gmail.com', 'صنعاء، شرتون، Yemen / sanaa / sanaa/00967، مبنى 99، شقة 71', NULL, 26750.00, 5000.00, 31750.00, 'YER', '', 'pending', 0.00, 'shipped', '2025-11-11 20:21:25', '2025-11-11 20:22:59', 'صنعاء', 'شرتون', 'Yemen / sanaa / sanaa/00967', '99', '71', ''),
(23, NULL, 'Ali', '775975551', 'aliabdulkafy646@gmail.com', 'صنعاء، شرتون، Yemen / sanaa / sanaa/00967، مبنى 99، شقة 71', NULL, 321000.00, 0.00, 321000.00, 'YER', '', 'pending', 0.00, 'delivered', '2025-11-11 20:49:44', '2025-11-11 20:54:52', 'صنعاء', 'شرتون', 'Yemen / sanaa / sanaa/00967', '99', '71', ''),
(24, NULL, 'Ali', '077597555', 'aliabdulkafy646@gmail.com', 'صنعاء، شرتون، Yemen / sanaa / sanaa/00967، مبنى 99، شقة 71', NULL, 321000.00, 0.00, 321000.00, 'YER', '', 'pending', 0.00, 'delivered', '2025-11-11 21:24:56', '2025-11-11 21:25:57', 'صنعاء', 'شرتون', 'Yemen / sanaa / sanaa/00967', '99', '71', ''),
(25, NULL, 'Ali', '077597555', 'aliabdulkafy646@gmail.com', 'صنعاء، شرتون، Yemen / sanaa / sanaa/00967، مبنى 99، شقة 71', NULL, 18000.00, 5000.00, 23000.00, 'YER', '', 'pending', 0.00, 'confirmed', '2025-11-12 17:58:47', '2025-11-12 19:31:56', 'صنعاء', 'شرتون', 'Yemen / sanaa / sanaa/00967', '99', '71', ''),
(26, 'ORD-20251112-2997', 'Ali', '077597555', 'aliabdulkafy646@gmail.com', '', NULL, 321000.00, 0.00, 321000.00, 'YER', '', 'pending', 0.00, 'confirmed', '2025-11-12 18:53:05', '2025-11-12 18:55:26', 'صنعاء', 'شرتون', 'Yemen / sanaa / sanaa/00967', '99', '71', ''),
(27, 'ORD-20251112-1629', 'Ali', '775975551', 'aliabdulkafy646@gmail.com', '', NULL, 18000.00, 5000.00, 23000.00, 'YER', '', 'pending', 0.00, 'confirmed', '2025-11-12 19:12:11', '2025-11-12 19:31:59', 'صنعاء', 'شرتون', 'Yemen / sanaa / sanaa/00967', '99', '71', ''),
(28, 'ORD-20251112-2829', 'Ali', '077597555', 'aliabdulkafy646@gmail.com', '', NULL, 321000.00, 0.00, 321000.00, 'YER', '', 'pending', 0.00, 'shipped', '2025-11-12 19:27:37', '2025-11-12 19:32:29', 'صنعاء', 'القاعدة', 'Yemen / sanaa / sanaa/00967', '99', '71', '');

-- --------------------------------------------------------

--
-- بنية الجدول `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `product_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `unit_price`, `product_price`, `total_price`, `created_at`) VALUES
(5, 8, 2, 1, 0.00, 26750.00, 26750.00, '2025-11-08 18:40:51'),
(6, 9, 2, 1, 0.00, 26750.00, 26750.00, '2025-11-08 19:16:42'),
(8, 11, 2, 1, 0.00, 26750.00, 26750.00, '2025-11-08 19:51:39'),
(10, 13, 2, 1, 0.00, 26750.00, 26750.00, '2025-11-08 20:08:30'),
(12, 15, 2, 1, 26750.00, 0.00, 26750.00, '2025-11-09 17:53:17'),
(13, 16, 2, 1, 26750.00, 0.00, 26750.00, '2025-11-09 18:11:19'),
(15, 18, 4, 1, 0.00, 321000.00, 321000.00, '2025-11-11 18:36:45'),
(16, 19, 4, 1, 0.00, 321000.00, 321000.00, '2025-11-11 19:54:49'),
(17, 20, 3, 1, 0.00, 18000.00, 18000.00, '2025-11-11 20:10:13'),
(18, 21, 3, 1, 0.00, 18000.00, 18000.00, '2025-11-11 20:13:24'),
(19, 22, 2, 1, 26750.00, 0.00, 26750.00, '2025-11-11 20:21:25'),
(20, 23, 4, 1, 321000.00, 0.00, 321000.00, '2025-11-11 20:49:44'),
(21, 24, 4, 1, 321000.00, 0.00, 321000.00, '2025-11-11 21:24:56'),
(22, 25, 3, 1, 18000.00, 0.00, 18000.00, '2025-11-12 17:58:47'),
(23, 26, 4, 1, 321000.00, 0.00, 321000.00, '2025-11-12 18:53:05'),
(24, 27, 3, 1, 18000.00, 0.00, 18000.00, '2025-11-12 19:12:12'),
(25, 28, 4, 1, 321000.00, 0.00, 321000.00, '2025-11-12 19:27:37');

-- --------------------------------------------------------

--
-- بنية الجدول `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price_yer` decimal(10,2) NOT NULL,
  `price_sar` decimal(10,2) NOT NULL,
  `price_usd` decimal(10,2) NOT NULL,
  `main_image` varchar(255) DEFAULT NULL,
  `featured` tinyint(1) DEFAULT 0,
  `category_id` int(11) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price_yer`, `price_sar`, `price_usd`, `main_image`, `featured`, `category_id`, `stock_quantity`, `created_at`, `updated_at`) VALUES
(2, 'samsung TAP 2020', 'GDDDDDDDDDDDDDDDDDDDDDDDD', 26750.00, 191.00, 50.00, 'Samsung-Galaxy-Tab-Active-3-specifications-mytechspace-288x300_jpg.webp', 0, NULL, 2, '2025-11-06 20:33:15', '2025-11-11 20:21:25'),
(3, 'SAMSUNG TAP E', 'نببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببببل', 18000.00, 128.00, 33.00, 'tab-a7-samsung-p3.webp', 0, NULL, 3, '2025-11-08 19:14:56', '2025-11-12 17:58:47'),
(4, 'سامسونج تاب اس عشرة ', 'سامسونج جالاكسي تاب إس 10 إف إي بلس، واي فاي، 256 جيجا، فضي · احتفظ بذكرياتك. 256 جيجا بايت · استمتع بالمحتوى المرئي. 13 انش · آداء البطارية. 10090 مللي أمبير', 321000.00, 2292.00, 600.00, '____________1_.jpeg', 0, NULL, 4, '2025-11-11 17:36:15', '2025-11-11 21:24:56');

-- --------------------------------------------------------

--
-- بنية الجدول `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_main` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- بنية الجدول `store_settings`
--

CREATE TABLE `store_settings` (
  `id` int(11) NOT NULL,
  `store_name` varchar(255) NOT NULL DEFAULT 'متجر الجابري',
  `primary_color` varchar(7) DEFAULT '#3498db',
  `secondary_color` varchar(7) DEFAULT '#2c3e50',
  `announcement_text` text DEFAULT NULL,
  `welcome_message` text DEFAULT NULL,
  `store_address` text DEFAULT NULL,
  `store_phone` varchar(20) DEFAULT NULL,
  `store_whatsapp` varchar(20) DEFAULT NULL,
  `store_logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- إرجاع أو استيراد بيانات الجدول `store_settings`
--

INSERT INTO `store_settings` (`id`, `store_name`, `primary_color`, `secondary_color`, `announcement_text`, `welcome_message`, `store_address`, `store_phone`, `store_whatsapp`, `store_logo`, `created_at`, `updated_at`) VALUES
(1, 'متجر الجابري ستور', '#3498db', '#2c3e50', 'مرحباً بكم في متجرنا، شحن مجاني للطلبات فوق 50000 ريال', 'أهلاً وسهلاً بكم في متجر الجابري لأفضل المنتجات', 'صنعاء، اليمن الصياح امم محطة براش', '+967123456789', '+967123456789', 'logo.jpg', '2025-11-09 19:51:16', '2025-11-11 20:27:26');

-- --------------------------------------------------------

--
-- بنية الجدول `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- إرجاع أو استيراد بيانات الجدول `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `name`, `role`, `status`, `created_at`) VALUES
(1, 'admin', '$2y$10$XgnOJpAdz.fB24wGaQCM6ubgabBjy288AwEekI9yGxx0tSs9KBOA6', 'مدير النظام', 'admin', 'active', '2025-11-09 16:54:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `store_settings`
--
ALTER TABLE `store_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `store_settings`
--
ALTER TABLE `store_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- قيود الجداول المُلقاة.
--

--
-- قيود الجداول `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- قيود الجداول `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
