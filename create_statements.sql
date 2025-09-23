-- Drop tables for safe testing
DROP TABLE IF EXISTS PromoTicket, RegularTicket, Ticket;
DROP TABLE IF EXISTS Concert, Theatre, Sports;
DROP TABLE IF EXISTS IndoorVenue, OutdoorVenue, Seat;
DROP TABLE IF EXISTS Customer, Organizer;
DROP TABLE IF EXISTS `Order`, Waitlist;
DROP TABLE IF EXISTS User, Event, Venue;

CREATE TABLE User (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('CUSTOMER', 'ORGANIZER') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status ENUM('ACTIVE', 'INACTIVE') NOT NULL
);

CREATE TABLE Venue (
    venue_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    address VARCHAR(255),
    seating_scheme TEXT
);

CREATE TABLE Seat (
    seat_id INT AUTO_INCREMENT PRIMARY KEY,
    venue_id INT NOT NULL,
    section VARCHAR(50),
    row_label VARCHAR(10),
    seat_number INT,
    seat_label VARCHAR(50) NOT NULL,
    UNIQUE (venue_id, seat_label),
    FOREIGN KEY (venue_id) REFERENCES Venue(venue_id) ON DELETE CASCADE
);

CREATE TABLE Event (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    category VARCHAR(100),
    description TEXT,
    venue_id INT NOT NULL,
    img_url VARCHAR(255),
    start_at DATETIME NOT NULL,
    status ENUM('SCHEDULED', 'ON_SALE', 'SOLD_OUT', 'CANCELLED') NOT NULL,
    FOREIGN KEY (venue_id) REFERENCES Venue(venue_id) ON DELETE CASCADE
);

CREATE TABLE Ticket (
    ticket_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    seat_id INT NOT NULL,
    price INT NOT NULL,
    status ENUM('AVAILABLE', 'HELD', 'TICKETED', 'BLOCKED') NOT NULL,
    qr_code VARCHAR(255) UNIQUE,
    order_id INT NULL,
    hold_expires_at DATETIME NULL,
    UNIQUE (event_id, seat_id),
    FOREIGN KEY (event_id) REFERENCES Event(event_id) ON DELETE CASCADE,
    FOREIGN KEY (seat_id) REFERENCES Seat(seat_id) ON DELETE CASCADE
);

CREATE TABLE `Order` (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status enum('PENDING', 'PAID', 'CANCELLED', 'REFUNDED') NOT NULL,
    total INT NOT NULL,
    payment_status ENUM('PENDING', 'SUCCESS', 'FAILED') NOT NULL,
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE
);

CREATE TABLE Waitlist (
    entry_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    seats_requested INT NOT NULL,
    status ENUM('ACTIVE', 'OFFERED', 'EXPIRED', 'FULFILLED', 'CANCELLED') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES Event(event_id) ON DELETE CASCADE
);


-- User ISA
CREATE TABLE Customer (
    user_id INT NOT NULL PRIMARY KEY,
    membership_level ENUM('REGULAR', 'VIP') NOT NULL,
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE
);

CREATE TABLE Organizer (
    user_id INT NOT NULL PRIMARY KEY,
    organization_name VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES User(user_id) ON DELETE CASCADE
);

-- Event ISA
CREATE TABLE Concert (
    event_id INT NOT NULL PRIMARY KEY,
    genre VARCHAR(100),
    FOREIGN KEY (event_id) REFERENCES Event(event_id) ON DELETE CASCADE
);

CREATE TABLE Theatre (
    event_id INT NOT NULL PRIMARY KEY,
    playwright VARCHAR(100),
    FOREIGN KEY (event_id) REFERENCES Event(event_id) ON DELETE CASCADE
);

CREATE TABLE Sports (
    event_id INT NOT NULL PRIMARY KEY,
    sport_type VARCHAR(100),
    FOREIGN KEY (event_id) REFERENCES Event(event_id) ON DELETE CASCADE
);

-- Ticket ISA
CREATE TABLE RegularTicket (
    ticket_id INT PRIMARY KEY,
    FOREIGN KEY (ticket_id) REFERENCES Ticket(ticket_id) ON DELETE CASCADE
);

CREATE TABLE PromoTicket (
    ticket_id INT PRIMARY KEY,
    promo_code VARCHAR(50) UNIQUE NOT NULL,
    discount INT NOT NULL,
    FOREIGN KEY (ticket_id) REFERENCES Ticket(ticket_id) ON DELETE CASCADE
);

-- Venue ISA
CREATE TABLE IndoorVenue (
    venue_id INT PRIMARY KEY,
    capacity INT,
    FOREIGN KEY (venue_id) REFERENCES Venue(venue_id) ON DELETE CASCADE
);

CREATE TABLE OutdoorVenue (
    venue_id INT PRIMARY KEY,
    weather_policy VARCHAR(100),
    FOREIGN KEY (venue_id) REFERENCES Venue(venue_id) ON DELETE CASCADE
);

ALTER TABLE Ticket ADD CONSTRAINT ticket_order FOREIGN KEY (order_id) REFERENCES `Order`(order_id) ON DELETE SET NULL;