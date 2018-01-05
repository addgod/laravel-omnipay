<?php
namespace Addgod\DibsD2\app\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class DibsD2Controller extends Controller
{

    public function purchase(Request $request)
    {
        $params = [
            'returnUrl'     => url(config('dibsd2.route_prefix') . '/complete/purchase'),
            'amount'        => $request->amount,
            'callbackUrl'   => url(config('dibsd2.route_prefix') . '/callback'),
            'currency'      => $request->currency,
            'orderid'       => $request->orderid,
            'cancelurl'     => $request->cancelurl
        ];

        $omnipay = app()->make('DibsD2');
        $omnipay::setDefaultAccount($request->account);

        $response = $omnipay::purchase($params)->send();

        if ($response->isSuccessful()) {

            print_r($response);
        } elseif ($response->isRedirect()) {
            return $response->getRedirectResponse();
        } else {
            return view('test.dibs.index', compact('response'));
        }
    }

    public function completePurchase()
    {

    }

    public function authorize()
    {

    }

    public function reAuthorize()
    {

    }

    public function completeAuthorize()
    {

    }

    public function capture()
    {

    }

    public function void()
    {

    }

    public function refund()
    {

    }

    public function callback()
    {

    }
}