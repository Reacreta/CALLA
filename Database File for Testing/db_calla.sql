-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 22, 2025 at 05:34 AM
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
-- Database: `db_calla`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity`
--

CREATE TABLE `activity` (
  `logID` varchar(10) NOT NULL,
  `userID` varchar(10) NOT NULL,
  `action` varchar(120) NOT NULL,
  `dateTimeCreated` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity`
--

INSERT INTO `activity` (`logID`, `userID`, `action`, `dateTimeCreated`) VALUES
('L0cDgx6O3b', 'UrnY76gkZ2', 'Logged in as Administrator', '2025-05-18 19:28:22'),
('L0pK2zbY6g', 'UJwyfS6wVu', 'Logged in as Instructor', '2025-05-22 11:24:42'),
('L3efa9osD9', 'UJwyfS6wVu', 'Logged in as Instructor', '2025-05-18 21:08:14'),
('L3HXYbtfeb', 'UJwyfS6wVu', 'Logged in as Instructor', '2025-05-21 12:03:01'),
('L3KOtgxCO0', 'UJwyfS6wVu', 'Logged in as Instructor', '2025-05-21 21:48:08'),
('L4wlN8oFQG', 'UJwyfS6wVu', 'Logged in as Instructor', '2025-05-21 21:25:05'),
('L8PHputh4y', 'UJwyfS6wVu', 'Logged in as Instructor', '2025-05-18 18:06:00'),
('La78k3FlK4', 'UJwyfS6wVu', 'Logged in as Instructor', '2025-05-18 21:08:00'),
('LBcJxfYHxL', 'UJwyfS6wVu', 'Logged in as Instructor', '2025-05-21 08:02:45'),
('Lc3iKFAWNj', 'UrnY76gkZ2', 'Logged in as ', '2025-05-18 17:59:35'),
('LfiaVh93L8', 'UJwyfS6wVu', 'Logged in as Instructor', '2025-05-18 21:39:50'),
('Li4xZRM9Pp', 'UJwyfS6wVu', 'Created Classroom: CjyLMnO25I', '2025-05-18 23:29:41'),
('LKNhrjsYQf', 'UJwyfS6wVu', 'Logged in as Instructor', '2025-05-18 20:01:13'),
('LNRKB7qOVh', 'UrnY76gkZ2', 'Logged in as Administrator', '2025-05-18 18:39:06'),
('LO0dwjonSz', 'UJwyfS6wVu', 'Created Classroom: CrfdCWU6TM', '2025-05-18 23:26:03'),
('LqaDAo8D5w', 'UrnY76gkZ2', 'Logged in as Administrator', '2025-05-18 18:00:12'),
('LQzAnfMfRd', 'UJwyfS6wVu', 'Logged in as Instructor', '2025-05-18 21:37:09'),
('LRcZgksbAy', 'UrnY76gkZ2', 'Logged in as Administrator', '2025-05-18 18:38:50'),
('LRQG47zc7E', 'UrnY76gkZ2', 'Logged in as Administrator', '2025-05-18 18:39:02'),
('LTPRsZPwMu', 'UrnY76gkZ2', 'Logged in as Administrator', '2025-05-18 18:40:16'),
('LUut9sM6KA', 'UJwyfS6wVu', 'Logged in as Instructor', '2025-05-21 13:15:11'),
('LV8K2AGimY', 'UJwyfS6wVu', 'Created Classroom: CklPP5v9Kk', '2025-05-18 23:27:21'),
('LWfYURCAQ9', 'UJwyfS6wVu', 'Logged in as Instructor', '2025-05-18 18:41:14'),
('LX6PrQw3kC', 'UbHUJ2MfCH', 'Logged in as Instructor', '2025-05-20 19:11:21'),
('LxpZ6S1iGE', 'UJwyfS6wVu', 'Logged in as Instructor', '2025-05-18 18:38:28'),
('LZeFsDmWFY', 'UbHUJ2MfCH', 'Created Classroom: Gooning Class', '2025-05-18 17:48:16');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `adminID` varchar(10) NOT NULL,
  `adminTokenID` varchar(10) NOT NULL,
  `userID` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`adminID`, `adminTokenID`, `userID`) VALUES
('Af7u0wyfYi', 'adm0098583', 'UrnY76gkZ2'),
('AVfPXJgXRj', 'adm1501287', 'UFWLbMr9BK');

-- --------------------------------------------------------

--
-- Table structure for table `admintoken`
--

CREATE TABLE `admintoken` (
  `adminTokenID` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admintoken`
--

INSERT INTO `admintoken` (`adminTokenID`) VALUES
('adm0098583'),
('adm0947581'),
('adm1028847'),
('adm1128472'),
('adm1248912'),
('adm1501287'),
('adm1928471'),
('adm3498788'),
('adm6854740'),
('adm8894245');

-- --------------------------------------------------------

--
-- Table structure for table `classinstructor`
--

CREATE TABLE `classinstructor` (
  `classInstID` varchar(10) NOT NULL,
  `instID` varchar(10) NOT NULL,
  `classroomID` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classinstructor`
--

INSERT INTO `classinstructor` (`classInstID`, `instID`, `classroomID`) VALUES
('CI2eZtyINS', 'IJgJYwuIFJ', 'CjyLMnO25I'),
('CI5A3x5NVL', 'IhJ2RgYp3L', 'C4C9bbrs8H'),
('CIDdm34rsy', 'IhJ2RgYp3L', 'CSMqUeGxWl'),
('CIe38RpVNB', 'IJgJYwuIFJ', 'CklPP5v9Kk'),
('CIg4HXBdMm', 'IhJ2RgYp3L', 'Cv06i9cvKi'),
('CIHeVY6NSo', 'IJgJYwuIFJ', 'CVtBiuFWpW'),
('CIKptqF7VD', 'IhJ2RgYp3L', 'CmoElcJXlk'),
('CILprP1mZ0', 'IJgJYwuIFJ', 'C8i72Tcn27'),
('CIlza0c9dl', 'IhJ2RgYp3L', 'CPtrw4Wywd'),
('CIMWIW67ST', 'IhJ2RgYp3L', 'C6rYIWJQO0'),
('CInisUNZ1i', 'IhJ2RgYp3L', 'CRJyrrHvMn'),
('CIO96DZYxV', 'IJgJYwuIFJ', 'C1YL6DQ0Zl'),
('CIpdScThzi', 'IhJ2RgYp3L', 'CrnfbsH4AE'),
('CIpTVzLuzv', 'IhJ2RgYp3L', 'CMt6C4aEM2'),
('CIQ0wq9EEu', 'IJgJYwuIFJ', 'ChY8eSExsZ'),
('CIqpTgzYxp', 'IhJ2RgYp3L', 'CJhNHQzr3p'),
('CIRlnkTPM1', 'IJgJYwuIFJ', 'C4C9bbrs8H'),
('CIs5bPsQ6y', 'IhJ2RgYp3L', 'ChY8eSExsZ'),
('CIV6ictjhK', 'IJgJYwuIFJ', 'CrfdCWU6TM'),
('CIY0W6Jh3q', 'IhJ2RgYp3L', 'C1YL6DQ0Zl'),
('CIYBA1Kepp', 'IJgJYwuIFJ', 'CSMqUeGxWl'),
('CIYXw1jpqv', 'IhJ2RgYp3L', 'C8eEwRA07p');

-- --------------------------------------------------------

--
-- Table structure for table `classmodule`
--

CREATE TABLE `classmodule` (
  `cmID` varchar(10) NOT NULL,
  `classInstID` varchar(10) NOT NULL,
  `langID` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classmodule`
--

INSERT INTO `classmodule` (`cmID`, `classInstID`, `langID`) VALUES
('CM00001', 'CIHeVY6NSo', 'MOD001'),
('CMJM1PGcdi', 'CI2eZtyINS', 'MJ30p4XiIo');

-- --------------------------------------------------------

--
-- Table structure for table `classroom`
--

CREATE TABLE `classroom` (
  `classroomID` varchar(10) NOT NULL,
  `instID` varchar(10) NOT NULL,
  `className` varchar(50) NOT NULL,
  `classDesc` varchar(500) DEFAULT NULL,
  `classCode` varchar(16) DEFAULT NULL,
  `dateCreated` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `classroom`
--

INSERT INTO `classroom` (`classroomID`, `instID`, `className`, `classDesc`, `classCode`, `dateCreated`) VALUES
('C1YL6DQ0Zl', 'IhJ2RgYp3L', 'asdf', 'asdf', 'CCunkwr', '2025-05-17'),
('C4C9bbrs8H', 'IJgJYwuIFJ', 'Meth Making Class', 'Brief Introduction to creating 97% pure Medical Grade Methamphetamine     ', 'CC9wTsC', '2025-05-18'),
('C6rYIWJQO0', 'IhJ2RgYp3L', 'asdf', 'asdf', 'CC1GjoR', '2025-05-17'),
('C8eEwRA07p', 'IhJ2RgYp3L', 'asdfads', 'asdfasdfasdfasdf', 'CCTm7P2', '2025-05-17'),
('C8i72Tcn27', 'IJgJYwuIFJ', 'Kyle Classroom', 'Test 2', 'CC4vr4w', '2025-05-15'),
('ChY8eSExsZ', 'IhJ2RgYp3L', 'Music Class', 'gab 123', 'CCH0dnx', '2025-05-15'),
('CJhNHQzr3p', 'IhJ2RgYp3L', 'asdf', 'asdf', 'CCg0YY1', '2025-05-17'),
('CjyLMnO25I', 'IJgJYwuIFJ', 'Dogs', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas eu lorem lacus. In et dignissim nisl. Nunc vitae rutrum sapien, sed accumsan urna. Cras ut ultrices mi, a dictum tortor. Quisque eu diam eu mauris dictum convallis. Phasellus volutpat velit eu lorem vehicula ullamcorper. Integer vitae nulla molestie, fermentum leo imperdiet, placerat leo.\r\n\r\nAenean rutrum, dolor id vestibulum ornare, lorem urna placerat magna, efficitur pharetra est metus eget justo. Fusce eu laoreet nulla, et dic', 'CCS6HaA', '2025-05-18'),
('CklPP5v9Kk', 'IJgJYwuIFJ', 'This class', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas eu lorem lacus. In et dignissim nisl. Nunc vitae rutrum sapien, sed accumsan urna. Cras ut ultrices mi, a dictum tortor. Quisque eu diam eu mauris dictum convallis. Phasellus volutpat velit', 'CCLSsSv', '2025-05-18'),
('CmoElcJXlk', 'IhJ2RgYp3L', 'asdf', 'asdf', 'CCFot72', '2025-05-17'),
('CMt6C4aEM2', 'IhJ2RgYp3L', 'asdf', 'asdf', 'CCxYUQC', '2025-05-17'),
('CPtrw4Wywd', 'IhJ2RgYp3L', 'asdf', 'asdf', 'CCdJArj', '2025-05-17'),
('CrfdCWU6TM', 'IJgJYwuIFJ', 'Latin Class', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas eu lorem lacus. In et dignissim ni', 'CC5jeCB', '2025-05-18'),
('CRJyrrHvMn', 'IhJ2RgYp3L', 'Gooning Class', 'Learn how to jelq, dock, and london bridge', 'CCR2yEB', '2025-05-18'),
('CrnfbsH4AE', 'IhJ2RgYp3L', 'asdf', 'asdf', 'CCtvaog', '2025-05-17'),
('CSMqUeGxWl', 'IhJ2RgYp3L', 'kjsdhfsdf', 'sadfasdf', 'CCjO3KH', '2025-05-17'),
('Cv06i9cvKi', 'IhJ2RgYp3L', 'asdf', 'asdfasdf', 'CCTvs3j', '2025-05-17'),
('CVtBiuFWpW', 'IJgJYwuIFJ', 'Gab Classroom', 'Test classroom 1', 'CC7LO6H', '2025-05-15');

-- --------------------------------------------------------

--
-- Table structure for table `enrolledstudent`
--

CREATE TABLE `enrolledstudent` (
  `enrolledID` varchar(10) NOT NULL,
  `studentID` varchar(10) NOT NULL,
  `classroomID` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `instructor`
--

CREATE TABLE `instructor` (
  `instID` varchar(10) NOT NULL,
  `userID` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor`
--

INSERT INTO `instructor` (`instID`, `userID`) VALUES
('IJDG4KGhZr', 'U1hfDk1Sdt'),
('In7t8P88Wf', 'U2QhKDJXuL'),
('IefUUCqo7r', 'U57yYd2wh5'),
('IKcDSsUJnH', 'U5YNX2xA51'),
('IhJ2RgYp3L', 'UbHUJ2MfCH'),
('Iol4nBc3a0', 'Uiab16ESPa'),
('IOZdF0dl8u', 'UIhLnPCxBn'),
('IvnCxYN0TH', 'UjUr8DBDpb'),
('IJgJYwuIFJ', 'UJwyfS6wVu'),
('IPuAYCHYAe', 'URpQCNAWYk'),
('ImNE8p0h3t', 'UTxGYVhLYi'),
('ICXuYgV2r5', 'Uu7vavrq6P'),
('Ix2PkPjny9', 'UyFtsbDzZd'),
('IdgyTW6sH5', 'UYLWBfZbus');

-- --------------------------------------------------------

--
-- Table structure for table `languagemodule`
--

CREATE TABLE `languagemodule` (
  `langID` varchar(10) NOT NULL,
  `moduleName` varchar(50) NOT NULL,
  `moduleDesc` varchar(100) DEFAULT NULL,
  `dateCreated` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `languagemodule`
--

INSERT INTO `languagemodule` (`langID`, `moduleName`, `moduleDesc`, `dateCreated`) VALUES
('MJ30p4XiIo', 'Bisaya 101', 'This module will tackle the fundamental vocabulary of Bisaya', '2025-05-22'),
('MOD001', 'Basic English', 'Introductory English module', '2025-01-10'),
('MOD002', 'Conversational Spanish', 'Learn everyday Spanish conversations', '2025-02-12'),
('MOD003', 'Japanese Travel Phrases', 'Useful Japanese for travelers', '2025-03-05');

-- --------------------------------------------------------

--
-- Table structure for table `lesson`
--

CREATE TABLE `lesson` (
  `lessID` varchar(10) NOT NULL,
  `langID` varchar(10) NOT NULL,
  `lessonName` varchar(50) NOT NULL,
  `lessonDesc` varchar(100) DEFAULT NULL,
  `dateCreated` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lesson`
--

INSERT INTO `lesson` (`lessID`, `langID`, `lessonName`, `lessonDesc`, `dateCreated`) VALUES
('L3kQeAbU2P', 'MJ30p4XiIo', 'Lesson 2: Lingo', 'This lesson will introduce modern lingo in the bisaya language', '2025-05-22'),
('La4w6kjNxW', 'MJ30p4XiIo', 'Lesson 1: Greetings', 'This lesson will introduce basic greetings in the bisaya language', '2025-05-22'),
('LES001', 'MOD001', 'Greetings', 'Common English greetings', '2025-01-11'),
('LES002', 'MOD001', 'Numbers', 'Basic English numbers', '2025-01-12'),
('LES003', 'MOD001', 'Colors', 'Names of common colors', '2025-01-13'),
('LES004', 'MOD002', 'Introductions', 'Introducing yourself in Spanish', '2025-02-13'),
('LES005', 'MOD002', 'Ordering Food', 'Useful restaurant phrases', '2025-02-14'),
('LES006', 'MOD002', 'Asking for Directions', 'Getting around in Spanish', '2025-02-15'),
('LES007', 'MOD003', 'At the Airport', 'Key airport vocabulary', '2025-03-06'),
('LES008', 'MOD003', 'At the Hotel', 'Checking in and hotel phrases', '2025-03-07'),
('LES009', 'MOD003', 'Shopping', 'Useful words for shopping', '2025-03-08');

-- --------------------------------------------------------

--
-- Table structure for table `partner`
--

CREATE TABLE `partner` (
  `partnerID` varchar(10) NOT NULL,
  `partnerName` varchar(50) NOT NULL,
  `partnerDesc` varchar(100) DEFAULT NULL,
  `email` varchar(50) NOT NULL,
  `contact` varchar(13) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `partner`
--

INSERT INTO `partner` (`partnerID`, `partnerName`, `partnerDesc`, `email`, `contact`) VALUES
('PEk9V6QRsi', 'asdfasdf', 'asdfasdfadf', 'asdfasdfadf@gmail.com', 'asdfasdf'),
('PRzzTOboS8', 'asdfasdf', 'House', 'asdfasdfklj@gmail.com', '09123456789');

-- --------------------------------------------------------

--
-- Table structure for table `partnermodule`
--

CREATE TABLE `partnermodule` (
  `pmID` varchar(10) NOT NULL,
  `partnerID` varchar(10) NOT NULL,
  `adminID` varchar(10) NOT NULL,
  `langID` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personalmodule`
--

CREATE TABLE `personalmodule` (
  `plID` varchar(10) NOT NULL,
  `userID` varchar(10) NOT NULL,
  `pmID` varchar(10) NOT NULL,
  `currentProgress` float NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `studentID` varchar(10) NOT NULL,
  `userID` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`studentID`, `userID`) VALUES
('SJYRMaI3SW', 'U7A4lrStlh'),
('SVaBCdOe5k', 'UC69gOAKu7'),
('SzlxOM1SrC', 'UCvTFdnXN7'),
('SCKN5MTExk', 'UJAYFrjkai'),
('SaenRMZJ3a', 'UJwmSCkkdy'),
('SITJsPvwQj', 'Um0fe5LjmI'),
('S3yWcovA7P', 'UOhNDzP4Y9'),
('SFQBjm4d7F', 'UP5VRXqKt5'),
('SEvYQP5HO1', 'URcxHytbUQ'),
('SyFYgqLjVV', 'Us18ws9PLr'),
('SDJfrxJUGU', 'UwovfwgEy4');

-- --------------------------------------------------------

--
-- Table structure for table `studentprogress`
--

CREATE TABLE `studentprogress` (
  `spID` varchar(10) NOT NULL,
  `studentID` varchar(10) NOT NULL,
  `cmID` varchar(10) NOT NULL,
  `currentProgress` float NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userID` varchar(10) NOT NULL,
  `userType` enum('Administrator','Instructor','Student') NOT NULL,
  `username` varchar(20) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstName` varchar(35) NOT NULL,
  `lastName` varchar(35) NOT NULL,
  `sex` varchar(10) NOT NULL,
  `dateOfBirth` date NOT NULL,
  `contact` varchar(15) NOT NULL,
  `active` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userID`, `userType`, `username`, `email`, `password`, `firstName`, `lastName`, `sex`, `dateOfBirth`, `contact`, `active`) VALUES
('U1hfDk1Sdt', 'Instructor', 'ctfgjn', 'etrfcgvbhjklm@gmail.com', '$2y$10$8ePba59Ekws3sZZ1dywAq.3I4QaB.4/pRHG3k8ti91jIldl6FX1bq', 'awsedtrfhyuji', 'rdxctfgvhbjn', 'male', '2004-01-29', '09156119882', 1),
('U2QhKDJXuL', 'Instructor', 'asdsdaga', '1927561972@gmail.com', '$2y$10$HrzkRUB2QO8OJ2dCXXREH.y.3XDGGb9QZdNKc3cicpz2qPEucXqxe', 'asdasdasd', 'asdasdasgasdg', 'male', '2004-01-27', '12345678912', 1),
('U57yYd2wh5', 'Instructor', 'wreyxtcbi;', 'srdxtfcgbolk@gmail.com', '$2y$10$K1qHRPL9ZCDi3oY2KI8/Ief.NNvNT.YKxJFuGrD6nMS4MIZ.x66c2', 'sxdctfguhij', 'xrdtfvgbhjn', 'male', '2004-01-27', '12345678912', 1),
('U5YNX2xA51', 'Instructor', 'xrdtfcybhijn', 'akhsjfaksfasf@gmail.com', '$2y$10$0QraPHB7Z5FYpQhG.gZ1D.94nCIWeZ.BUevMBXEe0DsEkv73j8q6C', 'zwrxtcybijno', 'ezrxtfcglj', 'male', '2004-12-29', '09156119882', 1),
('U7A4lrStlh', 'Student', 't3riteki', 'ygvikjl@gmail.com', '$2y$10$OtytqDNrzolfYiRCton4XOpAJFNzzNoNDAfC4KFPzqQmy8Lt.PUyy', 'gab', 'asdfasdfads', 'male', '2004-01-12', '12345678912', 1),
('UbHUJ2MfCH', 'Instructor', 'B.Withers', 'bwithers@gmail.com', '$2y$10$jkep.VfJMDwVf6qa9iwIUugGFNi8ApvEjV2OijuxpbgjxS9hxWrnS', 'bill', 'withers', 'male', '2004-01-23', '12345678912', 1),
('UC69gOAKu7', 'Student', 'yvghbjkm', 'rxdctfgbhjnk@gmail.com', '$2y$10$fPUkgtUCi2OtAFwGMi3qae3/fLgHpXAlZjOGZ9HkCNJYtKPjOIUUW', 'tcvygbhujnk', 'ghjnk', 'male', '2004-12-12', '12345678912', 1),
('UCvTFdnXN7', 'Student', 'serduvgioh', 'ewzrtfcgikl@gmail.com', '$2y$10$9vhr91KF7xH6hkjJLzXe9.cLsMm7GTQJ6nemK4IITduY4Wx6zQNXG', 'zwxretfbhiojmk', 'rtcybhi', 'male', '2003-12-21', '12345678912', 1),
('UFWLbMr9BK', 'Administrator', 'learrrr', 'prglang@gmail.com', '$2y$10$NEvy//ttFzgYveOt6Ydo7uh2xkEF7pPfCZfLp5C2vDP/vSKQud.E.', 'Kendrick ', 'Lamar', 'male', '2004-01-23', '09871872871', 1),
('Uiab16ESPa', 'Instructor', 'k.Dot', 'pglang@gmail.com', '$2y$10$Zsf9.QF7kHBbdk/S07.n3uf3ZGrj2h3WkvdQ8NPjI/h4oyEczJdRy', 'Kendrick', 'Lamar', 'male', '2004-12-23', '09871872871', 1),
('UIhLnPCxBn', 'Instructor', 'cctrgvhjn', 'dxrtfgikj@gmail.com', '$2y$10$RDNRzyphkNCDiiPKgDITjO8TQ5y3Os7Zkn.YdGm4IQ7nCEX8wnHMS', 'trcfvgbhuj', 'dctfgvbhj', 'male', '2004-01-12', '12345678912', 1),
('UJAYFrjkai', 'Student', 'learrrr', 'drxtfgvbhjk@gmail.com', '$2y$10$2OTH1mwH0nJCGomsWtH3COj8WEQl4zpOwjUJEkJ3x8nX/SQp/EiP.', 'earl', 'lumata', 'male', '2004-01-29', '12345678912', 1),
('UjUr8DBDpb', 'Instructor', 'rtfgyuij', 'drxtfgvyikml@gmail.com', '$2y$10$lGTHXo9jd.q3Xyg2K29VCOc8c7AGJykoifVVnnC..2l4GpocobotG', 'erctfgjk', 'fdg hb', 'male', '2004-02-12', '09156119882', 1),
('UJwmSCkkdy', 'Student', 'cftvghbj', 'xerctfgvyhjnk@gmail.com', '$2y$10$1q53714k8MUtD2yYTnyqBOlQv9RMml0YrY.2hHMgtpNFB4his6R3G', 'cdtfgvybhj', 'fcgbhj', 'male', '2004-12-12', '12345678912', 1),
('UJwyfS6wVu', 'Instructor', 'learrrr', 'gadg@gmail.com', '$2y$10$J6BN2LX2EjEsE2toeTqUeO9UFK/yMOUTTn/eIExILZQRDOydq17kW', 'Kyle', 'Lamar', 'male', '2004-02-23', '09871872871', 1),
('Um0fe5LjmI', 'Student', 't3riteki', 'ctfbhjk@gmail.com', '$2y$10$KSpl5Wcq3BeTEFlgWRfyNu.UKZQNUKxDFindDiXSaA0gdzcwLz19C', 'Gooner', 'Pino', 'female', '2004-01-12', '12345678912', 1),
('UOhNDzP4Y9', 'Student', 'asdfasdf', 'xretcfubhjk@gmail.com', '$2y$10$yGENU5lqVexBRUV9T9wp2uy5vC.QVk2Y5ASm2JbOQC4iBqcTfolke', 'earl', 'asdfsdf', 'male', '2004-01-21', '12345678912', 1),
('UP5VRXqKt5', 'Student', 'tfcvgyu', 'tfgvybhujnkm@gmail.com', '$2y$10$HtL95VLEyHRcrQASxpg4ZuspfCrCLASIJViju3jCXS9Kn2MhkvzKe', 'f ghjk', ' hbj', 'female', '2004-01-12', '12345678912', 1),
('URcxHytbUQ', 'Student', 'learrrr', 'rtcfgvybj@gmail.com', '$2y$10$vf0sRcm3625z.0QsFkQiGOrGFK5vpm9nWIVhWiMi6FbsZvnBo4wRG', 'earl', 'Gatmin', 'female', '2004-02-12', '12345678912', 1),
('UrnY76gkZ2', 'Administrator', 't3riteki', 'gobythebox@gmail.com', '$2y$10$21VxGbZ8h.9/ZvHmTRhFZOF7V6r1o861gA0kfw2ePax4d0qZ2Wmby', 'Gabriel', 'de Guzman', 'male', '2004-01-23', '09156119882', 1),
('URpQCNAWYk', 'Instructor', 'asdasd', 'sdasdadasd@gmail.com', '$2y$10$J4Go4OkEaeHSFF7cJ90Fu.X9Kjt4SnUTjnxVQZ.u3IETXSPtM8jAy', 'asdasd', 'asdasd', 'female', '2004-01-12', '12345678912', 1),
('Us18ws9PLr', 'Student', 'ggt', 'drxgvhb@gmail.com', '$2y$10$hGXhK40HXxKRlTUWLqpQ6.9M/6s31BplBjNwMHwO4CJI5VUSQ3vz.', 'joshua', 'Gatmin', 'female', '2004-01-12', '12345678912', 1),
('UTxGYVhLYi', 'Instructor', 'fcgvybhj', 'dtrcfgvyhuj@gmail.com', '$2y$10$XHDN3Axxji2WfgUaRmrgie0qjKPBgJhZYK/o6oLHZ1XGjyU17NMJ6', 'qwexrtfgyhujn', 'fghj', 'female', '2004-05-12', '12345678912', 1),
('Uu7vavrq6P', 'Instructor', 'asdfasdf', 'adjahsfk@gmail.com', '$2y$10$bhknXZ73lowavOAs5/pKvOPHmvNClMJvLBWc3ZmPTS1uL.xU00gO6', 'asdfasdf', 'asdfasdf', 'female', '2004-01-12', '12345678912', 1),
('UwovfwgEy4', 'Student', 'learrrr', 'qwertyuio@gmail.com', '$2y$10$38I81i2HeZ/yTuNvVfRVqOu5uEwirgUQO8It1/.mjuPQ3uitboLHi', 'gyhjaksdg', 'sadgasdgk', 'female', '2004-03-29', '12345678912', 1),
('UyFtsbDzZd', 'Instructor', 'fyghjbn', 'rdtfghuijkm@gmail.com', '$2y$10$iL2bgNyTeGayruxB0DtV0uYXWPQTCJRdu0IzHA4k7LwC72kRmoH3K', 'dtcfghbjn', 'dfg hbj', 'male', '2004-02-12', '12345678912', 1),
('UYLWBfZbus', 'Instructor', 'fgh', 'xrdgvkml@gmail.com', '$2y$10$bBoJrvP8HcYmfKxlnx4NW.HOZgAuAb0KgMnB2Kz1Ej9e4xZwrJtpK', 'drctfgj', 'g vh', 'male', '2004-01-12', '12345678912', 1);

-- --------------------------------------------------------

--
-- Table structure for table `vocabulary`
--

CREATE TABLE `vocabulary` (
  `wordID` varchar(10) NOT NULL,
  `lessID` varchar(10) NOT NULL,
  `word` varchar(75) NOT NULL,
  `meaning` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vocabulary`
--

INSERT INTO `vocabulary` (`wordID`, `lessID`, `word`, `meaning`) VALUES
('VOC001', 'LES001', 'Hello', 'A greeting used when meeting someone'),
('VOC002', 'LES001', 'Good morning', 'Greeting used before noon'),
('VOC003', 'LES001', 'Goodbye', 'Used when leaving'),
('VOC004', 'LES002', 'One', '1'),
('VOC005', 'LES002', 'Two', '2'),
('VOC006', 'LES002', 'Three', '3'),
('VOC007', 'LES003', 'Red', 'A primary color'),
('VOC008', 'LES003', 'Blue', 'A color of the sky'),
('VOC009', 'LES003', 'Green', 'The color of grass'),
('VOC010', 'LES004', 'Hola', 'Hello'),
('VOC011', 'LES004', 'Me llamo', 'My name is'),
('VOC012', 'LES004', '¿Cómo estás?', 'How are you?'),
('VOC013', 'LES005', 'La cuenta', 'The bill'),
('VOC014', 'LES005', 'Por favor', 'Please'),
('VOC015', 'LES005', 'Quiero', 'I want'),
('VOC016', 'LES006', '¿Dónde está?', 'Where is...?'),
('VOC017', 'LES006', 'A la derecha', 'To the right'),
('VOC018', 'LES006', 'A la izquierda', 'To the left'),
('VOC019', 'LES007', 'Kūkō', 'Airport'),
('VOC020', 'LES007', 'Pasupōto', 'Passport'),
('VOC021', 'LES007', 'Tōjōken', 'Boarding pass'),
('VOC022', 'LES008', 'Hoteru', 'Hotel'),
('VOC023', 'LES008', 'Yoyaku', 'Reservation'),
('VOC024', 'LES008', 'Kagi', 'Key'),
('VOC025', 'LES009', 'Ikura', 'How much?'),
('VOC026', 'LES009', 'Kaimasu', 'To buy'),
('VOC027', 'LES009', 'Kaimono', 'Shopping'),
('W0k0TgYdZz', 'La4w6kjNxW', 'Bogo', 'I love you'),
('W91PXZ7yuK', 'L3kQeAbU2P', 'Giatay', 'A multi-use word to express shock or concern'),
('Wre0QnVglo', 'L3kQeAbU2P', 'Skibidi', 'Expresses admiration'),
('WUzQNHKV4g', 'La4w6kjNxW', 'Yawa', 'Salutations!');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity`
--
ALTER TABLE `activity`
  ADD PRIMARY KEY (`logID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`adminID`),
  ADD KEY `adminTokenID` (`adminTokenID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `admintoken`
--
ALTER TABLE `admintoken`
  ADD PRIMARY KEY (`adminTokenID`);

--
-- Indexes for table `classinstructor`
--
ALTER TABLE `classinstructor`
  ADD PRIMARY KEY (`classInstID`),
  ADD KEY `instID` (`instID`),
  ADD KEY `classroomID` (`classroomID`);

--
-- Indexes for table `classmodule`
--
ALTER TABLE `classmodule`
  ADD PRIMARY KEY (`cmID`),
  ADD KEY `classInstID` (`classInstID`),
  ADD KEY `langID` (`langID`);

--
-- Indexes for table `classroom`
--
ALTER TABLE `classroom`
  ADD PRIMARY KEY (`classroomID`),
  ADD KEY `instID` (`instID`);

--
-- Indexes for table `enrolledstudent`
--
ALTER TABLE `enrolledstudent`
  ADD PRIMARY KEY (`enrolledID`),
  ADD KEY `studentID` (`studentID`),
  ADD KEY `classroomID` (`classroomID`);

--
-- Indexes for table `instructor`
--
ALTER TABLE `instructor`
  ADD PRIMARY KEY (`instID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `languagemodule`
--
ALTER TABLE `languagemodule`
  ADD PRIMARY KEY (`langID`);

--
-- Indexes for table `lesson`
--
ALTER TABLE `lesson`
  ADD PRIMARY KEY (`lessID`),
  ADD KEY `langID` (`langID`);

--
-- Indexes for table `partner`
--
ALTER TABLE `partner`
  ADD PRIMARY KEY (`partnerID`);

--
-- Indexes for table `partnermodule`
--
ALTER TABLE `partnermodule`
  ADD PRIMARY KEY (`pmID`),
  ADD KEY `partnerID` (`partnerID`),
  ADD KEY `adminID` (`adminID`),
  ADD KEY `langID` (`langID`);

--
-- Indexes for table `personalmodule`
--
ALTER TABLE `personalmodule`
  ADD PRIMARY KEY (`plID`),
  ADD KEY `userID` (`userID`),
  ADD KEY `pmID` (`pmID`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`studentID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `studentprogress`
--
ALTER TABLE `studentprogress`
  ADD PRIMARY KEY (`spID`),
  ADD KEY `studentID` (`studentID`),
  ADD KEY `cmID` (`cmID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`);

--
-- Indexes for table `vocabulary`
--
ALTER TABLE `vocabulary`
  ADD PRIMARY KEY (`wordID`),
  ADD KEY `lessID` (`lessID`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity`
--
ALTER TABLE `activity`
  ADD CONSTRAINT `activity_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`);

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`adminTokenID`) REFERENCES `admintoken` (`adminTokenID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `admin_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `classinstructor`
--
ALTER TABLE `classinstructor`
  ADD CONSTRAINT `classinstructor_ibfk_1` FOREIGN KEY (`instID`) REFERENCES `instructor` (`instID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `classinstructor_ibfk_2` FOREIGN KEY (`classroomID`) REFERENCES `classroom` (`classroomID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `classmodule`
--
ALTER TABLE `classmodule`
  ADD CONSTRAINT `classmodule_ibfk_1` FOREIGN KEY (`classInstID`) REFERENCES `classinstructor` (`classInstID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `classmodule_ibfk_2` FOREIGN KEY (`langID`) REFERENCES `languagemodule` (`langID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `classroom`
--
ALTER TABLE `classroom`
  ADD CONSTRAINT `classroom_ibfk_1` FOREIGN KEY (`instID`) REFERENCES `instructor` (`instID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `enrolledstudent`
--
ALTER TABLE `enrolledstudent`
  ADD CONSTRAINT `enrolledstudent_ibfk_1` FOREIGN KEY (`studentID`) REFERENCES `student` (`studentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `enrolledstudent_ibfk_2` FOREIGN KEY (`classroomID`) REFERENCES `classroom` (`classroomID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `instructor`
--
ALTER TABLE `instructor`
  ADD CONSTRAINT `instructor_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `lesson`
--
ALTER TABLE `lesson`
  ADD CONSTRAINT `lesson_ibfk_1` FOREIGN KEY (`langID`) REFERENCES `languagemodule` (`langID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `partnermodule`
--
ALTER TABLE `partnermodule`
  ADD CONSTRAINT `partnermodule_ibfk_1` FOREIGN KEY (`partnerID`) REFERENCES `partner` (`partnerID`),
  ADD CONSTRAINT `partnermodule_ibfk_3` FOREIGN KEY (`langID`) REFERENCES `languagemodule` (`langID`);

--
-- Constraints for table `personalmodule`
--
ALTER TABLE `personalmodule`
  ADD CONSTRAINT `personalmodule_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `personalmodule_ibfk_2` FOREIGN KEY (`pmID`) REFERENCES `partnermodule` (`pmID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `studentprogress`
--
ALTER TABLE `studentprogress`
  ADD CONSTRAINT `studentprogress_ibfk_1` FOREIGN KEY (`studentID`) REFERENCES `student` (`studentID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `studentprogress_ibfk_2` FOREIGN KEY (`cmID`) REFERENCES `classmodule` (`cmID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `vocabulary`
--
ALTER TABLE `vocabulary`
  ADD CONSTRAINT `vocabulary_ibfk_1` FOREIGN KEY (`lessID`) REFERENCES `lesson` (`lessID`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
