<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversations extends Model
{
    /** @use HasFactory<\Database\Factories\ConversationsFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'assigned_agent_id',
        'status',
        'channel',
        'subject',
        'closed_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
