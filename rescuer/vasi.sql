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
    phone INT(10) NOT NULL,
    FOREIGN KEY (username) REFERENCES users(username)
    ON DELETE CASCADE ON UPDATE CASCADE
)engine=InnoDB;

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
---- Sto warehouse giati sumfwna me ekfwnhsh o admin mporei na kanei drag and drop to marker tou warehouse kai na allaksei thn topothesia etsi------
CREATE TABLE warehouse (
    productId INT AUTO_INCREMENT PRIMARY KEY,
    productName VARCHAR(25) NOT NULL,
    productCategory ENUM('FOOD', 'DRINK', 'TOOL', 'OTHER') NOT NULL,
    productQuantity INT NOT NULL,
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
    username INT NOT NULL,
    productId INT NOT NULL,
    quantity INT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'taken', 'finished') NOT NULL DEFAULT 'pending',
    FOREIGN KEY (username) REFERENCES citizens(username),
    FOREIGN KEY (productId) REFERENCES warehouse(productId)
)engine=InnoDB;

CREATE TABLE offers (
    offerId INT AUTO_INCREMENT PRIMARY KEY,
    username INT NOT NULL,
    productId INT NOT NULL,
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
    FOREIGN KEY (rescuerId) REFERENCES users(userId),
    FOREIGN KEY (taskIdRef) REFERENCES requests(requestId) ON DELETE CASCADE,
    FOREIGN KEY (taskIdRef) REFERENCES offers(offerId) ON DELETE CASCADE
)engine=InnoDB;