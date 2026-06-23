CREATE TABLE locations (
    barcode VARCHAR(64) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    parent_location VARCHAR(64) NULL,
    CONSTRAINT fk_locations_parent
        FOREIGN KEY (parent_location) REFERENCES locations(barcode)
        ON DELETE SET NULL
);

CREATE TABLE items (
    barcode VARCHAR(64) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    location VARCHAR(64) NOT NULL,
    CONSTRAINT fk_items_location
        FOREIGN KEY (location) REFERENCES locations(barcode)
        ON DELETE RESTRICT
);

CREATE INDEX idx_locations_name ON locations(name);
CREATE INDEX idx_items_name ON items(name);
CREATE INDEX idx_items_location ON items(location);
