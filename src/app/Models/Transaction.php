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
    const STATUS_DECLINED           = 9;

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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
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

    /**
     * Saves the model.
     *
     * @param array $options
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
     * Send a purchase request to the payment gateway
     *
     * @return bool
     * @throws \Exception
     */
    public function purchase()
    {
        $omnipay = app()->make('Omnipay');
        $omnipay::setDefaultMerchant($this->merchant_id);

        $response = $omnipay::purchase($this->getParameters())->send();

        $this->status = Transaction::STATUS_PURCHASE;
        $this->save();

        if ($response->isSuccessful()) {
            return true;
        } elseif ($response->isRedirect()) {
            return $response->getRedirectResponse();
        } else {
            throw new \Exception('Purchase request failed');
        }
    }

    /**
     * Get a purchase response from the payment gateway
     *
     * @return bool
     */
    public function completePurchase()
    {
        $omnipay = app()->make('Omnipay');
        $omnipay::setDefaultMerchant($this->merchant_id);
        $response = $omnipay::completePurchase()->send();

        $this->transaction = $response->getTransactionId();

        if ($response->isSuccessful()) {
            $this->status = Transaction::STATUS_PURCHASE_COMPLETE;
        } else {
            $this->status = Transaction::STATUS_DECLINED;
        }
        $this->save();

        $this->logs()->create([
            'payload' => [
                'action'    => 'Complete Purchase',
                'message'   => $response->getMessage(),
                'data'      => $response->getData()
            ]
        ]);

        return true;
    }

    /**
     * Sends a authorizeation request to the payment gateway.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function authorize()
    {
        $omnipay = app()->make('Omnipay');
        $omnipay::setDefaultMerchant($this->merchant_id);

        $response = $omnipay::authorize($this->getParameters())->send();

        $this->status = Transaction::STATUS_AUTHORIZE;
        $this->save();

        if ($response->isSuccessful()) {
            return redirect($this->redirect_to);
        } elseif ($response->isRedirect()) {
            return $response->getRedirectResponse();
        } else {
            throw new \Exception('Authorize request failed');
        }
    }

    /**
     * Get a authorization response from the payment gateway
     *
     * @return bool
     */
    public function completeAuthorize()
    {
        $omnipay = app()->make('Omnipay');
        $omnipay::setDefaultMerchant($this->merchant_id);
        $response = $omnipay::completeAuthorize()->send();

        $this->transaction = $response->getTransactionId();

        if ($response->isSuccessful()) {
            $this->status = Transaction::STATUS_AUTHORIZE_COMPLETE;
        } else {
            $this->status = Transaction::STATUS_DECLINED;
        }

        $this->save();

        $this->logs()->create([
            'payload' => [
                'action'    => 'Complete Authorization',
                'message'    => $response->getMessage(),
                'data'      => $response->getData()
            ]
        ]);

        return true;
    }

    /**
     * Sends a re-authorization request to the payment gateway
     *
     * @return bool
     * @throws \Exception
     */
    public function reAuthorize()
    {
        $omnipay = app()->make('Omnipay');
        $omnipay::setDefaultMerchant($this->merchant_id);

        $response = $omnipay::reAuthorize($this->getParameters())->send();

        $this->logs()->create([
            'payload' => [
                'action'    => 'Re-Authorization',
                'message'    => $response->getMessage(),
                'data'      => $response->getData()
            ]
        ]);

        if (!$response->isSuccessful()) {
            throw new \Exception('Re-authorization failed');
        }
        return true;
    }

    /**
     * Sends a capture request to the payment gateway.
     *
     * @return bool
     * @throws \Exception
     */
    public function capture()
    {
        $omnipay = app()->make('Omnipay');
        $omnipay::setDefaultMerchant($this->merchant_id);

        $response = $omnipay::capture($this->getParameters())->send();

        $this->logs()->create([
            'payload' => [
                'action'    => 'Capture',
                'user'      => \Auth::check() ? \Auth::user()->toArray() : 'Automatic',
                'message'    => $response->getMessage(),
                'data'      => $response->getData()
            ]
        ]);

        if ($response->isSuccessful()) {
            $this->status = Transaction::STATUS_CAPTURE;
            $this->save();
        } else {
            throw new \Exception('Capture of payment failed');
        }

        return true;
    }

    /**
     * Sends a void request to the payment gateway.
     *
     * @return bool
     * @throws \Exception
     */
    public function void()
    {
        $omnipay = app()->make('Omnipay');
        $omnipay::setDefaultMerchant($this->merchant_id);

        $response = $omnipay::void($this->getParameters())->send();

        $this->logs()->create([
            'payload' => [
                'action'    => 'Void',
                'user'      => \Auth::check() ? \Auth::user()->toArray() : 'Automatic',
                'message'    => $response->getMessage(),
                'data'      => $response->getData()
            ]
        ]);

        if ($response->isSuccessful()) {
            $this->status = Transaction::STATUS_VOID;
            $this->save();
        } else {
            throw new \Exception('Void of payment failed');
        }

        return true;
    }

    /**
     * Sends a refund request to the payment gateway.
     *
     * @param null $amount
     * @return bool
     * @throws \Exception
     */
    public function refund($amount = null)
    {
        $omnipay = app()->make('Omnipay');
        $omnipay::setDefaultMerchant($this->merchant_id);

        $params = $this->getParameters();
        if ($amount) {
            $params['amount'] = $amount;
        }

        $response = $omnipay::refund($params)->send();

        $this->logs()->create([
            'payload' => [
                'action'    => 'Refund',
                'user'      => \Auth::user()->toArray(),
                'message'    => $response->getMessage(),
                'data'      => $response->getData()
            ]
        ]);

        if ($response->isSuccessful()) {
            if (!is_null($amount) && $amount < $this->amount) {
                $this->status = Transaction::STATUS_REFUND_PARTIALLY;
                $this->amount = $this->amount - $amount;
            } else {
                $this->status = Transaction::STATUS_REFUND_FULLY;
                $this->amount = 0;
            }
            $this->save();
        } else {
            throw new \Exception('Refund of payment failed');
        }

        return true;
    }

    /**
     *  Get a notify response from the payment gateway.
     *
     */
    public function notify()
    {
        $omnipay = app()->make('Omnipay');
        $omnipay::setDefaultMerchant($this->merchant_id);
        $response = $omnipay::acceptNotification()->send();

        $this->transaction = $response->getTransactionId();

        if ($response->isSuccessful()) {
            if ($this->status == Transaction::STATUS_PURCHASE) {
                $this->status = Transaction::STATUS_PURCHASE_COMPLETE;
            } elseif ($this->status == Transaction::STATUS_AUTHORIZE) {
                $this->status = Transaction::STATUS_AUTHORIZE_COMPLETE;
            }
        } else {
            $this->status = Transaction::STATUS_DECLINED;
        }

        $this->save();

        $this->logs()->create([
            'payload' => [
                'action'    => 'Notify',
                'message'    => $response->getMessage(),
                'data'      => $response->getData()
            ]
        ]);
    }

    /**
     * Get all parameters that is used by the different requests.
     *
     * @return array
     */
    private function getParameters()
    {
        return [
            'returnUrl'      => route('omnipay.complete.authorize', [$this->id]),
            'notifyUrl'      => route('omnipay.notify', [$this->id]),
            'transactionId'  => $this->transaction,
            'amount'         => $this->amount,
            'orderId'        => $this->id,
        ];
    }
}
