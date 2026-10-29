
-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 28, 2026 at 10:34 PM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.5.12

-- Configure SQL behavior for this database import.
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- Start a transaction so the import operations can be grouped together.
START TRANSACTION;

-- Use UTC as the session time zone during this import.
SET time_zone = "+00:00";

-- Save the current connection character settings before changing them.
-- These settings are restored at the end of the import.
 /*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
 /*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
 /*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;

-- Set UTF-8 encoding for the database import.
 /*!40101 SET NAMES utf8mb4 */;

-- Database: `serviceready`

-- --------------------------------------------------------

-- TABLE 1: attempts
-- Stores each submitted assessment, its original response, overall score,
-- selected scenario, and submission timestamp.
CREATE TABLE `attempts` (
  `id` int(11) NOT NULL,
  `scenario_id` int(11) NOT NULL,
  `response_text` text NOT NULL,
  `total_score` decimal(5,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- TABLE 2: attempt_scores
-- Stores the score and feedback for each criterion evaluated in an attempt.
-- Multiple rows can belong to the same assessment.
CREATE TABLE `attempt_scores` (
  `id` int(11) NOT NULL,
  `attempt_id` int(11) NOT NULL,
  `criterion_id` int(11) NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `feedback` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- TABLE 3: criteria
-- Defines how each scenario is evaluated, including the criterion name,
-- description, weight, rule type, and configured rule values.
CREATE TABLE `criteria` (
  `id` int(11) NOT NULL,
  `scenario_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `weight` int(11) NOT NULL,
  `rule_type` varchar(50) NOT NULL,
  `rule_value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- TABLE 4: scenarios
-- Stores the practice scenarios displayed to users.
-- is_active determines whether a scenario can currently be selected.
CREATE TABLE `scenarios` (
  `id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `category` varchar(100) NOT NULL,
  `customer_message` text NOT NULL,
  `instructions` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

-- Add primary keys and indexes to support record identification and joins.
-- A primary key uniquely identifies each row.
-- The additional indexes help the database find related rows efficiently.

-- Indexes for table `attempts`
ALTER TABLE `attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_attempts_scenario` (`scenario_id`);

-- Indexes for table `attempt_scores`
ALTER TABLE `attempt_scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_scores_attempt` (`attempt_id`),
  ADD KEY `fk_scores_criterion` (`criterion_id`);

-- Indexes for table `criteria`
ALTER TABLE `criteria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_criteria_scenario` (`scenario_id`);

-- Indexes for table `scenarios`
ALTER TABLE `scenarios`
  ADD PRIMARY KEY (`id`);

-- --------------------------------------------------------

-- Enable automatic ID generation for new records.
-- AUTO_INCREMENT assigns a new ID when one is not provided during an insert.

-- AUTO_INCREMENT for table `attempts`
ALTER TABLE `attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- AUTO_INCREMENT for table `attempt_scores`
ALTER TABLE `attempt_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- AUTO_INCREMENT for table `criteria`
ALTER TABLE `criteria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- AUTO_INCREMENT for table `scenarios`
ALTER TABLE `scenarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- --------------------------------------------------------

-- Define foreign keys to enforce relationships between tables.
-- A foreign key prevents a record from referring to a parent record
-- that does not exist.

-- Each assessment attempt must refer to an existing scenario.
ALTER TABLE `attempts`
  ADD CONSTRAINT `fk_attempts_scenario` FOREIGN KEY (`scenario_id`) REFERENCES `scenarios` (`id`);

-- Each criterion score must refer to an existing attempt and criterion.
ALTER TABLE `attempt_scores`
  ADD CONSTRAINT `fk_scores_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `attempts` (`id`),
  ADD CONSTRAINT `fk_scores_criterion` FOREIGN KEY (`criterion_id`) REFERENCES `criteria` (`id`);

-- Each evaluation criterion must belong to an existing scenario.
ALTER TABLE `criteria`
  ADD CONSTRAINT `fk_criteria_scenario` FOREIGN KEY (`scenario_id`) REFERENCES `scenarios` (`id`);

-- Finish the transaction and commit the schema changes.
COMMIT;

-- Restore the original connection character settings.
 /*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
 /*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
 /*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
