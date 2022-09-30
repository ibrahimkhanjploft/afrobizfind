<?php

namespace App\Http\Controllers\Api;

use PDF;
use Auth;
use Braintree;
use Validator;
use App\Company;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Controller;

class PaymentsController extends Controller
{

    public $gateway;
    public function __construct() {
          $this->gateway = new Braintree\Gateway([
                'environment' => 'sandbox',
                'merchantId' => 'ywskmmxn4mt5v5nv',
                'publicKey' => 'g84zvqvgp6xrywmb',
                'privateKey' => '61dc3e1d6bab92b7b85f4f543f4a8f55'
            ]);
    }

    public function gettoken(Request $request)
    {
        $user = Auth::user();
        
        if(!$user->braintree_customer_id) {
            $result = $this->gateway->customer()->create([
                'firstName' => $user->first_name,
                'lastName' => $user->surname,
                'email' => $user->email
            ]);
            if($result->success){
                $user->braintree_customer_id = $result->customer->id;
                $user->save();
            }
        }
        if($user->braintree_customer_id) {
            $token = $this->gateway->clientToken()->generate(["customerId" => $user->braintree_customer_id,'merchantAccountId' => 'Afrobizfind_Wiiliam']);
            return response()->json(['result' => 1,'token'=> $token ]);
        }
        return response()->json(['result' => 0,'message'=> 'Someting went wrong' ]);
    }    

    public function make(Request $request)
    {
        try {
            $user = Auth::user();
            $result = $this->gateway->transaction()->sale([
                'amount' => '5.00',
                'taxAmount' => '1.00',
                'merchantAccountId' => 'Afrobizfind_Wiiliam',
                'customerId' => $user->braintree_customer_id,
                'paymentMethodNonce' => $request->nonce,
                'options' => [ 'submitForSettlement' => true ]
            ]);
            //$result = (object)['success' => 1,'transaction' => (object)['id'=>1]];
            if ($result->success) {

                $company   = Company::find($request->id);
                $company->paypal_nonce = $request->nonce;
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
                $invoice->braintree_transaction_id = $result->transaction->id;
                $invoice->start_date = $today;
                $invoice->end_date = $expiry_date;
                $invoice->pdf = $filepath;
                $invoice->save();
                $filepath = public_path($filepath);
                $pdf = PDF::loadView('emails.invoice', ['company' =>$company])->save($filepath);
                  

                
                \Mail::to($company->user->email)->send(new \App\Mail\InvoiceMail($company,$filepath));
                


                return response()->json(['result' => 1, "company" => $company]);
            } else if ($result->transaction) {
                 \Log::error( 'payment/make transaction Error Start ======================');
                 \Log::emergency( $request->all() );
                 \Log::emergency( $result->transaction->processorResponseCode );
                 \Log::emergency( $result->transaction->processorResponseText );
                 \Log::error( 'payment/make transaction Error End ======================');
                return response()->json(['result' => 0,"message" => "transaction Failed. Something went wrong" ]);
                /* print_r("Error processing transaction:");
                print_r("\n  code: " . $result->transaction->processorResponseCode);
                print_r("\n  text: " . $result->transaction->processorResponseText);*/
            } else {
                 \Log::error( 'payment/make Validation Error Start ======================');
                 \Log::emergency( $request->all() );
                 \Log::emergency( $result->errors->deepAll() );
                  \Log::error( 'payment/make Validation Error End ======================');

                 return response()->json(['result' => 0,"message" => "Validation Error. Something went wrong" ]);
                /* print_r("Validation errors: \n");
                print_r($result->errors->deepAll());*/
            }

        } catch (Braintree\Exception\Authorization $e) {
             \Log::error( 'payment/make Validation Error Start ======================');
             \Log::emergency( $request->all() );
             \Log::emergency( 'Braintree\Exception\Authorization' );
             \Log::error( 'payment/make Validation Error End ======================');
             return response()->json(['result' => 0,"message" => "Authorization Error. Something went wrong" ]);
        }
    }

}