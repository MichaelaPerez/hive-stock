<?php

namespace HiveStock\Controllers;

use HiveStock\Config\Database;
use HiveStock\Http\Request;
use HiveStock\Http\Response;
use PDO;

class LocationController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function index(Request $request): Response
    {
        $stmt = $this->db->query(
            'SELECT child.barcode,
                    child.name,
                    child.parent_location,
                    parent.name AS parent_location_name
             FROM locations child
             LEFT JOIN locations parent ON parent.barcode = child.parent_location
             ORDER BY child.name, child.barcode'
        );

        return Response::json(['data' => $stmt->fetchAll()]);
    }

    public function getByBarcode(Request $request, string $barcode): Response
    {
        $location = $this->findByBarcode($barcode);

        if (!$location) {
            return Response::jsonError('Location not found', 404, 'not_found');
        }

        return Response::json(['data' => $location]);
    }

    public function getByName(Request $request, string $name): Response
    {
        $stmt = $this->db->prepare(
            'SELECT child.barcode,
                    child.name,
                    child.parent_location,
                    parent.name AS parent_location_name
             FROM locations child
             LEFT JOIN locations parent ON parent.barcode = child.parent_location
             WHERE child.name = ?
             ORDER BY child.barcode'
        );
        $stmt->execute([urldecode($name)]);
        $locations = $stmt->fetchAll();

        if (count($locations) === 0) {
            return Response::jsonError('Location not found', 404, 'not_found');
        }

        if (count($locations) > 1) {
            return Response::jsonError('Multiple locations match that name; search by barcode instead', 409, 'ambiguous_name');
        }

        return Response::json(['data' => $locations[0]]);
    }

    public function items(Request $request, string $barcode): Response
    {
        if (!$this->findByBarcode($barcode)) {
            return Response::jsonError('Location not found', 404, 'not_found');
        }

        $stmt = $this->db->prepare(
            'SELECT i.barcode, i.name, i.location, l.name AS location_name
             FROM items i
             INNER JOIN locations l ON l.barcode = i.location
             WHERE i.location = ?
             ORDER BY i.name, i.barcode'
        );
        $stmt->execute([urldecode($barcode)]);

        return Response::json(['data' => $stmt->fetchAll()]);
    }

    public function create(Request $request): Response
    {
        $payload = $request->json();
        $barcode = $this->requiredString($payload, 'barcode');
        $name = $this->requiredString($payload, 'name');
        $parent = $this->nullableString($payload, 'parent_location');

        if (!$barcode || !$name) {
            return Response::jsonError('Barcode and name are required', 422, 'validation_error');
        }

        if ($this->findByBarcode($barcode)) {
            return Response::jsonError('Location barcode already exists', 409, 'duplicate_location');
        }

        $cycleError = $this->validateParent($barcode, $parent);
        if ($cycleError) {
            return $cycleError;
        }

        $stmt = $this->db->prepare('INSERT INTO locations (barcode, name, parent_location) VALUES (?, ?, ?)');
        $stmt->execute([$barcode, $name, $parent]);

        return Response::json(['data' => $this->findByBarcode($barcode)], 201);
    }

    public function updateParent(Request $request, string $barcode): Response
    {
        $barcode = urldecode($barcode);

        if (!$this->findByBarcode($barcode)) {
            return Response::jsonError('Location not found', 404, 'not_found');
        }

        $payload = $request->json();
        if (!array_key_exists('parent_location', $payload)) {
            return Response::jsonError('Parent location is required; use null for no parent', 422, 'validation_error');
        }

        $parent = $this->nullableString($payload, 'parent_location');
        $cycleError = $this->validateParent($barcode, $parent);
        if ($cycleError) {
            return $cycleError;
        }

        $stmt = $this->db->prepare('UPDATE locations SET parent_location = ? WHERE barcode = ?');
        $stmt->execute([$parent, $barcode]);

        return Response::json(['data' => $this->findByBarcode($barcode)]);
    }

    private function validateParent(string $barcode, ?string $parent): ?Response
    {
        if ($parent === null) {
            return null;
        }

        if ($parent === $barcode) {
            return Response::jsonError('A location cannot be its own parent', 400, 'location_cycle');
        }

        if (!$this->findByBarcode($parent)) {
            return Response::jsonError('Parent location does not exist', 422, 'invalid_parent_location');
        }

        $ancestor = $parent;
        while ($ancestor !== null) {
            if ($ancestor === $barcode) {
                return Response::jsonError('Would create a cycle in the location hierarchy', 400, 'location_cycle');
            }

            $ancestor = $this->parentOf($ancestor);
        }

        return null;
    }

    private function parentOf(string $barcode): ?string
    {
        $stmt = $this->db->prepare('SELECT parent_location FROM locations WHERE barcode = ?');
        $stmt->execute([$barcode]);
        $parent = $stmt->fetchColumn();

        return $parent === false ? null : $parent;
    }

    private function findByBarcode(string $barcode): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT child.barcode,
                    child.name,
                    child.parent_location,
                    parent.name AS parent_location_name
             FROM locations child
             LEFT JOIN locations parent ON parent.barcode = child.parent_location
             WHERE child.barcode = ?'
        );
        $stmt->execute([urldecode($barcode)]);
        $location = $stmt->fetch();

        return $location ?: null;
    }

    private function requiredString(array $payload, string $key): ?string
    {
        if (!isset($payload[$key]) || !is_string($payload[$key])) {
            return null;
        }

        $value = trim($payload[$key]);

        return $value === '' ? null : $value;
    }

    private function nullableString(array $payload, string $key): ?string
    {
        if (!array_key_exists($key, $payload) || $payload[$key] === null) {
            return null;
        }

        if (!is_string($payload[$key])) {
            return null;
        }

        $value = trim($payload[$key]);

        return $value === '' ? null : $value;
    }
}
?>