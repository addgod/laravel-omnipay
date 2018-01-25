<?php

namespace Addgod\Omnipay\app\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionLog extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payload',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'payload' => 'object',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
