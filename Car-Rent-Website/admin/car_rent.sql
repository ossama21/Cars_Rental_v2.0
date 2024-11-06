-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 06, 2024 at 10:57 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `car_rent`
--

-- --------------------------------------------------------

--
-- Table structure for table `cars`
--

CREATE TABLE `cars` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text NOT NULL,
  `model` varchar(100) NOT NULL,
  `transmission` varchar(50) DEFAULT NULL,
  `interior` varchar(50) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cars`
--

INSERT INTO `cars` (`id`, `name`, `price`, `description`, `model`, `transmission`, `interior`, `brand`, `image`) VALUES
(1, 'Hyundai i20', 100.00, 'The Hyundai i20 is a sleek hatchback, ideal for city and long trips. Fuel efficient.', 'i20', 'Automatic', 'Fabric', 'Hyundai', 'images/img1.png'),
(2, 'Skoda Rapid', 250.00, 'The Skoda Rapid combines elegance with performance. Spacious cabin, high-end infotainment, excellent mileage, durable.', 'Rapid', 'Automatic', 'Fabric', 'Skoda', 'images/img2.png'),
(3, 'Tata Nexon', 130.00, 'Stylish Tata Nexon SUV with bold design, safety features, good mileage on city streets, terrains.', 'Nexon', 'Automatic', 'Fabric', 'Tata', 'images/img3.png'),
(4, 'Scorpio', 280.00, 'Mahindra Scorpio is a rugged SUV with bold exterior, powerful engine, excellent urban, rural performance.', 'Scorpio', 'Automatic', 'Fabric', 'Mahindra', 'images/img4.png'),
(5, 'Baleno', 300.00, 'Baleno, a premium 7-seater SUV with luxury, power, advanced technology, spacious interiors, safety features.', 'Hexa', 'Automatic', 'Leather', 'Tata', 'images/img5.png'),
(6, 'Tata Tiago', 200.00, 'Tata Tiago is an affordable, fuel-efficient hatchback with modern design, comfortable seating, advanced safety features.', 'Tiago', 'Automatic', 'Fabric', 'Tata', 'images/img6.png'),
(7, 'Suzuki Baleno', 220.00, 'Suzuki Baleno is a premium hatchback with stylish design, spacious interiors, fuel efficiency, latest technology.', 'Baleno', 'Automatic', 'Fabric', 'Suzuki', 'images/img7.png'),
(8, 'HYUNDAI City', 280.00, 'Hyundai City offers a premium look, reliable performance, refined driving, advanced infotainment, spacious, comfortable seating.', 'City', 'Automatic', 'Fabric', 'Hyundai', 'images/img8.png'),
(9, 'Creta', 300.00, 'Creta offers a premium look, reliable performance, refined driving, advanced infotainment, spacious, comfortable seating.', 'creta', 'Automatic', 'Fabric', 'Creta', 'images/img10.png'),
(10, 'Mustang', 280.00, 'The Mustang is one of the best muscle cars in the world and the oldest', 'EcoBoost 2.3', 'Automatic', 'Sport', 'Ford', 'images/img22.png'),
(17, 'SEAT', 120.00, 'add a slide talking about each team member and his specialisation and and what have he worked on in this project . put this slide int number two', '2023', 'Automatic', 'Lether', 'Ibiza', './images/img13.png');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `duration` int(11) NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `username`, `phone`, `start_date`, `end_date`, `duration`, `amount`, `email`) VALUES
(74, 'fffgrhrh', '127575', '2024-09-23', '2024-09-25', 2, 260.00, 'jyjjjyj@gmail.com'),
(75, 'test', '023145', '2024-09-24', '2024-09-26', 2, 700.00, 'fjfjd@ghg.com'),
(76, 'User user', '0123456789', '2024-09-24', '2024-09-30', 6, 1500.00, 'user@gmail.com'),
(77, 'User user', '0123456789', '2024-09-24', '2024-09-28', 4, 400.00, 'user@gmail.com'),
(78, 'User user', '0123456789', '2024-09-24', '2024-09-26', 2, 500.00, 'user@gmail.com'),
(79, 'new', '0123456789', '2024-09-23', '2024-09-26', 3, 750.00, 'new@gmail.com'),
(80, 'new', '0123456789', '2024-09-25', '2024-09-28', 3, 840.00, 'new@gmail.com'),
(81, 'new', '0123456789', '2024-09-24', '2024-10-04', 10, 1200.00, 'new@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('admin','user') DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstName`, `lastName`, `email`, `password`, `created_at`, `role`) VALUES
(3, 'ali', 'idrissi', 'ali@gmail.com', '202cb962ac59075b964b07152d234b70', '2024-09-18 22:35:11', 'admin'),
(4, 'walo', 'lolo', 'walo@gmail.com', '202cb962ac59075b964b07152d234b70', '2024-09-18 22:45:21', 'user'),
(6, 'hhhh', 'hhhh', 'admin@ghg.com', '81dc9bdb52d04dc20036dbd8313ed055', '2024-09-22 20:49:41', 'admin'),
(7, 'thth', 'htht', 'httthth@ghg.com', '81dc9bdb52d04dc20036dbd8313ed055', '2024-09-23 00:36:50', 'user'),
(10, 'new', 'account', 'newaccount@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '2024-09-23 11:04:10', 'user'),
(11, 'admin', 'user', 'admin.user@gmail.com', 'bb7946e7d85c81a9e69fee1cea4a087c', '2024-09-23 11:06:52', 'admin'),
(12, 'new', 'new', 'new@gmail.com', '81dc9bdb52d04dc20036dbd8313ed055', '2024-09-23 11:08:57', 'admin');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cars`
--
ALTER TABLE `cars`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `cars`
--
ALTER TABLE `cars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
