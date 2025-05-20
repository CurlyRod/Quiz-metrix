-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 20, 2025 at 09:21 PM
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
-- Database: `quizmetrix`
--

-- --------------------------------------------------------

--
-- Table structure for table `shortcut_url`
--

CREATE TABLE `shortcut_url` (
  `id` int(11) NOT NULL,
  `sitename` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `user_id` int(11) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shortcut_url`
--

INSERT INTO `shortcut_url` (`id`, `sitename`, `url`, `user_id`, `date_created`) VALUES
(1, 'Google', 'https://www.google.com/', 1, '2025-05-19 22:37:45'),
(4, 'w3schools', 'https://www.w3schools.com/', 1, '2025-05-21 02:52:27'),
(5, 'Bootstrap', 'https://getbootstrap.com/', 1, '2025-05-21 03:02:03'),
(8, 'Youtube.', 'https://www.youtube.com/', 1, '2025-05-21 03:06:14'),
--
-- Indexes for dumped tables
--

--
-- Indexes for table `shortcut_url`
--
ALTER TABLE `shortcut_url`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `shortcut_url`
--
ALTER TABLE `shortcut_url`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
