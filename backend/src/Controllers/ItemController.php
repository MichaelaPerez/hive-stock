<?php

namespace HiveStock\Controllers;

use HiveStock\Config\Database;
use HiveStock\Http\Request;
use HiveStock\Http\Response;
use PDO;

class ItemController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function index(Request $request): Response
    {
        $stmt = $this->db->query(
            'SELECT i.barcode, i.name, i.location, l.name AS location_name
             FROM items i
             INNER JOIN locations l ON l.barcode = i.location
             ORDER BY i.name, i.barcode'
        );

        return Response::json(['data' => $stmt->fetchAll()]);
    }

    public function getByBarcode(Request $request, string $barcode): Response
    {
        $item = $this->findByBarcode($barcode);

        if (!$item) {
            return Response::jsonError('Item not found', 404, 'not_found');
        }

        return Response::json(['data' => $item]);
    }

    public function getByName(Request $request, string $name): Response
    {
        $stmt = $this->db->prepare(
            'SELECT i.barcode, i.name, i.location, l.name AS location_name
             FROM items i
             INNER JOIN locations l ON l.barcode = i.location
             WHERE i.name = ?
             ORDER BY i.barcode'
        );
        $stmt->execute([urldecode($name)]);
        $items = $stmt->fetchAll();

        if (count($items) === 0) {
            return Response::jsonError('Item not found', 404, 'not_found');
        }

        if (count($items) > 1) {
            return Response::jsonError('Multiple items match that name; search by barcode instead', 409, 'ambiguous_name');
        }

        return Response::json(['data' => $items[0]]);
    }

    public function create(Request $request): Response
    {
        $payload = $request->json();
        $barcode = $this->requiredString($payload, 'barcode');
        $name = $this->requiredString($payload, 'name');
        $location = $this->requiredString($payload, 'location');

        if (!$barcode || !$name || !$location) {
            return Response::jsonError('Barcode, name, and location are required', 422, 'validation_error');
        }

        if ($this->findByBarcode($barcode)) {
            return Response::jsonError('Item barcode already exists', 409, 'duplicate_item');
        }

        if (!$this->locationExists($location)) {
            return Response::jsonError('Location does not exist', 422, 'invalid_location');
        }

        $stmt = $this->db->prepare('INSERT INTO items (barcode, name, location) VALUES (?, ?, ?)');
        $stmt->execute([$barcode, $name, $location]);

        return Response::json(['data' => $this->findByBarcode($barcode)], 201);
    }

    public function updateLocation(Request $request, string $barcode): Response
    {
        if (!$this->findByBarcode($barcode)) {
            return Response::jsonError('Item not found', 404, 'not_found');
        }

        $payload = $request->json();
        $location = $this->requiredString($payload, 'location');

        if (!$location) {
            return Response::jsonError('Location is required', 422, 'validation_error');
        }

        if (!$this->locationExists($location)) {
            return Response::jsonError('Location does not exist', 422, 'invalid_location');
        }

        $stmt = $this->db->prepare('UPDATE items SET location = ? WHERE barcode = ?');
        $stmt->execute([$location, urldecode($barcode)]);

        return Response::json(['data' => $this->findByBarcode($barcode)]);
    }

    private function findByBarcode(string $barcode): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT i.barcode, i.name, i.location, l.name AS location_name
             FROM items i
             INNER JOIN locations l ON l.barcode = i.location
             WHERE i.barcode = ?'
        );
        $stmt->execute([urldecode($barcode)]);
        $item = $stmt->fetch();

        return $item ?: null;
    }

    private function locationExists(string $barcode): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM locations WHERE barcode = ?');
        $stmt->execute([$barcode]);

        return (bool) $stmt->fetchColumn();
    }

    private function requiredString(array $payload, string $key): ?string
    {
        if (!isset($payload[$key]) || !is_string($payload[$key])) {
            return null;
        }

        $value = trim($payload[$key]);

        return $value === '' ? null : $value;
    }
}
