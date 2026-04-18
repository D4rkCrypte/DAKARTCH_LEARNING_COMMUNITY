CREATE DATABASE IF NOT EXISTS dakartech_hack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE dakartech_hack;

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('MEMBER','ADMIN','MENTOR','SUPERADMIN') NOT NULL DEFAULT 'MEMBER',
  avatar_url VARCHAR(500) NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email),
  UNIQUE KEY uq_users_username (username),
  KEY idx_users_role (role)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS user_tokens (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  token CHAR(64) NOT NULL,
  expires_at DATETIME NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_user_tokens_token (token),
  KEY idx_user_tokens_user_id (user_id),
  CONSTRAINT fk_user_tokens_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS contact_messages (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL,
  subject VARCHAR(50) NOT NULL,
  message TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_contact_messages_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS team_members (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  team ENUM('formation','ctf') NOT NULL,
  pseudo VARCHAR(80) NOT NULL,
  avatar_url VARCHAR(500) NULL,
  is_captain TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_team_members_team (team)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS forum_topics (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  author_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(180) NOT NULL,
  content TEXT NOT NULL,
  category VARCHAR(50) NOT NULL,
  author_name VARCHAR(120) NOT NULL,
  author_email VARCHAR(190) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_forum_topics_category (category),
  KEY idx_forum_topics_author (author_id),
  CONSTRAINT fk_forum_topics_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS forum_replies (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  topic_id BIGINT UNSIGNED NOT NULL,
  author_id BIGINT UNSIGNED NOT NULL,
  content TEXT NOT NULL,
  author_name VARCHAR(120) NOT NULL,
  author_email VARCHAR(190) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_forum_replies_topic (topic_id),
  KEY idx_forum_replies_author (author_id),
  CONSTRAINT fk_forum_replies_topic FOREIGN KEY (topic_id) REFERENCES forum_topics(id) ON DELETE CASCADE,
  CONSTRAINT fk_forum_replies_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS news_articles (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  content TEXT NOT NULL,
  image_url VARCHAR(500) NULL,
  author VARCHAR(100) NOT NULL DEFAULT 'Admin',
  published_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ctf_challenges (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  title VARCHAR(255) NOT NULL,
  description TEXT NOT NULL,
  category VARCHAR(50) NOT NULL,
  points INT NOT NULL DEFAULT 0,
  flag VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_ctf_challenges_category (category)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS ctf_solves (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  challenge_id BIGINT UNSIGNED NOT NULL,
  solved_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_ctf_solves (user_id, challenge_id),
  CONSTRAINT fk_ctf_solves_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  CONSTRAINT fk_ctf_solves_challenge FOREIGN KEY (challenge_id) REFERENCES ctf_challenges(id) ON DELETE CASCADE
) ENGINE=InnoDB;
