<?php

namespace App\Models;

use App\Models\TwitterErrorLog;
use Illuminate\Database\Eloquent\Model;

class Tweet extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'to',
        'from',
        'category',
        'message',
    ];

    /**
     * Get the errors for the tweet.
     */
    public function errors()
    {
        return $this->hasMany(TwitterErrorLog::class);
    }
}
