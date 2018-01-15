<?php
namespace Addgod\DibsD2\app\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Models\Transaction;

class DibsD2Controller extends Controller
{

    public function purchase(Transaction $transaction)
    {
        $params = [
            'returnUrl'     => route('dibsd2.complete.purchase'),
            'callbackUrl'   => route('dibsd2.callback'),
            'amount'        => $transaction->amount,
            'orderid'       => $transaction->id,
        ];

        $dibs = app()->make('DibsD2');
        $dibs::setDefaultAccount($transaction->account);

        $response = $dibs::purchase($params)->send();

        $transaction->status = Transaction::STATUS_PURCHASE;
        $transaction->save();

        if ($response->isRedirect()) {
            return $response->getRedirectResponse();
        } else {
            throw new \Exception('Payment failed');
        }
    }

    public function completePurchase()
    {
        $dibs = app()->make('DibsD2');
        $response = $dibs::completePurchase()->send();

        $transaction = Transaction::find($response->getOrderId());
        $transaction->transaction = $response->getTransactionReference();
        $transaction->status = Transaction::STATUS_PURCHASE_COMPLETE;
        $transaction->save();

        $transaction->logs()->create([
            'payload' => [
                'action'    => 'Complete Purchase',
                'data'      => $response->getData()
            ]
        ]);

        return redirect($transaction->redirect_to);
    }

    public function authorize(Transaction $transaction)
    {
        $params = [
            'returnUrl'     => route('dibsd2.complete.authorize'),
            'callbackUrl'   => route('dibsd2.callback'),
            'amount'        => $transaction->amount,
            'orderid'       => $transaction->id,
        ];

        $dibs = app()->make('DibsD2');
        $dibs::setDefaultAccount($transaction->account);

        $response = $dibs::authorize($params)->send();

        $transaction->status = Transaction::STATUS_AUTHORIZE;
        $transaction->save();

        if ($response->isRedirect()) {
            return $response->getRedirectResponse();
        } else {
            throw new \Exception('Payment failed');
        }
    }

    public function completeAuthorize()
    {
        $dibs = app()->make('DibsD2');
        $response = $dibs::completeAuthorize()->send();

        $transaction = Transaction::find($response->getOrderId());
        $transaction->transaction = $response->getTransactionReference();
        $transaction->status = Transaction::STATUS_AUTHORIZE_COMPLETE;
        $transaction->save();

        $transaction->logs()->create([
            'payload' => [
                'action'    => 'Complete Authorization',
                'data'      => $response->getData()
            ]
        ]);
        return redirect($transaction->redirect_to);
    }

    public function reAuthorize(Transaction $transaction)
    {
        $params = [
            'transactionId' => $transaction->transaction
        ];
        $dibs = app()->make('DibsD2');
        $dibs::setDefaultAccount($transaction->account);

        $response = $dibs::reAuthorize($params)->send();

        if ($response->isSuccessful()) {
            $transaction->logs()->create([
                'payload' => [
                    'action'    => 'Re-Authorization',
                    'data'      => $response->getData()
                ]
            ]);
        } else {
            throw new \Exception('Re-authorization failed');
        }
    }

    public function capture(Transaction $transaction)
    {
        $params = [
            'amount'        => $transaction->amount,
            'transactionId' => $transaction->transaction
        ];

        $dibs = app()->make('DibsD2');
        $dibs::setDefaultAccount($transaction->account);

        $response = $dibs::capture($params)->send();

        if ($response->isSuccessful()) {
            $transaction->status = Transaction::STATUS_CAPTURE;
            $transaction->save();
            $transaction->logs()->create([
                'payload' => [
                    'user'      => \Auth::user()->toArray(),
                    'action'    => 'Capture',
                    'data'      => $response->getData()
                ]
            ]);
        } else {
            throw new \Exception('Capture of payment failed');
        }
    }

    public function void(Transaction $transaction)
    {
        $params = [
            'orderId'        => $transaction->id,
            'transactionId'  => $transaction->transaction
        ];

        $dibs = app()->make('DibsD2');
        $dibs::setDefaultAccount($transaction->account);

        $response = $dibs::void($params)->send();

        if ($response->isSuccessful()) {
            $transaction->status = Transaction::STATUS_VOID;
            $transaction->save();
            $transaction->logs()->create([
                'payload' => [
                    'user'      => \Auth::user()->toArray(),
                    'action'    => 'Void',
                    'data'      => $response->getData()
                ]
            ]);
        } else {
            throw new \Exception('Void of payment failed');
        }
    }

    public function refund(Transaction $transaction, $amount = null)
    {
        $params = [
            'orderId'       => $transaction->id,
            'amount'        => (!is_null($amount) && $amount < $transaction->amount) ? $amount : $transaction->amount,
            'transactionId' => $transaction->transaction
        ];

        $dibs = app()->make('DibsD2');
        $dibs::setDefaultAccount($transaction->account);

        $response = $dibs::refund($params)->send();

        if ($response->isSuccessful()) {
            if (!is_null($amount) && $amount < $transaction->amount) {
                $transaction->status = Transaction::STATUS_REFUND_PARTIALLY;
                $transaction->amount = $transaction->amount - $amount;
            } else {
                $transaction->status = Transaction::STATUS_REFUND_FULLY;
                $transaction->amount = 0;
            }
            $transaction->save();
            $transaction->logs()->create([
                'payload' => [
                    'user'      => \Auth::user()->toArray(),
                    'action'    => 'Refund',
                    'data'      => $response->getData()
                ]
            ]);
        } else {
            throw new \Exception('Refund of payment failed');
        }
    }

    public function callback()
    {
        $dibs = app()->make('DibsD2');
        $response = $dibs::completeAuthorize()->send();

        $transaction = Transaction::find($response->getOrderId());
        $transaction->transaction = $response->getTransactionReference();

        if ($response->isCaptured()) {
            $transaction->status = Transaction::STATUS_PURCHASE_COMPLETE;
            $transaction->save();
        } elseif ($response->isAuthorized()) {
            $transaction->status = Transaction::STATUS_AUTHORIZE_COMPLETE;
            $transaction->save();
        }

        $transaction->logs()->create([
            'payload' => [
                'action'    => 'Callback',
                'status'    => $response->getStatus(),
                'data'      => $response->getData()
            ]
        ]);
    }
}
