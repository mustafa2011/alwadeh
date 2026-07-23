<?php
namespace App\Repositories;
use PDO;
class InvoiceSnapshotRepository
{
    private PDO $db;
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    public function create(int $invoiceId,array $invoice,array $invoiceData):void
    {
        $supplierId = $this->supplier($invoiceId,$invoice['supplier'] ?? []);
        $buyer = $invoice['customer'] ?? [];
    
        $customerId = $this->customer(
            $invoiceId,
            $buyer,
            (int)($invoiceData['customerId'] ?? 0)
        );
    
        $this->address($invoiceId,'supplier',$supplierId,$invoice['supplier']['address'] ?? []);
        $this->address($invoiceId,'customer',$customerId,$buyer['address'] ?? []);
    
        $this->tax($invoiceId,'supplier',$supplierId,$invoice['supplier'] ?? []);
        $this->tax($invoiceId,'customer',$customerId,$buyer);
    
        $this->legalEntity($invoiceId,'supplier',$supplierId,$invoice['supplier'] ?? []);
        $this->legalEntity($invoiceId,'customer',$customerId,$buyer);
    }
    
    private function customer(
        int $invoiceId,
        array $data,
        int $customerId
    ): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO invoice_customer_party
            (
                invoice_id,
                customer_id,
                party_name,
                party_identification_id,
                party_identification_scheme
            )
            VALUES (?,?,?,?,?)
        ");
    
        $stmt->execute([
            $invoiceId,
            $customerId,
            $data['registrationName']
                ?? $data['name']
                ?? '',
            $data['taxId']
                ?? $data['vatNumber']
                ?? null,
            'VAT'
        ]);
    
        return (int)$this->db->lastInsertId();
    }
    
    private function legalEntity(
        int $invoiceId,
        string $partyType,
        int $partyId,
        array $data
    ):void
    {
        $stmt=$this->db->prepare("
            INSERT INTO invoice_party_legal_entity
            (
                invoice_id,
                party_type,
                party_id,
                registration_name,
                company_id_value,
                company_id_scheme
            )
            VALUES (?,?,?,?,?,?)
        ");
    
        $stmt->execute([
            $invoiceId,
            $partyType,
            $partyId,
            $data['registrationName'] ?? $data['name'] ?? '',
            $data['taxId'] ?? $data['vatNumber'] ?? null,
            'VAT'
        ]);
    }
    private function supplier(int $invoiceId,array $data):int
    {
        $stmt=$this->db->prepare("
            INSERT INTO invoice_supplier_party
            (invoice_id,party_name,endpoint_id,endpoint_scheme,party_identification_id,party_identification_scheme)
            VALUES(?,?,?,?,?,?)
        ");
        $stmt->execute([
            $invoiceId,
            $data['registrationName']??'',
            $data['taxId']??null,
            'VAT',
            $data['identificationId']??null,
            $data['identificationType']??null
        ]);
        return (int)$this->db->lastInsertId();
    }
    private function address(int $invoiceId,string $type,int $partyId,array $data):void
    {
        $stmt=$this->db->prepare("
            INSERT INTO invoice_party_address
            (invoice_id,party_type,party_id,street_name,building_number,plot_identification,city_name,postal_zone,country_code)
            VALUES(?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $invoiceId,
            $type,
            $partyId,
            $data['street']??null,
            $data['buildingNumber']??null,
            $data['subdivision']??null,
            $data['city']??null,
            $data['postalZone']??$data['postalCode']??null,
            $data['country']??'SA'
        ]);
    }
    private function tax(int $invoiceId,string $type,int $partyId,array $data):void
    {
        $stmt=$this->db->prepare("
            INSERT INTO invoice_party_tax_scheme
            (invoice_id,party_type,party_id,tax_scheme_id,vat_number)
            VALUES(?,?,?,?,?)
        ");
        $stmt->execute([
            $invoiceId,
            $type,
            $partyId,
            $data['taxScheme']['id']??'VAT',
            $data['taxId']??$data['vatNumber']??null
        ]);
    }
}