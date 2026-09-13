<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'form_type',
        'form_label',
        'full_name',
        'company_name',
        'email',
        'phone',
        'city',
        'message',
    ];
}
