DROP DATABASE IF EXISTS vasi;
CREATE DATABASE vasi;
USE vasi;
 
CREATE TABLE users (
    username VARCHAR(25) PRIMARY KEY,
    password VARCHAR(25) NOT NULL,
    is_admin BOOLEAN NOT NULL DEFAULT FALSE
)engine=InnoDB;

CREATE TABLE rescuers (
    username VARCHAR(25) PRIMARY KEY,
    name VARCHAR(25) NOT NULL,
    surname VARCHAR(25) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    latitude DECIMAL(9,6) NOT NULL,
    longitude DECIMAL(9,6) NOT NULL,
    FOREIGN KEY (username) REFERENCES users(username)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE citizens (
    username VARCHAR(25) PRIMARY KEY,
    name VARCHAR(25) NOT NULL,
    surname VARCHAR(25) NOT NULL,
    phone VARCHAR(10) NOT NULL,
    latitude DECIMAL(9,6),
    longitude DECIMAL(9,6),
    FOREIGN KEY (username) REFERENCES users(username)
    ON DELETE CASCADE ON UPDATE CASCADE
)engine=InnoDB;

CREATE TABLE base (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    latitude DECIMAL(9,6) NOT NULL,
    longitude DECIMAL(9,6) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE warehouse (
	productId INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    productName VARCHAR(25) NOT NULL,
    productCategory ENUM('FOOD', 'DRINK', 'MEDS', 'TOOL', 'OTHER') NOT NULL,
    productQuantity INT NOT NULL
)engine=InnoDB;

CREATE TABLE onvehicles (
    productName VARCHAR(25) NOT NULL,
    productQuantity INT NOT NULL,
    rescuerUsername VARCHAR(25) NOT NULL,
    FOREIGN KEY (rescuerUsername) REFERENCES rescuers(username)
    ON DELETE CASCADE ON UPDATE CASCADE
)engine=InnoDB;

CREATE TABLE announcements (
    announcementId INT AUTO_INCREMENT PRIMARY KEY,
    announcementTitle VARCHAR(255) NOT NULL,
    announcementText TEXT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)engine=InnoDB;

CREATE TABLE requests (
    requestId INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(25) NOT NULL,
    productId INT UNSIGNED NOT NULL,
    quantity INT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'taken', 'finished') NOT NULL DEFAULT 'pending',
    FOREIGN KEY (username) REFERENCES citizens(username),
    FOREIGN KEY (productId) REFERENCES warehouse(productId)
)engine=InnoDB;

CREATE TABLE offers (
    offerId INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(25) NOT NULL,
    productId INT UNSIGNED NOT NULL,
    quantity INT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'taken', 'finished') NOT NULL DEFAULT 'pending',
    FOREIGN KEY (username) REFERENCES citizens(username),
    FOREIGN KEY (productId) REFERENCES warehouse(productId)
)engine=InnoDB;

CREATE TABLE rescuer_tasks (
    rescuerUsername VARCHAR(25) NOT NULL,
    taskType ENUM('request', 'offer') NOT NULL,
    taskIdRef INT NOT NULL,
    FOREIGN KEY (rescuerUsername) REFERENCES users(username),
    FOREIGN KEY (taskIdRef) REFERENCES requests(requestId) ON DELETE CASCADE,
    FOREIGN KEY (taskIdRef) REFERENCES offers(offerId) ON DELETE CASCADE
)engine=InnoDB;

-- Insert data into base table
INSERT INTO base (latitude, longitude) VALUES (37.9038, 23.7275);

-- Insert data into users table
INSERT INTO users (username, password, is_admin) VALUES
('rescuer1', 'pass1', FALSE),
('rescuer2', 'pass2', FALSE),
('rescuer3', 'pass3', FALSE),
('rescuer4', 'pass4', FALSE),
('rescuer5', 'pass5', FALSE),
('citizen1', 'pass6', FALSE),
('citizen2', 'pass7', FALSE),
('citizen3', 'pass8', FALSE),
('citizen4', 'pass9', FALSE),
('citizen5', 'pass10', FALSE);

-- Insert data into rescuers table
INSERT INTO rescuers (username, name, surname, phone, latitude, longitude) VALUES
('rescuer1', 'John', 'Doe', '1234567890', 37.9838, 23.7275),
('rescuer2', 'Jane', 'Smith', '1234567891', 37.9839, 23.7276),
('rescuer3', 'Jim', 'Beam', '1234567892', 37.9840, 23.7277),
('rescuer4', 'Jack', 'Daniels', '1234567893', 37.9841, 23.7278),
('rescuer5', 'Johnny', 'Walker', '1234567894', 37.9842, 23.7279);

-- Insert data into citizens table
INSERT INTO citizens (username, name, surname, phone, latitude, longitude) VALUES
('citizen1', 'Alice', 'Johnson', '9876543210', 37.9850, 23.7280),
('citizen2', 'Bob', 'Brown', '9876543211', 37.9851, 23.7281),
('citizen3', 'Charlie', 'Davis', '9876543212', 37.9852, 23.7282),
('citizen4', 'Daisy', 'Evans', '9876543213', 37.9853, 23.7283),
('citizen5', 'Eve', 'Williams', '9876543214', 37.9854, 23.7284);

-- Insert data into warehouse table
INSERT INTO warehouse (productName, productCategory, productQuantity) VALUES
('Water', 'DRINK', 100),
('Bread', 'FOOD', 200),
('Hammer', 'TOOL', 50),
('Bandages', 'OTHER', 75),
('Milk', 'DRINK', 150);

-- Insert data into onvehicles table
INSERT INTO onvehicles (productName, productQuantity, rescuerUsername) VALUES
('Water', 10, 'rescuer1'),
('Bread', 20, 'rescuer2'),
('Hammer', 5, 'rescuer3'),
('Bandages', 7, 'rescuer4'),
('Milk', 15, 'rescuer5');

-- Insert data into announcements table
INSERT INTO announcements (announcementTitle, announcementText) VALUES
('Meeting', 'There will be a meeting at 5 PM'),
('Supplies', 'New supplies have arrived'),
('Training', 'Training session on Monday'),
('Event', 'Community event this weekend'),
('Maintenance', 'System maintenance on Friday');

-- Insert data into requests table
INSERT INTO requests (username, productId, quantity, status) VALUES
('citizen1', 1, 10, 'pending'),
('citizen2', 2, 20, 'taken'),
('citizen3', 3, 5, 'finished'),
('citizen4', 4, 7, 'pending'),
('citizen5', 5, 15, 'taken');

-- Insert data into offers table
INSERT INTO offers (username, productId, quantity, status) VALUES
('citizen1', 1, 5, 'pending'),
('citizen2', 2, 10, 'taken'),
('citizen3', 3, 3, 'finished'),
('citizen4', 4, 8, 'pending'),
('citizen5', 5, 12, 'taken');

-- Insert data into rescuer_tasks table
INSERT INTO rescuer_tasks (rescuerUsername, taskType, taskIdRef) VALUES
('rescuer1', 'request', 1),
('rescuer2', 'request', 2),
('rescuer3', 'offer', 3),
('rescuer4', 'offer', 4),
('rescuer5', 'request', 5);