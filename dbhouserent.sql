-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 29, 2026 at 05:33 AM
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
-- Database: `dbhouserent`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblink_user_role`
--

CREATE TABLE `tblink_user_role` (
  `user_role_ID` int(11) NOT NULL,
  `user_ID` int(11) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `user_ID` int(11) NOT NULL,
  `userFname` varchar(50) NOT NULL,
  `userLname` varchar(50) NOT NULL,
  `user_email` varchar(100) DEFAULT NULL,
  `contact_no` varchar(10) DEFAULT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`user_ID`, `userFname`, `userLname`, `user_email`, `contact_no`, `password`) VALUES
(1, 'Juan', 'Dela Cruz', 'juandelacruz@gmail.com', '9297348193', '1234'),
(2, 'Pedro', 'Penduko', 'pedropenduko@gmail.com', '9316363323', '1234'),
(3, 'Spongebob', 'Squarepants', 'sponge@gmail.com', '933253232', '1234'),
(4, 'Dora', 'Explorer', 'Doraexp@gmail.com', '983726323', '1234');

-- --------------------------------------------------------

--
-- Table structure for table `tb_boardhouse`
--

CREATE TABLE `tb_boardhouse` (
  `house_ID` int(11) NOT NULL,
  `house_name` varchar(155) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `capacity` int(10) UNSIGNED NOT NULL,
  `bh_status` enum('available','full') NOT NULL DEFAULT 'available',
  `user_ID` int(11) DEFAULT NULL,
  `price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_boardhouse`
--

INSERT INTO `tb_boardhouse` (`house_ID`, `house_name`, `city`, `capacity`, `bh_status`, `user_ID`, `price`) VALUES
(3, 'St Thomas House', 'Santo Tomas', 5, 'available', 1, 10000),
(4, 'Mugiwara Luffy', 'Tanauan', 3, 'available', 3, 0),
(5, 'Mobile Legends', 'Lipa', 4, 'full', 4, 0),
(6, 'Wano', 'Onigashima', 1, 'available', NULL, 123),
(9, 'chester', 'jdoff', 16, 'available', NULL, 138466),
(10, 'Wano', 'Onigashima', 12, 'available', NULL, 1234567);

-- --------------------------------------------------------

--
-- Table structure for table `tb_rent`
--

CREATE TABLE `tb_rent` (
  `rent_ID` int(11) NOT NULL,
  `rent_date` date NOT NULL,
  `user_ID` int(11) DEFAULT NULL,
  `house_ID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_role`
--

CREATE TABLE `tb_role` (
  `role_id` int(11) NOT NULL,
  `user_role` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_role`
--

INSERT INTO `tb_role` (`role_id`, `user_role`) VALUES
(1, 'User'),
(2, 'User-Owner');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblink_user_role`
--
ALTER TABLE `tblink_user_role`
  ADD PRIMARY KEY (`user_role_ID`),
  ADD KEY `user_ID` (`user_ID`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`user_ID`),
  ADD UNIQUE KEY `user_email` (`user_email`);

--
-- Indexes for table `tb_boardhouse`
--
ALTER TABLE `tb_boardhouse`
  ADD PRIMARY KEY (`house_ID`),
  ADD KEY `fk_user_boardhouse` (`user_ID`);

--
-- Indexes for table `tb_rent`
--
ALTER TABLE `tb_rent`
  ADD PRIMARY KEY (`rent_ID`),
  ADD KEY `user_ID` (`user_ID`),
  ADD KEY `house_ID` (`house_ID`);

--
-- Indexes for table `tb_role`
--
ALTER TABLE `tb_role`
  ADD PRIMARY KEY (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblink_user_role`
--
ALTER TABLE `tblink_user_role`
  MODIFY `user_role_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `user_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tb_boardhouse`
--
ALTER TABLE `tb_boardhouse`
  MODIFY `house_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tb_rent`
--
ALTER TABLE `tb_rent`
  MODIFY `rent_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tb_role`
--
ALTER TABLE `tb_role`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tblink_user_role`
--
ALTER TABLE `tblink_user_role`
  ADD CONSTRAINT `tblink_user_role_ibfk_1` FOREIGN KEY (`user_ID`) REFERENCES `tbl_user` (`user_ID`),
  ADD CONSTRAINT `tblink_user_role_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `tb_role` (`role_id`);

--
-- Constraints for table `tb_boardhouse`
--
ALTER TABLE `tb_boardhouse`
  ADD CONSTRAINT `fk_user_boardhouse` FOREIGN KEY (`user_ID`) REFERENCES `tbl_user` (`user_ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tb_rent`
--
ALTER TABLE `tb_rent`
  ADD CONSTRAINT `tb_rent_ibfk_1` FOREIGN KEY (`user_ID`) REFERENCES `tbl_user` (`user_ID`),
  ADD CONSTRAINT `tb_rent_ibfk_2` FOREIGN KEY (`house_ID`) REFERENCES `tb_boardhouse` (`house_ID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
