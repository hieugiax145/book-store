-- SQL Script to Create All Database Tables

-- Table: sach (Books)
-- Stores information about individual books
CREATE TABLE sach (
    sachId INT AUTO_INCREMENT PRIMARY KEY,
    ten VARCHAR(255) NOT NULL,
    tacGia VARCHAR(255),
    namXuatBan YEAR,
    nhaXuatBan VARCHAR(255),
    soLuong INT DEFAULT 0,
    donGia DECIMAL(10, 2) DEFAULT 0.00,
    moTa TEXT,
    image VARCHAR(255) -- Path or URL to the book's cover image
);

-- Table: nguoi_dung (Users)
-- Stores information about all users (customers, employees, admins)
CREATE TABLE nguoi_dung (
    nguoiDungId INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- IMPORTANT: Store hashed passwords, NEVER plain text!
    hoTen VARCHAR(255),
    ngaySinh DATE,
    diaChi VARCHAR(255),
    sdt VARCHAR(20), -- Phone number
    email VARCHAR(255) UNIQUE,
    vaiTro VARCHAR(50), -- e.g., 'Customer', 'Employee', 'Admin'
    moTa TEXT
);

-- Table: don_hang (Orders)
-- Stores main information about each customer order
CREATE TABLE don_hang (
    donHangId INT AUTO_INCREMENT PRIMARY KEY,
    khachHangId INT, -- ID of the customer who placed the order (can be NULL for guest orders)
    nhanVienId INT, -- ID of the employee who handled the order (if applicable)
    tenKhachHang VARCHAR(255), -- Customer name at the time of order (for historical accuracy/guest orders)
    sdt VARCHAR(20), -- Customer phone number at the time of order
    email VARCHAR(255), -- Customer email at the time of order
    tongTien DECIMAL(10, 2) DEFAULT 0.00,
    diaChiNhanHang VARCHAR(255),
    ngayBan DATE,
    trangThai VARCHAR(50), -- e.g., 'Pending', 'Completed', 'Cancelled'
    phanLoai VARCHAR(50), -- e.g., 'Online', 'In-store'
    ghiChu TEXT,
    hinhThucThanhToan VARCHAR(100), -- e.g., 'Cash', 'Credit Card', 'Bank Transfer'
    FOREIGN KEY (khachHangId) REFERENCES nguoi_dung(nguoiDungId),
    FOREIGN KEY (nhanVienId) REFERENCES nguoi_dung(nguoiDungId)
);

-- Table: chi_tiet_don_hang (Order Details)
-- Links books to specific orders, storing price and quantity for that order.
-- This is a junction table for a many-to-many relationship between orders and books.
CREATE TABLE chi_tiet_don_hang (
    chiTietDonHangId INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each detail row
    donHangId INT NOT NULL,
    sachId INT NOT NULL,
    donGia DECIMAL(10, 2) NOT NULL, -- Price of the book at the time of order (important for historical accuracy)
    soLuong INT NOT NULL,
    FOREIGN KEY (donHangId) REFERENCES don_hang(donHangId),
    FOREIGN KEY (sachId) REFERENCES sach(sachId)
);

-- Table: cart (Shopping Cart)
-- Stores items currently in a customer's shopping cart
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    khachHangId INT NOT NULL, -- ID of the customer who owns this cart item
    sachId INT NOT NULL, -- ID of the book in the cart
    soLuong INT NOT NULL DEFAULT 1,
    FOREIGN KEY (khachHangId) REFERENCES nguoi_dung(nguoiDungId),
    FOREIGN KEY (sachId) REFERENCES sach(sachId)
);