<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation_events extends Model
{
    /** @use HasFactory<\Database\Factories\ConversationEventsFactory> */
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'type',
    ];
    
    protected $casts = [
    'payload' => 'array'
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversations::class);
    }
}
