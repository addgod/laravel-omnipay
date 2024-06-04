<?php

namespace Addgod\Omnipay\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Addgod\Omnipay\Models\Transaction;
use Addgod\Omnipay\Facades\Omnipay;
use Illuminate\Support\Facades\Auth;

class OmnipayController extends Controller
{
    /**
     * Sends an instant capture request to the payment gateway
     *
     * @param \Addgod\Omnipay\Models\Transaction $transaction
     *
     * @return mixed
     */
    public function purchase(Transaction $transaction)
    {
        if ($transaction->status === Transaction::STATUS_PURCHASE) {
            return redirect($transaction->redirect_to)->with('success', 'Already in status purchase');
        }

        if (!$transaction->isUnguarded() && $transaction->status !== Transaction::STATUS_CREATED) {
            return redirect($transaction->redirect_to)->with('error', 'Invalid state. Must have status of ' . Transaction::STATUS_CREATED);
        }

        Omnipay::setMerchant($transaction->merchant_id);
        $response = Omnipay::purchase($transaction->getParameters('purchase'))->send();

        $transaction->status = Transaction::STATUS_PURCHASE;
        $transaction->save();

        $transaction->logs()->create([
            'payload' => [
                'action'  => 'Purchase',
                'message' => $response->getMessage(),
                'data'    => $response->getData(),
            ],
        ]);

        // We assume this means API driven
        if ($response->isTransparentRedirect()) {
            if ($response->isSuccessful()) {
                $transaction->transaction = $response->getTransactionReference();
                $transaction->status = Transaction::STATUS_PURCHASE_COMPLETE;
                $transaction->save();
            }

            return response()->json($response->getData());
        }
        if ($response->isSuccessful()) {
            return redirect($transaction->redirect_to);
        } 
        if ($response->isRedirect()) {
            return $response->getRedirectResponse();
        } 
        
        return redirect($transaction->redirect_to)->with('error', 'Purchase request failed');
    }

    /**
     * Updates the payment to completed.
     *
     * @param \Addgod\Omnipay\Models\Transaction $transaction
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\Http\JsonResponse
     */
    public function completePurchase(Transaction $transaction)
    {
        if ($transaction->status === Transaction::STATUS_PURCHASE_COMPLETE) {
            return redirect($transaction->redirect_to)->with('success', 'Already in status purchase complete');
        }

        if (!$transaction->isUnguarded() && $transaction->status !== Transaction::STATUS_PURCHASE) {
            return redirect($transaction->redirect_to)->with('error', 'Invalid state. Must have status of ' . Transaction::STATUS_PURCHASE);
        }

        Omnipay::setMerchant($transaction->merchant_id);
        $response = Omnipay::completePurchase($transaction->getParameters('purchase'))->send();

        $transaction->transaction = $response->getTransactionReference();

        $transaction->logs()->create([
            'payload' => [
                'action'  => 'Complete Purchase',
                'message' => $response->getMessage(),
                'data'    => $response->getData(),
            ],
        ]);


        // We assume this means API driven
        if ($response->isTransparentRedirect()) {
            if ($response->isSuccessful()) {
                $transaction->status = Transaction::STATUS_PURCHASE_COMPLETE;
            } elseif ($response->isCancelled()) {
                $transaction->status = Transaction::STATUS_VOID;
            } elseif ($response->isPending()) {
                $transaction->status = Transaction::STATUS_PURCHASE;
            } else {
                $transaction->status = Transaction::STATUS_DECLINED;
            }
            $transaction->save();

            return response()->json($response->getData());
        }
        if ($response->isSuccessful()) {
            $transaction->status = Transaction::STATUS_PURCHASE_COMPLETE;
            $transaction->save();
            return redirect($transaction->redirect_to);
        }
        if ($response->isCancelled()) {
            $transaction->status = Transaction::STATUS_DECLINED;
            $transaction->save();
            return redirect($transaction->redirect_to);
        }
        if ($response->isRedirect()) {
            return $response->getRedirectResponse();
        }

        $transaction->status = Transaction::STATUS_DECLINED;
        $transaction->save();

        return redirect($transaction->redirect_to);
    }

    /**
     * Sends a autorize request to the payments gateway.
     *
     * @param \Addgod\Omnipay\Models\Transaction $transaction
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\Http\JsonResponse
     */
    public function authorize(Transaction $transaction)
    {
        if ($transaction->status === Transaction::STATUS_AUTHORIZE) {
            return redirect($transaction->redirect_to)->with('success', 'Already in status authorize');
        }

        if (!$transaction->isUnguarded() && $transaction->status !== Transaction::STATUS_CREATED) {
            return redirect($transaction->redirect_to)->with('error', 'Invalid state. Must have status of ' . Transaction::STATUS_CREATED);
        }

        Omnipay::setMerchant($transaction->merchant_id);
        $response = Omnipay::authorize($transaction->getParameters('authorize'))->send();

        $transaction->status = Transaction::STATUS_AUTHORIZE;
        $transaction->save();

        $transaction->logs()->create([
            'payload' => [
                'action'  => 'Authorize',
                'message' => $response->getMessage(),
                'data'    => $response->getData(),
            ],
        ]);
        
        // We assume this means API driven
        if ($response->isTransparentRedirect()) {
            if ($response->isSuccessful()) {
                $transaction->transaction = $response->getTransactionReference();
                $transaction->status = Transaction::STATUS_AUTHORIZE_COMPLETE;
                $transaction->save();
            }

            return response()->json($response->getData());
        }
        if ($response->isSuccessful()) {
            return redirect($transaction->redirect_to);
        }
        if ($response->isRedirect()) {
            return $response->getRedirectResponse();
        }

        return redirect($transaction->redirect_to)->with('error', 'Authorize request failed');
    }

    /**
     * Updates the payment to completed.
     *
     * @param \Addgod\Omnipay\Models\Transaction $transaction
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function completeAuthorize(Transaction $transaction)
    {
        if ($transaction->status === Transaction::STATUS_AUTHORIZE_COMPLETE) {
            return redirect($transaction->redirect_to)->with('success', 'Already in status authorize complete');
        }

        if (!$transaction->isUnguarded() && $transaction->status !== Transaction::STATUS_AUTHORIZE) {
            return redirect($transaction->redirect_to)->with('error', 'Invalid state. Must have status of ' . Transaction::STATUS_AUTHORIZE);
        }

        Omnipay::setMerchant($transaction->merchant_id);
        $response = Omnipay::completeAuthorize($transaction->getParameters('authorize'))->send();

        $transaction->transaction = $response->getTransactionReference();

        $transaction->logs()->create([
            'payload' => [
                'action'  => 'Complete Authorize',
                'message' => $response->getMessage(),
                'data'    => $response->getData(),
            ],
        ]);

        // We assume this means API driven
        if ($response->isTransparentRedirect()) {
            if ($response->isSuccessful()) {
                $transaction->status = Transaction::STATUS_AUTHORIZE_COMPLETE;
            } elseif ($response->isCancelled()) {
                $transaction->status = Transaction::STATUS_VOID;
            } elseif ($response->isPending()) {
                $transaction->status = Transaction::STATUS_AUTHORIZE;
            } else {
                $transaction->status = Transaction::STATUS_DECLINED;
            }

            $transaction->save();

            return response()->json($response->getData());
        }
        if ($response->isSuccessful()) {
            $transaction->status = Transaction::STATUS_AUTHORIZE_COMPLETE;
            $transaction->save();
            return redirect($transaction->redirect_to);
        }
        if ($response->isCancelled()) {
            $transaction->status = Transaction::STATUS_DECLINED;
            $transaction->save();
            return redirect($transaction->redirect_to);
        }
        if ($response->isRedirect()) {
            return $response->getRedirectResponse();
        }

        $transaction->status = Transaction::STATUS_DECLINED;
        $transaction->save();

        return redirect($transaction->redirect_to);
    }

    /**
     * Sends a re-authorize request to the payment gateway.
     *
     * @param \Addgod\Omnipay\Models\Transaction $transaction
     */
    public function reAuthorize(Transaction $transaction)
    {
        if (!$transaction->isUnguarded() && $transaction->status !== Transaction::STATUS_AUTHORIZE_COMPLETE) {
            abort(400, 'Invalid state. Must have status of ' . Transaction::STATUS_AUTHORIZE_COMPLETE);
        }

        Omnipay::setMerchant($transaction->merchant_id);
        $response = Omnipay::reAuthorize($transaction->getParameters())->send();

        $transaction->logs()->create([
            'payload' => [
                'action'  => 'Re-Authorization',
                'message' => $response->getMessage(),
                'data'    => $response->getData(),
            ],
        ]);

        if (!$response->isSuccessful()) {
            abort(400, 'Re-authorization failed');
        }
    }

    /**
     * Sends a capture request to the payment gateway.
     *
     * @param \Addgod\Omnipay\Models\Transaction $transaction
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function capture(Transaction $transaction)
    {
        if ($transaction->status === Transaction::STATUS_CAPTURE) {
            return redirect()->back();
        }

        if (!$transaction->isUnguarded() && $transaction->status !== Transaction::STATUS_AUTHORIZE_COMPLETE) {
            return redirect()->back()->with('error', 'Invalid state. Must have status of ' . Transaction::STATUS_AUTHORIZE_COMPLETE);
        }

        Omnipay::setMerchant($transaction->merchant_id);
        $response = Omnipay::capture($transaction->getParameters())->send();

        $transaction->logs()->create([
            'payload' => [
                'action'  => 'Capture',
                'user'    => Auth::check() ? Auth::user()->toArray() : 'Automatic',
                'message' => $response->getMessage(),
                'data'    => $response->getData(),
            ],
        ]);

        if ($response->isSuccessful()) {
            $transaction->transaction = $response->getTransactionReference();
            $transaction->status = Transaction::STATUS_CAPTURE;
            $transaction->save();
        } else {
            redirect()->back()->with('error', 'Capture of payment failed');
        }

        return redirect()->back();
    }

    /**
     * Sends a void request to the payment gateway.
     *
     * @param \Addgod\Omnipay\Models\Transaction $transaction
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function void(Transaction $transaction)
    {
        if ($transaction->status === Transaction::STATUS_VOID) {
            return redirect()->back();
        }

        if (!$transaction->isUnguarded() && $transaction->status !== Transaction::STATUS_AUTHORIZE_COMPLETE) {
            redirect()->back()->with('error', 'Invalid state. Must have status of ' . Transaction::STATUS_AUTHORIZE_COMPLETE);
        }

        Omnipay::setMerchant($transaction->merchant_id);
        $response = Omnipay::void($transaction->getParameters())->send();

        $transaction->logs()->create([
            'payload' => [
                'action'  => 'Void',
                'user'    => \Auth::check() ? \Auth::user()->toArray() : 'Automatic',
                'message' => $response->getMessage(),
                'data'    => $response->getData(),
            ],
        ]);

        if ($response->isSuccessful()) {
            $transaction->status = Transaction::STATUS_VOID;
            $transaction->save();
        } else {
            redirect()->back()->with('error', 'Void of payment failed');
        }

        return redirect()->back();
    }

    /**
     * Sends a refund request to the payment gateway.
     *
     * @param \Addgod\Omnipay\Models\Transaction $transaction
     * @param int|null                           $amount
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function refund(Transaction $transaction, int $amount = null)
    {
        if ($transaction->status === Transaction::STATUS_REFUND_FULLY) {
            return redirect()->back();
        }

        $allowedStates = [
            Transaction::STATUS_PURCHASE_COMPLETE,
            Transaction::STATUS_CAPTURE,
            Transaction::STATUS_REFUND_PARTIALLY,
        ];
        if (!$transaction->isUnguarded() && !\in_array($transaction->status, $allowedStates)) {
            return redirect()->back()->with('error', 'Invalid state. Must have status of ' . implode(' or ', $allowedStates));
        }

        Omnipay::setMerchant($transaction->merchant_id);

        $params = $transaction->getParameters();
        if ($amount) {
            $params['amount'] = $amount;
        }

        $response = Omnipay::refund($params)->send();

        $transaction->logs()->create([
            'payload' => [
                'action'  => 'Refund',
                'user'    => \Auth::user()->toArray(),
                'message' => $response->getMessage(),
                'data'    => $response->getData(),
            ],
        ]);

        if ($response->isSuccessful()) {
            if (!\is_null($amount) && $amount < $transaction->amount) {
                $transaction->status = Transaction::STATUS_REFUND_PARTIALLY;
                $transaction->amount = $transaction->amount - $amount;
            } else {
                $transaction->status = Transaction::STATUS_REFUND_FULLY;
                $transaction->amount = 0;
            }
            $transaction->save();
        } else {
            return redirect()->back()->with('error', 'Refund of payment failed');
        }

        return redirect()->back();
    }

    /**
     * Receives data for a transaction, from the payment gateway.
     *
     * @param \Addgod\Omnipay\Models\Transaction $transaction
     *
     * @return \Illuminate\Http\Response
     */
    public function notify(Transaction $transaction = null)
    {
        Omnipay::setMerchant($transaction->merchant_id);
        $response = Omnipay::acceptNotification()->send();

        $transaction->transaction = $response->getTransactionReference();

        $transaction->logs()->create([
            'payload' => [
                'action'  => 'Notify',
                'message' => $response->getMessage(),
                'data'    => $response->getData(),
            ],
        ]);

        if ($response->isSuccessful()) {
            if ($transaction->status == Transaction::STATUS_PURCHASE) {
                $transaction->status = Transaction::STATUS_PURCHASE_COMPLETE;
            } elseif ($transaction->status == Transaction::STATUS_AUTHORIZE) {
                $transaction->status = Transaction::STATUS_AUTHORIZE_COMPLETE;
            }
        } elseif ($response->isCancelled()) {
            $transaction->status = Transaction::STATUS_VOID;
        } elseif (method_exists($response, 'isCaptured') && $response->isCaptured()) {
            $transaction->status = Transaction::STATUS_CAPTURE;
        } elseif (method_exists($response, 'isRefunded') && $response->isRefunded()) {
            $transaction->status = Transaction::STATUS_REFUND_FULLY;
        } else {
            $transaction->status = Transaction::STATUS_DECLINED;
        }

        $transaction->save();

        return response()->noContent();
    }

    /**
     * Fetch a payment providors available payment methods.
     *
     * @param mixed $merchant
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentMethods($merchant, Request $request)
    {
        Omnipay::setMerchant($merchant);
        $response = Omnipay::paymentMethods($request->all())->send();

        return response()->json($response->getPaymentMethods());
    }
}
