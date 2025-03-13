<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScheduleReport extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'information'];

    public function getInformationAttribute($value)
    {
        return json_decode($value, true);
    }
}
