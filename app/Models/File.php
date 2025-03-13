<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use HasFactory;

    // fillable fields
    protected $fillable = [
        'user_id',
        'alt',
        'category',
        'path',
        'type',
        'is_chunked',
        'is_approved',
    ];

    // base url for files
    // public static $baseUrl = 'https://roiket.storage.iran.liara.space/'; // old url
    public static $baseUrl = 'https://cdn.omdico.ir/';
    // storage disk name : liara/arvan
    public static $disk = 'arvan';
    protected $appends = ['url'];

    // relations with user model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // finde last file by user id
    public static function findLastFileByUserId($user_id)
    {
        return File::where('user_id', $user_id)->orderBy('id', 'desc')->first();
    }

    // Uploaded files in the last 24 hours by user id
    public static function LastByUser($user_id)
    {
        return File::where('user_id', $user_id)->where('created_at', '>=', now()->subHours(24))->get();
    }

    /**
     * This code loads the user's file
     * from the database and returns a URL to the
     * file.
     * Summary of getPathAttribute
     * @param mixed $value
     * @return string
     */
    // public function getPathAttribute($value)
    // {
    //     $expiration = now()->addMinutes(1440); // set expiration date to 30 minutes from now
    //     $temporaryUrl = Storage::disk($this->disk)->temporaryUrl($value, $expiration);
    //     // return self::$baseUrl . $value;
    //     return $temporaryUrl;
    // }

    /**
     * This is direct link to files
     */
    public function getUrlAttribute()
    {
        return self::$baseUrl . $this->path;

        // $expiration = now()->addMinutes(1440); // set expiration date to 30 minutes from now
        // $temporaryUrl = Storage::disk($this->disk)->temporaryUrl($this->path, $expiration);
        // // return self::$baseUrl . $value;
        // return $temporaryUrl;
    }
}
