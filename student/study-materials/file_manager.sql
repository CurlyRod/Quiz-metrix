-- Create the database
CREATE DATABASE IF NOT EXISTS file_manager;
USE file_manager;

-- Create folders table
CREATE TABLE IF NOT EXISTS folders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    parent_id INT NULL,
    position INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_id) REFERENCES folders(id) ON DELETE CASCADE
);

-- Create files table
CREATE TABLE IF NOT EXISTS files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type ENUM('pdf', 'docx', 'txt') NOT NULL,
    size INT NOT NULL,
    folder_id INT NULL,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    file_path VARCHAR(255) NOT NULL,
    position INT DEFAULT 0,
    is_deleted BOOLEAN DEFAULT FALSE,
    deleted_at TIMESTAMP NULL,
    FOREIGN KEY (folder_id) REFERENCES folders(id) ON DELETE SET NULL
);

-- Add indexes for better performance
CREATE INDEX idx_folders_parent_id ON folders(parent_id);
CREATE INDEX idx_files_folder_id ON files(folder_id);
CREATE INDEX idx_files_upload_date ON files(upload_date);
CREATE INDEX idx_files_is_deleted ON files(is_deleted);
CREATE INDEX idx_folders_position ON folders(position);
CREATE INDEX idx_files_position ON files(position);
CREATE INDEX idx_files_name ON files(name);
CREATE INDEX idx_folders_name ON folders(name);

