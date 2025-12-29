<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrivateLink extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'url',
        'description',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
