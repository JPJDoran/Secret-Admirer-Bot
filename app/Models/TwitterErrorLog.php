<?php

namespace App\Models;

use App\Models\Tweet;
use Illuminate\Database\Eloquent\Model;

class TwitterErrorLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'error'
    ];

    /**
     * Get the tweet that the error relates to.
     */
    public function tweet()
    {
        return $this->belongsTo(Tweet::class);
    }
}
