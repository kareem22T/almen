<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'photo_path',
        'title',
        'link',
        'description',
        'title_ar',
        'description_ar',
    ];
}
