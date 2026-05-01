-- booksales.sql


-- Create database if not exists
CREATE DATABASE IF NOT EXISTS booksales;
USE booksales;

DROP TABLE IF EXISTS OrderedBooks;
DROP TABLE IF EXISTS Orders;
DROP TABLE IF EXISTS Books;
DROP TABLE IF EXISTS Genres;
DROP TABLE IF EXISTS Accounts;

-- Table 1: Accounts
CREATE TABLE Accounts (
    AccountID MEDIUMINT AUTO_INCREMENT PRIMARY KEY,
    Forename VARCHAR(30) NOT NULL,
    Surname VARCHAR(30) NOT NULL,
    Email VARCHAR(50) NOT NULL UNIQUE,
    Password VARCHAR(30) NOT NULL,
    Eircode VARCHAR(7) NOT NULL,
    AccountStatus ENUM('A', 'C') NOT NULL DEFAULT 'A'
) ENGINE=InnoDB;

-- Table 2: Genres
CREATE TABLE Genres (
    GenreCode CHAR(2) PRIMARY KEY,
    Description VARCHAR(20) NOT NULL
) ENGINE=InnoDB;

-- Table 3: Books
CREATE TABLE Books (
    BookID MEDIUMINT AUTO_INCREMENT PRIMARY KEY,
    BookTitle VARCHAR(50) NOT NULL,
    Author VARCHAR(50) NOT NULL,
    GenreCode CHAR(2) NOT NULL,
    Price DECIMAL(10,2) NOT NULL CHECK (Price > 0),
    StockAmount SMALLINT NOT NULL CHECK (StockAmount >= 0),
    BookStatus ENUM('A', 'D') NOT NULL DEFAULT 'A',
    FOREIGN KEY (GenreCode) REFERENCES Genres(GenreCode)
) ENGINE=InnoDB;

-- Table 4: Orders
CREATE TABLE Orders (
    OrderID MEDIUMINT AUTO_INCREMENT PRIMARY KEY,
    AccountID MEDIUMINT NOT NULL,
    TotalPrice DECIMAL(10,2) NOT NULL CHECK (TotalPrice >= 0),
    DateOrdered DATE NOT NULL,
    FOREIGN KEY (AccountID) REFERENCES Accounts(AccountID)
) ENGINE=InnoDB;

-- Table 5: OrderedBooks
CREATE TABLE OrderedBooks (
    OrderID MEDIUMINT NOT NULL,
    BookID MEDIUMINT NOT NULL,
    QtyOrdered SMALLINT NOT NULL CHECK (QtyOrdered > 0),
    OrderPrice DECIMAL(10,2) NOT NULL CHECK (OrderPrice >= 0),
    PRIMARY KEY (OrderID, BookID),
    FOREIGN KEY (OrderID) REFERENCES Orders(OrderID),
    FOREIGN KEY (BookID) REFERENCES Books(BookID)
) ENGINE=InnoDB;


-- Populate Genres table
INSERT INTO Genres VALUES('DE', 'Detective');
INSERT INTO Genres VALUES('SF', 'Sci-fi');
INSERT INTO Genres VALUES('HI', 'History');
INSERT INTO Genres VALUES('FA', 'Fantasy');
INSERT INTO Genres VALUES('RO', 'Romance');

-- Populate Books table
INSERT INTO Books (BookTitle, Author, GenreCode, Price, StockAmount, BookStatus) VALUES
('Running Grave', 'R. Galbraith', 'DE', 18.00, 97, 'A'),
('American Gods', 'N. Gaiman', 'FA', 12.00, 101, 'A'),
('Sapiens', 'Y. N. Harari', 'HI', 14.00, 55, 'A'),
('Project Hail Mary', 'A. Weir', 'SF', 16.00, 43, 'A'),
('Normal People', 'S. Rooney', 'RO', 11.00, 62, 'A');

-- Populate Accounts table with 4 test accounts
INSERT INTO Accounts (Forename, Surname, Email, Password, Eircode, AccountStatus) VALUES
('John', 'Doe', 'john@test.com', 'pass', 'V92AA11', 'A'),
('Jane', 'Doe', 'jane@test.com', 'pass', 'V92BB22', 'A'),
('Bob', 'Jones', 'bob@test.com', 'pass', 'V92CC33', 'A'),
('Alice', 'Brown', 'alice@test.com', 'pass', 'V92DD44', 'A');

-- Populate Orders table
INSERT INTO Orders (AccountID, TotalPrice, DateOrdered) VALUES
(1, 30.00, '2026-04-20'),
(1, 18.00, '2026-04-22'),
(2, 28.00, '2026-04-23'),
(3, 16.00, '2026-04-25');

-- Populate OrderedBooks table
INSERT INTO OrderedBooks VALUES(1, 2, 1, 12.00);
INSERT INTO OrderedBooks VALUES(1, 1, 1, 18.00);

INSERT INTO OrderedBooks VALUES(2, 1, 1, 18.00);

INSERT INTO OrderedBooks VALUES(3, 3, 2, 28.00);

INSERT INTO OrderedBooks VALUES(4, 4, 1, 16.00);