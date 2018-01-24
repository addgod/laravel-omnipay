<?php

namespace Addgod\DibsD2\app\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    const STATUS_CREATED            = 0;
    const STATUS_PURCHASE           = 1;
    const STATUS_PURCHASE_COMPLETE  = 2;
    const STATUS_AUTHORIZE          = 3;
    const STATUS_AUTHORIZE_COMPLETE = 4;
    const STATUS_CAPTURE            = 5;
    const STATUS_REFUND_PARTIALLY   = 6;
    const STATUS_REFUND_FULLY       = 7;
    const STATUS_VOID               = 8;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'transaction',
        'merchant_id',
        'status',
        'amount',
        'redirect_to',
    ];

    public function logs()
    {
        return $this->hasMany(TransactionLog::class);
    }

    public function save(array $options = [])
    {
        if (empty($this->attributes['merchant_id'])) {
            $this->attributes['merchant_id'] = config('dibsd2.default_merchant');
        }
        return parent::save($options);
    }
}
