# Hive Stock Home Inventory

Hive Stock is a simple home inventory system with a MySQL database, a PHP REST API, and a SvelteKit frontend.

## Data Model

The app uses two tables:

- `locations`: barcode primary key, name, and optional `parent_location` pointing to another location barcode.
- `items`: barcode primary key, name, and `location` pointing to a location barcode.

## Backend Setup

1. Create a MySQL database.

```bash
mysql -u root -p -e "CREATE DATABASE hive_stock CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

2. Create and/or update environment credentials. Your file should look something like this:

```bash
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=hive_stock
DB_USERNAME=root
DB_PASSWORD=secret
CORS_ORIGIN=http://localhost:5173
```

3. Run migrations.
If PHP is not already installed, I suggest installing it using chocolatey. Go where it's installed (i.e. 'C:\tools\php85'), and find the php.ini file. Uncomment 'extension=pdo_mysql'. Then run the migrations.

```bash
php backend/scripts/migrate.php
```

4. Start the PHP API server.

```bash
php -S localhost:8080 -t backend/public
```

The API base path is `http://localhost:8080/api/v1`.

## Frontend Setup

1. Install frontend dependencies.

```bash
cd frontend
npm install
```

2. Start SvelteKit.

```bash
npm run dev
```

Open `http://localhost:5173`. During local development, Vite proxies `/api` requests to `http://localhost:8080`.

## API Endpoints

| Method | Endpoint | Purpose |
| ------ | -------- | ------- |
| `GET` | `/api/v1/health` | Check API and database connectivity |
| `GET` | `/api/v1/items` | List all items |
| `GET` | `/api/v1/items/barcode/{barcode}` | Get one item by barcode |
| `GET` | `/api/v1/items/name/{name}` | Get one item by exact name |
| `POST` | `/api/v1/items` | Create an item |
| `PATCH` | `/api/v1/items/{barcode}` | Update an item's location |
| `GET` | `/api/v1/locations` | List all locations |
| `GET` | `/api/v1/locations/barcode/{barcode}` | Get one location by barcode |
| `GET` | `/api/v1/locations/name/{name}` | Get one location by exact name |
| `GET` | `/api/v1/locations/{barcode}/items` | List items in a location |
| `POST` | `/api/v1/locations` | Create a location |
| `PATCH` | `/api/v1/locations/{barcode}` | Update a location's parent |

## Quick Dev Start-Up
In different command line terminals...

1. Start the PHP API server.

```bash
php -S localhost:8080 -t backend/public
```

2. Start SvelteKit.

```bash
cd frontend
npm run dev
```

## Validation

- Item creation rejects duplicate item barcodes.
- Item creation and item location updates require an existing location barcode.
- Location creation rejects duplicate location barcodes.
- Location creation and parent updates reject missing parent locations and parent loops.
