<?php

namespace App\Models;

use App\Enums\ContactSubmissionStatus;
use Illuminate\Database\Eloquent\Model;

class ContactSubmission extends Model
{
    protected $fillable = [
        'name', 'email', 'organization', 'reason', 'message', 'status', 'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return ['status' => ContactSubmissionStatus::class];
    }
}
