DROP DATABASE IF EXISTS vasi;
CREATE DATABASE vasi;
USE vasi;
---------------------BAZW SE SXOLIO OTI PINAKA THEWRW OTI XREIAZETAI ATTRIBUTE LAT, LONG--------------------
-----O LOGOS EINAI OTI H ALLHLEPIDRASH TOU XARTH KAI TOU BACKEND THA GINETAI MESW MARKER--------------  
CREATE TABLE users (
    username VARCHAR(25) PRIMARY KEY,
    password VARCHAR(25) NOT NULL,
    is_admin BOOLEAN NOT NULL DEFAULT FALSE
)engine=InnoDB;

CREATE TABLE rescuers (
    username VARCHAR(25) PRIMARY KEY,
    name VARCHAR (25) NOT NULL,
    surname VARCHAR(25) NOT NULL,
    phone VARCHAR(10) NOT NULL,
    FOREIGN KEY (username) REFERENCES users(username)
    ON DELETE CASCADE ON UPDATE CASCADE
)engine=InnoDB;

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
---- Sto warehouse giati sumfwna me ekfwnhsh o admin mporei na kanei drag and drop to marker tou warehouse kai na allaksei thn topothesia etsi------
CREATE TABLE warehouse (
	productId INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    productName VARCHAR(25) NOT NULL,
    productCategory ENUM('FOOD', 'DRINK', 'TOOL', 'OTHER') NOT NULL,
    productQuantity INT NOT NULL
)engine=InnoDB;
----PROTEINW metonomasia se sketo vehicles kai prosthhkh pediwn: lat, long, isws kai enos boolean onDuty pediou gia na kanw etsi diaforopoihsh twn marker----
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
---- 
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

INSERT INTO users (username, password, is_admin) VALUES
('user1', 'pass1', TRUE),
('user2', 'pass2', FALSE),
('user3', 'pass3', FALSE),
('user4', 'pass4', FALSE);

INSERT INTO rescuers (username, name, surname, phone) VALUES
('user2', 'John', 'Doe', '1234567890');

INSERT INTO citizens (username, name, surname, phone, latitude, longitude) VALUES
('user3', 'Jane', 'Smith', '0987654321', 40.712776, -74.005974),
('user4', 'Alice', 'Johnson', '1122334455', 34.052235, -118.243683);

INSERT INTO warehouse (productName, productCategory, productQuantity) VALUES
('Water Bottle', 'DRINK', 100),
('Canned Beans', 'FOOD', 200),
('Flashlight', 'TOOL', 50),
('Blanket', 'OTHER', 75),
('First Aid Kit', 'TOOL', 30);

INSERT INTO onvehicles (productName, productQuantity, rescuerUsername) VALUES
('Water Bottle', 10, 'user2'),
('First Aid Kit', 5, 'user2');

INSERT INTO announcements (announcementTitle, announcementText) VALUES
('Emergency Alert', 'There is a severe weather warning in your area.'),
('Food Distribution', 'Free food distribution will take place at the community center.');

INSERT INTO requests (username, productId, quantity, status) VALUES
('user3', 1, 2, 'pending'),
('user4', 2, 5, 'pending');

INSERT INTO offers (username, productId, quantity, status) VALUES
('user3', 3, 1, 'pending'),
('user4', 4, 2, 'pending');

INSERT INTO rescuer_tasks (rescuerUsername, taskType, taskIdRef) VALUES
('user2', 'request', 1),
('user2', 'offer', 1);