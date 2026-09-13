<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseFlowFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_salesman_only_sees_own_expenses_and_can_create_expense(): void
    {
        $salesman = User::factory()->create([
            'role' => User::ROLE_SALESMAN,
            'name' => 'Salesman One',
        ]);
        $otherSalesman = User::factory()->create([
            'role' => User::ROLE_SALESMAN,
            'name' => 'Salesman Two',
        ]);
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'name' => 'Main Admin',
        ]);

        Expense::query()->create($this->expensePayload('My Fuel Expense', $salesman->id));
        Expense::query()->create($this->expensePayload('Other Travel Expense', $otherSalesman->id));
        Expense::query()->create($this->expensePayload('Admin Rent Expense', $admin->id));

        $response = $this->actingAs($salesman)->get(route('admin.accounts.expenses'));

        $response->assertOk();
        $response->assertSeeText('My Fuel Expense');
        $response->assertDontSeeText('Other Travel Expense');
        $response->assertDontSeeText('Admin Rent Expense');

        $this->actingAs($salesman)
            ->post(route('admin.accounts.expenses.store'), [
                'expense_date' => '2026-09-09',
                'title' => 'Salesman Lunch Expense',
                'category' => $this->categoryName(),
                'amount' => 250,
                'payment_method' => 'cash',
                'reference' => 'EXP-SLS-001',
                'notes_user' => 'Created by salesman',
            ])
            ->assertRedirect(route('admin.accounts.expenses'));

        $this->assertDatabaseHas('expenses', [
            'title' => 'Salesman Lunch Expense',
            'created_by' => $salesman->id,
            'category' => $this->categoryName(),
        ]);
    }

    public function test_admin_can_see_all_expenses_and_manage_categories(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'name' => 'Admin User',
        ]);
        $salesman = User::factory()->create([
            'role' => User::ROLE_SALESMAN,
            'name' => 'Expense Salesman',
        ]);

        Expense::query()->create($this->expensePayload('Admin Office Expense', $admin->id));
        Expense::query()->create($this->expensePayload('Salesman Fuel Expense', $salesman->id));

        $response = $this->actingAs($admin)->get(route('admin.accounts.expenses'));

        $response->assertOk();
        $response->assertSeeText('Admin Office Expense');
        $response->assertSeeText('Salesman Fuel Expense');

        $this->actingAs($admin)
            ->post(route('admin.accounts.expense-categories.store'), [
                'name' => 'Tea Refreshment',
            ])
            ->assertRedirect(route('admin.accounts.expense-categories.index'));

        $this->assertDatabaseHas('expense_categories', [
            'name' => 'Tea Refreshment',
            'is_active' => true,
        ]);
    }

    public function test_salesman_cannot_edit_another_users_expense(): void
    {
        $owner = User::factory()->create([
            'role' => User::ROLE_SALESMAN,
        ]);
        $viewer = User::factory()->create([
            'role' => User::ROLE_SALESMAN,
        ]);

        $expense = Expense::query()->create($this->expensePayload('Owner Expense', $owner->id));

        $this->actingAs($viewer)
            ->get(route('admin.accounts.expenses.edit', $expense))
            ->assertForbidden();
    }

    public function test_expense_csv_export_respects_filters_and_default_scope(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'name' => 'CSV Admin',
        ]);

        Expense::query()->create([
            ...$this->expensePayload('September Expense', $admin->id),
            'expense_date' => '2026-09-10',
            'reference' => 'SEP-001',
        ]);

        Expense::query()->create([
            ...$this->expensePayload('August Expense', $admin->id),
            'expense_date' => '2026-08-10',
            'reference' => 'AUG-001',
        ]);

        $defaultExport = $this->actingAs($admin)
            ->get(route('admin.accounts.expenses.csv'));

        $defaultExport->assertOk();
        $defaultExport->assertDownload('expenses.csv');
        $defaultExportContent = $defaultExport->streamedContent();
        $this->assertStringContainsString('September Expense', $defaultExportContent);
        $this->assertStringContainsString('August Expense', $defaultExportContent);

        $filteredExport = $this->actingAs($admin)
            ->get(route('admin.accounts.expenses.csv', [
                'from_date' => '2026-09-01',
                'to_date' => '2026-09-30',
            ]));

        $filteredExport->assertOk();
        $filteredExportContent = $filteredExport->streamedContent();
        $this->assertStringContainsString('September Expense', $filteredExportContent);
        $this->assertStringNotContainsString('August Expense', $filteredExportContent);
    }

    private function categoryName(): string
    {
        return ExpenseCategory::query()->first()?->name
            ?? ExpenseCategory::query()->create([
                'name' => 'Testing Expense Category',
                'slug' => 'testing-expense-category',
                'is_active' => true,
            ])->name;
    }

    private function expensePayload(string $title, int $createdBy): array
    {
        return [
            'expense_date' => '2026-09-09',
            'title' => $title,
            'category' => $this->categoryName(),
            'amount' => 500,
            'account_user_id' => null,
            'payment_method' => 'cash',
            'reference' => 'REF-' . $createdBy . '-' . str($title)->slug(),
            'notes' => json_encode([
                'user_notes' => $title,
                'deduction_from' => 'company',
            ]),
            'created_by' => $createdBy,
        ];
    }
}
