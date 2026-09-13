<?php

namespace App\Http\Controllers;

use App\Models\ContactRequest;
use App\Http\Controllers\WebsiteContactController;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminContactController extends Controller
{
    public function index(Request $request): View
    {
        $formOptions = WebsiteContactController::formOptions();
        $filters = $request->validate([
            'form_type' => ['nullable', 'string'],
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $contactQuery = ContactRequest::query()
            ->when(
                filled($filters['form_type'] ?? null) && array_key_exists($filters['form_type'], $formOptions),
                fn ($query) => $query->where('form_type', $filters['form_type'])
            )
            ->when(filled($filters['search'] ?? null), function ($query) use ($filters) {
                $search = trim((string) $filters['search']);

                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery
                        ->where('full_name', 'like', '%' . $search . '%')
                        ->orWhere('company_name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%')
                        ->orWhere('phone', 'like', '%' . $search . '%');
                });
            })
            ->latest();

        $summaryContacts = (clone $contactQuery)->get();
        $contacts = $contactQuery->paginate(10)->withQueryString();

        return view('admin.contacts.index', [
            'contacts' => $contacts,
            'formOptions' => $formOptions,
            'filters' => [
                'form_type' => $filters['form_type'] ?? '',
                'search' => $filters['search'] ?? '',
            ],
            'summary' => [
                'total' => $summaryContacts->count(),
                'dealer' => $summaryContacts->where('form_type', 'dealer_account')->count(),
                'support' => $summaryContacts->whereIn('form_type', ['repair_return', 'ffl_shipping', 'missing_item'])->count(),
                'other' => $summaryContacts->where('form_type', 'responder_account')->count(),
            ],
        ]);
    }

    public function show(ContactRequest $contact): View
    {
        return view('admin.contacts.show', [
            'contact' => $contact,
        ]);
    }

    public function destroy(ContactRequest $contact)
    {
        $contact->delete();

        return redirect()
            ->route('admin.contacts.index')
            ->with('status', 'Contact deleted successfully.');
    }
}
