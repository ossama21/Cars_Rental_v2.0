-- Add new columns to cars table
ALTER TABLE cars 
ADD COLUMN IF NOT EXISTS engine_type VARCHAR(50) DEFAULT 'Unknown',
ADD COLUMN IF NOT EXISTS fuel_type VARCHAR(50) DEFAULT 'Unknown',
ADD COLUMN IF NOT EXISTS seating_capacity INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS mileage VARCHAR(50) DEFAULT 'Unknown',
ADD COLUMN IF NOT EXISTS features TEXT,
ADD COLUMN IF NOT EXISTS year INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS color VARCHAR(50) DEFAULT 'Unknown',
ADD COLUMN IF NOT EXISTS registration_number VARCHAR(50) DEFAULT 'Unknown',
ADD COLUMN IF NOT EXISTS vin VARCHAR(50) DEFAULT 'Unknown';

-- Create table for car images
CREATE TABLE IF NOT EXISTS car_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    car_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;