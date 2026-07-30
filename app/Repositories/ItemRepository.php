<?php
namespace App\Repositories;
use App\Core\Database;
use PDO;
use Throwable;
class ItemRepository
{
    private PDO $db;
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    public function create(array $data): int
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO items (
                    company_id,
                    category_id,
                    unit_id,
                    tax_category_id,
                    item_code,
                    barcode,
                    item_name,
                    description,
                    item_type,
                    cost_price,
                    selling_price,
                    track_inventory,
                    status
                ) VALUES (
                    :company_id,
                    :category_id,
                    :unit_id,
                    :tax_category_id,
                    :item_code,
                    :barcode,
                    :item_name,
                    :description,
                    :item_type,
                    :cost_price,
                    :selling_price,
                    :track_inventory,
                    :status
                )'
            );
            $stmt->execute([
                ':company_id'       => $data['company_id'],
                ':category_id'      => $data['category_id'],
                ':unit_id'          => !empty($data['unit_id']) ? $data['unit_id'] : null,
                ':tax_category_id'  => $data['tax_category_id'],
                ':item_code'        => $data['item_code'],
                ':barcode'          => $data['barcode'] ?: null,
                ':item_name'        => $data['item_name'],
                ':description'      => $data['description'] ?: null,
                ':item_type'        => $data['item_type'],
                ':cost_price'       => $data['cost_price'],
                ':selling_price'    => $data['selling_price'],
                ':track_inventory'  => (int)$data['track_inventory'],
                ':status'           => (int)$data['status'],
            ]);
            $id = (int)$this->db->lastInsertId();
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    public function all(int $companyId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                i.*,
                c.category_name,
                u.unit_name,
                t.description tax_category_name,
                t.tax_percent
                FROM items i
                LEFT JOIN item_categories c
                ON c.id=i.category_id
                LEFT JOIN item_units u
                ON u.id=i.unit_id
                LEFT JOIN item_tax_categories t
                ON t.id=i.tax_category_id
                WHERE i.company_id=?
                ORDER BY i.item_name'
        );
        
        $stmt->execute([$companyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function find(int $companyId, int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT
                i.*,
                c.category_name,
                u.unit_name,
                t.description tax_category_name
                FROM items i
                LEFT JOIN item_categories c
                ON c.id=i.category_id
                LEFT JOIN item_units u
                ON u.id=i.unit_id
                LEFT JOIN item_tax_categories t
                ON t.id=i.tax_category_id
                WHERE i.company_id=?
                AND i.id=?
                LIMIT 1'
        );
        $stmt->execute([$companyId, $id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        return $item ?: null;
    }
    public function update(int $id, array $data): void
    {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'UPDATE items SET
                    category_id = :category_id,
                    unit_id = :unit_id,
                    tax_category_id = :tax_category_id,
                    item_code = :item_code,
                    barcode = :barcode,
                    item_name = :item_name,
                    description = :description,
                    item_type = :item_type,
                    cost_price = :cost_price,
                    selling_price = :selling_price,
                    track_inventory = :track_inventory,
                    status = :status
                WHERE id = :id
                AND company_id = :company_id'
            );
            $stmt->execute([
                ':id'               => $id,
                ':company_id'       => $data['company_id'],
                ':category_id'      => $data['category_id'],
                ':unit_id'          => !empty($data['unit_id']) ? $data['unit_id'] : null,
                ':tax_category_id'  => $data['tax_category_id'],
                ':item_code'        => $data['item_code'],
                ':barcode'          => $data['barcode'] ?: null,
                ':item_name'        => $data['item_name'],
                ':description'      => $data['description'] ?: null,
                ':item_type'        => $data['item_type'],
                ':cost_price'       => $data['cost_price'],
                ':selling_price'    => $data['selling_price'],
                ':track_inventory'  => (int)$data['track_inventory'],
                ':status'           => (int)$data['status'],
            ]);
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    public function delete(int $companyId,int $id):void
    {
        $check=$this->db->prepare(
            'SELECT COUNT(*)
            FROM invoice_lines
            WHERE item_id = ?'
        );
        $check->execute([$id]);
        if((int)$check->fetchColumn()>0){
            throw new \Exception('Cannot delete item because it is used in invoices.');
        }
        $stmt=$this->db->prepare(
            'DELETE FROM items
            WHERE company_id = ?
            AND id = ?'
        );
        $stmt->execute([
            $companyId,
            $id
        ]);
    }
}