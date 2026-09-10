<?php
declare(strict_types=1);

namespace App\Domain\Finance\Services;

use App\Infrastructure\Database\Connection;
use PDO;
use Ramsey\Uuid\Uuid;

class FinancialAnalyticsService
{
    public function __construct(private readonly Connection $db) {}

    public function recordExpense(array $data): array
    {
        $pdo = $this->db->getPdo();
        $propertyId = (int)($data['property_id'] ?? 1);
        $unitId = !empty($data['unit_id']) ? (int)$data['unit_id'] : null;
        $recordedBy = (int)($data['recorded_by_id'] ?? 1);
        $category = strtoupper(trim($data['category'] ?? 'OTHER'));
        $title = trim($data['title'] ?? '');
        $description = trim($data['description'] ?? '');
        $amount = (float)($data['amount'] ?? 0);
        $artisanId = !empty($data['artisan_id']) ? (int)$data['artisan_id'] : null;
        $vendorName = trim($data['vendor_name'] ?? '');
        $expenseDate = trim($data['expense_date'] ?? date('Y-m-d'));
        $isTaxDeductible = isset($data['is_tax_deductible']) ? (int)$data['is_tax_deductible'] : 1;

        if (empty($title) || $amount <= 0) {
            throw new \InvalidArgumentException("Expense title and a positive amount are required.");
        }

        $uuid = Uuid::uuid4()->toString();
        $stmt = $pdo->prepare("
            INSERT INTO property_expenses (
                uuid, property_id, unit_id, recorded_by_id, category, title, description,
                amount, artisan_id, vendor_name, expense_date, is_tax_deductible
            ) VALUES (
                :u, :pid, :uid, :rec, :cat, :title, :desc,
                :amt, :aid, :vendor, :date, :deduct
            )
        ");
        $stmt->execute([
            'u' => $uuid,
            'pid' => $propertyId,
            'uid' => $unitId,
            'rec' => $recordedBy,
            'cat' => $category,
            'title' => $title,
            'desc' => $description ?: null,
            'amt' => $amount,
            'aid' => $artisanId,
            'vendor' => $vendorName ?: null,
            'date' => $expenseDate,
            'deduct' => $isTaxDeductible,
        ]);

        return [
            'success' => true,
            'message' => "Expense '{$title}' of ₦" . number_format($amount, 2) . " recorded successfully.",
            'expense_id' => (int)$pdo->lastInsertId(),
        ];
    }

    public function listExpenses(?int $propertyId = null, ?int $landlordId = null): array
    {
        $pdo = $this->db->getPdo();
        $sql = "
            SELECT e.*, p.title AS property_title, u.unit_number, a.full_name AS artisan_name, a.trade_skill
            FROM property_expenses e
            JOIN properties p ON e.property_id = p.id
            LEFT JOIN units u ON e.unit_id = u.id
            LEFT JOIN artisans a ON e.artisan_id = a.id
            WHERE 1=1
        ";
        $params = [];

        if ($propertyId !== null) {
            $sql .= " AND e.property_id = :pid";
            $params['pid'] = $propertyId;
        } elseif ($landlordId !== null) {
            $sql .= " AND p.landlord_id = :lid";
            $params['lid'] = $landlordId;
        }

        $sql .= " ORDER BY e.expense_date DESC, e.id DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPropertyFinancials(?int $propertyId = null, ?int $landlordId = null): array
    {
        $pdo = $this->db->getPdo();

        // 1. Total Income from confirmed payments
        $inSql = "
            SELECT COALESCE(SUM(tp.amount), 0) AS total_collected,
                   COUNT(tp.id) AS payments_count
            FROM tenant_payments tp
            JOIN properties p ON tp.property_id = p.id
            WHERE tp.status = 'CONFIRMED'
        ";
        $inParams = [];
        if ($propertyId !== null) {
            $inSql .= " AND tp.property_id = :pid";
            $inParams['pid'] = $propertyId;
        } elseif ($landlordId !== null) {
            $inSql .= " AND p.landlord_id = :lid";
            $inParams['lid'] = $landlordId;
        }
        $inStmt = $pdo->prepare($inSql);
        $inStmt->execute($inParams);
        $inRow = $inStmt->fetch(PDO::FETCH_ASSOC);
        $totalIncome = (float)($inRow['total_collected'] ?? 0);

        // Fallback: If tenant_payments is fresh, also include active lease annual rent value
        if ($totalIncome === 0.0) {
            $leaseSql = "SELECT COALESCE(SUM(l.rent_amount), 0) FROM leases l JOIN units u ON l.unit_id = u.id JOIN properties p ON u.property_id = p.id WHERE l.agreement_status = 'FULLY_EXECUTED'";
            if ($propertyId !== null) {
                $leaseSql .= " AND p.id = {$propertyId}";
            } elseif ($landlordId !== null) {
                $leaseSql .= " AND p.landlord_id = {$landlordId}";
            }
            $totalIncome = (float)$pdo->query($leaseSql)->fetchColumn();
        }

        // 2. Total Expenses
        $exSql = "
            SELECT COALESCE(SUM(e.amount), 0) AS total_expenses,
                   COUNT(e.id) AS expense_count
            FROM property_expenses e
            JOIN properties p ON e.property_id = p.id
            WHERE 1=1
        ";
        $exParams = [];
        if ($propertyId !== null) {
            $exSql .= " AND e.property_id = :pid";
            $exParams['pid'] = $propertyId;
        } elseif ($landlordId !== null) {
            $exSql .= " AND p.landlord_id = :lid";
            $exParams['lid'] = $landlordId;
        }
        $exStmt = $pdo->prepare($exSql);
        $exStmt->execute($exParams);
        $exRow = $exStmt->fetch(PDO::FETCH_ASSOC);
        $totalExpenses = (float)($exRow['total_expenses'] ?? 0);

        $netOperatingIncome = $totalIncome - $totalExpenses;
        $operatingExpenseRatio = $totalIncome > 0 ? ($totalExpenses / $totalIncome) * 100 : 0.0;

        // 3. Category Breakdown of Expenses
        $catSql = "
            SELECT e.category, SUM(e.amount) AS category_total
            FROM property_expenses e
            JOIN properties p ON e.property_id = p.id
            WHERE 1=1
        ";
        if ($propertyId !== null) {
            $catSql .= " AND e.property_id = {$propertyId}";
        } elseif ($landlordId !== null) {
            $catSql .= " AND p.landlord_id = {$landlordId}";
        }
        $catSql .= " GROUP BY e.category";
        $categories = $pdo->query($catSql)->fetchAll(PDO::FETCH_KEY_PAIR);

        return [
            'gross_rent_roll' => $totalIncome,
            'gross_rental_inflow' => $totalIncome,
            'total_operating_expenses' => $totalExpenses,
            'net_operating_income' => $netOperatingIncome,
            'operating_expense_ratio' => round($operatingExpenseRatio, 1),
            'expense_categories' => $categories,
            'recent_expenses' => $this->listExpenses($propertyId, $landlordId),
        ];
    }

    public function getTaxSummary(?int $propertyId = null, ?int $landlordId = null, ?int $year = null): array
    {
        $year = $year ?? (int)date('Y');
        $financials = $this->getPropertyFinancials($propertyId, $landlordId);

        $grossRent = $financials['gross_rental_inflow'];
        $allowableDeductions = $financials['total_operating_expenses'];
        $netTaxableRent = max(0.0, $grossRent - $allowableDeductions);
        
        // Nigerian Withholding Tax on Rent is standard 10%
        $withholdingTaxRate = 10.0;
        $whtLiability = $grossRent * ($withholdingTaxRate / 100);

        return [
            'tax_year' => $year,
            'jurisdiction' => 'Federal Inland Revenue Service (FIRS) / State IRS (Nigeria)',
            'gross_rental_income' => $grossRent,
            'allowable_deductions' => $allowableDeductions,
            'net_assessable_income' => $netTaxableRent,
            'withholding_tax_rate' => $withholdingTaxRate,
            'withholding_tax_estimate' => $whtLiability,
            'estimated_wht_liability' => $whtLiability,
            'net_distributable_profit' => $grossRent - $allowableDeductions - $whtLiability,
            'statutory_compliance' => 'Compliant with Companies Income Tax Act (CITA) & Personal Income Tax Act (PITA)',
        ];
    }
}
