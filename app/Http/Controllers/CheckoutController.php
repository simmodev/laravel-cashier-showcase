<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function index($id){
        $product = Product::where('id', $id)->first();

        $order = $product->orders()->create([
            'user_id'=>auth()->user()->id,
            'price'=>$product->price,
        ]);
        $payment_intent = auth()->user()->createSetupIntent();
        return view('checkout', ['product'=>$product, 'order'=>$order, 'payment_intent'=>$payment_intent]);
    }

    public function pay(Request $request){
        $order = Order::where('user_id', auth()->user()->id)->findOrFail($request->order_id);
        $user = auth()->user();
        $paymentMethod = $request->payment_method;
        try{
            $user->createOrGetStripeCustomer();
            $user->updateDefaultPaymentMethod($paymentMethod);
            $user->charge($order->price*100, $paymentMethod);
            $order->update(['paid_at'=>now()]);
        }catch(\Exception $ex){
            return back()->with('error', $ex->getMessage());
        }

        return redirect()->route('thank-you');
    }

    public function success(){
        return view('thank-you');
    }
}
