<?php

namespace Addgod\Omnipay\Models;

use Addgod\Omnipay\Facades\Omnipay;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Transaction extends Model
{
    public const STATUS_CREATED = 0;
    public const STATUS_PURCHASE = 1;
    public const STATUS_PURCHASE_COMPLETE = 2;
    public const STATUS_AUTHORIZE = 3;
    public const STATUS_AUTHORIZE_COMPLETE = 4;
    public const STATUS_CAPTURE = 5;
    public const STATUS_REFUND_PARTIALLY = 6;
    public const STATUS_REFUND_FULLY = 7;
    public const STATUS_VOID = 8;
    public const STATUS_DECLINED = 9;

    protected $table = 'omnipay_transactions';

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
        'config',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'config' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs()
    {
        return $this->hasMany(TransactionLog::class);
    }

    /**
     * Associated entity.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function entity()
    {
        return $this->morphTo('entity');
    }

    /**
     * Get the merchant associated with the transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Saves the model.
     *
     * @param array $options
     *
     * @return bool
     */
    public function save(array $options = [])
    {
        if (empty($this->attributes['merchant_id'])) {
            $this->attributes['merchant_id'] = config('omnipay.default_merchant');
        }

        return parent::save($options);
    }

    /**
     * Get all parameters that is used by the different requests.
     *
     * @param string $type
     *
     * @return array
     */
    public function getParameters($type = 'authorize')
    {
        $prefixedTransactionId = config('omnipay.transaction_route_prefix') . $this->id;

        return array_merge([
            'returnUrl'            => route('omnipay.complete.' . $type, [$this->id]),
            'notifyUrl'            => route('omnipay.notify', [$this->id]),
            'redirectUrl'          => $this->redirect_to,
            'transactionId'        => $prefixedTransactionId,
            'transactionReference' => $this->transaction ?? null,
            'amount'               => $this->amount,
        ], ($this->config ?? []));
    }
}
