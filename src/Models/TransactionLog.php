<?php

namespace Addgod\Omnipay\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionLog extends Model
{

    protected $table = 'omnipay_transaction_logs';

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

    /**
     * The associated transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
