<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    /** @use HasFactory<\Database\Factories\EventsFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'occurrence_at',
        'description'
    ];

    protected $casts = [
        'occurrence_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
