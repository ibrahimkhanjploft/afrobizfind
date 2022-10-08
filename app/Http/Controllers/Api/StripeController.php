<?php

namespace App\Http\Controllers\Api;

use App\Company;
use App\Currency;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Stripe;
use Validator;
use PDF;
use Auth;
use URL;
use Session;

class StripeController extends Controller
{
    
    function payment(Request $request){
        
        try {
		$error_message =     [
			'company_id.required'             => 'Company Id should be required',
			'company_id.exists'               => 'Company Id not found',
		];
		$rules = [
			'company_id'                      => 'required|exists:companies,id',
		];

		$validator = Validator::make($request->all(), $rules, $error_message);
        
		if ($validator->fails()) {
            return response()->json(['result' => 0, 'message' => "Validation error",'errors' => $validator->errors()->messages()]);
		}


        $company = Company::find($request->company_id);        
        $currency = Currency::find($company->currency_id);

        
        
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
        
        $price = $stripe->prices->create(
                [
                    'unit_amount' => $currency->country_price * 100,
                    'currency' => $currency->currency_code,
                    'tax_behavior' => 'exclusive',
                    'product_data' => ['name' => $company->company_name],
                ]
            );

            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
           
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price' => $price->id,
                    'quantity' => 1,
                ]],
                'automatic_tax' => [
                    'enabled' => false,
                ],
                'mode' => 'payment',
                'success_url' => url('payment/success?session_id={CHECKOUT_SESSION_ID}&&company_id='.$request->company_id),
                'cancel_url' => route('payment.cancel'),
            ]);


                    if (isset($session->url)) {
                        return response()->json(['result'=>1,'payment_url'=>$session->url]);
                }

            } catch (\Throwable $e) {
                return response()->json(['result' => 0, 'message' => $e->getMessage() . ' on line ' . $e->getLine()]);
		}
            }



            public function cancel()
            {
                return redirect()->route('payment.failed_callback');
                dd('Your payment is canceled. You can create cancel page here.');
            }

    /**
     * Responds with a welcome message with instructions
     *
     * @return \Illuminate\Http\Response
     */
    public function success(Request $request)
    {
        $session_id =  $request->session_id;
            \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
            $result = \Stripe\Checkout\Session::retrieve(
                $session_id
            );

            $payment_id = $result->payment_intent;
            //echo $payment_id;die;
            $company_id = $request->company_id;

       if (true) {
       // if (in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING'])) {

                $company   = Company::find($company_id);



                $company->paypal_nonce = $payment_id;
                $today = date('Y-m-d');
                if($company->expiry_date && $company->expiry_date > $today ) {
                    // $today = $company->expiry_date;
                    $today = date('Y-m-d', strtotime($company->expiry_date. ' + 1 days'));
                }
                $expiry_date = date('Y-m-d', strtotime($today. ' + 30 days'));
                $company->expiry_date = $expiry_date;
                $company->save();

                // PDF transaction save and send email
                $company->duration_start = $today;
                $company->duration_end =  $expiry_date;
                // $result->transaction->id
                $filepath = 'company_invoce/'.md5(time().rand().time()).'.pdf';

                $invoice = new \App\Invoice();
                $invoice->user_id = $company->user_id;
                $invoice->company_id = $company->id;
                $invoice->braintree_transaction_id = $payment_id;
                $invoice->start_date = $today;
                $invoice->end_date = $expiry_date;
                $invoice->pdf = $filepath;
                $invoice->save();
                $filepath = public_path($filepath);
                $pdf = PDF::loadView('emails.invoice', ['company' =>$company])->save($filepath);



                \Mail::to($company->user->email)->send(new \App\Mail\InvoiceMail($company,$filepath));


return redirect()->route('payment.success_call_back');
                return response()->json(['result' => 1, "company" => $company]);


        }else{

            \Log::error( 'payment/make Validation Error Start ======================');
                 \Log::emergency( $request->all() );                
                  \Log::error( 'payment/make Validation Error End ======================');

                 return response()->json(['result' => 0,"message" => "Validation Error. Something went wrong" ]);

        }

        dd('Something is wrong.');
    }

    function success_call_back(){

        echo 'success';
    }


    function failed_callback(){

echo 'failed';
}

}
