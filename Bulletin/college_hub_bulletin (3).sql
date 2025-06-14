-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 11, 2025 at 02:15 AM
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
-- Database: `college_hub_bulletin`
--

-- --------------------------------------------------------

--
-- Table structure for table `about_college_hub`
--

CREATE TABLE `about_college_hub` (
  `id` int(11) NOT NULL,
  `headline` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `features` text NOT NULL,
  `citations` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `about_college_hub`
--

INSERT INTO `about_college_hub` (`id`, `headline`, `description`, `features`, `citations`, `image_path`) VALUES
(1, 'College Hub Bulletin Board System', 'An all-in-one digital platform to streamline communication, guidance services, and manage academic and administrative tasks efficiently within the college or university.', '<ul>\r\n<li><strong>Lack of a Centralized Platform:</strong> Students often miss out on updates because information is scattered.</li>\r\n<li><strong>Accessibility:</strong> Designed with inclusivity and accessibility in mind.</li>\r\n</ul>', '- Doe (2023): 60% of students struggle to receive real-time updates about scholarship opportunities.<br>- Smith et al. (2022): Systems like Blackboard have been proven effective in enhancing academic communication.', '');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `announcement_type` enum('general','academic','event','emergency') DEFAULT 'general',
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `target_audience` enum('all','students','faculty','staff') DEFAULT 'all',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `author` varchar(100) NOT NULL DEFAULT 'Admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `user_id`, `title`, `content`, `announcement_type`, `priority`, `start_date`, `end_date`, `target_audience`, `is_active`, `created_at`, `author`) VALUES
(1, 1, 'EDSA People Power Revolution Holiday', 'February 25, 2025 is declared as a special non-working holiday in commemoration of the EDSA People Power Revolution Anniversary.', 'general', 'high', '2025-02-24', '2025-02-25', 'all', 1, '2025-05-31 19:11:43', 'Admin'),
(2, 1, 'Labor Day Holiday', 'May 1, 2025 is a regular holiday in observance of Labor Day. Classes and work are suspended.', 'general', 'medium', '2025-04-30', '2025-05-01', 'all', 1, '2025-05-31 19:11:43', 'Admin'),
(3, 1, 'System Launch', 'Welcome to the new College Hub Bulletin Board System!', 'general', 'high', '2025-06-03', '2025-07-03', 'all', 1, '2025-06-03 13:30:17', 'Admin'),
(5, 9, 'UTOK', 'HHAHAHAHAHAH', 'general', 'medium', '0000-00-00', '0000-00-00', 'all', 1, '2025-06-10 16:42:44', 'ain'),
(6, 9, 'HALA', 'HAHAHAHAAH', 'general', 'medium', '0000-00-00', '0000-00-00', 'all', 1, '2025-06-10 16:43:14', 'ain');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `color_code` varchar(7) DEFAULT '#007bff',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` varchar(100) NOT NULL DEFAULT 'General',
  `name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`, `description`, `color_code`, `created_at`, `category`, `name`) VALUES
(1, 'Academic Resources', 'Study materials, assignments, and academic information', '#28a745', '2025-05-31 19:11:42', 'Career', NULL),
(2, 'Career Services', 'Job opportunities, internships, and career guidance', '#17a2b8', '2025-05-31 19:11:42', 'Events', NULL),
(3, 'Student Life', 'Events, clubs, and student activities', '#ffc107', '2025-05-31 19:11:42', 'General', NULL),
(4, 'Financial Aid', 'Scholarships, grants, and financial assistance', '#dc3545', '2025-05-31 19:11:42', 'General', NULL),
(5, 'General', 'General announcements and information', '#6c757d', '2025-05-31 19:11:42', 'General', NULL),
(6, 'Academic Resources', 'Study materials, textbooks, and educational content', '#28a745', '2025-06-03 13:29:50', 'General', NULL),
(7, 'Career Services', 'Job postings, internships, and career guidance', '#007bff', '2025-06-03 13:29:50', 'General', NULL),
(8, 'Financial Aid', 'Scholarships, grants, and financial assistance', '#ffc107', '2025-06-03 13:29:50', 'General', NULL),
(9, 'Student Life', 'Events, clubs, and campus activities', '#dc3545', '2025-06-03 13:29:50', 'General', NULL),
(10, 'General', 'General announcements and discussions', '#6c757d', '2025-06-03 13:29:50', 'General', NULL),
(11, 'Academic Resources', 'Study materials, textbooks, and educational content', '#28a745', '2025-06-03 13:30:17', 'General', NULL),
(12, 'Career Services', 'Job postings, internships, and career guidance', '#007bff', '2025-06-03 13:30:17', 'General', NULL),
(13, 'Financial Aid', 'Scholarships, grants, and financial assistance', '#ffc107', '2025-06-03 13:30:17', 'General', NULL),
(14, 'Student Life', 'Events, clubs, and campus activities', '#dc3545', '2025-06-03 13:30:17', 'General', NULL),
(15, 'General', 'General announcements and discussions', '#6c757d', '2025-06-03 13:30:17', 'General', NULL),
(16, 'General', NULL, '#007bff', '2025-06-03 14:54:17', 'General', NULL),
(17, '', NULL, '#007bff', '2025-06-04 04:22:52', 'Career', NULL),
(18, '', NULL, '#007bff', '2025-06-04 04:22:52', 'Events', NULL),
(19, '', NULL, '#007bff', '2025-06-04 04:22:52', 'Announcements', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `event_date` date NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `event_date`, `description`, `created_at`) VALUES
(1, 'Graduation Day', '2025-06-19', 'agikaa', '2025-06-10 22:36:48'),
(2, 'mwuu', '2025-06-13', 'dddd', '2025-06-10 22:58:41');

-- --------------------------------------------------------

--
-- Table structure for table `financial_office_info`
--

CREATE TABLE `financial_office_info` (
  `id` int(11) NOT NULL,
  `deadlines` text NOT NULL,
  `contact_email` varchar(100) NOT NULL,
  `contact_phone` varchar(50) NOT NULL,
  `office_days` varchar(100) NOT NULL,
  `office_hours` varchar(100) NOT NULL,
  `walkin_label` varchar(100) DEFAULT NULL,
  `walkin_hours` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financial_office_info`
--

INSERT INTO `financial_office_info` (`id`, `deadlines`, `contact_email`, `contact_phone`, `office_days`, `office_hours`, `walkin_label`, `walkin_hours`) VALUES
(1, 'June 30: FAFSA Application<hr>July 1: Scholarship Applications<hr>July 15: Fall Payment Due', 'finaid@collegehub.edu', '(555) 123-4567', 'Monday - Friday', '8:00 AM - 4:30 PM', 'Walk-in Hours', '9:00 AM - 3:00 PM');

-- --------------------------------------------------------

--
-- Table structure for table `office_hours`
--

CREATE TABLE `office_hours` (
  `id` int(11) NOT NULL,
  `days` varchar(100) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `office_hours`
--

INSERT INTO `office_hours` (`id`, `days`, `start_time`, `end_time`, `email`, `phone`) VALUES
(1, 'Monday - Saturday', '06:00:00', '17:00:00', 'SSU.EDU@gmail.com', '09758283955');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_urgent` tinyint(1) DEFAULT 0,
  `expiry_date` date DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'published',
  `views_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `category` varchar(100) DEFAULT 'General',
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quick_tips`
--

CREATE TABLE `quick_tips` (
  `id` int(11) NOT NULL,
  `icon` varchar(10) DEFAULT NULL,
  `title` varchar(100) NOT NULL,
  `tip` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quick_tips`
--

INSERT INTO `quick_tips` (`id`, `icon`, `title`, `tip`, `created_at`) VALUES
(1, 'tryy', 'again', 'and again', '2025-06-10 22:47:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('student','faculty','admin') DEFAULT 'student',
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `username`, `email`, `password`, `user_type`, `profile_image`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 'Admin', 'User', 'admin', 'admin@college.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, '2025-05-31 19:11:43', '2025-05-31 19:11:43', 1),
(6, 'makmak', 'castroo', 'makmak', 'hilvano@gmail.com', '$2y$10$wAmiejq2jbWx7lu7SREk8eimykljgPQmHC8L8lkIdVYkMsejERb6K', '', NULL, '2025-06-08 14:40:16', '2025-06-08 14:40:16', 1),
(9, 'ain', 'versoxa', 'ain', 'yanyan@gmail.com', '$2y$10$xMYPVyluD.ht54Ue8aFWBe.VaWODaVfyUk7ZHquR1ugiHWnxaqn/2', 'admin', NULL, '2025-06-10 08:33:05', '2025-06-10 08:33:05', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about_college_hub`
--
ALTER TABLE `about_college_hub`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `financial_office_info`
--
ALTER TABLE `financial_office_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `office_hours`
--
ALTER TABLE `office_hours`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `quick_tips`
--
ALTER TABLE `quick_tips`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `quick_tips`
--
ALTER TABLE `quick_tips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `posts_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `posts_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `posts_ibfk_4` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
