<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CentralTicketReply extends Model
{
    // Tambahkan ini agar Laravel mengizinkan datanya disimpan
    protected $fillable = [
        'central_ticket_id',
        'original_reply_id',
        'replier_name',
        'replier_role',
        'content',
        'attachment_url'
    ];

    // Sekalian tambahkan relasi baliknya untuk jaga-jaga
    public function centralTicket()
    {
        return $this->belongsTo(CentralTicket::class, 'central_ticket_id');
    }
}