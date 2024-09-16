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
    productName VARCHAR(25) UNIQUE NOT NULL,
    productCategory ENUM('FOOD', 'DRINK', 'MEDS', 'TOOL', 'OTHER') NOT NULL,
    productQuantity INT NOT NULL DEFAULT 1
) ENGINE=InnoDB;


CREATE TABLE onvehicles (
    productName VARCHAR(25) NOT NULL,
    productQuantity INT NOT NULL,
    rescuerUsername VARCHAR(25) NOT NULL,
    FOREIGN KEY (rescuerUsername) REFERENCES rescuers(username)
    ON DELETE CASCADE ON UPDATE CASCADE
)engine=InnoDB;

CREATE TABLE requests (
    requestId INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(25) NOT NULL,
    productId INT UNSIGNED NOT NULL,
    quantity INT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'taken', 'finished') NOT NULL DEFAULT 'pending',
    acceptDate DATETIME DEFAULT NULL,
    completeDate DATETIME DEFAULT NULL,
    numberOfPeople INT NOT NULL,
    FOREIGN KEY (username) REFERENCES citizens(username) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (productId) REFERENCES warehouse(productId) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE announcements (
    announcementId INT AUTO_INCREMENT PRIMARY KEY,
    requestId INT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (requestId) REFERENCES requests(requestId)
)engine=InnoDB;

CREATE TABLE offers (
    offerId INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(25) NOT NULL,
    productId INT UNSIGNED NOT NULL,
    quantity INT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'taken', 'finished') NOT NULL DEFAULT 'pending',
    acceptDate DATETIME DEFAULT NULL,
    completeDate DATETIME DEFAULT NULL,
    numberOfPeople INT NOT NULL,
    FOREIGN KEY (username) REFERENCES citizens(username),
    FOREIGN KEY (productId) REFERENCES warehouse(productId)
)engine=InnoDB;

CREATE TABLE rescuer_tasks (
    taskId INT AUTO_INCREMENT PRIMARY KEY,
    rescuerUsername VARCHAR(25) NOT NULL,
    taskType ENUM('request', 'offer') NOT NULL,
    requestId INT,
    offerId INT,
    FOREIGN KEY (rescuerUsername) REFERENCES rescuers(username),
    FOREIGN KEY (requestId) REFERENCES requests(requestId) ON DELETE CASCADE,
    FOREIGN KEY (offerId) REFERENCES offers(offerId) ON DELETE CASCADE,
    CHECK ((requestId IS NOT NULL AND offerId IS NULL) OR (requestId IS NULL AND offerId IS NOT NULL))
) engine=InnoDB;


DELIMITER //

CREATE TRIGGER status_update_for_task_taking
AFTER INSERT ON rescuer_tasks
FOR EACH ROW
BEGIN
    IF NEW.taskType = 'offer' THEN
        UPDATE offers
        SET status = 'taken'
        WHERE offerId = NEW.offerId;
    ELSEIF NEW.taskType = 'request' THEN
        UPDATE requests
        SET status = 'taken'
        WHERE requestId = NEW.requestId;
    END IF;
END;

//

CREATE TRIGGER status_update_for_task_cancelation
AFTER DELETE ON rescuer_tasks
FOR EACH ROW
BEGIN
    IF OLD.taskType = 'offer' THEN
        UPDATE offers
        SET status = 'pending'
        WHERE offerId = OLD.offerId;
    ELSEIF OLD.taskType = 'request' THEN
        UPDATE requests
        SET status = 'pending'
        WHERE requestId = OLD.requestId;
    END IF;
END;

//

CREATE TRIGGER requests_status_change
BEFORE UPDATE ON requests
FOR EACH ROW
BEGIN
    IF NEW.status <> OLD.status THEN
        IF NEW.status = 'taken' THEN
            SET NEW.acceptDate = NOW();
        ELSEIF NEW.status = 'pending' THEN
            SET NEW.acceptDate = NULL;
        ELSEIF NEW.status = 'finished' THEN
            SET NEW.completeDate = NOW();
        END IF;
    END IF;
END 

//

CREATE TRIGGER offers_status_change
BEFORE UPDATE ON offers
FOR EACH ROW
BEGIN
    IF NEW.status <> OLD.status THEN
        IF NEW.status = 'taken' THEN
            SET NEW.acceptDate = NOW();
        ELSEIF NEW.status = 'pending' THEN
            SET NEW.acceptDate = NULL;
        ELSEIF NEW.status = 'finished' THEN
            SET NEW.completeDate = NOW();
        END IF;
    END IF;
END 

//

DELIMITER ;

-- Insert data into base table
INSERT INTO base (latitude, longitude) VALUES (37.97199, 23.73416);

-- Insert data into users table
INSERT INTO users (username, password, is_admin) VALUES
('rescuer1', 'pass1', FALSE),
('rescuer2', 'pass2', FALSE),
('rescuer3', 'pass3', FALSE),
('rescuer4', 'pass4', FALSE),
('rescuer5', 'pass5', FALSE),
('citizen1', 'pass1', FALSE),
('citizen2', 'pass2', FALSE),
('citizen3', 'pass3', FALSE),
('citizen4', 'pass4', FALSE),
('citizen5', 'pass5', FALSE),
('citizen6', 'pass6', FALSE),
('citizen7', 'pass7', FALSE),
('citizen8', 'pass8', FALSE),
('citizen9', 'pass9', FALSE),
('citizen10', 'pass10', FALSE);

-- Insert data into rescuers table with random coordinates near Athens, Greece
INSERT INTO rescuers (username, name, surname, phone, latitude, longitude) VALUES
('rescuer1', 'John', 'Doe', '1234567890', 37.9812, 23.7253),
('rescuer2', 'Jane', 'Smith', '1234567891', 37.9867, 23.7301),
('rescuer3', 'Jim', 'Beam', '1234567892', 37.9192, 23.6994),
('rescuer4', 'Jack', 'Daniels', '1234567893', 37.9257, 23.7480),
('rescuer5', 'Johnny', 'Walker', '1234567894', 37.9804, 23.7400);

-- Insert data into citizens table
INSERT INTO citizens (username, name, surname, phone, latitude, longitude) VALUES
('citizen1', 'Alice', 'Johnson', '6927345832', 37.9292, 23.6894),
('citizen2', 'Bob', 'Brown', '6927345833', 37.9357, 23.7420),
('citizen3', 'Charlie', 'Davis', '6927345834', 37.9482, 23.7634),
('citizen4', 'Daisy', 'Evans', '6927345835', 37.9156, 23.7567),
('citizen5', 'Eve', 'Williams', '6927345836', 37.9641, 23.7023),
('citizen6', 'John', 'Poulopoulos', '6927345837', 37.9773, 23.7325),
('citizen7', 'George', 'Iliakis', '6927345838', 37.9510, 23.7214),
('citizen8', 'Dennis', 'Davis', '6927345839', 37.9824, 23.7596),
('citizen9', 'Mary', 'Christians', '6927345840', 37.9967, 23.7708),
('citizen10', 'Sofoklis', 'Toliopoulos', '6927345841', 37.9105, 23.7792);

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

-- Insert data into requests table
INSERT INTO requests (username, productId, quantity, status) VALUES
('citizen1', 1, 2, 'taken'),
('citizen2', 2, 3, 'pending'),
('citizen3', 3, 5, 'pending'),
('citizen4', 4, 7, 'pending'),
('citizen5', 5, 6, 'pending');

-- Insert data into offers table
INSERT INTO offers (username, productId, quantity, status) VALUES
('citizen6', 1, 7, 'pending'),
('citizen7', 2, 12, 'pending'),
('citizen8', 3, 12, 'pending'),
('citizen9', 4, 9, 'pending'),
('citizen10', 5, 11, 'pending');

-- Insert requests into rescuer_tasks table
INSERT INTO rescuer_tasks (rescuerUsername, taskType, requestId) VALUES
('rescuer1', 'request', 1);

INSERT INTO users (username, password, is_admin) 
VALUES ('admin1', 'pass1', 1);

ALTER TABLE requests ADD COLUMN isHidden TINYINT(1) DEFAULT 0;

INSERT INTO offers (username, productId, quantity, status, numberOfPeople, acceptDate, completeDate) 
VALUES 
('citizen1', 3, 8, 'finished', 3, '2024-08-30 14:00:00', '2024-09-01 10:00:00'), -- Adjust the productId as needed
('citizen1', 4, 12, 'finished', 5, '2024-08-28 09:00:00', '2024-08-29 17:00:00'); -- Adjust the productId as needed