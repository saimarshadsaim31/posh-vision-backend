<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Collection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'shopify_collection_id',
        'image',
        'description',
        'status',
        'shopify_publication_status',
    ];

    public function user()
    {
        $this->belongsTo(Collection::class);
    }
}
