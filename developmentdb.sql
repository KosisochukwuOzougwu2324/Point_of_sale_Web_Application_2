-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: mysql
-- Generation Time: Apr 05, 2026 at 10:21 PM
-- Server version: 12.0.2-MariaDB-ubu2404
-- PHP Version: 8.3.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `developmentdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `driver_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `delivery_method` enum('Pickup','Delivery') NOT NULL DEFAULT 'Pickup',
  `delivery_address` text DEFAULT NULL,
  `delivery_phone` varchar(20) DEFAULT NULL,
  `payment_method` enum('Online','Pay on Delivery','Pay at Pickup') NOT NULL DEFAULT 'Pay at Pickup',
  `payment_status` enum('Pending','Paid','Failed') NOT NULL DEFAULT 'Pending',
  `stripe_payment_id` varchar(255) DEFAULT NULL,
  `order_status` enum('Pending','Processing','Out for Delivery','Delivered','Ready for Pickup','Picked Up','Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `product_code` varchar(50) NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `quantity`, `product_code`, `image_url`, `category`, `created_at`, `updated_at`) VALUES
(1, 'Aardappelen', 'Irish Potatoes, whole or in sliced shapes.', 200.00, 500, '000005', 'https://images.unsplash.com/photo-1590165482129-1b8b27698780?w=400', 'Vegetables', '2026-04-05 20:29:22', '2026-04-05 20:34:07'),
(2, 'Strawberries', 'Red-spotted heart-shaped fruit.', 300.00, 200, '000007', 'https://images.unsplash.com/photo-1464965911861-746a04b4bca6?w=400', 'Fruits', '2026-04-05 20:29:22', '2026-04-05 20:29:22'),
(3, 'Kip Gehakt', 'Chicken minced meat.', 400.00, 150, '000008', 'https://images.unsplash.com/photo-1602470520998-f4a52199a3d6?w=400', 'Meat', '2026-04-05 20:29:22', '2026-04-05 20:34:47'),
(4, 'Milk', 'Full cream milk.', 500.00, 300, '000009', 'https://images.unsplash.com/photo-1563636619-e9143da7973b?w=400', 'Dairy', '2026-04-05 20:29:22', '2026-04-05 20:29:22'),
(5, 'Avocado', 'Pear-shaped fruit.', 800.00, 250, '500005', 'https://images.unsplash.com/photo-1523049673857-eb18f1d7b578?w=400', 'Fruits', '2026-04-05 20:29:22', '2026-04-05 20:29:22'),
(6, 'Tortilla Wraps', 'Soft thin round flatbread used for Shawarma.', 350.00, 400, '000089', 'https://images.unsplash.com/photo-1626700051175-6818013e1d4f?w=400', 'Bakery', '2026-04-05 20:29:22', '2026-04-05 20:29:22'),
(7, 'Hot Chocolate', 'Warm cocoa beverage.', 105.00, 300, '000014', 'https://images.unsplash.com/photo-1517578239113-b03992dcdd25?w=400', 'Beverages', '2026-04-05 20:29:22', '2026-04-05 20:29:22'),
(8, 'Sushi', 'Cold fish wrapped in rice and seaweed.', 350.00, 100, '000012', 'https://images.unsplash.com/photo-1579871494447-9811cf80d66c?w=400', 'Ready Meals', '2026-04-05 20:29:22', '2026-04-05 20:29:22'),
(9, 'Clipper', 'Equipment for Men\'s Barbing.', 200.00, 200, '000025', 'https://images.unsplash.com/photo-1621607512214-68297480165e?w=400', 'Electronics', '2026-04-05 20:29:22', '2026-04-05 20:29:22'),
(10, 'Egg', 'L. 30 in one crate.', 56000.00, 399, '000045', 'https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?w=400', 'Dairy', '2026-04-05 20:29:22', '2026-04-05 20:29:22');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Editor','User','Customer','Driver') NOT NULL DEFAULT 'Customer',
  `status` enum('Active','Blocked') NOT NULL DEFAULT 'Active',
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `status`, `phone`, `address`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin@pos.com', '$2y$12$0.HpRWNEdG8a/azbaK4/c.ZWUgoMiOfECHHSbLiQD1C3kUuzDDONW', 'Admin', 'Active', NULL, NULL, '2026-04-05 20:29:22', '2026-04-05 20:30:45'),
(2, 'MaryJane', 'emjay3669@gmail.com', '$2y$12$5vxOpF7Qihlm4wWF7zdpAe6SwYkjaRDRNsRqMqcKGiVbvWbOyzV.6', 'User', 'Active', NULL, NULL, '2026-04-05 22:09:32', '2026-04-05 22:09:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_orders_customer` (`customer_id`),
  ADD KEY `fk_orders_driver` (`driver_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_items_order` (`order_id`),
  ADD KEY `fk_order_items_product` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_code` (`product_code`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sales_user` (`user_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_sale_items_sale` (`sale_id`),
  ADD KEY `fk_sale_items_product` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_customer` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_orders_driver` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `fk_sales_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD CONSTRAINT `fk_sale_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `fk_sale_items_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
