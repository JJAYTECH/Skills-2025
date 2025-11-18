CREATE DATABASE IF NOT EXISTS election1;
USE election1;

-- positions table
CREATE TABLE positions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(255),
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- candidates table
CREATE TABLE candidates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  position_id INT NOT NULL,
  party VARCHAR(100),
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (position_id) REFERENCES positions(id)
);

-- voters table
CREATE TABLE voters (
  id INT AUTO_INCREMENT PRIMARY KEY,
  voter_id VARCHAR(50) NOT NULL UNIQUE,
  full_name VARCHAR(150) NOT NULL,
  password VARCHAR(100) NOT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);







//updatessss ni!!!!!

USE election1;

-- add column para limit sa votes per position
ALTER TABLE positions 
ADD COLUMN max_seats INT NOT NULL DEFAULT 1;

-- add column para ma mark kung naka vote na ang voter
ALTER TABLE voters
ADD COLUMN voted TINYINT(1) NOT NULL DEFAULT 0;

-- table para sa votes
CREATE TABLE votes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  voter_id INT NOT NULL,
  candidate_id INT NOT NULL,
  position_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (voter_id) REFERENCES voters(id),
  FOREIGN KEY (candidate_id) REFERENCES candidates(id),
  FOREIGN KEY (position_id) REFERENCES positions(id)
);
