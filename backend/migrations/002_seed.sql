INSERT INTO locations (barcode, name, parent_location) VALUES
    ('LOC-HOME', 'Home', NULL);

INSERT INTO locations (barcode, name, parent_location) VALUES
    ('LOC-KITCHEN', 'Kitchen', 'LOC-HOME'),
    ('LOC-GARAGE', 'Garage', 'LOC-HOME');

INSERT INTO locations (barcode, name, parent_location) VALUES
    ('LOC-PANTRY', 'Pantry', 'LOC-KITCHEN'),
    ('LOC-SHELF-A', 'Shelf A', 'LOC-GARAGE');

INSERT INTO items (barcode, name, location) VALUES
    ('ITEM-FLASHLIGHT', 'Flashlight', 'LOC-SHELF-A'),
    ('ITEM-RICE', 'Rice', 'LOC-PANTRY'),
    ('ITEM-HDMI-CABLE', 'HDMI Cable', 'LOC-SHELF-A');
