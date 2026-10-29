
-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 28, 2026 at 10:37 PM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.5.12

-- Configure SQL behavior for this import.
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

-- Group the seed-data inserts into a transaction.
START TRANSACTION;

-- Use UTC as the session time zone during this import.
SET time_zone = "+00:00";

-- Save the current connection character settings for restoration later.
 /*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
 /*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
 /*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;

-- Set UTF-8 encoding for the import.
 /*!40101 SET NAMES utf8mb4 */;

-- Database: `serviceready`

-- --------------------------------------------------------

-- Insert the evaluation criteria for the three practice scenarios.
-- Each row defines a criterion name, explanation, weight, rule type,
-- and semicolon-separated keywords used by the scoring logic.
INSERT INTO `criteria` (`id`, `scenario_id`, `name`, `description`, `weight`, `rule_type`, `rule_value`) VALUES
(1, 1, 'Acknowledgement', 'Acknowledges the customer concern or inconvenience.', 30, 'keyword', 'sorry;apologize;understand;inconvenience;concern;delay'),
(2, 1, 'Clear next steps', 'Explains a useful next step for resolving the delivery issue.', 40, 'keyword', 'tracking;check;investigate;update;delivery;order id;order details;confirm;contact;resolve;next step'),
(3, 1, 'Professional tone', 'Uses courteous and professional language.', 30, 'keyword', 'please;thank you;help;appreciate;could you;I will;we will'),
(4, 2, 'Acknowledgement', 'Recognizes the customer request for a return or refund.', 30, 'keyword', 'sorry;understand;concern;disappointed;inconvenience;thank you'),
(5, 2, 'Policy awareness', 'Explains that eligibility and policy details must be checked.', 40, 'keyword', 'policy;eligible;return;refund;purchase;receipt;proof of purchase;condition'),
(6, 2, 'Clear next steps', 'Explains what the customer should do next.', 30, 'keyword', 'instructions;steps;send;provide;process;return label;order number;next step'),
(7, 3, 'Acknowledgement', 'Acknowledges the customer access issue.', 30, 'keyword', 'understand;sorry;issue;problem;difficulty;inconvenience'),
(8, 3, 'Safe troubleshooting', 'Suggests reasonable account troubleshooting steps.', 40, 'keyword', 'reset;password;browser;verify;login;log in;check;try;troubleshoot'),
(9, 3, 'Clear next steps', 'Explains how the customer can receive further assistance.', 30, 'keyword', 'support;contact;assist;steps;help;account;error;let me know');

-- --------------------------------------------------------

-- Insert the practice scenarios shown on the ServiceReady dashboard.
-- scenario IDs connect these records to the criteria inserted above.
INSERT INTO `scenarios` (`id`, `title`, `category`, `customer_message`, `instructions`, `is_active`) VALUES
(1, 'Delivery Exception Management', 'Order Management', 'My order was supposed to arrive yesterday, but it has not arrived. Can you please tell me what is happening?', 'Write a professional response that acknowledges the delay, explains the next steps, and sets clear expectations without inventing tracking information.', 1),
(2, 'Refund and Returns Processing', 'Refunds and Returns', 'I received my order, but the product is not what I expected. I would like to return it and request a refund.', 'Write a helpful response explaining that you will check the return policy and guide the customer through the next steps.', 1),
(3, 'Technical Issue Resolution', 'Technical Support', 'I cannot log in to my account even though I believe my password is correct. I need help accessing it.', 'Write a clear response that acknowledges the issue, suggests safe troubleshooting steps, and explains how further assistance can be provided.', 1);

-- Commit the inserted records.
COMMIT;

-- Restore the original connection character settings.
 /*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
 /*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
 /*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
