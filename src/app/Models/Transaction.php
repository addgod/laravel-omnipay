<?php

namespace Addgod\Omnipay\app\Models;

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

    /**
     * Get the merchant associated with the transaction.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function save(array $options = [])
    {
        if (empty($this->attributes['merchant_id'])) {
            $this->attributes['merchant_id'] = config('omnipay.default_merchant');
        }
        return parent::save($options);
    }

    public function purchase()
    {
        $dibs = app()->make('Omnipay');
        $dibs::setDefaultMerchant($transaction->merchant_id);

        $response = $dibs::purchase($this->getParameters())->send();

        $transaction->status = Transaction::STATUS_PURCHASE;
        $transaction->save();

        if ($response->isSuccessful()) {
            return redirect($transaction->redirect_to);
        } else if ($response->isRedirect()) {
            return $response->getRedirectResponse();
        } else {
            throw new \Exception('Purchase request failed');
        }
    }

    public function completePurchase()
    {

    }

    public function authorize()
    {

    }

    private function getParameters()
    {
        return [
            'returnUrl'      => route('dibsd2.complete.authorize', [$this->id]),
            'callbackUrl'    => route('dibsd2.callback', [$this->id]),
            'transactionId'  => $this->transaction,
            'amount'         => $this->amount,
            'orderId'        => $this->id,
        ];
    }
}
