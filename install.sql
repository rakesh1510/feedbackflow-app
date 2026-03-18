-- FeedbackFlow Database Schema
-- Run this file to set up your database
-- Compatible with MySQL 5.7+ and MariaDB 10.3+

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Users & Authentication
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ff_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(191) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` enum('owner','admin','manager','member','viewer') NOT NULL DEFAULT 'member',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verify_token` varchar(64) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Projects
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ff_projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL UNIQUE,
  `description` text DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `owner_id` int(11) NOT NULL,
  `is_public` tinyint(1) NOT NULL DEFAULT 1,
  `allow_anonymous` tinyint(1) NOT NULL DEFAULT 1,
  `widget_key` varchar(64) NOT NULL,
  `widget_color` varchar(7) NOT NULL DEFAULT '#6366f1',
  `widget_position` enum('bottom-right','bottom-left','top-right','top-left') DEFAULT 'bottom-right',
  `widget_theme` enum('light','dark','auto') DEFAULT 'light',
  `widget_title` varchar(100) DEFAULT 'Share your feedback',
  `widget_placeholder` varchar(200) DEFAULT 'Tell us what you think...',
  `custom_domain` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Project Members
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ff_project_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('admin','manager','member','viewer') NOT NULL DEFAULT 'member',
  `invited_by` int(11) DEFAULT NULL,
  `joined_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_user` (`project_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Feedback Categories
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ff_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `name` varchar(80) NOT NULL,
  `slug` varchar(80) NOT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#6366f1',
  `icon` varchar(50) DEFAULT 'tag',
  `description` text DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_slug` (`project_id`,`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Feedback
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ff_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('new','under_review','planned','in_progress','done','declined','duplicate') NOT NULL DEFAULT 'new',
  `priority` enum('critical','high','medium','low') NOT NULL DEFAULT 'medium',
  `is_public` tinyint(1) NOT NULL DEFAULT 1,
  `submitter_name` varchar(100) DEFAULT NULL,
  `submitter_email` varchar(191) DEFAULT NULL,
  `submitter_id` int(11) DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `vote_count` int(11) NOT NULL DEFAULT 0,
  `comment_count` int(11) NOT NULL DEFAULT 0,
  `view_count` int(11) NOT NULL DEFAULT 0,
  `ai_sentiment` enum('positive','neutral','negative') DEFAULT NULL,
  `ai_sentiment_score` decimal(4,3) DEFAULT NULL,
  `ai_summary` text DEFAULT NULL,
  `ai_priority_score` decimal(4,2) DEFAULT NULL,
  `ai_tags` text DEFAULT NULL,
  `duplicate_of` int(11) DEFAULT NULL,
  `impact_score` decimal(4,2) DEFAULT NULL,
  `page_url` varchar(500) DEFAULT NULL,
  `browser_info` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `category_id` (`category_id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tags
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ff_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#94a3b8',
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_name` (`project_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ff_feedback_tags` (
  `feedback_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  PRIMARY KEY (`feedback_id`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Votes
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ff_votes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feedback_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `voter_ip` varchar(45) DEFAULT NULL,
  `voter_email` varchar(191) DEFAULT NULL,
  `emoji` varchar(10) DEFAULT '👍',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `feedback_user` (`feedback_id`,`user_id`),
  KEY `feedback_ip` (`feedback_id`,`voter_ip`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Comments (Internal Notes + Public Comments)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ff_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feedback_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `commenter_name` varchar(100) DEFAULT NULL,
  `commenter_email` varchar(191) DEFAULT NULL,
  `content` text NOT NULL,
  `is_internal` tinyint(1) NOT NULL DEFAULT 0,
  `is_admin_reply` tinyint(1) NOT NULL DEFAULT 0,
  `parent_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `feedback_id` (`feedback_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- File Attachments
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ff_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `feedback_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `file_size` int(11) NOT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `feedback_id` (`feedback_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Roadmap Items
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ff_roadmap` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `feedback_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('planned','in_progress','done') NOT NULL DEFAULT 'planned',
  `quarter` varchar(20) DEFAULT NULL,
  `target_date` date DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `is_public` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Changelog
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ff_changelog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `type` enum('new','improvement','bugfix','breaking') NOT NULL DEFAULT 'new',
  `version` varchar(50) DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 0,
  `author_id` int(11) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Notifications
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ff_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `feedback_id` int(11) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `is_read` (`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Settings
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ff_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) DEFAULT NULL,
  `key` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `project_key` (`project_id`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Activity Log
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ff_activity` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `feedback_id` int(11) DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `meta` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `feedback_id` (`feedback_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Webhook Endpoints
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ff_webhooks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `url` varchar(500) NOT NULL,
  `secret` varchar(64) DEFAULT NULL,
  `events` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `last_triggered` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- AI Weekly Reports
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ff_ai_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `report_date` date NOT NULL,
  `content` longtext DEFAULT NULL,
  `top_themes` text DEFAULT NULL,
  `sentiment_breakdown` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Sessions (for custom session handling)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ff_sessions` (
  `id` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(300) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Spam Protection
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `ff_rate_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(45) NOT NULL,
  `action` varchar(50) NOT NULL,
  `count` int(11) NOT NULL DEFAULT 1,
  `window_start` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_action` (`ip`,`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Multi-Channel Collection (Email, QR, WhatsApp, SMS)
-- --------------------------------------------------------
ALTER TABLE `ff_feedback`
  ADD COLUMN IF NOT EXISTS `source` varchar(30) NOT NULL DEFAULT 'widget' AFTER `project_id`,
  ADD COLUMN IF NOT EXISTS `rating` tinyint(1) DEFAULT NULL AFTER `source`,
  ADD COLUMN IF NOT EXISTS `campaign_id` int(11) DEFAULT NULL AFTER `rating`;

CREATE TABLE IF NOT EXISTS `ff_email_campaigns` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL DEFAULT 'We''d love your feedback',
  `intro_text` text DEFAULT NULL,
  `rating_question` varchar(255) NOT NULL DEFAULT 'How was your experience?',
  `show_category` tinyint(1) NOT NULL DEFAULT 1,
  `show_message` tinyint(1) NOT NULL DEFAULT 1,
  `status` enum('draft','sending','sent') NOT NULL DEFAULT 'draft',
  `sent_count` int(11) NOT NULL DEFAULT 0,
  `open_count` int(11) NOT NULL DEFAULT 0,
  `submit_count` int(11) NOT NULL DEFAULT 0,
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ff_campaign_recipients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `campaign_id` int(11) NOT NULL,
  `email` varchar(191) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `token` varchar(64) NOT NULL,
  `pre_rating` tinyint(1) DEFAULT NULL,
  `opened_at` datetime DEFAULT NULL,
  `submitted_at` datetime DEFAULT NULL,
  `feedback_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `campaign_id` (`campaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- AI Copilot: Intent + Reply columns on feedback
-- --------------------------------------------------------
ALTER TABLE `ff_feedback`
  ADD COLUMN IF NOT EXISTS `ai_intent` enum('bug','feature','ux','pricing','performance','praise','other') DEFAULT NULL AFTER `ai_tags`,
  ADD COLUMN IF NOT EXISTS `ai_reply` text DEFAULT NULL AFTER `ai_intent`,
  ADD COLUMN IF NOT EXISTS `ai_reply_sent` tinyint(1) NOT NULL DEFAULT 0 AFTER `ai_reply`,
  ADD COLUMN IF NOT EXISTS `ai_reply_sent_at` datetime DEFAULT NULL AFTER `ai_reply_sent`;

-- AI Feedback Clusters
CREATE TABLE IF NOT EXISTS `ff_ai_clusters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `intent` enum('bug','feature','ux','pricing','performance','praise','other') NOT NULL DEFAULT 'other',
  `severity` enum('critical','high','medium','low') NOT NULL DEFAULT 'medium',
  `feedback_count` int(11) NOT NULL DEFAULT 0,
  `avg_sentiment` varchar(20) DEFAULT NULL,
  `trend` enum('rising','stable','falling') NOT NULL DEFAULT 'stable',
  `trend_pct` int(11) NOT NULL DEFAULT 0,
  `suggested_action` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Maps feedback items to clusters
CREATE TABLE IF NOT EXISTS `ff_cluster_feedback` (
  `cluster_id` int(11) NOT NULL,
  `feedback_id` int(11) NOT NULL,
  PRIMARY KEY (`cluster_id`,`feedback_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- AI-generated CEO-level insights
CREATE TABLE IF NOT EXISTS `ff_ai_insights` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `type` enum('trending','sentiment','release_impact','praise','warning') NOT NULL DEFAULT 'trending',
  `title` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `metric` varchar(100) DEFAULT NULL,
  `icon` varchar(10) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `generated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `ff_feedback_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL DEFAULT 'Feedback Link',
  `source` varchar(30) NOT NULL DEFAULT 'direct',
  `token` varchar(64) NOT NULL,
  `rating_question` varchar(255) DEFAULT 'How was your experience?',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `click_count` int(11) NOT NULL DEFAULT 0,
  `submit_count` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
