<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Faq_entries extends Model
{
    /** @use HasFactory<\Database\Factories\FaqEntriesFactory> */
    use HasFactory;
    
    protected $fillable = [
        'title',
        'category',
        'answer',
        'keywords',
        'is_active',
    ];
}
