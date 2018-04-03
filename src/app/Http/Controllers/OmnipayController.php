<?php
namespace Addgod\Omnipay\app\Http\Controllers;

use Illuminate\Routing\Controller;
use Addgod\Omnipay\app\Models\Transaction;

class OmnipayController extends Controller
{

    /**
     * Sends an instant capture request to the payment gateway
     *
     * @param \Addgod\Omnipay\app\Models\Transaction $transaction
     *
     * @return bool
     */
    public function purchase(Transaction $transaction)
    {
        return $transaction->purchase();
    }

    /**
     * Updates the payment to completed.
     *
     * @param \Addgod\Omnipay\app\Models\Transaction $transaction
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function completePurchase(Transaction $transaction)
    {
        $transaction->completePurchase();

        return redirect($transaction->redirect_to);
    }

    /**
     * Sends a autorize request to the payments gateway.
     *
     * @param \Addgod\Omnipay\app\Models\Transaction $transaction
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function authorize(Transaction $transaction)
    {
        return $transaction->authorize();
    }

    /**
     * Updates the payment to completed.
     *
     * @param \Addgod\Omnipay\app\Models\Transaction $transaction
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function completeAuthorize(Transaction $transaction)
    {
        $transaction->completeAuthorize();
        return redirect($transaction->redirect_to);
    }

    /**
     * Sends a re-authorize request to the payment gateway.
     *
     * @param \Addgod\Omnipay\app\Models\Transaction $transaction
     */
    public function reAuthorize(Transaction $transaction)
    {
        $transaction->reAuthorize();
    }

    /**
     * Sends a capture request to the payment gateway.
     *
     * @param \Addgod\Omnipay\app\Models\Transaction $transaction
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function capture(Transaction $transaction)
    {
        $transaction->capture();
        return redirect()->back();
    }

    /**
     * Sends a void request to the payment gateway.
     *
     * @param \Addgod\Omnipay\app\Models\Transaction $transaction
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function void(Transaction $transaction)
    {
        $transaction->void();
        return redirect()->back();
    }

    /**
     * Sends a refund request to the payment gateway.
     *
     * @param \Addgod\Omnipay\app\Models\Transaction $transaction
     * @param null                                   $amount
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function refund(Transaction $transaction, $amount = null)
    {
        $transaction->refund($amount);
        return redirect()->back();
    }

    /**
     * Receives data for a transaction, from the payment gateway.
     *
     * @param \Addgod\Omnipay\app\Models\Transaction $transaction
     *
     * @return \Illuminate\Http\Response
     */
    public function notify(Transaction $transaction)
    {
        $transaction->notify();
        return response()->make(null, 200);
    }
}
