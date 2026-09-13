<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('admin.accounts.users', $this->pageData());
    }

    public function create(): View
    {
        return view('admin.accounts.user-form', $this->formData());
    }

    public function edit(User $user): View
    {
        abort_unless($user->role === User::ROLE_SALESMAN, 404);

        return view('admin.accounts.user-form', $this->formData($user));
    }

    public function store(Request $request): RedirectResponse
    {
        try {
            User::query()->create($this->validateUser($request));
        } catch (QueryException $exception) {
            $this->throwFriendlyDuplicateEmailError($exception);
        }

        return redirect()
            ->route('admin.accounts.users')
            ->with('status', 'User saved successfully.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless($user->role === User::ROLE_SALESMAN, 404);

        try {
            $user->update($this->validateUser($request, $user));
        } catch (QueryException $exception) {
            $this->throwFriendlyDuplicateEmailError($exception);
        }

        return redirect()
            ->route('admin.accounts.users')
            ->with('status', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_unless($user->role === User::ROLE_SALESMAN, 404);

        DB::transaction(function () use ($user) {
            $user->forceFill([
                'email' => $user->deletedEmailValue(),
            ])->saveQuietly();

            $user->delete();
        });

        return redirect()
            ->route('admin.accounts.users')
            ->with('status', 'User deleted successfully.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($user?->id)
                    ->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'role' => ['required', Rule::in([User::ROLE_SALESMAN])],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:6'],
        ]);

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        return $data;
    }

    private function pageData(): array
    {
        $userQuery = User::query()
            ->where('role', User::ROLE_SALESMAN)
            ->orderBy('name');
        $summaryUsers = (clone $userQuery)->get();
        $users = $userQuery->paginate(10)->withQueryString();

        return [
            'users' => $users,
            'summary' => [
                'total' => $summaryUsers->count(),
                'salesmen' => $summaryUsers->where('role', User::ROLE_SALESMAN)->count(),
            ],
        ];
    }

    private function formData(?User $user = null): array
    {
        return [
            'editingUser' => $user,
        ];
    }

    private function throwFriendlyDuplicateEmailError(QueryException $exception): never
    {
        if (($exception->errorInfo[1] ?? null) === 1062) {
            throw ValidationException::withMessages([
                'email' => 'This email address is already in use. Please use a different email.',
            ]);
        }

        throw $exception;
    }
}
