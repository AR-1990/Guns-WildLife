<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Salary;
use App\Models\SalaryPaymentPortion;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalarySeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->where('email', 'admin@guns.local')->firstOrFail();
        $salesman = User::query()->where('email', 'salesman@guns.local')->firstOrFail();
        $seededMonths = ['2026-08', '2026-09'];

        $seededSalaries = Salary::withTrashed()
            ->where('employee_id', $salesman->id)
            ->whereIn('salary_month', $seededMonths)
            ->get();

        $salaryIds = $seededSalaries->pluck('id')->all();

        if ($salaryIds !== []) {
            SalaryPaymentPortion::query()->whereIn('salary_id', $salaryIds)->delete();
        }

        Expense::withTrashed()
            ->where(function ($query) use ($seededMonths) {
                foreach ($seededMonths as $month) {
                    $query->orWhere('reference', 'like', 'SALARY-%-M' . $month);
                }
            })
            ->forceDelete();

        Salary::withTrashed()
            ->where('employee_id', $salesman->id)
            ->whereIn('salary_month', $seededMonths)
            ->forceDelete();

        $this->createSalaryEntry(
            adminId: $admin->id,
            employee: $salesman,
            salaryMonth: '2026-08',
            status: 'salary',
            amount: 32000,
            paidDate: '2026-08-31',
            notes: 'Monthly salary paid from admin account.'
        );

        $this->createSalaryEntry(
            adminId: $admin->id,
            employee: $salesman,
            salaryMonth: '2026-09',
            status: 'advance',
            amount: 8000,
            paidDate: '2026-09-03',
            notes: 'Advance released from admin account.'
        );
    }

    private function createSalaryEntry(
        int $adminId,
        User $employee,
        string $salaryMonth,
        string $status,
        float $amount,
        string $paidDate,
        string $notes
    ): void {
        $salary = Salary::query()->create([
            'employee_id' => $employee->id,
            'salary_month' => $salaryMonth,
            'basic_salary' => $status === 'salary' ? $amount : 0,
            'advance_salary' => $status === 'advance' ? $amount : 0,
            'advance_adjustment_amount' => 0,
            'advance_waived_amount' => 0,
            'loan_amount' => $status === 'loan' ? $amount : 0,
            'loan_adjustment_amount' => 0,
            'loan_waived_amount' => 0,
            'bonus' => 0,
            'deduction' => 0,
            'net_salary' => $amount,
            'account_user_id' => null,
            'salary_waived_amount' => 0,
            'paid_amount' => $amount,
            'balance_amount' => 0,
            'paid_date' => $paidDate,
            'status' => $status,
            'notes' => '',
            'created_by' => $adminId,
        ]);

        SalaryPaymentPortion::query()->create([
            'salary_id' => $salary->id,
            'portion_no' => 1,
            'amount' => $amount,
            'paid_date' => $paidDate,
            'notes' => 'Paid from Admin account.',
            'created_by' => $adminId,
        ]);

        $notesPayload = [
            'user_notes' => $notes,
            'deduction_from' => 'admin',
            'entry_type' => $status,
            'employee_name' => $employee->name,
            'salary_month' => $salaryMonth,
        ];

        $expense = Expense::query()->create([
            'expense_date' => $paidDate,
            'title' => ucfirst($status) . ' Payment — ' . $employee->name . ' (' . $salaryMonth . ')',
            'category' => 'Salary',
            'amount' => $amount,
            'payment_method' => 'bank',
            'reference' => 'SALARY-' . $salary->id . '-M' . $salaryMonth,
            'account_user_id' => null,
            'notes' => json_encode($notesPayload + ['salary_id' => $salary->id]),
            'created_by' => $adminId,
        ]);

        $salary->update([
            'notes' => json_encode($notesPayload + ['expense_id' => $expense->id]),
        ]);
    }
}
