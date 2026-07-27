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
            SELECT *
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
                    customer_code,
                    customer_name,
                    registration_name,
                    customer_type,
                    vat_number,
                    commercial_registration_number,
                    country_code,
                    currency_code,
                    payment_terms,
                    credit_limit
                )
                VALUES
                (
                    :company_id,
                    :customer_code,
                    :customer_name,
                    :registration_name,
                    :customer_type,
                    :vat_number,
                    :commercial_registration_number,
                    :country_code,
                    :currency_code,
                    :payment_terms,
                    :credit_limit
                )
            ");
    
            $stmt->execute([
                'company_id' => $data['company_id'],
                'customer_code' => $data['customer_code'] ?: null,
                'customer_name' => $data['customer_name'],
                'registration_name' => $data['registration_name'] ?: $data['customer_name'],
                'customer_type' => $data['customer_type'] ?? 'company',
                'vat_number' => $data['vat_number'] ?: null,
                'commercial_registration_number' => $data['commercial_registration_number'] ?: null,
                'country_code' => $data['country_code'] ?: 'SA',
                'currency_code' => $data['currency_code'] ?: 'SAR',
                'payment_terms' => $data['payment_terms'] ?: null,
                'credit_limit' => $data['credit_limit'] ?: 0
            ]);
    
            $customerId = (int) $this->db->lastInsertId();
    
            $stmt = $this->db->prepare("
                INSERT INTO customer_party
                (
                    customer_id,
                    endpoint_id,
                    endpoint_scheme,
                    party_name
                )
                VALUES
                (
                    :customer_id,
                    :endpoint_id,
                    :endpoint_scheme,
                    :party_name
                )
            ");
    
            $stmt->execute([
                'customer_id' => $customerId,
                'endpoint_id' => $data['commercial_registration_number'] ?? null,
                'endpoint_scheme' => (($data['customer_type'] ?? 'company') === 'company') 
                                        ? 'CRN' 
                                        : null,
                'party_name' => $data['registration_name'] ?? $data['customer_name']
            ]);
    
            if (($data['customer_type'] ?? 'company') === 'company') {
                $stmt = $this->db->prepare("
                    INSERT INTO customer_address
                    (
                        customer_id,
                        street_name,
                        building_number,
                        city_name,
                        postal_zone,
                        country_subentity,
                        additional_number,
                        country_code
                    )
                    VALUES
                    (
                        :customer_id,
                        :street_name,
                        :building_number,
                        :city_name,
                        :postal_zone,
                        :country_subentity,
                        :additional_number,
                        :country_code
                    )
                ");
                $stmt->execute([
                    'customer_id' => $customerId,
                    'street_name' => $data['street_name'] ?? null,
                    'building_number' => $data['building_number'] ?: null,
                    'city_name' => $data['city_name'] ?: null,
                    'postal_zone' => $data['postal_zone'] ?: null,
                    'country_subentity' => $data['country_subentity'] ?: null,
                    'additional_number' => $data['additional_number'] ?: null,
                    'country_code' => $data['country_code'] ?: 'SA'
                ]);
                $stmt = $this->db->prepare("
                    INSERT INTO customer_tax_scheme
                    (
                        customer_id,
                        vat_number
                    )
                    VALUES
                    (
                        :customer_id,
                        :vat_number
                    )
                ");            
                $stmt->execute([
                    'customer_id' => $customerId,
                    'vat_number' => $data['vat_number'] ?: null
                ]);
                $stmt = $this->db->prepare("
                    INSERT INTO customer_legal_entity
                    (
                        customer_id,
                        company_id_value,
                        company_id_scheme,
                        registration_name
                    )
                    VALUES
                    (
                        :customer_id,
                        :company_id_value,
                        :company_id_scheme,
                        :registration_name
                    )
                ");    
                $stmt->execute([
                    'customer_id' => $customerId,
                    'company_id_value' => $data['commercial_registration_number'],
                    'company_id_scheme' => 'CRN',
                    'registration_name' => $data['registration_name'] ?: $data['customer_name']
                ]);
            }
            $stmt = $this->db->prepare("
                INSERT INTO customer_contact
                (
                    customer_id,
                    contact_name,
                    telephone,
                    electronic_mail
                )
                VALUES
                (
                    :customer_id,
                    :contact_name,
                    :telephone,
                    :electronic_mail
                )
            ");    
            $stmt->execute([
                'customer_id' => $customerId,
                'contact_name' => $data['contact_name'] ?: null,
                'telephone' => $data['telephone'] ?: null,
                'electronic_mail' => $data['electronic_mail'] ?: null
            ]);
    
            $this->db->commit();
    
            return $customerId;
    
        } catch (\Throwable $e) {
    
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

    public function find(int $id): array
    {
        $stmt = $this->db->prepare("
            SELECT
                c.*,
                cp.endpoint_id,
                cp.endpoint_scheme,
                cp.party_name,
                ca.street_name,
                ca.building_number,
                ca.plot_identification,
                ca.city_name,
                ca.postal_zone,
                ca.country_subentity,
                ca.additional_number,
                cc.contact_name,
                cc.telephone,
                cc.electronic_mail
            FROM customers c
            LEFT JOIN customer_party cp
                ON cp.customer_id = c.id
            LEFT JOIN customer_address ca
                ON ca.customer_id = c.id
            LEFT JOIN customer_contact cc
                ON cc.customer_id = c.id
            WHERE c.id = :id
            LIMIT 1
        ");
    
        $stmt->execute([
            'id' => $id
        ]);
    
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if (!$customer) {
            throw new Exception('Customer not found.');
        }
    
        return $customer;
    }  
    
    public function update(int $id,array $data): void
    {
        $this->db->beginTransaction();
    
        try {
    
            $stmt = $this->db->prepare("
                UPDATE customers
                SET
                    customer_code=:customer_code,
                    customer_name=:customer_name,
                    registration_name=:registration_name,
                    customer_type=:customer_type,
                    vat_number=:vat_number,
                    commercial_registration_number=:commercial_registration_number,
                    country_code=:country_code,
                    currency_code=:currency_code,
                    payment_terms=:payment_terms,
                    credit_limit=:credit_limit
                WHERE id=:id
            ");
            $stmt->execute([
                'id'=>$id,
                'customer_code'=>$data['customer_code'] ?? null,
                'customer_name'=>$data['customer_name'],
                'registration_name'=>$data['registration_name'] ?? $data['customer_name'],
                'customer_type'=>$data['customer_type'],
                'vat_number'=>$data['vat_number'] ?? null,
                'commercial_registration_number'=>$data['commercial_registration_number'] ?? null,
                'country_code'=>$data['country_code'] ?? 'SA',
                'currency_code'=>$data['currency_code'] ?? 'SAR',
                'payment_terms'=>$data['payment_terms'] ?? null,
                'credit_limit'=>$data['credit_limit'] ?? 0
            ]);
    
            $stmt=$this->db->prepare("
                UPDATE customer_party
                SET
                    endpoint_id=:endpoint_id,
                    endpoint_scheme=:endpoint_scheme,
                    party_name=:party_name
                WHERE customer_id=:customer_id
            ");
            $stmt->execute([
                'customer_id'=>$id,
                'endpoint_id'=>$data['commercial_registration_number'] ?? null,
                'endpoint_scheme'=>($data['customer_type']==='company') ? 'CRN' : null,
                'party_name'=>$data['registration_name'] ?? $data['customer_name']
            ]);
    
            $stmt=$this->db->prepare("
                UPDATE customer_contact
                SET
                    contact_name=:contact_name,
                    telephone=:telephone,
                    electronic_mail=:electronic_mail
                WHERE customer_id=:customer_id
            ");
    
            $stmt->execute([
                'customer_id'=>$id,
                'contact_name'=>$data['contact_name'] ?? null,
                'telephone'=>$data['telephone'] ?? null,
                'electronic_mail'=>$data['electronic_mail'] ?? null
            ]);
    
            if(($data['customer_type'] ?? 'company')==='company'){
    
                $stmt=$this->db->prepare("
                    UPDATE customer_address
                    SET
                        street_name=:street_name,
                        building_number=:building_number,
                        city_name=:city_name,
                        postal_zone=:postal_zone,
                        country_subentity=:country_subentity,
                        additional_number=:additional_number,
                        country_code=:country_code
                    WHERE customer_id=:customer_id
                ");
    
                $stmt->execute([
                    'customer_id'=>$id,
                    'street_name'=>$data['street_name'] ?? null,
                    'building_number'=>$data['building_number'] ?? null,
                    'city_name'=>$data['city_name'] ?? null,
                    'postal_zone'=>$data['postal_zone'] ?? null,
                    'country_subentity'=>$data['country_subentity'] ?? null,
                    'additional_number'=>$data['additional_number'] ?? null,
                    'country_code'=>$data['country_code'] ?? 'SA'
                ]);
    
                $stmt=$this->db->prepare("
                    UPDATE customer_tax_scheme
                    SET
                        vat_number=:vat_number
                    WHERE customer_id=:customer_id
                ");
    
                $stmt->execute([
                    'customer_id'=>$id,
                    'vat_number'=>$data['vat_number'] ?? null
                ]);
    
                $stmt=$this->db->prepare("
                    UPDATE customer_legal_entity
                    SET
                        company_id_value=:company_id_value,
                        company_id_scheme='CRN',
                        registration_name=:registration_name
                    WHERE customer_id=:customer_id
                ");
    
                $stmt->execute([
                    'customer_id'=>$id,
                    'company_id_value'=>$data['commercial_registration_number'] ?? null,
                    'registration_name'=>$data['registration_name'] ?? $data['customer_name']
                ]);
    
            }
    
            $this->db->commit();
    
        }catch(\Throwable $e){
    
            $this->db->rollBack();
    
            throw $e;
    
        }
    } 
    
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM invoices
            WHERE customer_id = :id
        ");
    
        $stmt->execute([
            'id' => $id
        ]);
    
        if ((int)$stmt->fetchColumn() > 0) {
            throw new Exception(
                'Cannot delete customer because invoices exist.'
            );
        }
    
        $stmt = $this->db->prepare("
            DELETE FROM customers
            WHERE id = :id
        ");
    
        $stmt->execute([
            'id' => $id
        ]);
    
        if ($stmt->rowCount() === 0) {
            throw new Exception('Customer not found.');
        }
    } 
}