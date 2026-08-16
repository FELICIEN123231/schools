-- Database: school
CREATE DATABASE IF NOT EXISTS school;
USE school;

-- Table: department
CREATE TABLE IF NOT EXISTS department (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: students
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    gender ENUM('Male','Female','Other') NOT NULL,
    dob DATE NOT NULL,
    department_id INT NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'Student',
    FOREIGN KEY (department_id) REFERENCES department(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: user
CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(32) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'User',
    status VARCHAR(20) NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Sample departments
INSERT INTO department (department_name, description) VALUES
('Computer Science', 'Study of computers and computational systems'),
('Information Technology', 'Application of technology to solve business problems'),
('Business Administration', 'Management and business operations'),
('Engineering', 'Applied science and design');

-- Sample admin user (password: Admin@123) - status Approved so admin can login
INSERT INTO user (fullname, username, email, password, role, status) VALUES
('Administrator', 'Admin', 'admin@school.com', MD5('Admin@123'), 'Admin', 'Approved');