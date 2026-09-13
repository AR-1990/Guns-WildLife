<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminExpenseController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.accounts.expenses', $this->pageData($request));
    }

    public function categories(Request $request): View
    {
        return view('admin.accounts.expense-categories', $this->categoryPageData($request));
    }

    public function create(Request $request): View
    {
        return view('admin.accounts.expense-form', $this->formData($request->user()));
    }

    public function edit(Request $request, Expense $expense): View
    {
        $this->authorizeExpenseAccess($request->user(), $expense);

        return view('admin.accounts.expense-form', $this->formData($request->user(), $expense));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateExpense($request);

        DB::transaction(function () use ($data, $request) {
            $this->persistExpense($data, null, $request);
        });

        return redirect()
            ->route('admin.accounts.expenses')
            ->with('status', 'Expense saved successfully. Rs. ' . number_format((float) $data['amount'], 2) . ' recorded in company expenses.');
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorizeExpenseAccess($request->user(), $expense);

        $data = $this->validateExpense($request, $expense);
        DB::transaction(function () use ($data, $request, $expense) {
            $this->rollbackSingleExpense($expense);
            $this->persistExpense($data, $expense, $request);
        });

        return redirect()
            ->route('admin.accounts.expenses')
            ->with('status', 'Expense updated successfully. Rs. ' . number_format((float) $data['amount'], 2) . ' recorded in company expenses.');
    }

    public function destroy(Request $request, Expense $expense): RedirectResponse
    {
        $this->authorizeExpenseAccess($request->user(), $expense);

        DB::transaction(function () use ($expense) {
            $this->rollbackSingleExpense($expense);
            $expense->delete();
        });

        return redirect()
            ->route('admin.accounts.expenses')
            ->with('status', 'Expense removed successfully.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $user = $request->user();
        $categoryFilters = $this->filterCategoryNames();
        $filters = $this->validatedListFilters($request, $user, $categoryFilters);

        $expenses = $this->filteredExpenseQuery($user, $filters)
            ->get();

        return response()->streamDownload(function () use ($expenses) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Date',
                'Title',
                'Category',
                'Payment Method',
                'Amount',
                'Reference',
                'Added By',
                'Notes',
            ]);

            foreach ($expenses as $expense) {
                $notesRaw = json_decode($expense->notes ?? '', true);
                $notes = is_array($notesRaw) ? ($notesRaw['user_notes'] ?? '') : (string) $expense->notes;

                fputcsv($handle, [
                    $expense->expense_date?->format('Y-m-d'),
                    $expense->title,
                    $expense->category,
                    Str::title((string) $expense->payment_method),
                    round((float) $expense->amount, 2),
                    $expense->reference,
                    $expense->creator?->name ?: 'Admin',
                    $notes,
                ]);
            }

            fclose($handle);
        }, 'expenses.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('expense_categories', 'name')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
        ]);

        ExpenseCategory::query()->create([
            'name' => trim((string) $data['name']),
            'slug' => $this->makeUniqueExpenseCategorySlug($data['name']),
            'is_active' => true,
        ]);

        return redirect()
            ->route('admin.accounts.expense-categories.index')
            ->with('status', 'Expense category added successfully.');
    }

    public function updateCategory(Request $request, ExpenseCategory $expenseCategory): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('expense_categories', 'name')
                    ->ignore($expenseCategory->id)
                    ->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
        ]);

        $oldName = $expenseCategory->name;
        $newName = trim((string) $data['name']);

        DB::transaction(function () use ($expenseCategory, $oldName, $newName) {
            $expenseCategory->update([
                'name' => $newName,
                'slug' => $this->makeUniqueExpenseCategorySlug($newName, $expenseCategory->id),
            ]);

            if ($oldName !== $newName) {
                Expense::query()
                    ->where('category', $oldName)
                    ->update(['category' => $newName]);
            }
        });

        return redirect()
            ->route('admin.accounts.expense-categories.index')
            ->with('status', 'Expense category updated successfully.');
    }

    public function toggleCategory(ExpenseCategory $expenseCategory): RedirectResponse
    {
        $expenseCategory->update([
            'is_active' => ! $expenseCategory->is_active,
        ]);

        return redirect()
            ->route('admin.accounts.expense-categories.index')
            ->with('status', $expenseCategory->is_active ? 'Expense category activated successfully.' : 'Expense category deactivated successfully.');
    }

    private function validateExpense(Request $request, ?Expense $expense = null): array
    {
        $allowedCategories = $this->allowedExpenseCategoryNames($expense?->category);

        return $request->validate([
            'expense_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255', Rule::in($allowedCategories)],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,bank,card,online'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes_user' => ['nullable', 'string'],
        ]);
    }

    private function baseExpenseQuery(User $user): Builder
    {
        return Expense::query()
            ->with(['creator'])
            ->when($user->isSalesman(), fn (Builder $query) => $query->where('created_by', $user->id));
    }

    private function aggregateExpenseSummary(Builder $expenseQuery): object
    {
        $summaryQuery = clone $expenseQuery;
        $summaryRaw = (array) $summaryQuery
            ->selectRaw('
                COUNT(*) AS record_count,
                COALESCE(SUM(amount), 0) AS total_amount,
                COALESCE(SUM(CASE WHEN payment_method = ? THEN amount ELSE 0 END), 0) AS cash_amount,
                COALESCE(SUM(CASE WHEN payment_method = ? THEN amount ELSE 0 END), 0) AS bank_amount
            ', ['cash', 'bank'])
            ->first()
            ?->toArray();

        return (object) [
            'records' => (int) ($summaryRaw['record_count'] ?? 0),
            'total' => (float) ($summaryRaw['total_amount'] ?? 0),
            'cash' => (float) ($summaryRaw['cash_amount'] ?? 0),
            'bank' => (float) ($summaryRaw['bank_amount'] ?? 0),
        ];
    }

    private function pageData(Request $request): array
    {
        $user = $request->user();
        $categoryFilters = $this->filterCategoryNames();
        $filters = $this->validatedListFilters($request, $user, $categoryFilters);

        $expenseQuery = $this->filteredExpenseQuery($user, $filters);

        $summaryExpenses = $this->aggregateExpenseSummary($expenseQuery);
        $expenses = $expenseQuery->paginate(10)->withQueryString();

        $staffList = $user->isAdmin()
            ? User::query()
                ->whereIn('role', [User::ROLE_ADMIN, User::ROLE_SALESMAN])
                ->orderBy('name')
                ->get(['id', 'name', 'role'])
            : collect();

        return [
            'expenses' => $expenses,
            'summary' => [
                'total' => $summaryExpenses->total,
                'cash' => $summaryExpenses->cash,
                'bank' => $summaryExpenses->bank,
                'records' => $summaryExpenses->records,
            ],
            'filters' => [
                'from_date' => $filters['from_date'] ?? '',
                'to_date' => $filters['to_date'] ?? '',
                'title' => $filters['title'] ?? '',
                'reference' => $filters['reference'] ?? '',
                'category' => $filters['category'] ?? '',
                'payment_method' => $filters['payment_method'] ?? '',
                'created_by' => $user->isAdmin() && isset($filters['created_by']) ? (int) $filters['created_by'] : '',
            ],
            'staffList' => $staffList,
            'categories' => $categoryFilters,
            'canFilterByCreator' => $user->isAdmin(),
            'canManageCategories' => $user->isAdmin(),
        ];
    }

    private function validatedListFilters(Request $request, User $user, array $categoryFilters): array
    {
        return $request->validate([
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'title' => ['nullable', 'string', 'max:250'],
            'reference' => ['nullable', 'string', 'max:250'],
            'category' => ['nullable', 'string', 'max:255', Rule::in($categoryFilters)],
            'payment_method' => ['nullable', 'in:cash,bank,card,online'],
            'created_by' => $user->isAdmin()
                ? ['nullable', 'integer', 'exists:users,id']
                : ['nullable'],
        ]);
    }

    private function filteredExpenseQuery(User $user, array $filters): Builder
    {
        return $this->baseExpenseQuery($user)
            ->when(filled($filters['from_date'] ?? null), function ($query) use ($filters) {
                $query->whereDate('expense_date', '>=', $filters['from_date']);
            })
            ->when(filled($filters['to_date'] ?? null), function ($query) use ($filters) {
                $query->whereDate('expense_date', '<=', $filters['to_date']);
            })
            ->when(filled($filters['title'] ?? null), function ($query) use ($filters) {
                $query->where('title', 'like', '%' . $filters['title'] . '%');
            })
            ->when(filled($filters['reference'] ?? null), function ($query) use ($filters) {
                $query->where('reference', 'like', '%' . $filters['reference'] . '%');
            })
            ->when(filled($filters['category'] ?? null), function ($query) use ($filters) {
                $query->where('category', $filters['category']);
            })
            ->when(filled($filters['payment_method'] ?? null), function ($query) use ($filters) {
                $query->where('payment_method', $filters['payment_method']);
            })
            ->when($user->isAdmin() && filled($filters['created_by'] ?? null), function ($query) use ($filters) {
                $query->where('created_by', (int) $filters['created_by']);
            })
            ->latest('expense_date')
            ->latest('id');
    }

    private function categoryPageData(Request $request): array
    {
        $filters = $request->validate([
            'expense_category' => ['nullable', 'integer', 'exists:expense_categories,id'],
        ]);

        $editingCategory = filled($filters['expense_category'] ?? null)
            ? ExpenseCategory::query()->find((int) $filters['expense_category'])
            : null;

        return [
            'expenseCategories' => ExpenseCategory::query()
                ->withCount('expenses')
                ->orderByDesc('is_active')
                ->orderBy('name')
                ->get(),
            'editingCategory' => $editingCategory,
        ];
    }

    private function formData(User $user, ?Expense $expense = null): array
    {
        $editing = null;
        if ($expense) {
            $editing = $expense;
            $notesRaw = json_decode($editing->notes ?? '', true);
            $editing->notes_user = is_array($notesRaw) ? ($notesRaw['user_notes'] ?? '') : '';
        }

        return [
            'categories' => $this->allowedExpenseCategoryNames($expense?->category),
            'editingExpense' => $editing,
            'isAdmin' => $user->isAdmin(),
        ];
    }

    private function persistExpense(array $data, ?Expense $originalExpense, Request $request): void
    {
        $notesPayload = [
            'user_notes' => trim((string) ($data['notes_user'] ?? '')),
            'applied_at' => now()->toDateTimeString(),
            'deduction_from' => 'company',
        ];

        $expensePayload = [
            'expense_date' => $data['expense_date'],
            'title' => trim((string) ($data['title'] ?? 'Expense')),
            'category' => $data['category'] ?? null,
            'amount' => (float) $data['amount'],
            'payment_method' => $data['payment_method'] ?? 'cash',
            'reference' => $data['reference'] ?? null,
            'account_user_id' => null,
            'notes' => json_encode($notesPayload),
            'created_by' => $originalExpense?->created_by ?? $request->user()->id,
        ];

        if ($originalExpense) {
            $originalExpense->update($expensePayload);
        } else {
            Expense::query()->create($expensePayload);
        }
    }

    private function rollbackSingleExpense(Expense $expense): void
    {
        $notes = json_decode($expense->notes ?? '', true);
        $isMulti = is_array($notes) && isset($notes['per_partner_share_map']) && is_array($notes['per_partner_share_map']);

        if ($isMulti) {
            $createdIds = array_values(array_filter(array_map('intval', $notes['created_expense_ids'] ?? [])));
            if ($createdIds !== []) {
                Expense::query()->whereKey($createdIds)->where('id', '<>', (int) $expense->id)->delete();
            }
        }
    }

    private function allowedExpenseCategoryNames(?string $currentCategory = null): array
    {
        return ExpenseCategory::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name')
            ->when(filled($currentCategory), fn ($categories) => $categories->push($currentCategory))
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique(fn ($name) => Str::lower($name))
            ->values()
            ->all();
    }

    private function filterCategoryNames(): array
    {
        return ExpenseCategory::query()
            ->withTrashed()
            ->orderBy('name')
            ->pluck('name')
            ->merge(
                Expense::query()
                    ->select('category')
                    ->whereNotNull('category')
                    ->where('category', '!=', '')
                    ->distinct()
                    ->orderBy('category')
                    ->pluck('category')
            )
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique(fn ($name) => Str::lower($name))
            ->values()
            ->all();
    }

    private function makeUniqueExpenseCategorySlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name) ?: 'expense-category';
        $slug = $baseSlug;
        $counter = 2;

        while (
            ExpenseCategory::query()
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private function authorizeExpenseAccess(User $user, Expense $expense): void
    {
        abort_unless(
            $user->isAdmin() || (int) $expense->created_by === (int) $user->id,
            403
        );
    }
}
