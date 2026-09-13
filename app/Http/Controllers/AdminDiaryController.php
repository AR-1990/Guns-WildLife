<?php

namespace App\Http\Controllers;

use App\Models\DiaryContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminDiaryController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'source' => ['nullable', 'in:manual,sale'],
        ]);

        $query = DiaryContact::query()
            ->with(['creator', 'sale'])
            ->when(filled($filters['search'] ?? null), function ($builder) use ($filters) {
                $search = trim((string) $filters['search']);

                $builder->where(function ($inner) use ($search) {
                    $inner->where('full_name', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('address', 'like', '%' . $search . '%')
                        ->orWhere('notes', 'like', '%' . $search . '%');
                });
            })
            ->when(filled($filters['source'] ?? null), fn ($builder) => $builder->where('source', $filters['source']))
            ->latest();

        $summaryContacts = (clone $query)->get();
        $contacts = $query->paginate(10)->withQueryString();

        return view('admin.diary.index', [
            'contacts' => $contacts,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'source' => $filters['source'] ?? '',
            ],
            'summary' => [
                'total' => $summaryContacts->count(),
                'manual' => $summaryContacts->where('source', 'manual')->count(),
                'sale' => $summaryContacts->where('source', 'sale')->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.diary.form', [
            'editingContact' => null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedPayload($request);
        $data['created_by'] = $request->user()->id;
        $data['source'] = 'manual';

        DiaryContact::query()->create($data);

        return redirect()
            ->route('admin.diary.index')
            ->with('status', 'Diary contact added successfully.');
    }

    public function edit(DiaryContact $diaryContact, Request $request): View
    {
        $this->ensureCanManage($diaryContact, $request);

        return view('admin.diary.form', [
            'editingContact' => $diaryContact,
        ]);
    }

    public function update(Request $request, DiaryContact $diaryContact): RedirectResponse
    {
        $this->ensureCanManage($diaryContact, $request);

        $diaryContact->update($this->validatedPayload($request));

        return redirect()
            ->route('admin.diary.index')
            ->with('status', 'Diary contact updated successfully.');
    }

    public function destroy(DiaryContact $diaryContact, Request $request): RedirectResponse
    {
        $this->ensureCanManage($diaryContact, $request);

        $diaryContact->delete();

        return redirect()
            ->route('admin.diary.index')
            ->with('status', 'Diary contact deleted successfully.');
    }

    private function validatedPayload(Request $request): array
    {
        return $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function ensureCanManage(DiaryContact $diaryContact, Request $request): void
    {
        $user = $request->user();

        abort_unless(
            $user && ($user->isAdmin() || (int) $diaryContact->created_by === (int) $user->id),
            403
        );
    }
}
