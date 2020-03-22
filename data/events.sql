-- phpMyAdmin SQL Dump
-- version 5.0.0-dev
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 03, 2018 at 06:19 PM
-- Server version: 5.5.55-0ubuntu0.14.04.1
-- PHP Version: 7.2.7-1+ubuntu14.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gabriela_microice_events`
--

-- --------------------------------------------------------

--
-- Table structure for table `entities`
--

CREATE TABLE `entities` (
  `Id` int(11) NOT NULL,
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entities_data`
--

CREATE TABLE `entities_data` (
  `Id` int(11) NOT NULL,
  `EntityId` int(11) NOT NULL,
  `DataId` int(11) NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entities_profiles`
--

CREATE TABLE `entities_profiles` (
  `Id` int(11) NOT NULL,
  `EntityId` int(11) NOT NULL,
  `ProfileId` int(11) NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entities_types`
--

CREATE TABLE `entities_types` (
  `Id` int(11) NOT NULL,
  `EntityId` int(11) NOT NULL,
  `TypeId` int(11) NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_data`
--

CREATE TABLE `entity_data` (
  `Id` int(11) NOT NULL,
  `Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `TypeId` int(11) NOT NULL,
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_data_fields`
--

CREATE TABLE `entity_data_fields` (
  `Id` int(11) NOT NULL,
  `Field` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Value` text COLLATE utf8_unicode_ci NOT NULL,
  `DataId` int(11) NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_data_field_config`
--

CREATE TABLE `entity_data_field_config` (
  `Id` int(11) NOT NULL,
  `Field` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'field as key',
  `Name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL COMMENT 'field name as label/description',
  `Value` text COLLATE utf8_unicode_ci COMMENT 'default value',
  `Required` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if is mandatory or not',
  `Type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'field type as text, checkbox, select, date etc...',
  `Multiple` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if is multiselectable',
  `Pattern` text COLLATE utf8_unicode_ci COMMENT 'validation pattern',
  `Category` text COLLATE utf8_unicode_ci,
  `Stash` text COLLATE utf8_unicode_ci COMMENT 'store anything you need here',
  `TypeId` int(11) NOT NULL COMMENT 'user type id',
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_data_preferences`
--

CREATE TABLE `entity_data_preferences` (
  `Id` int(11) NOT NULL,
  `DataId` int(11) NOT NULL,
  `Category` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Content` text COLLATE utf8_unicode_ci,
  `ContentType` enum('json','serialize','text') COLLATE utf8_unicode_ci NOT NULL,
  `CreationDate` datetime NOT NULL,
  `Status` int(3) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_data_types`
--

CREATE TABLE `entity_data_types` (
  `Id` int(11) NOT NULL,
  `Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_details`
--

CREATE TABLE `entity_details` (
  `Id` int(11) NOT NULL,
  `EntityId` int(11) NOT NULL,
  `Field` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Value` text COLLATE utf8_unicode_ci NOT NULL,
  `Category` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TypeId` int(11) NOT NULL COMMENT 'user type id - useful if one user has multiple types to not delete all fileds for diff types',
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_detail_fields`
--

CREATE TABLE `entity_detail_fields` (
  `Id` int(11) NOT NULL,
  `Field` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'field as key',
  `Name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL COMMENT 'field name as label/description',
  `Value` text COLLATE utf8_unicode_ci COMMENT 'default value',
  `Required` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if is mandatory or not',
  `Type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'field type as text, checkbox, select, date etc...',
  `Multiple` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if is multiselectable',
  `Pattern` text COLLATE utf8_unicode_ci COMMENT 'validation pattern',
  `Category` text COLLATE utf8_unicode_ci,
  `Stash` text COLLATE utf8_unicode_ci COMMENT 'store anything you need here',
  `TypeId` int(11) NOT NULL COMMENT 'user type id',
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_detail_field_options`
--

CREATE TABLE `entity_detail_field_options` (
  `Id` int(11) NOT NULL,
  `Field` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Option` text COLLATE utf8_unicode_ci NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_profiles`
--

CREATE TABLE `entity_profiles` (
  `Id` int(11) NOT NULL,
  `CreationDate` datetime NOT NULL,
  `Status` int(3) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_profiles_data`
--

CREATE TABLE `entity_profiles_data` (
  `Id` int(11) NOT NULL,
  `ProfileId` int(11) NOT NULL,
  `DataId` int(11) NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_profiles_types`
--

CREATE TABLE `entity_profiles_types` (
  `Id` int(11) NOT NULL,
  `ProfileId` int(11) NOT NULL,
  `TypeId` int(11) NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_profile_data`
--

CREATE TABLE `entity_profile_data` (
  `Id` int(11) NOT NULL,
  `Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `TypeId` int(11) NOT NULL,
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_profile_data_fields`
--

CREATE TABLE `entity_profile_data_fields` (
  `Id` int(11) NOT NULL,
  `Field` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Value` text COLLATE utf8_unicode_ci NOT NULL,
  `DataId` int(11) NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_profile_data_field_config`
--

CREATE TABLE `entity_profile_data_field_config` (
  `Id` int(11) NOT NULL,
  `Field` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'field as key',
  `Name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL COMMENT 'field name as label/description',
  `Value` text COLLATE utf8_unicode_ci COMMENT 'default value',
  `Required` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if is mandatory or not',
  `Type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'field type as text, checkbox, select, date etc...',
  `Multiple` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if is multiselectable',
  `Pattern` text COLLATE utf8_unicode_ci COMMENT 'validation pattern',
  `Category` text COLLATE utf8_unicode_ci,
  `Stash` text COLLATE utf8_unicode_ci COMMENT 'store anything you need here',
  `TypeId` int(11) NOT NULL COMMENT 'user type id',
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_profile_data_preferences`
--

CREATE TABLE `entity_profile_data_preferences` (
  `Id` int(11) NOT NULL,
  `DataId` int(11) NOT NULL,
  `Category` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Content` text COLLATE utf8_unicode_ci,
  `ContentType` enum('json','serialize','text') COLLATE utf8_unicode_ci NOT NULL,
  `CreationDate` datetime NOT NULL,
  `Status` int(3) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_profile_data_types`
--

CREATE TABLE `entity_profile_data_types` (
  `Id` int(11) NOT NULL,
  `Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_profile_details`
--

CREATE TABLE `entity_profile_details` (
  `Id` int(11) NOT NULL,
  `ProfileId` int(11) NOT NULL,
  `Field` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Value` text COLLATE utf8_unicode_ci NOT NULL,
  `Category` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TypeId` int(11) NOT NULL COMMENT 'profile type id - useful if one profile has multiple types to not delete all fileds for diff types',
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_profile_detail_fields`
--

CREATE TABLE `entity_profile_detail_fields` (
  `Id` int(11) NOT NULL,
  `Field` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'field as key',
  `Name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL COMMENT 'field name as label/description',
  `Value` text COLLATE utf8_unicode_ci COMMENT 'default value',
  `Required` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if is mandatory or not',
  `Type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'field type as text, checkbox, select, date etc...',
  `Multiple` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if is multiselectable',
  `Pattern` text COLLATE utf8_unicode_ci COMMENT 'validation pattern',
  `Category` text COLLATE utf8_unicode_ci,
  `Stash` text COLLATE utf8_unicode_ci COMMENT 'store anything you need here',
  `TypeId` int(11) NOT NULL COMMENT 'entity type id',
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_profile_preferences`
--

CREATE TABLE `entity_profile_preferences` (
  `Id` int(11) NOT NULL,
  `ProfileId` int(11) NOT NULL,
  `Category` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Content` text COLLATE utf8_unicode_ci,
  `ContentType` enum('json','serialize','text') COLLATE utf8_unicode_ci NOT NULL,
  `CreationDate` datetime NOT NULL,
  `Status` int(3) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_profile_translations`
--

CREATE TABLE `entity_profile_translations` (
  `Id` int(11) NOT NULL,
  `ProfileId` int(11) NOT NULL,
  `Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Language` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'lang iso code ex: en , en_GB',
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_profile_types`
--

CREATE TABLE `entity_profile_types` (
  `Id` int(11) NOT NULL,
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_profile_types_data_types`
--

CREATE TABLE `entity_profile_types_data_types` (
  `Id` int(11) NOT NULL,
  `EntityProfileTypeId` int(11) NOT NULL,
  `EntityProfileDataTypeId` int(11) NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_profile_type_translations`
--

CREATE TABLE `entity_profile_type_translations` (
  `Id` int(11) NOT NULL,
  `TypeId` int(11) NOT NULL,
  `Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Language` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'lang iso code ex: en , en_GB',
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_translations`
--

CREATE TABLE `entity_translations` (
  `Id` int(11) NOT NULL,
  `EntityId` int(11) NOT NULL,
  `Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Language` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'lang iso code ex: en , en_GB',
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_types`
--

CREATE TABLE `entity_types` (
  `Id` int(11) NOT NULL,
  `ParentId` int(11) NOT NULL DEFAULT '0',
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_types_data_types`
--

CREATE TABLE `entity_types_data_types` (
  `Id` int(11) NOT NULL,
  `EntityTypeId` int(11) NOT NULL,
  `EntityDataTypeId` int(11) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `entity_type_translations`
--

CREATE TABLE `entity_type_translations` (
  `Id` int(11) NOT NULL,
  `TypeId` int(11) NOT NULL,
  `Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Language` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'lang iso code ex: en , en_GB',
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `Id` int(11) NOT NULL,
  `DestinationId` int(11) DEFAULT NULL,
  `StartDate` datetime NOT NULL,
  `EndDate` datetime DEFAULT NULL,
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events_data`
--

CREATE TABLE `events_data` (
  `Id` int(11) NOT NULL,
  `EventId` int(11) NOT NULL,
  `DataId` int(11) NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events_entities`
--

CREATE TABLE `events_entities` (
  `Id` int(11) NOT NULL,
  `EventId` int(11) NOT NULL,
  `EntityId` int(11) NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events_profiles`
--

CREATE TABLE `events_profiles` (
  `Id` int(11) NOT NULL,
  `EventId` int(11) NOT NULL,
  `ProfileId` int(11) NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events_services`
--

CREATE TABLE `events_services` (
  `Id` int(11) NOT NULL,
  `EventId` int(11) NOT NULL,
  `Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ServiceType` enum('flight','hotel','package','product') COLLATE utf8_unicode_ci NOT NULL,
  `ServiceId` int(11) NOT NULL,
  `Quantity` int(11) DEFAULT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events_types`
--

CREATE TABLE `events_types` (
  `Id` int(11) NOT NULL,
  `EventId` int(11) NOT NULL,
  `TypeId` int(11) NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_data`
--

CREATE TABLE `event_data` (
  `Id` int(11) NOT NULL,
  `Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `TypeId` int(11) NOT NULL,
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_data_fields`
--

CREATE TABLE `event_data_fields` (
  `Id` int(11) NOT NULL,
  `Field` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Value` text COLLATE utf8_unicode_ci NOT NULL,
  `DataId` int(11) NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_data_field_config`
--

CREATE TABLE `event_data_field_config` (
  `Id` int(11) NOT NULL,
  `Field` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'field as key',
  `Name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL COMMENT 'field name as label/description',
  `Value` text COLLATE utf8_unicode_ci COMMENT 'default value',
  `Required` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if is mandatory or not',
  `Type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'field type as text, checkbox, select, date etc...',
  `Multiple` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if is multiselectable',
  `Pattern` text COLLATE utf8_unicode_ci COMMENT 'validation pattern',
  `Category` text COLLATE utf8_unicode_ci,
  `Stash` text COLLATE utf8_unicode_ci COMMENT 'store anything you need here',
  `TypeId` int(11) NOT NULL COMMENT 'user type id',
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_data_preferences`
--

CREATE TABLE `event_data_preferences` (
  `Id` int(11) NOT NULL,
  `DataId` int(11) NOT NULL,
  `Category` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Content` text COLLATE utf8_unicode_ci,
  `ContentType` enum('json','serialize','text') COLLATE utf8_unicode_ci NOT NULL,
  `Status` int(3) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_data_types`
--

CREATE TABLE `event_data_types` (
  `Id` int(11) NOT NULL,
  `Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_details`
--

CREATE TABLE `event_details` (
  `Id` int(11) NOT NULL,
  `EventId` int(11) NOT NULL,
  `Field` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Value` text COLLATE utf8_unicode_ci NOT NULL,
  `Category` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TypeId` int(11) NOT NULL COMMENT 'event type id - useful if one event has multiple types to not delete all fileds for diff types',
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_detail_fields`
--

CREATE TABLE `event_detail_fields` (
  `Id` int(11) NOT NULL,
  `Field` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'field as key',
  `Name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL COMMENT 'field name as label/description',
  `Value` text COLLATE utf8_unicode_ci COMMENT 'default value',
  `Required` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if is mandatory or not',
  `Type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'field type as text, checkbox, select, date etc...',
  `Multiple` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if is multiselectable',
  `Pattern` text COLLATE utf8_unicode_ci COMMENT 'validation pattern',
  `Category` text COLLATE utf8_unicode_ci,
  `Stash` text COLLATE utf8_unicode_ci COMMENT 'store anything you need here',
  `TypeId` int(11) NOT NULL COMMENT 'event type id',
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_detail_field_options`
--

CREATE TABLE `event_detail_field_options` (
  `Id` int(11) NOT NULL,
  `Field` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Option` text COLLATE utf8_unicode_ci NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_profiles`
--

CREATE TABLE `event_profiles` (
  `Id` int(11) NOT NULL,
  `CreationDate` datetime NOT NULL,
  `Status` int(3) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_profiles_data`
--

CREATE TABLE `event_profiles_data` (
  `Id` int(11) NOT NULL,
  `ProfileId` int(11) NOT NULL,
  `DataId` int(11) NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_profiles_types`
--

CREATE TABLE `event_profiles_types` (
  `Id` int(11) NOT NULL,
  `ProfileId` int(11) NOT NULL,
  `TypeId` int(11) NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_profile_data`
--

CREATE TABLE `event_profile_data` (
  `Id` int(11) NOT NULL,
  `Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `TypeId` int(11) NOT NULL,
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_profile_data_fields`
--

CREATE TABLE `event_profile_data_fields` (
  `Id` int(11) NOT NULL,
  `Field` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Value` text COLLATE utf8_unicode_ci NOT NULL,
  `DataId` int(11) NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_profile_data_field_config`
--

CREATE TABLE `event_profile_data_field_config` (
  `Id` int(11) NOT NULL,
  `Field` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'field as key',
  `Name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL COMMENT 'field name as label/description',
  `Value` text COLLATE utf8_unicode_ci COMMENT 'default value',
  `Required` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if is mandatory or not',
  `Type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'field type as text, checkbox, select, date etc...',
  `Multiple` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if is multiselectable',
  `Pattern` text COLLATE utf8_unicode_ci COMMENT 'validation pattern',
  `Category` text COLLATE utf8_unicode_ci,
  `Stash` text COLLATE utf8_unicode_ci COMMENT 'store anything you need here',
  `TypeId` int(11) NOT NULL COMMENT 'user type id',
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_profile_data_preferences`
--

CREATE TABLE `event_profile_data_preferences` (
  `Id` int(11) NOT NULL,
  `DataId` int(11) NOT NULL,
  `Category` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Content` text COLLATE utf8_unicode_ci,
  `ContentType` enum('json','serialize','text') COLLATE utf8_unicode_ci NOT NULL,
  `CreationDate` datetime NOT NULL,
  `Status` int(3) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_profile_data_types`
--

CREATE TABLE `event_profile_data_types` (
  `Id` int(11) NOT NULL,
  `Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_profile_details`
--

CREATE TABLE `event_profile_details` (
  `Id` int(11) NOT NULL,
  `ProfileId` int(11) NOT NULL,
  `Field` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `Value` text COLLATE utf8_unicode_ci NOT NULL,
  `Category` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `TypeId` int(11) NOT NULL COMMENT 'profile type id - useful if one profile has multiple types to not delete all fileds for diff types',
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_profile_detail_fields`
--

CREATE TABLE `event_profile_detail_fields` (
  `Id` int(11) NOT NULL,
  `Field` varchar(100) COLLATE utf8_unicode_ci NOT NULL COMMENT 'field as key',
  `Name` varchar(1024) COLLATE utf8_unicode_ci NOT NULL COMMENT 'field name as label/description',
  `Value` text COLLATE utf8_unicode_ci COMMENT 'default value',
  `Required` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if is mandatory or not',
  `Type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'field type as text, checkbox, select, date etc...',
  `Multiple` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'if is multiselectable',
  `Pattern` text COLLATE utf8_unicode_ci COMMENT 'validation pattern',
  `Category` text COLLATE utf8_unicode_ci,
  `Stash` text COLLATE utf8_unicode_ci COMMENT 'store anything you need here',
  `TypeId` int(11) NOT NULL COMMENT 'event type id',
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_profile_preferences`
--

CREATE TABLE `event_profile_preferences` (
  `Id` int(11) NOT NULL,
  `ProfileId` int(11) NOT NULL,
  `Category` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Content` text COLLATE utf8_unicode_ci,
  `ContentType` enum('json','serialize','text') COLLATE utf8_unicode_ci NOT NULL,
  `CreationDate` datetime NOT NULL,
  `Status` int(3) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_profile_translations`
--

CREATE TABLE `event_profile_translations` (
  `Id` int(11) NOT NULL,
  `ProfileId` int(11) NOT NULL,
  `Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Language` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'lang iso code ex: en , en_GB',
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_profile_types`
--

CREATE TABLE `event_profile_types` (
  `Id` int(11) NOT NULL,
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_profile_types_data_types`
--

CREATE TABLE `event_profile_types_data_types` (
  `Id` int(11) NOT NULL,
  `EventProfileTypeId` int(11) NOT NULL,
  `EventProfileDataTypeId` int(11) NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_profile_type_translations`
--

CREATE TABLE `event_profile_type_translations` (
  `Id` int(11) NOT NULL,
  `TypeId` int(11) NOT NULL,
  `Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Language` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'lang iso code ex: en , en_GB',
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_translations`
--

CREATE TABLE `event_translations` (
  `Id` int(11) NOT NULL,
  `EventId` int(11) NOT NULL,
  `Language` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
  `Identifier` varchar(50) COLLATE utf8_unicode_ci NOT NULL COMMENT 'slug friendly',
  `Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_types`
--

CREATE TABLE `event_types` (
  `Id` int(11) NOT NULL,
  `ParentId` int(11) NOT NULL DEFAULT '0',
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL DEFAULT '1',
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_types_data_types`
--

CREATE TABLE `event_types_data_types` (
  `Id` int(11) NOT NULL,
  `EventTypeId` int(11) NOT NULL,
  `EventDataTypeId` int(11) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_type_translations`
--

CREATE TABLE `event_type_translations` (
  `Id` int(11) NOT NULL,
  `TypeId` int(11) NOT NULL,
  `Name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `Language` varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'lang iso code ex: en , en_GB',
  `CreationDate` datetime NOT NULL,
  `Status` int(11) NOT NULL,
  `Timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `entities`
--
ALTER TABLE `entities`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Status` (`Status`);

--
-- Indexes for table `entities_data`
--
ALTER TABLE `entities_data`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `DataId` (`DataId`),
  ADD KEY `EntityId` (`EntityId`,`DataId`) USING BTREE;

--
-- Indexes for table `entities_profiles`
--
ALTER TABLE `entities_profiles`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `EntityIdProfileId` (`EntityId`,`ProfileId`) USING BTREE,
  ADD KEY `ProfileId` (`ProfileId`);

--
-- Indexes for table `entities_types`
--
ALTER TABLE `entities_types`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `EntityIdTypeId` (`EntityId`,`TypeId`) USING BTREE,
  ADD KEY `TypeId` (`TypeId`);

--
-- Indexes for table `entity_data`
--
ALTER TABLE `entity_data`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `TypeId` (`TypeId`,`Name`);

--
-- Indexes for table `entity_data_fields`
--
ALTER TABLE `entity_data_fields`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `ProfileId` (`DataId`,`Field`);

--
-- Indexes for table `entity_data_field_config`
--
ALTER TABLE `entity_data_field_config`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Field` (`Field`,`TypeId`),
  ADD KEY `TypeId` (`TypeId`,`Status`);

--
-- Indexes for table `entity_data_preferences`
--
ALTER TABLE `entity_data_preferences`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `data_category` (`DataId`,`Category`);

--
-- Indexes for table `entity_data_types`
--
ALTER TABLE `entity_data_types`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Name` (`Name`);

--
-- Indexes for table `entity_details`
--
ALTER TABLE `entity_details`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `EntityId` (`EntityId`,`Field`,`Status`,`TypeId`),
  ADD KEY `Field` (`Field`),
  ADD KEY `Status` (`Status`);

--
-- Indexes for table `entity_detail_fields`
--
ALTER TABLE `entity_detail_fields`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Field` (`Field`,`TypeId`),
  ADD KEY `TypeId` (`TypeId`,`Status`);

--
-- Indexes for table `entity_detail_field_options`
--
ALTER TABLE `entity_detail_field_options`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Field` (`Field`);

--
-- Indexes for table `entity_profiles`
--
ALTER TABLE `entity_profiles`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `entity_profiles_data`
--
ALTER TABLE `entity_profiles_data`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `DataId` (`DataId`),
  ADD KEY `ProfileId` (`ProfileId`,`DataId`);

--
-- Indexes for table `entity_profiles_types`
--
ALTER TABLE `entity_profiles_types`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `ProfileIdTypeId` (`ProfileId`,`TypeId`) USING BTREE,
  ADD KEY `TypeId` (`TypeId`);

--
-- Indexes for table `entity_profile_data`
--
ALTER TABLE `entity_profile_data`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `TypeId` (`TypeId`,`Name`);

--
-- Indexes for table `entity_profile_data_fields`
--
ALTER TABLE `entity_profile_data_fields`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `ProfileId` (`DataId`,`Field`);

--
-- Indexes for table `entity_profile_data_field_config`
--
ALTER TABLE `entity_profile_data_field_config`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Field` (`Field`,`TypeId`,`Status`) USING BTREE,
  ADD KEY `TypeId` (`TypeId`,`Status`);

--
-- Indexes for table `entity_profile_data_preferences`
--
ALTER TABLE `entity_profile_data_preferences`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `data_category` (`DataId`,`Category`,`Status`) USING BTREE;

--
-- Indexes for table `entity_profile_data_types`
--
ALTER TABLE `entity_profile_data_types`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Name` (`Name`,`Status`) USING BTREE;

--
-- Indexes for table `entity_profile_details`
--
ALTER TABLE `entity_profile_details`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `ProfileId` (`ProfileId`,`Field`,`Status`,`TypeId`),
  ADD KEY `Field` (`Field`),
  ADD KEY `Status` (`Status`);

--
-- Indexes for table `entity_profile_detail_fields`
--
ALTER TABLE `entity_profile_detail_fields`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Field` (`Field`,`TypeId`,`Status`) USING BTREE,
  ADD KEY `TypeId` (`TypeId`,`Status`);

--
-- Indexes for table `entity_profile_preferences`
--
ALTER TABLE `entity_profile_preferences`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `profile_category` (`ProfileId`,`Category`,`Status`) USING BTREE;

--
-- Indexes for table `entity_profile_translations`
--
ALTER TABLE `entity_profile_translations`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `ProfileId` (`ProfileId`,`Language`,`Status`) USING BTREE,
  ADD KEY `LanguageCode` (`Language`);

--
-- Indexes for table `entity_profile_types`
--
ALTER TABLE `entity_profile_types`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `entity_profile_types_data_types`
--
ALTER TABLE `entity_profile_types_data_types`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `EntityProfileTypeId` (`EntityProfileTypeId`,`EntityProfileDataTypeId`),
  ADD KEY `EntityProfileDataTypeId` (`EntityProfileDataTypeId`);

--
-- Indexes for table `entity_profile_type_translations`
--
ALTER TABLE `entity_profile_type_translations`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `TypeId` (`TypeId`,`Language`,`Status`) USING BTREE,
  ADD KEY `LanguageCode` (`Language`);

--
-- Indexes for table `entity_translations`
--
ALTER TABLE `entity_translations`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `EntityId` (`EntityId`,`Language`),
  ADD KEY `LanguageCode` (`Language`);

--
-- Indexes for table `entity_types`
--
ALTER TABLE `entity_types`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `ParentId` (`ParentId`);

--
-- Indexes for table `entity_types_data_types`
--
ALTER TABLE `entity_types_data_types`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `EntityTypeId` (`EntityTypeId`,`EntityDataTypeId`),
  ADD KEY `EntityDataTypeId` (`EntityDataTypeId`);

--
-- Indexes for table `entity_type_translations`
--
ALTER TABLE `entity_type_translations`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `TypeId` (`TypeId`,`Language`),
  ADD KEY `LanguageCode` (`Language`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `EventInterval` (`StartDate`,`EndDate`),
  ADD KEY `Status` (`Status`),
  ADD KEY `DestinationId` (`DestinationId`);

--
-- Indexes for table `events_data`
--
ALTER TABLE `events_data`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `DataId` (`DataId`),
  ADD KEY `EventId` (`EventId`,`DataId`);

--
-- Indexes for table `events_entities`
--
ALTER TABLE `events_entities`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `EventIdTypeId` (`EventId`,`EntityId`) USING BTREE,
  ADD KEY `TypeId` (`EntityId`);

--
-- Indexes for table `events_profiles`
--
ALTER TABLE `events_profiles`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `EventIdProfileId` (`EventId`,`ProfileId`) USING BTREE,
  ADD KEY `ProfileId` (`ProfileId`);

--
-- Indexes for table `events_services`
--
ALTER TABLE `events_services`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `events_types`
--
ALTER TABLE `events_types`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `EventIdTypeId` (`EventId`,`TypeId`) USING BTREE,
  ADD KEY `TypeId` (`TypeId`);

--
-- Indexes for table `event_data`
--
ALTER TABLE `event_data`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `TypeId` (`TypeId`,`Name`);

--
-- Indexes for table `event_data_fields`
--
ALTER TABLE `event_data_fields`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `ProfileId` (`DataId`,`Field`);

--
-- Indexes for table `event_data_field_config`
--
ALTER TABLE `event_data_field_config`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Field` (`Field`,`TypeId`),
  ADD KEY `TypeId` (`TypeId`,`Status`);

--
-- Indexes for table `event_data_preferences`
--
ALTER TABLE `event_data_preferences`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `data_category` (`DataId`,`Category`);

--
-- Indexes for table `event_data_types`
--
ALTER TABLE `event_data_types`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Name` (`Name`);

--
-- Indexes for table `event_details`
--
ALTER TABLE `event_details`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `EventId` (`EventId`,`Field`,`Status`,`TypeId`),
  ADD KEY `Field` (`Field`),
  ADD KEY `Status` (`Status`);

--
-- Indexes for table `event_detail_fields`
--
ALTER TABLE `event_detail_fields`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Field` (`Field`,`TypeId`),
  ADD KEY `TypeId` (`TypeId`,`Status`);

--
-- Indexes for table `event_detail_field_options`
--
ALTER TABLE `event_detail_field_options`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `Field` (`Field`);

--
-- Indexes for table `event_profiles`
--
ALTER TABLE `event_profiles`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `event_profiles_data`
--
ALTER TABLE `event_profiles_data`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `DataId` (`DataId`),
  ADD KEY `ProfileId` (`ProfileId`,`DataId`);

--
-- Indexes for table `event_profiles_types`
--
ALTER TABLE `event_profiles_types`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `ProfileIdTypeId` (`ProfileId`,`TypeId`) USING BTREE,
  ADD KEY `TypeId` (`TypeId`);

--
-- Indexes for table `event_profile_data`
--
ALTER TABLE `event_profile_data`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `TypeId` (`TypeId`,`Name`);

--
-- Indexes for table `event_profile_data_fields`
--
ALTER TABLE `event_profile_data_fields`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `ProfileId` (`DataId`,`Field`);

--
-- Indexes for table `event_profile_data_field_config`
--
ALTER TABLE `event_profile_data_field_config`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Field` (`Field`,`TypeId`,`Status`) USING BTREE,
  ADD KEY `TypeId` (`TypeId`,`Status`);

--
-- Indexes for table `event_profile_data_preferences`
--
ALTER TABLE `event_profile_data_preferences`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `data_category` (`DataId`,`Category`,`Status`) USING BTREE;

--
-- Indexes for table `event_profile_data_types`
--
ALTER TABLE `event_profile_data_types`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Name` (`Name`,`Status`) USING BTREE;

--
-- Indexes for table `event_profile_details`
--
ALTER TABLE `event_profile_details`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `ProfileId` (`ProfileId`,`Field`,`Status`,`TypeId`),
  ADD KEY `Field` (`Field`),
  ADD KEY `Status` (`Status`);

--
-- Indexes for table `event_profile_detail_fields`
--
ALTER TABLE `event_profile_detail_fields`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `Field` (`Field`,`TypeId`,`Status`) USING BTREE,
  ADD KEY `TypeId` (`TypeId`,`Status`);

--
-- Indexes for table `event_profile_preferences`
--
ALTER TABLE `event_profile_preferences`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `profile_category` (`ProfileId`,`Category`,`Status`) USING BTREE;

--
-- Indexes for table `event_profile_translations`
--
ALTER TABLE `event_profile_translations`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `ProfileId` (`ProfileId`,`Language`,`Status`) USING BTREE,
  ADD KEY `LanguageCode` (`Language`);

--
-- Indexes for table `event_profile_types`
--
ALTER TABLE `event_profile_types`
  ADD PRIMARY KEY (`Id`);

--
-- Indexes for table `event_profile_types_data_types`
--
ALTER TABLE `event_profile_types_data_types`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `EventProfileTypeId` (`EventProfileTypeId`,`EventProfileDataTypeId`),
  ADD KEY `EventProfileDataTypeId` (`EventProfileDataTypeId`);

--
-- Indexes for table `event_profile_type_translations`
--
ALTER TABLE `event_profile_type_translations`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `TypeId` (`TypeId`,`Language`,`Status`) USING BTREE,
  ADD KEY `LanguageCode` (`Language`);

--
-- Indexes for table `event_translations`
--
ALTER TABLE `event_translations`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `EventId` (`EventId`,`Language`),
  ADD KEY `Identifier` (`Identifier`) USING BTREE,
  ADD KEY `Language` (`Language`);

--
-- Indexes for table `event_types`
--
ALTER TABLE `event_types`
  ADD PRIMARY KEY (`Id`),
  ADD KEY `ParentId` (`ParentId`);

--
-- Indexes for table `event_types_data_types`
--
ALTER TABLE `event_types_data_types`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `EventTypeId` (`EventTypeId`,`EventDataTypeId`),
  ADD KEY `EventDataTypeId` (`EventDataTypeId`);

--
-- Indexes for table `event_type_translations`
--
ALTER TABLE `event_type_translations`
  ADD PRIMARY KEY (`Id`),
  ADD UNIQUE KEY `TypeId` (`TypeId`,`Language`),
  ADD KEY `LanguageCode` (`Language`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `entities`
--
ALTER TABLE `entities`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entities_data`
--
ALTER TABLE `entities_data`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entities_profiles`
--
ALTER TABLE `entities_profiles`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entities_types`
--
ALTER TABLE `entities_types`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_data`
--
ALTER TABLE `entity_data`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_data_fields`
--
ALTER TABLE `entity_data_fields`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_data_field_config`
--
ALTER TABLE `entity_data_field_config`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_data_preferences`
--
ALTER TABLE `entity_data_preferences`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_data_types`
--
ALTER TABLE `entity_data_types`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_details`
--
ALTER TABLE `entity_details`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_detail_fields`
--
ALTER TABLE `entity_detail_fields`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_detail_field_options`
--
ALTER TABLE `entity_detail_field_options`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_profiles`
--
ALTER TABLE `entity_profiles`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_profiles_data`
--
ALTER TABLE `entity_profiles_data`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_profiles_types`
--
ALTER TABLE `entity_profiles_types`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_profile_data`
--
ALTER TABLE `entity_profile_data`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_profile_data_fields`
--
ALTER TABLE `entity_profile_data_fields`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_profile_data_field_config`
--
ALTER TABLE `entity_profile_data_field_config`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_profile_data_preferences`
--
ALTER TABLE `entity_profile_data_preferences`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_profile_data_types`
--
ALTER TABLE `entity_profile_data_types`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_profile_details`
--
ALTER TABLE `entity_profile_details`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_profile_detail_fields`
--
ALTER TABLE `entity_profile_detail_fields`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_profile_preferences`
--
ALTER TABLE `entity_profile_preferences`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_profile_translations`
--
ALTER TABLE `entity_profile_translations`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_profile_types`
--
ALTER TABLE `entity_profile_types`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_profile_types_data_types`
--
ALTER TABLE `entity_profile_types_data_types`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_profile_type_translations`
--
ALTER TABLE `entity_profile_type_translations`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_translations`
--
ALTER TABLE `entity_translations`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_types`
--
ALTER TABLE `entity_types`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_types_data_types`
--
ALTER TABLE `entity_types_data_types`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `entity_type_translations`
--
ALTER TABLE `entity_type_translations`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events_data`
--
ALTER TABLE `events_data`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events_entities`
--
ALTER TABLE `events_entities`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events_profiles`
--
ALTER TABLE `events_profiles`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events_services`
--
ALTER TABLE `events_services`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events_types`
--
ALTER TABLE `events_types`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_data`
--
ALTER TABLE `event_data`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_data_fields`
--
ALTER TABLE `event_data_fields`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_data_field_config`
--
ALTER TABLE `event_data_field_config`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_data_preferences`
--
ALTER TABLE `event_data_preferences`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_data_types`
--
ALTER TABLE `event_data_types`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_details`
--
ALTER TABLE `event_details`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_detail_fields`
--
ALTER TABLE `event_detail_fields`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_detail_field_options`
--
ALTER TABLE `event_detail_field_options`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_profiles`
--
ALTER TABLE `event_profiles`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_profiles_data`
--
ALTER TABLE `event_profiles_data`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_profiles_types`
--
ALTER TABLE `event_profiles_types`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_profile_data`
--
ALTER TABLE `event_profile_data`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_profile_data_fields`
--
ALTER TABLE `event_profile_data_fields`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_profile_data_field_config`
--
ALTER TABLE `event_profile_data_field_config`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_profile_data_preferences`
--
ALTER TABLE `event_profile_data_preferences`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_profile_data_types`
--
ALTER TABLE `event_profile_data_types`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_profile_details`
--
ALTER TABLE `event_profile_details`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_profile_detail_fields`
--
ALTER TABLE `event_profile_detail_fields`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_profile_preferences`
--
ALTER TABLE `event_profile_preferences`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_profile_translations`
--
ALTER TABLE `event_profile_translations`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_profile_types`
--
ALTER TABLE `event_profile_types`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_profile_types_data_types`
--
ALTER TABLE `event_profile_types_data_types`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_profile_type_translations`
--
ALTER TABLE `event_profile_type_translations`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_translations`
--
ALTER TABLE `event_translations`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_types`
--
ALTER TABLE `event_types`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_types_data_types`
--
ALTER TABLE `event_types_data_types`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `event_type_translations`
--
ALTER TABLE `event_type_translations`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
