-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 19, 2024 at 05:29 PM
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
-- Database: `db_test`
--
CREATE DATABASE IF NOT EXISTS `db_test` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_test`;

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `check_customer_existence`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `check_customer_existence` (IN `ID` INT)   BEGIN
    DECLARE customer_exists INT;
    IF ID IS NOT NULL THEN

        -- Check if the ProductID exists in either products or old_products
        SET customer_exists = (SELECT COUNT(*) FROM customers WHERE CustomerID = ID)
                           + (SELECT COUNT(*) FROM old_customers WHERE CustomerID = ID);

        IF customer_exists = 0 THEN
            SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Cannot insert or update. CustomerID does not exist in customers or old_customers';
        END IF;
    END IF;
END$$

DROP PROCEDURE IF EXISTS `check_product_existence`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `check_product_existence` (IN `ID` INT)   BEGIN
    DECLARE product_exists INT;
    
    -- Check if the ProductID exists in either products or old_products
    SET product_exists = (SELECT COUNT(*) FROM products WHERE ProductID = ID)
                       + (SELECT COUNT(*) FROM old_products WHERE ProductID = ID);
    
    IF product_exists = 0 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Cannot insert or update. ProductID does not exist in products or old_products';
    END IF;
END$$

DROP PROCEDURE IF EXISTS `get_bill_details`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `get_bill_details` (IN `order_id_param` INT)   BEGIN
    -- Select the order details, customer details (if available), and sales information
    SELECT 
        o.OrderID,
        o.CustomerID, -- Show the actual CustomerID (could be NULL)
        IF(o.CustomerID IS NULL, 'Unknown Customer', IFNULL(c.Name, old_c.Name)) AS CustomerName, -- Show "Unknown Customer" if CustomerID is NULL
        IFNULL(c.Address, old_c.Address) AS CustomerAddress, -- Show address from customers or old_customers
        o.Date_Time,
        o.Completion_Status,
        s.SaleID,
        s.ProductID,
        IFNULL(p.Name, old_p.Name) AS ProductName,
        IFNULL(p.Price, old_p.Price) AS ProductPrice,
        s.Quantity,
        s.Amount
    FROM 
        orders o
        -- Join with sales table
        INNER JOIN sales s ON o.OrderID = s.OrderID
        -- Join with customers if CustomerID is not NULL
        LEFT JOIN customers c ON o.CustomerID = c.CustomerID
        LEFT JOIN old_customers old_c ON o.CustomerID = old_c.CustomerID
        -- Join with products and old products
        LEFT JOIN products p ON s.ProductID = p.ProductID
        LEFT JOIN old_products old_p ON s.ProductID = old_p.ProductID
    WHERE 
        o.OrderID = order_id_param;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `CustomerID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `customers`
--
DROP TRIGGER IF EXISTS `after_customer_delete`;
DELIMITER $$
CREATE TRIGGER `after_customer_delete` AFTER DELETE ON `customers` FOR EACH ROW BEGIN
    INSERT INTO old_customers (CustomerID, Name, Address)
    VALUES (OLD.CustomerID, OLD.Name, OLD.Address);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `old_customers`
--

DROP TABLE IF EXISTS `old_customers`;
CREATE TABLE `old_customers` (
  `CustomerID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `old_products`
--

DROP TABLE IF EXISTS `old_products`;
CREATE TABLE `old_products` (
  `ProductID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Price` decimal(7,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE `orders` (
  `OrderID` varchar(100) NOT NULL,
  `CustomerID` int(11) DEFAULT NULL,
  `Date_Time` datetime NOT NULL,
  `Completion_Status` enum('pending','complete') NOT NULL DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `orders`
--
DROP TRIGGER IF EXISTS `check_customer_existence_before_insert`;
DELIMITER $$
CREATE TRIGGER `check_customer_existence_before_insert` BEFORE INSERT ON `orders` FOR EACH ROW BEGIN
    CALL check_customer_existence(NEW.CustomerID);
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `check_customer_existence_before_update`;
DELIMITER $$
CREATE TRIGGER `check_customer_existence_before_update` BEFORE UPDATE ON `orders` FOR EACH ROW BEGIN
    CALL check_customer_existence(NEW.CustomerID);
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `orders_update_check`;
DELIMITER $$
CREATE TRIGGER `orders_update_check` BEFORE UPDATE ON `orders` FOR EACH ROW BEGIN
    -- Prevent changing Completion_Status from 'complete' to 'pending'
    IF OLD.Completion_Status = 'complete' AND NEW.Completion_Status = 'pending' THEN
        SET NEW.Completion_Status = OLD.Completion_Status;
    END IF;

    -- Prevent changing OrderID
    IF OLD.OrderID != NEW.OrderID THEN
        SET NEW.OrderID = OLD.OrderID;
    END IF;

    -- Prevent changing CustomerID
    IF OLD.CustomerID != NEW.CustomerID THEN
        SET NEW.CustomerID = OLD.CustomerID;
    END IF;

    -- Prevent changing Date_Time
    IF OLD.Date_Time != NEW.Date_Time THEN
        SET NEW.Date_Time = OLD.Date_Time;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `ProductID` int(11) NOT NULL,
  `Name` varchar(255) NOT NULL,
  `Price` decimal(7,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `products`
--
DROP TRIGGER IF EXISTS `after_product_delete`;
DELIMITER $$
CREATE TRIGGER `after_product_delete` AFTER DELETE ON `products` FOR EACH ROW BEGIN
    INSERT INTO old_products (ProductID, Name, Price)
    VALUES (OLD.ProductID, OLD.Name, OLD.Price);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
CREATE TABLE `sales` (
  `SaleID` int(11) NOT NULL,
  `OrderID` varchar(100) NOT NULL,
  `ProductID` int(11) NOT NULL,
  `Quantity` int(11) NOT NULL,
  `Amount` decimal(7,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Triggers `sales`
--
DROP TRIGGER IF EXISTS `calculate_amount_before_insert`;
DELIMITER $$
CREATE TRIGGER `calculate_amount_before_insert` BEFORE INSERT ON `sales` FOR EACH ROW BEGIN
    DECLARE product_price DECIMAL(7, 2);

    -- Get the Price using ProductID
    SELECT Price INTO product_price
    FROM products
    WHERE ProductID = NEW.ProductID;

    -- Set the calculated Amount
    SET NEW.Amount = NEW.Quantity * product_price;
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `check_product_existence_before_insert`;
DELIMITER $$
CREATE TRIGGER `check_product_existence_before_insert` BEFORE INSERT ON `sales` FOR EACH ROW BEGIN
    call check_product_existence(NEW.ProductID);
END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `check_product_existence_before_update`;
DELIMITER $$
CREATE TRIGGER `check_product_existence_before_update` BEFORE UPDATE ON `sales` FOR EACH ROW BEGIN
	call check_product_existence(NEW.ProductID);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `notAdmin` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`CustomerID`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`OrderID`),
  ADD KEY `CustomerID` (`CustomerID`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`ProductID`,`Name`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`SaleID`),
  ADD KEY `OrderID` (`OrderID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`,`user_name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `CustomerID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `ProductID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `SaleID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`OrderID`) REFERENCES `orders` (`OrderID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
