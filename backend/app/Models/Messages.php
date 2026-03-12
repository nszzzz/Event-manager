<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Messages extends Model
{
    /** @use HasFactory<\Database\Factories\MessagesFactory> */
    use HasFactory;
    
    protected $fillable = [
        'conversation_id',
        'sender_type',
        'sender_user_id',
        'content',
        'message_type',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversations::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
