DROP DATABASE IF EXISTS ssd_db;
CREATE DATABASE ssd_db;
USE ssd_db;

CREATE TABLE Users(
    UserId int NOT NULL AUTO_INCREMENT,
    Email varchar(132) NOT NULL UNIQUE,
    Password varchar(255) NOT NULL,
    PRIMARY KEY (UserId)
);