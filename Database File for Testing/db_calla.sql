-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 25, 2025 at 04:05 AM
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

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `adminID` varchar(10) NOT NULL,
  `adminTokenID` varchar(10) NOT NULL,
  `userID` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `classmodule`
--

CREATE TABLE `classmodule` (
  `cmID` varchar(10) NOT NULL,
  `classInstID` varchar(10) NOT NULL,
  `langID` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `classroom`
--

CREATE TABLE `classroom` (
  `classroomID` varchar(10) NOT NULL,
  `instID` varchar(10) NOT NULL,
  `className` varchar(50) NOT NULL,
  `classDesc` varchar(100) DEFAULT NULL,
  `classCode` varchar(16) DEFAULT NULL,
  `dateCreated` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `password` varchar(20) NOT NULL,
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
('UnDyVpJQ8', 'Instructor', 't3riteki', 'gobythebox@gmail.com', 'gabriel123', 'gab', 'de guzman', 'male', '2004-12-23', '09156119882', 1),
('Uz4tzZ0Nq', 'Administrator', 'learrrr', 'learrrr1027@gmail.com', 'lear1027', 'earl', 'lumata', 'male', '2004-10-27', '09156119882', 1);

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
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`adminTokenID`) REFERENCES `admintoken` (`adminTokenID`),
  ADD CONSTRAINT `admin_ibfk_2` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`);

--
-- Constraints for table `classinstructor`
--
ALTER TABLE `classinstructor`
  ADD CONSTRAINT `classinstructor_ibfk_1` FOREIGN KEY (`instID`) REFERENCES `instructor` (`instID`),
  ADD CONSTRAINT `classinstructor_ibfk_2` FOREIGN KEY (`classroomID`) REFERENCES `classroom` (`classroomID`);

--
-- Constraints for table `classmodule`
--
ALTER TABLE `classmodule`
  ADD CONSTRAINT `classmodule_ibfk_1` FOREIGN KEY (`classInstID`) REFERENCES `classinstructor` (`classInstID`),
  ADD CONSTRAINT `classmodule_ibfk_2` FOREIGN KEY (`langID`) REFERENCES `languagemodule` (`langID`);

--
-- Constraints for table `classroom`
--
ALTER TABLE `classroom`
  ADD CONSTRAINT `classroom_ibfk_1` FOREIGN KEY (`instID`) REFERENCES `instructor` (`instID`);

--
-- Constraints for table `enrolledstudent`
--
ALTER TABLE `enrolledstudent`
  ADD CONSTRAINT `enrolledstudent_ibfk_1` FOREIGN KEY (`studentID`) REFERENCES `student` (`studentID`),
  ADD CONSTRAINT `enrolledstudent_ibfk_2` FOREIGN KEY (`classroomID`) REFERENCES `classroom` (`classroomID`);

--
-- Constraints for table `instructor`
--
ALTER TABLE `instructor`
  ADD CONSTRAINT `instructor_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`);

--
-- Constraints for table `lesson`
--
ALTER TABLE `lesson`
  ADD CONSTRAINT `lesson_ibfk_1` FOREIGN KEY (`langID`) REFERENCES `languagemodule` (`langID`);

--
-- Constraints for table `partnermodule`
--
ALTER TABLE `partnermodule`
  ADD CONSTRAINT `partnermodule_ibfk_1` FOREIGN KEY (`partnerID`) REFERENCES `partner` (`partnerID`),
  ADD CONSTRAINT `partnermodule_ibfk_2` FOREIGN KEY (`adminID`) REFERENCES `admin` (`adminID`),
  ADD CONSTRAINT `partnermodule_ibfk_3` FOREIGN KEY (`langID`) REFERENCES `languagemodule` (`langID`);

--
-- Constraints for table `personalmodule`
--
ALTER TABLE `personalmodule`
  ADD CONSTRAINT `personalmodule_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`),
  ADD CONSTRAINT `personalmodule_ibfk_2` FOREIGN KEY (`pmID`) REFERENCES `partnermodule` (`pmID`);

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `student_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`);

--
-- Constraints for table `studentprogress`
--
ALTER TABLE `studentprogress`
  ADD CONSTRAINT `studentprogress_ibfk_1` FOREIGN KEY (`studentID`) REFERENCES `student` (`studentID`),
  ADD CONSTRAINT `studentprogress_ibfk_2` FOREIGN KEY (`cmID`) REFERENCES `classmodule` (`cmID`);

--
-- Constraints for table `vocabulary`
--
ALTER TABLE `vocabulary`
  ADD CONSTRAINT `vocabulary_ibfk_1` FOREIGN KEY (`lessID`) REFERENCES `lesson` (`lessID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
