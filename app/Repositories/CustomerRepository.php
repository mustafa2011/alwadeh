<?php

namespace App\Repositories;

use App\Core\Database;
use PDO;
use Exception;

class CustomerRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findForInvoice(int $id): array
    {
        $sql = "
        SELECT
            c.customer_name,
            c.registration_name,
            c.vat_number,
            cp.party_name,
            ca.street_name,
            ca.building_number,
            ca.city_name,
            ca.plot_identification,
            ca.postal_zone,
            ca.country_code,
            cle.registration_name AS legal_registration_name,
            cts.tax_scheme_id
        FROM customers c
        LEFT JOIN customer_party cp
            ON cp.customer_id = c.id
        LEFT JOIN customer_address ca
            ON ca.customer_id = c.id
        LEFT JOIN customer_legal_entity cle
            ON cle.customer_id = c.id
        LEFT JOIN customer_tax_scheme cts
            ON cts.customer_id = c.id
        WHERE c.id = :id
        LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id' => $id
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            throw new Exception('Customer not found.');
        }

        $registrationName =
            $row['legal_registration_name']
            ?: $row['registration_name']
            ?: $row['party_name']
            ?: $row['customer_name'];

        return [
            'registrationName' => $registrationName,
            'taxId' => $row['vat_number'],
            'address' => [
                'street' => $row['street_name'],
                'buildingNumber' => $row['building_number'],
                'subdivision' => $row['plot_identification'] ?: 'Al-Murooj',
                'city' => $row['city_name'],
                'postalZone' => $row['postal_zone'],
                'country' => $row['country_code'] ?: 'SA'
            ]
        ];
    }
}