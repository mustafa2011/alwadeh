<?php
namespace App\Repositories;
use App\Core\Database;
use PDO;
class AllowanceReasonRepository
{
    private PDO $db;
    public function __construct()
    {
        $this->db = Database::getConnection();
    }
    public function getAllowanceReasons(int $companyId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                code,
                name_en,
                name_ar
            FROM company_allowance_reason_codes
            WHERE company_id = ?
              AND is_active = 1
            ORDER BY name_en"
        );
        $stmt->execute([$companyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getChargeReasons(int $companyId): array
    {
        $stmt = $this->db->prepare(
            "SELECT
                code,
                name_en,
                name_ar
            FROM company_charge_reason_codes
            WHERE company_id = ?
              AND is_active = 1
            ORDER BY name_en"
        );
        $stmt->execute([$companyId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}