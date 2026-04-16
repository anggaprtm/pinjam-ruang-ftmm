<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CentralTicket extends Model
{
    protected $fillable = [
        'original_ticket_id',
        'code',
        'reporter_name',
        'reporter_email',
        'is_guest',
        'title',
        'category',
        'description',
        'priority',
        'status',
        'attachment_url'
    ];
}