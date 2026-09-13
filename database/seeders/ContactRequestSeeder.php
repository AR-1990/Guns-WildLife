<?php

namespace Database\Seeders;

use App\Models\ContactRequest;
use Illuminate\Database\Seeder;

class ContactRequestSeeder extends Seeder
{
    public function run(): void
    {
        ContactRequest::withTrashed()
            ->whereIn('email', ['bilal@example.com', 'saad@example.com', 'hina@example.com'])
            ->forceDelete();

        collect([
            [
                'form_type' => 'dealer',
                'form_label' => 'Become a Dealer',
                'full_name' => 'Bilal Ahmed',
                'company_name' => 'Bilal Outdoor Store',
                'email' => 'bilal@example.com',
                'phone' => '03014445555',
                'city' => 'Lahore',
                'message' => 'Interested in becoming an authorized dealer.',
            ],
            [
                'form_type' => 'support',
                'form_label' => 'Support Request',
                'full_name' => 'Saad Khan',
                'company_name' => null,
                'email' => 'saad@example.com',
                'phone' => '03125556666',
                'city' => 'Islamabad',
                'message' => 'Need help with product availability and pricing.',
            ],
            [
                'form_type' => 'general',
                'form_label' => 'General Inquiry',
                'full_name' => 'Hina Malik',
                'company_name' => 'Hina Sporting Goods',
                'email' => 'hina@example.com',
                'phone' => '03236667777',
                'city' => 'Karachi',
                'message' => 'Requesting a call back for wholesale purchase options.',
            ],
        ])->each(fn (array $contact) => ContactRequest::query()->create($contact));
    }
}
