<?php

namespace App\Http\Controllers;

use App\Models\ContactRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WebsiteContactController extends Controller
{
    public function create(Request $request): View
    {
        $formOptions = self::formOptions();
        $selectedForm = $request->string('form')->toString();

        if (! array_key_exists($selectedForm, $formOptions)) {
            $selectedForm = 'dealer_account';
        }

        return view('website.contact', [
            'formOptions' => $formOptions,
            'selectedForm' => $selectedForm,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $formOptions = self::formOptions();
        $data = $request->validate([
            'form_type' => ['required', 'string'],
            'full_name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'city' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string'],
        ]);

        if (! array_key_exists($data['form_type'], $formOptions)) {
            return back()
                ->withInput()
                ->withErrors(['form_type' => 'Selected contact form is invalid.']);
        }

        ContactRequest::query()->create([
            'form_type' => $data['form_type'],
            'form_label' => $formOptions[$data['form_type']],
            'full_name' => $data['full_name'],
            'company_name' => $data['company_name'] ?: null,
            'email' => $data['email'],
            'phone' => $data['phone'],
            'city' => $data['city'] ?: null,
            'message' => $data['message'] ?: null,
        ]);

        return redirect()
            ->route('contact', ['form' => $data['form_type']])
            ->with('status', 'Aap ki request successfully submit ho gayi hai.');
    }

    public function dealerRequest(Request $request): RedirectResponse
    {
        $payload = $request->merge(['form_type' => 'dealer_account']);

        return $this->store($payload);
    }

    public static function formOptions(): array
    {
        return [
            'dealer_account' => 'Request Dealer Account',
            'responder_account' => 'Request Military/Law/1st Responder Account',
            'repair_return' => 'Submit Repair and Return Form (RMA)',
            'ffl_shipping' => 'FFL Required Shipping Form',
            'missing_item' => 'Submit Missing Item Form',
        ];
    }
}
