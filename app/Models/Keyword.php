<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    use HasFactory;
    # we haven't times in migration
    public $timestamps = false;

    protected $fillable = ['title', 'product_id', 'category_id'];

    public function product()
    {
        return $this->belongsTo(product::class);
    }

    public function category()
    {
        return $this->belongsTo(category::class);
    }
}
