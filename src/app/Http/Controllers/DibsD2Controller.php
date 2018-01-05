<?php
namespace Addgod\DibsD2\app\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class DibsD2Controller extends Controller
{

    public function purchase(Request $request, $account = 'default')
    {
        DibsD2::setDefaultAccount($account);
        $response = DibsD2::purchase($request->toArray())->send();

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