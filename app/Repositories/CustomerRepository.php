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

    public function all(): array
    {
        $stmt = $this->db->query("
            SELECT
                id,
                customer_name,
                vat_number,
                customer_type,
                status
            FROM customers
            ORDER BY id DESC
        ");

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(array $data): int
    {
        $this->db->beginTransaction();

        try {

            $stmt = $this->db->prepare("
                INSERT INTO customers
                (
                    company_id,
                    customer_name,
                    registration_name,
                    customer_type,
                    vat_number,
                    country_code
                )
                VALUES
                (
                    :company_id,
                    :customer_name,
                    :registration_name,
                    :customer_type,
                    :vat_number,
                    'SA'
                )
            ");

            $stmt->execute([
                'company_id' => $data['company_id'],
                'customer_name' => $data['customer_name'],
                'registration_name' => $data['registration_name'] ?? $data['customer_name'],
                'customer_type' => $data['customer_type'] ?? 'company',
                'vat_number' => $data['vat_number'] ?? null
            ]);

            $customerId = (int)$this->db->lastInsertId();


            $stmt = $this->db->prepare("
                INSERT INTO customer_party
                (
                    customer_id,
                    party_name
                )
                VALUES
                (
                    :customer_id,
                    :party_name
                )
            ");

            $stmt->execute([
                'customer_id'=>$customerId,
                'party_name'=>$data['customer_name']
            ]);


            $stmt = $this->db->prepare("
                INSERT INTO customer_address
                (
                    customer_id,
                    street_name,
                    building_number,
                    city_name,
                    postal_zone,
                    country_code
                )
                VALUES
                (
                    :customer_id,
                    :street,
                    :building,
                    :city,
                    :postal,
                    'SA'
                )
            ");

            $stmt->execute([
                'customer_id'=>$customerId,
                'street'=>$data['street'] ?? null,
                'building'=>$data['building_number'] ?? null,
                'city'=>$data['city'] ?? null,
                'postal'=>$data['postal_zone'] ?? null
            ]);


            $this->db->commit();

            return $customerId;

        } catch(\Throwable $e){

            $this->db->rollBack();
            throw $e;

        }
    }


    public function findForInvoice(int $id): array
    {
        $sql="
        SELECT
            c.customer_name,
            c.registration_name,
            cts.vat_number,
            cp.party_name,
            ca.street_name,
            ca.building_number,
            ca.city_name,
            ca.plot_identification,
            ca.postal_zone,
            ca.country_code
        FROM customers c
        LEFT JOIN customer_party cp
            ON cp.customer_id=c.id
        LEFT JOIN customer_address ca
            ON ca.customer_id=c.id
        LEFT JOIN customer_tax_scheme cts
            ON cts.customer_id = c.id
        LEFT JOIN customer_legal_entity cle
            ON cle.customer_id = c.id            
        WHERE c.id=:id
        LIMIT 1";

        $stmt=$this->db->prepare($sql);
        $stmt->execute([
            'id'=>$id
        ]);

        $row=$stmt->fetch(PDO::FETCH_ASSOC);

        if(!$row){
            throw new Exception('Customer not found.');
        }

        return [
            'registrationName' => $row['registration_name']
                ?: $row['party_name']
                ?: $row['customer_name'],            
            'taxId'=>$row['vat_number'],
            'address'=>[
                'street'=>$row['street_name'],
                'buildingNumber'=>$row['building_number'],
                'subdivision'=>$row['plot_identification'] ?: 'NA',
                'city'=>$row['city_name'],
                'postalZone'=>$row['postal_zone'],
                'country'=>$row['country_code'] ?: 'SA'
            ]
        ];
    }
}