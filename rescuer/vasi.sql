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
    phone INT(10) NOT NULL,
    latitude DECIMAL(9,6),
    longitude DECIMAL(9,6),
    FOREIGN KEY (username) REFERENCES users(username)
    ON DELETE CASCADE ON UPDATE CASCADE
)engine=InnoDB;

CREATE TABLE warehouse (
	productId INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    productName VARCHAR(25) NOT NULL,
    productCategory ENUM('FOOD', 'DRINK', 'TOOL', 'OTHER') NOT NULL,
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


-- Insert users into the users table
INSERT INTO users (username, password, is_admin) VALUES
('citizen1', 'password1', 0),
('citizen2', 'password2', 0),
('citizen3', 'password3', 0);

-- Insert new citizens into the citizens table
INSERT INTO citizens (username, name, surname, phone, latitude, longitude) VALUES
('citizen1', 'Alice', 'Smith', '1234567890', 37.9768, 23.7361),
('citizen2', 'Bob', 'Johnson', '0987654321', 37.9833, 23.7278),
('citizen3', 'Charlie', 'Brown', '1122334455', 37.9789, 23.7315);
