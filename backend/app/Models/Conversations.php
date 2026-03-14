<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversations extends Model
{
    /** @use HasFactory<\Database\Factories\ConversationsFactory> */
    use HasFactory;

    public const STATUS_BOT_ACTIVE = 'bot_active';
    public const STATUS_WAITING_FOR_AGENT = 'waiting_for_agent';
    public const STATUS_AGENT_ACTIVE = 'agent_active';
    public const STATUS_CLOSED = 'closed';

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

    public function assignedAgent()
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function messages()
    {
        return $this->hasMany(Messages::class);
    }
}
