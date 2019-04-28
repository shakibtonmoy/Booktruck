<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use Session;
use Cart;
use Illuminate\Support\Facades\Redirect;
session_start();

class CheckoutController extends Controller
{
    public function login_check()
    {
    	return view('pages.login');
    }
    public function customer_registration(Request $request)
    {
    	$data=array();
    	$data['customer_name']=$request->customer_name;
    	$data['customer_email']=$request->customer_email;
    	$data['password']=sha1($request->password);
    	$data['mobile_number']=$request->mobile_number;

    	$customer_id=DB::table('tbl_customer')
    				->insertGetId($data);

    			Session::put('customer_id',$customer_id);
    			Session::put('customer_name',$request->customer_name);
    			return Redirect('/checkout');

    }
    public function checkout()
    {
    	return view('pages.checkout');
    }
    public function save_shipping_details(Request $request)
    {
    	$data=array();
    	$data['shipping_email']=$request->shipping_email;
    	$data['shipping_first_name']=$request->shipping_first_name;
    	$data['shipping_last_name']=$request->shipping_last_name;
    	$data['shipping_address']=$request->shipping_address;
    	$data['shipping_mobile_number']=$request->shipping_mobile_number;

    	$shipping_id=DB::table('tbl_shipping')
    				->insertGetId($data);
    				Session::put('shipping_id',$shipping_id);
    				return Redirect::to('/payment');

    	// echo "<pre>";
    	// print_r($data);
    	// echo "</pre>";
    }
    public function customer_login(Request $request)
    {
    	$customer_email=$request->customer_email;
    	$password=sha1($request->password);

    	$result=DB::table('tbl_customer')
    			->where('customer_email',$customer_email)
    			->where('password',$password)
    			->first();

    			if($result)
    			{

    				Session::put('customer_id',$result->customer_id);
    				return Redirect::to('/');
    			}
    			else
    			{
    				return Redirect::to('/login-check');
    			}
    }

    public function payment()
    {
    	return view('pages.payment');
    }
    public function order_place(Request $request)
    {
    	$payment_gateway=$request->payment_method;


    	$pdata=array();
    	$pdata['payment_method']=$payment_gateway;
    	$pdata['payment_status']='pending';
		$payment_id=DB::table('tbl_payment')
    			->insertGetId($pdata);

    	$odata=array();
    	$odata['customer_id']=Session::get('customer_id');
    	$odata['shipping_id']=Session::get('shipping_id');
    	$odata['payment_id']=$payment_id;
    	$odata['order_total']=Cart::total();
    	$odata['order_status']='pending';

    	$order_id=DB::table('tbl_order')
    			 ->insertGetId($odata);

    	$contents=Cart::content();
    	$oddata=array();

    	foreach ($contents as $v_content) 
    	{
    		$oddata['order_id']=$order_id;
    		$oddata['product_id']=$v_content->id;
    		$oddata['product_name']=$v_content->name;
    		$oddata['product_price']=$v_content->price;
    		$oddata['product_sales_quantity']=$v_content->qty;

    		DB::table('tbl_order_details')
    			->insert($oddata);

    	}

    	if($payment_gateway=='handcash')
    	{
    		echo "successfully done by handcash";
    	}
    	elseif($payment_gateway=='cart')
    	{
    		echo "cart";
    	}
    	elseif($payment_gateway=='bkash')
    	{
    		echo "bkash";
    	}
    	else
    	{
    		echo "not selected";
    	}
    	

    }

    public function customer_logout()
    {
    	Session::flush();
    	return \Redirect::to('/');
    }
}
