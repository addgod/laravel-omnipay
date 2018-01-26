<?php
namespace Addgod\Omnipay\app\Http\Controllers;

use Illuminate\Routing\Controller;
use App\Models\Transaction;

class OmnipayController extends Controller
{

    public function purchase(Transaction $transaction)
    {
        return $transaction->purchase();
    }

    public function completePurchase(Transaction $transaction)
    {
        $transaction->completePurchase();

        return redirect($transaction->redirect_to);
    }

    public function authorize(Transaction $transaction)
    {
        return $transaction->authorize();
    }

    public function completeAuthorize(Transaction $transaction)
    {
        $transaction->completeAuthorize();
        return redirect($transaction->redirect_to);
    }

    public function reAuthorize(Transaction $transaction)
    {
        $transaction->reAuthorize();
    }

    public function capture(Transaction $transaction)
    {
        $transaction->capture();
        return redirect()->back();
    }

    public function void(Transaction $transaction)
    {
        $transaction->void();
        return redirect()->back();
    }

    public function refund(Transaction $transaction, $amount = null)
    {
        $transaction->refund($amount);
        return redirect()->back();
    }

    public function notify(Transaction $transaction)
    {
        $transaction->notify();
        return response()->make(null, 200);
    }
}
