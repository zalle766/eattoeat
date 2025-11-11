-- Eat to Eat Database Schema
-- يمكنك رفع هذا الملف مباشرة في phpMyAdmin

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS `eattoeat` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `eattoeat`;

-- جدول المستخدمين
CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` enum('admin','restaurant','courier','customer') NOT NULL DEFAULT 'customer',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول tokens إعادة تعيين كلمة المرور
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول المهام الفاشلة
CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول personal access tokens
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول المطاعم
CREATE TABLE `restaurants` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `address` varchar(500) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `cover_image` varchar(255) DEFAULT NULL,
  `opening_time` time DEFAULT NULL,
  `closing_time` time DEFAULT NULL,
  `delivery_fee` decimal(8,2) DEFAULT 0.00,
  `minimum_order` decimal(8,2) DEFAULT 0.00,
  `rating` decimal(3,2) DEFAULT 0.00,
  `total_reviews` int(11) DEFAULT 0,
  `is_open` tinyint(1) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `restaurants_user_id_foreign` (`user_id`),
  CONSTRAINT `restaurants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول المنتجات
CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `restaurant_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(8,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 1,
  `preparation_time` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_restaurant_id_foreign` (`restaurant_id`),
  CONSTRAINT `products_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول السائقين
CREATE TABLE `couriers` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `vehicle_type` varchar(50) NOT NULL,
  `license_number` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 0.00,
  `total_deliveries` int(11) DEFAULT 0,
  `total_earnings` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `couriers_user_id_foreign` (`user_id`),
  CONSTRAINT `couriers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول الطلبات
CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_number` varchar(20) NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `restaurant_id` bigint(20) UNSIGNED NOT NULL,
  `courier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('pending','confirmed','preparing','ready','picked_up','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `subtotal` decimal(10,2) NOT NULL,
  `delivery_fee` decimal(8,2) DEFAULT 0.00,
  `tax` decimal(8,2) DEFAULT 0.00,
  `total_price` decimal(10,2) NOT NULL,
  `delivery_address` text NOT NULL,
  `delivery_latitude` decimal(10,8) DEFAULT NULL,
  `delivery_longitude` decimal(11,8) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `estimated_delivery_time` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_order_number_unique` (`order_number`),
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_restaurant_id_foreign` (`restaurant_id`),
  KEY `orders_courier_id_foreign` (`courier_id`),
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_restaurant_id_foreign` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_courier_id_foreign` FOREIGN KEY (`courier_id`) REFERENCES `couriers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول عناصر الطلبات
CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_foreign` (`order_id`),
  KEY `order_items_product_id_foreign` (`product_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول التقييمات والمراجعات
CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `target_type` varchar(255) NOT NULL,
  `target_id` bigint(20) UNSIGNED NOT NULL,
  `rating` int(11) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reviews_user_id_foreign` (`user_id`),
  KEY `reviews_target_type_target_id_index` (`target_type`,`target_id`),
  CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول الإشعارات
CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `data` json DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_foreign` (`user_id`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- إدراج البيانات التجريبية

-- إدراج المستخدمين
INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `phone`, `address`, `latitude`, `longitude`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'مدير النظام', 'admin@eattoeat.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01234567890', 'القاهرة، مصر', 30.0444, 31.2357, 'admin', 1, NOW(), NOW()),
(2, 'أحمد محمد', 'restaurant@eattoeat.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01234567891', 'شارع التحرير، القاهرة', 30.0444, 31.2357, 'restaurant', 1, NOW(), NOW()),
(3, 'محمد علي', 'courier@eattoeat.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01234567892', 'المعادي، القاهرة', 30.0444, 31.2357, 'courier', 1, NOW(), NOW()),
(4, 'فاطمة أحمد', 'customer@eattoeat.com', NULL, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '01234567893', 'مدينة نصر، القاهرة', 30.0444, 31.2357, 'customer', 1, NOW(), NOW());

-- إدراج المطاعم
INSERT INTO `restaurants` (`id`, `user_id`, `name`, `description`, `address`, `latitude`, `longitude`, `phone`, `email`, `opening_time`, `closing_time`, `delivery_fee`, `minimum_order`, `rating`, `total_reviews`, `is_open`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 2, 'مطعم الشرق الأوسط', 'أفضل المأكولات العربية الأصيلة مع أجود أنواع اللحوم والخضار الطازجة', 'شارع التحرير، القاهرة', 30.0444, 31.2357, '01234567891', 'info@middleeast.com', '09:00:00', '23:00:00', 15.00, 50.00, 4.50, 120, 1, 1, NOW(), NOW()),
(2, 2, 'بيتزا إيطاليا', 'أشهى البيتزا الإيطالية الأصيلة مع العجين المخمر والجبن المستورد', 'شارع النيل، القاهرة', 30.0444, 31.2357, '01234567892', 'info@pizzaitalia.com', '10:00:00', '24:00:00', 20.00, 80.00, 4.20, 95, 1, 1, NOW(), NOW()),
(3, 2, 'برجر هاوس', 'ألذ البرجرز والوجبات السريعة مع اللحوم الطازجة والصلصات المميزة', 'مدينة نصر، القاهرة', 30.0444, 31.2357, '01234567893', 'info@burgerhouse.com', '11:00:00', '02:00:00', 12.00, 40.00, 4.30, 80, 1, 1, NOW(), NOW()),
(4, 2, 'مطعم الصين الذهبية', 'أشهى الأطباق الصينية الأصيلة مع التوابل الطبيعية', 'شارع الهرم، الجيزة', 30.0131, 31.2089, '01234567894', 'info@goldenchina.com', '12:00:00', '23:00:00', 18.00, 60.00, 4.40, 75, 1, 1, NOW(), NOW()),
(5, 2, 'كافيه الحلويات', 'أجمل الحلويات الشرقية والغربية مع القهوة المميزة', 'شارع المعز، القاهرة القديمة', 30.0444, 31.2357, '01234567895', 'info@sweetscafe.com', '08:00:00', '22:00:00', 10.00, 30.00, 4.60, 200, 1, 1, NOW(), NOW()),
(6, 2, 'مطعم البحار', 'أطيب المأكولات البحرية الطازجة من البحر المتوسط', 'كورنيش النيل، القاهرة', 30.0444, 31.2357, '01234567896', 'info@seafood.com', '13:00:00', '23:00:00', 25.00, 100.00, 4.70, 150, 1, 1, NOW(), NOW()),
(7, 2, 'مطعم الهندي الذهبي', 'أشهى الأطباق الهندية الحارة مع التوابل الأصيلة', 'شارع رمسيس، القاهرة', 30.0444, 31.2357, '01234567897', 'info@goldenindia.com', '11:00:00', '23:00:00', 16.00, 55.00, 4.30, 90, 1, 1, NOW(), NOW()),
(8, 2, 'مطعم اللحوم المشوية', 'أجود أنواع اللحوم المشوية مع الصلصات المميزة', 'شارع الهرم، الجيزة', 30.0131, 31.2089, '01234567898', 'info@grilledmeat.com', '14:00:00', '01:00:00', 20.00, 70.00, 4.50, 110, 1, 1, NOW(), NOW());

-- إدراج المنتجات
INSERT INTO `products` (`id`, `restaurant_id`, `name`, `description`, `price`, `category`, `is_available`, `preparation_time`, `created_at`, `updated_at`) VALUES
-- منتجات مطعم الشرق الأوسط
(1, 1, 'كباب مشوي', 'كباب لحم مشوي مع الأرز والخضار الطازجة', 85.00, 'مشويات', 1, 25, NOW(), NOW()),
(2, 1, 'شاورما دجاج', 'شاورما دجاج مع الخضار والصلصات المميزة', 65.00, 'مشويات', 1, 20, NOW(), NOW()),
(3, 1, 'فتة', 'فتة باللحم والخبز المحمص مع الثوم', 75.00, 'أطباق عربية', 1, 15, NOW(), NOW()),
(4, 1, 'كشري مصري', 'كشري مصري أصيل مع العدس والأرز والمعكرونة', 45.00, 'أطباق مصرية', 1, 12, NOW(), NOW()),
(5, 1, 'محشي ورق عنب', 'محشي ورق عنب باللحم والأرز', 60.00, 'أطباق عربية', 1, 30, NOW(), NOW()),
(6, 1, 'مشروب ليمون بالنعناع', 'مشروب منعش بالليمون والنعناع الطازج', 15.00, 'مشروبات', 1, 5, NOW(), NOW()),
(7, 1, 'عصير برتقال طازج', 'عصير برتقال طبيعي 100%', 20.00, 'مشروبات', 1, 5, NOW(), NOW()),

-- منتجات بيتزا إيطاليا
(8, 2, 'بيتزا مارجريتا', 'بيتزا بالجبن الموزاريلا والطماطم والريحان', 120.00, 'بيتزا', 1, 20, NOW(), NOW()),
(9, 2, 'بيتزا بيبروني', 'بيتزا بالجبن والبيبروني الحار', 140.00, 'بيتزا', 1, 20, NOW(), NOW()),
(10, 2, 'بيتزا هاواي', 'بيتزا بالجبن والأناناس واللحم', 130.00, 'بيتزا', 1, 20, NOW(), NOW()),
(11, 2, 'معكرونة بولونيز', 'معكرونة باللحم المفروم والطماطم', 90.00, 'معكرونة', 1, 15, NOW(), NOW()),
(12, 2, 'معكرونة كاربونارا', 'معكرونة بالكريمة والبيض والجبن', 95.00, 'معكرونة', 1, 15, NOW(), NOW()),
(13, 2, 'سلطة سيزر', 'سلطة طازجة مع الدجاج والجبن والصلصة', 60.00, 'سلطات', 1, 10, NOW(), NOW()),
(14, 2, 'مشروب كولا', 'مشروب غازي منعش', 12.00, 'مشروبات', 1, 2, NOW(), NOW()),

-- منتجات برجر هاوس
(15, 3, 'برجر كلاسيك', 'برجر لحم مع الخضار والصلصات', 55.00, 'برجر', 1, 15, NOW(), NOW()),
(16, 3, 'برجر دجاج', 'برجر دجاج مشوي مع الخضار الطازجة', 50.00, 'برجر', 1, 15, NOW(), NOW()),
(17, 3, 'برجر دبل', 'برجر مزدوج باللحم والجبن', 75.00, 'برجر', 1, 18, NOW(), NOW()),
(18, 3, 'بطاطس مقلية', 'بطاطس مقلية مقرمشة مع الملح', 25.00, 'مقبلات', 1, 8, NOW(), NOW()),
(19, 3, 'حلقات البصل', 'حلقات بصل مقلية مقرمشة', 30.00, 'مقبلات', 1, 10, NOW(), NOW()),
(20, 3, 'ميلك شيك فراولة', 'ميلك شيك بالفراولة الطازجة', 35.00, 'مشروبات', 1, 5, NOW(), NOW()),
(21, 3, 'ميلك شيك شوكولاتة', 'ميلك شيك بالشوكولاتة الكريمية', 35.00, 'مشروبات', 1, 5, NOW(), NOW()),

-- منتجات مطعم الصين الذهبية
(22, 4, 'أرز مقلي بالدجاج', 'أرز مقلي بالدجاج والخضار الصينية', 70.00, 'أطباق صينية', 1, 15, NOW(), NOW()),
(23, 4, 'نودلز باللحم', 'نودلز باللحم والخضار الصينية', 65.00, 'نودلز', 1, 12, NOW(), NOW()),
(24, 4, 'دجاج بالبرتقال', 'دجاج مقلي بصلصة البرتقال الحلوة', 80.00, 'أطباق صينية', 1, 18, NOW(), NOW()),
(25, 4, 'سبرينج رولز', 'لفائف الربيع المقرمشة بالخضار', 40.00, 'مقبلات', 1, 10, NOW(), NOW()),
(26, 4, 'شاي أخضر', 'شاي أخضر صيني أصيل', 15.00, 'مشروبات', 1, 3, NOW(), NOW()),

-- منتجات كافيه الحلويات
(27, 5, 'كنافة بالجبن', 'كنافة نابلسية بالجبن والقطر', 45.00, 'حلويات شرقية', 1, 20, NOW(), NOW()),
(28, 5, 'بسبوسة', 'بسبوسة بالعسل والجوز', 35.00, 'حلويات شرقية', 1, 15, NOW(), NOW()),
(29, 5, 'تيراميسو', 'تيراميسو إيطالي بالقهوة والجبن', 55.00, 'حلويات غربية', 1, 25, NOW(), NOW()),
(30, 5, 'تشيز كيك', 'تشيز كيك بالفراولة', 50.00, 'حلويات غربية', 1, 30, NOW(), NOW()),
(31, 5, 'قهوة إسبريسو', 'قهوة إسبريسو إيطالية أصيلة', 25.00, 'قهوة', 1, 5, NOW(), NOW()),
(32, 5, 'كابتشينو', 'كابتشينو بالحليب الرغوي', 30.00, 'قهوة', 1, 7, NOW(), NOW()),
(33, 5, 'لاتيه', 'لاتيه بالحليب والرغوة', 28.00, 'قهوة', 1, 6, NOW(), NOW()),

-- منتجات مطعم البحار
(34, 6, 'سمك مشوي', 'سمك مشوي بالليمون والأعشاب', 120.00, 'مأكولات بحرية', 1, 25, NOW(), NOW()),
(35, 6, 'جمبري مقلي', 'جمبري مقلي بالثوم والزبد', 100.00, 'مأكولات بحرية', 1, 20, NOW(), NOW()),
(36, 6, 'كابوريا', 'كابوريا بالصلصة الحارة', 90.00, 'مأكولات بحرية', 1, 30, NOW(), NOW()),
(37, 6, 'سلطة بحرية', 'سلطة بالجمبري والكابوريا', 70.00, 'سلطات', 1, 15, NOW(), NOW()),
(38, 6, 'مشروب ليمونادة', 'ليمونادة طازجة بالليمون والنعناع', 18.00, 'مشروبات', 1, 5, NOW(), NOW()),

-- منتجات مطعم الهندي الذهبي
(39, 7, 'دجاج تيكا ماسالا', 'دجاج بالصلصة الهندية الحارة', 85.00, 'أطباق هندية', 1, 20, NOW(), NOW()),
(40, 7, 'لحم بالكاري', 'لحم بالكاري الهندي الأصيل', 90.00, 'أطباق هندية', 1, 25, NOW(), NOW()),
(41, 7, 'أرز بسمتي', 'أرز بسمتي هندي أصيل', 25.00, 'أرز', 1, 10, NOW(), NOW()),
(42, 7, 'نان خبز', 'خبز نان هندي طازج', 15.00, 'خبز', 1, 8, NOW(), NOW()),
(43, 7, 'مشروب لاسي', 'مشروب لاسي بالمانجو', 20.00, 'مشروبات', 1, 5, NOW(), NOW()),

-- منتجات مطعم اللحوم المشوية
(44, 8, 'ستيك لحم', 'ستيك لحم مشوي بالملح والفلفل', 150.00, 'مشويات', 1, 30, NOW(), NOW()),
(45, 8, 'كباب مشوي', 'كباب لحم مشوي مع الخضار', 80.00, 'مشويات', 1, 25, NOW(), NOW()),
(46, 8, 'دجاج مشوي', 'دجاج مشوي كامل مع التوابل', 70.00, 'مشويات', 1, 35, NOW(), NOW()),
(47, 8, 'سلطة خضار', 'سلطة خضار طازجة مع الخل', 30.00, 'سلطات', 1, 8, NOW(), NOW()),
(48, 8, 'مشروب غازي', 'مشروب غازي منعش', 12.00, 'مشروبات', 1, 2, NOW(), NOW());

-- إدراج السائقين
INSERT INTO `couriers` (`id`, `user_id`, `vehicle_type`, `license_number`, `latitude`, `longitude`, `is_available`, `rating`, `total_deliveries`, `total_earnings`, `created_at`, `updated_at`) VALUES
(1, 3, 'دراجة نارية', 'MC123456', 30.0444, 31.2357, 1, 4.50, 150, 2500.00, NOW(), NOW()),
(2, 3, 'سيارة', 'CAR789012', 30.0131, 31.2089, 1, 4.30, 120, 3000.00, NOW(), NOW()),
(3, 3, 'دراجة نارية', 'MC345678', 30.0444, 31.2357, 0, 4.60, 200, 4000.00, NOW(), NOW()),
(4, 3, 'دراجة هوائية', 'BIKE901234', 30.0131, 31.2089, 1, 4.20, 80, 1500.00, NOW(), NOW()),
(5, 3, 'سيارة', 'CAR567890', 30.0444, 31.2357, 1, 4.40, 180, 3500.00, NOW(), NOW());

-- إدراج طلبات تجريبية
INSERT INTO `orders` (`id`, `order_number`, `user_id`, `restaurant_id`, `courier_id`, `status`, `subtotal`, `delivery_fee`, `tax`, `total_price`, `delivery_address`, `delivery_latitude`, `delivery_longitude`, `notes`, `estimated_delivery_time`, `created_at`, `updated_at`) VALUES
(1, 'ORD-2024-001', 4, 1, 1, 'delivered', 150.00, 15.00, 16.50, 181.50, 'مدينة نصر، القاهرة', 30.0444, 31.2357, 'الطابق الثالث', '2024-01-15 14:30:00', '2024-01-15 13:45:00', '2024-01-15 14:30:00'),
(2, 'ORD-2024-002', 4, 2, 2, 'delivered', 250.00, 20.00, 27.00, 297.00, 'شارع التحرير، القاهرة', 30.0444, 31.2357, 'مكتب رقم 15', '2024-01-16 19:45:00', '2024-01-16 19:00:00', '2024-01-16 19:45:00'),
(3, 'ORD-2024-003', 4, 3, 4, 'picked_up', 120.00, 12.00, 13.20, 145.20, 'المعادي، القاهرة', 30.0131, 31.2089, 'فيلا رقم 8', '2024-01-17 20:15:00', '2024-01-17 19:30:00', '2024-01-17 20:00:00'),
(4, 'ORD-2024-004', 4, 5, 5, 'preparing', 85.00, 10.00, 9.50, 104.50, 'مدينة نصر، القاهرة', 30.0444, 31.2357, 'شقة رقم 12', '2024-01-17 21:00:00', '2024-01-17 20:15:00', '2024-01-17 20:30:00'),
(5, 'ORD-2024-005', 4, 6, 1, 'confirmed', 200.00, 25.00, 22.50, 247.50, 'كورنيش النيل، القاهرة', 30.0444, 31.2357, 'مطعم الشاطئ', '2024-01-17 22:30:00', '2024-01-17 21:45:00', '2024-01-17 22:00:00');

-- إدراج عناصر الطلبات التجريبية
INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`, `notes`, `created_at`, `updated_at`) VALUES
-- عناصر الطلب الأول
(1, 1, 1, 1, 85.00, '', NOW(), NOW()),
(2, 1, 2, 1, 65.00, '', NOW(), NOW()),

-- عناصر الطلب الثاني
(3, 2, 8, 1, 120.00, '', NOW(), NOW()),
(4, 2, 9, 1, 140.00, '', NOW(), NOW()),
(5, 2, 14, 2, 12.00, '', NOW(), NOW()),

-- عناصر الطلب الثالث
(6, 3, 15, 1, 55.00, '', NOW(), NOW()),
(7, 3, 16, 1, 50.00, '', NOW(), NOW()),
(8, 3, 18, 1, 25.00, '', NOW(), NOW()),

-- عناصر الطلب الرابع
(9, 4, 27, 1, 45.00, '', NOW(), NOW()),
(10, 4, 31, 1, 25.00, '', NOW(), NOW()),
(11, 4, 32, 1, 30.00, '', NOW(), NOW()),

-- عناصر الطلب الخامس
(12, 5, 34, 1, 120.00, '', NOW(), NOW()),
(13, 5, 35, 1, 100.00, '', NOW(), NOW()),
(14, 5, 38, 1, 18.00, '', NOW(), NOW());

-- إدراج تقييمات تجريبية
INSERT INTO `reviews` (`id`, `user_id`, `target_type`, `target_id`, `rating`, `comment`, `created_at`, `updated_at`) VALUES
(1, 4, 'App\\Models\\Restaurant', 1, 5, 'مطعم رائع والطعام لذيذ جداً', NOW(), NOW()),
(2, 4, 'App\\Models\\Courier', 1, 4, 'توصيل سريع ومهذب', NOW(), NOW()),
(3, 4, 'App\\Models\\Restaurant', 2, 4, 'البيتزا لذيذة جداً والعجين طازج', NOW(), NOW()),
(4, 4, 'App\\Models\\Courier', 2, 5, 'سائق ممتاز ومهذب', NOW(), NOW()),
(5, 4, 'App\\Models\\Restaurant', 3, 4, 'البرجر لذيذ والبطاطس مقرمشة', NOW(), NOW()),
(6, 4, 'App\\Models\\Restaurant', 5, 5, 'الحلويات رائعة والقهوة ممتازة', NOW(), NOW()),
(7, 4, 'App\\Models\\Restaurant', 6, 5, 'المأكولات البحرية طازجة ولذيذة', NOW(), NOW()),
(8, 4, 'App\\Models\\Courier', 4, 4, 'توصيل سريع ومرتب', NOW(), NOW());

-- إدراج إشعارات تجريبية
INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `data`, `read_at`, `created_at`, `updated_at`) VALUES
(1, 4, 'order_status', 'تحديث حالة الطلب', 'تم تسليم طلبك بنجاح', '{"order_id": 1, "status": "delivered"}', NULL, NOW(), NOW()),
(2, 2, 'new_order', 'طلب جديد', 'تم استلام طلب جديد من العميل', '{"order_id": 1, "customer_name": "فاطمة أحمد"}', NULL, NOW(), NOW()),
(3, 4, 'order_status', 'تحديث حالة الطلب', 'تم تسليم طلبك بنجاح', '{"order_id": 2, "status": "delivered"}', NULL, NOW(), NOW()),
(4, 2, 'new_order', 'طلب جديد', 'تم استلام طلب جديد من العميل', '{"order_id": 2, "customer_name": "فاطمة أحمد"}', NULL, NOW(), NOW()),
(5, 4, 'order_status', 'تحديث حالة الطلب', 'تم استلام طلبك من المطعم', '{"order_id": 3, "status": "picked_up"}', NULL, NOW(), NOW()),
(6, 3, 'new_order', 'طلب جديد', 'تم تعيين طلب جديد لك', '{"order_id": 3, "restaurant_name": "برجر هاوس"}', NULL, NOW(), NOW()),
(7, 4, 'order_status', 'تحديث حالة الطلب', 'يتم تحضير طلبك الآن', '{"order_id": 4, "status": "preparing"}', NULL, NOW(), NOW()),
(8, 2, 'new_order', 'طلب جديد', 'تم استلام طلب جديد من العميل', '{"order_id": 4, "customer_name": "فاطمة أحمد"}', NULL, NOW(), NOW()),
(9, 4, 'order_status', 'تحديث حالة الطلب', 'تم تأكيد طلبك', '{"order_id": 5, "status": "confirmed"}', NULL, NOW(), NOW()),
(10, 2, 'new_order', 'طلب جديد', 'تم استلام طلب جديد من العميل', '{"order_id": 5, "customer_name": "فاطمة أحمد"}', NULL, NOW(), NOW());

COMMIT;
