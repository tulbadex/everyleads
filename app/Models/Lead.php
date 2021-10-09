<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasOne};

class Lead extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    const STATUS_FOLLOW_UP = 'follow up';
    const STATUS_PROSPECT = 'prospect';
    const STATUS_NEGOTIATION = 'negotiation';
    const STATUS_WON = 'won';
    const STATUS_LOST = 'lost';

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator');
    }

    public function assign(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assign_to');
    }

    /* public function assign(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'assign_to');
    } */

    public function scopeStatus($query, $status, $creator, $assign)
    {
        $query->where(function($query) use($status, $creator, $assign){
            $query->whereStatus($status)
             ->Where(function ($query) use($status, $creator, $assign){
                 $query->where('creator', $creator)
                    ->orWhere('assign_to', $assign);
             });
        });
    }
}
