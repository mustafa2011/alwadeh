<?php

namespace App\Validators;

use App\Repositories\ItemCategoryRepository;
use App\Repositories\ItemTaxCategoryRepository;
use App\Repositories\ItemUnitRepository;
use Exception;

class ItemValidator
{
    private ItemCategoryRepository $categoryRepository;
    private ItemUnitRepository $unitRepository;
    private ItemTaxCategoryRepository $taxCategoryRepository;

    public function __construct()
    {
        $this->categoryRepository = new ItemCategoryRepository();
        $this->unitRepository = new ItemUnitRepository();
        $this->taxCategoryRepository = new ItemTaxCategoryRepository();
    }

    public function validate(array $data): void
    {
        if (empty($data['item_code'])) {
            throw new Exception('Item code is required.');
        }

        if (empty($data['item_name'])) {
            throw new Exception('Item name is required.');
        }

        if (empty($data['item_type'])) {
            throw new Exception('Item type is required.');
        }

        if (($data['item_type'] ?? '') !== 'product'
            && ($data['item_type'] ?? '') !== 'service') {
            throw new Exception('Invalid item type.');
        }
        
        if (empty($data['category_id'])) {
            throw new Exception('Category is required.');
        }

        if (($data['item_type'] ?? '') === 'product') {

            if (empty($data['unit_id'])) {
                throw new Exception('Unit is required.');
            }

            if (!isset($data['track_inventory'])) {
                throw new Exception('Inventory option is required.');
            }

        }

        if (empty($data['tax_category_id'])) {
            throw new Exception('Tax category is required.');
        }

        if (!isset($data['cost_price']) || !is_numeric($data['cost_price'])) {
            throw new Exception('Invalid cost price.');
        }

        if ((float)$data['cost_price'] < 0) {
            throw new Exception('Cost price cannot be negative.');
        }

        if (!isset($data['selling_price']) || !is_numeric($data['selling_price'])) {
            throw new Exception('Invalid selling price.');
        }

        if ((float)$data['selling_price'] < 0) {
            throw new Exception('Selling price cannot be negative.');
        }

        $companyId = (int)($data['company_id'] ?? 0);

        if (!$companyId) {
            throw new Exception('Company is required.');
        }

        if (!$this->categoryRepository->find(
            $companyId,
            (int)$data['category_id']
        )) {
            throw new Exception('Invalid category.');
        }

        if (($data['item_type'] ?? '') === 'product') {

            if (($data['item_type'] ?? '') === 'product') {
                if (!$this->unitRepository->find(
                    $companyId,
                    (int)$data['unit_id']
                )) {
                    throw new Exception('Invalid unit.');
                }
            }

        }

        if (!$this->taxCategoryRepository->find(
            $companyId,
            (int)$data['tax_category_id']
        )) {
            throw new Exception('Invalid tax category.');
        }
    }
}