<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Shipping;
use App\Models\WalletTransaction;
use App\User;
use PDF;
use Notification;
use Helper;
use Illuminate\Support\Str;
use App\Notifications\StatusNotification;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $orders=Order::orderBy('id','DESC')->paginate(10);
        return view('backend.order.index')->with('orders',$orders);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.order.create');
    }

    /**
     * Search for user by ID or email
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function searchUser(Request $request)
    {
        $search = $request->search;
        
        // Try to find user by ID first, then by email
        $user = User::where('id', $search)
                   ->orWhere('email', 'like', '%' . $search . '%')
                   ->first();
        
        if ($user) {
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $user->status,
                    'wallet_balance' => $user->wallet_balance ?? 0
                ]
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => 'User not found'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
            'user_id'=>'required|exists:users,id',
            'first_name'=>'string|required',
            'last_name'=>'string|required',
            'address1'=>'string|required',
            'address2'=>'string|nullable',
            'country'=>'string|required',
            'phone'=>'string|required',
            'post_code'=>'string|nullable',
            'email'=>'string|required|email',
            'shipping'=>'required|exists:shippings,id',
            'payment_method'=>'required|in:wallet',
            'status'=>'required|in:new,process,delivered,cancel',
            'sub_total'=>'required|numeric|min:0',
            'quantity'=>'required|integer|min:1',
            'total_amount'=>'required|numeric|min:0'
        ]);

        // Check if creating order from admin panel (no cart required)
        $isAdminOrder = $request->has('user_id') && $request->user_id != auth()->id();
        
        // Check if this is a Buy Now order
        $isBuyNow = $request->has('buy_now_mode') && $request->buy_now_mode == 1;
        
        if (!$isAdminOrder && !$isBuyNow && empty(Cart::where('user_id',auth()->user()->id)->where('order_id',null)->first())){
            request()->session()->flash('error','Cart is Empty !');
            return back();
        }
        // $cart=Cart::get();
        // // return $cart;
        // $cart_index='ORD-'.strtoupper(uniqid());
        // $sub_total=0;
        // foreach($cart as $cart_item){
        //     $sub_total+=$cart_item['amount'];
        //     $data=array(
        //         'cart_id'=>$cart_index,
        //         'user_id'=>$request->user()->id,
        //         'product_id'=>$cart_item['id'],
        //         'quantity'=>$cart_item['quantity'],
        //         'amount'=>$cart_item['amount'],
        //         'status'=>'new',
        //         'price'=>$cart_item['price'],
        //     );

        //     $cart=new Cart();
        //     $cart->fill($data);
        //     $cart->save();
        // }

        // $total_prod=0;
        // if(session('cart')){
        //         foreach(session('cart') as $cart_items){
        //             $total_prod+=$cart_items['quantity'];
        //         }
        // }

        $order=new Order();
        $order_data=$request->all();
        $order_data['order_number']='ORD-'.strtoupper(Str::random(10));
        
        // Set user_id based on whether it's admin order, buy now, or regular order
        if ($isAdminOrder) {
            $order_data['user_id'] = $request->user_id;
            // For admin orders, use provided values
            $order_data['sub_total'] = $request->sub_total;
            $order_data['quantity'] = $request->quantity;
            $order_data['total_amount'] = $request->total_amount;
        } elseif ($isBuyNow) {
            $order_data['user_id'] = $request->user()->id;
            // For Buy Now orders, use session data
            $buyNowItem = session('buy_now');
            $order_data['sub_total'] = $buyNowItem['amount'];
            $order_data['quantity'] = $buyNowItem['quantity'];
            
            $shipping_cost = 0;
            if($request->shipping){
                $shipping = Shipping::where('id', $request->shipping)->pluck('price');
                $shipping_cost = $shipping[0] ?? 0;
            }
            
            if(session('coupon')){
                $order_data['coupon'] = session('coupon')['value'];
                $order_data['total_amount'] = $buyNowItem['amount'] + $shipping_cost - session('coupon')['value'];
            } else {
                $order_data['total_amount'] = $buyNowItem['amount'] + $shipping_cost;
            }
        } else {
            $order_data['user_id'] = $request->user()->id;
            // For regular orders, calculate from cart
            $order_data['sub_total'] = Helper::totalCartPrice();
            $order_data['quantity'] = Helper::cartCount();
            
            $shipping = Shipping::where('id', $request->shipping)->pluck('price');
            if(session('coupon')){
                $order_data['coupon'] = session('coupon')['value'];
            }
            if($request->shipping){
                if(session('coupon')){
                    $order_data['total_amount'] = Helper::totalCartPrice() + $shipping[0] - session('coupon')['value'];
                }
                else{
                    $order_data['total_amount'] = Helper::totalCartPrice() + $shipping[0];
                }
            }
            else{
                if(session('coupon')){
                    $order_data['total_amount'] = Helper::totalCartPrice() - session('coupon')['value'];
                }
                else{
                    $order_data['total_amount'] = Helper::totalCartPrice();
                }
            }
        }
        
        $order_data['shipping_id'] = $request->shipping;
        
        // Get the user who will pay for this order
        $payingUser = null;
        if ($isAdminOrder) {
            $payingUser = User::find($request->user_id);
        } else {
            $payingUser = $request->user();
        }
        
        // Check wallet balance and process payment
        if($request->payment_method == 'wallet'){
            $totalAmount = $order_data['total_amount'];
            $currentBalance = $payingUser->wallet_balance ?? 0;
            
            if($currentBalance < $totalAmount){
                request()->session()->flash('error','Insufficient wallet balance. Your balance: $' . number_format($currentBalance, 2) . ', Required: $' . number_format($totalAmount, 2) . '. Please add funds to your wallet before placing the order.');
                return back();
            }
            
            // Deduct money from wallet
            $payingUser->wallet_balance = $currentBalance - $totalAmount;
            $payingUser->save();
            
            $order_data['payment_status'] = 'paid';
        } else {
            $order_data['payment_status'] = 'Unpaid';
        }
        
        $order->fill($order_data);
        $status=$order->save();
        
        if($order) {
            // Send notification to admin
            $users=User::where('role','admin')->first();
            $details=[
                'title'=>'New order created',
                'actionURL'=>route('order.show',$order->id),
                'fas'=>'fa-file-alt'
            ];
            Notification::send($users, new StatusNotification($details));
            
            if ($isAdminOrder) {
                // For admin created orders, redirect back to orders list
                request()->session()->flash('success','Đơn hàng đã được tạo thành công cho user ID: ' . $request->user_id);
                return redirect()->route('order.index');
            } elseif ($isBuyNow) {
                // For Buy Now orders from frontend
                $buyNowItem = session('buy_now');
                
                // Clear buy now session
                session()->forget('buy_now');
                session()->forget('coupon');
                
                // Create cart entry for this buy now item linked to the order
                $cart = new Cart;
                $cart->user_id = auth()->user()->id;
                $cart->product_id = $buyNowItem['product_id'];
                $cart->order_id = $order->id;
                $cart->quantity = $buyNowItem['quantity'];
                $cart->price = $buyNowItem['discount_price'];
                $cart->amount = $buyNowItem['amount'];
                $cart->save();
                
                request()->session()->flash('success','Your product successfully placed in order. Payment deducted from wallet.');
                return redirect()->route('home');
            } else {
                // For regular orders from frontend
                session()->forget('cart');
                session()->forget('coupon');
                
                Cart::where('user_id', auth()->user()->id)->where('order_id', null)->update(['order_id' => $order->id]);
                request()->session()->flash('success','Your product successfully placed in order. Payment deducted from wallet.');
                return redirect()->route('home');
            }
        }
        
        request()->session()->flash('error','Có lỗi xảy ra khi tạo đơn hàng');
        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $order=Order::find($id);
        // return $order;
        return view('backend.order.show')->with('order',$order);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $order=Order::find($id);
        return view('backend.order.edit')->with('order',$order);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $order=Order::find($id);
        $this->validate($request,[
            'status'=>'required|in:new,process,delivered,cancel'
        ]);
        $data=$request->all();
        // return $request->status;
        $oldStatus = $order->status;
        if($request->status=='delivered'){
            foreach($order->cart as $cart){
                $product=$cart->product;
                // return $product;
                $product->stock -=$cart->quantity;
                $product->save();
            }
            
            // Add commission to wallet if status changed from non-delivered to delivered
            if($oldStatus != 'delivered') {
                $this->processCommission($order);
            }
        }
        $status=$order->fill($data)->save();
        if($status){
            request()->session()->flash('success','Successfully updated order');
        }
        else{
            request()->session()->flash('error','Error while updating order');
        }
        return redirect()->route('order.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $order=Order::find($id);
        if($order){
            $status=$order->delete();
            if($status){
                request()->session()->flash('success','Order Successfully deleted');
            }
            else{
                request()->session()->flash('error','Order can not deleted');
            }
            return redirect()->route('order.index');
        }
        else{
            request()->session()->flash('error','Order can not found');
            return redirect()->back();
        }
    }

    public function orderTrack(){
        return view('frontend.pages.order-track');
    }

    public function productTrackOrder(Request $request){
        // return $request->all();
        $order=Order::where('user_id',auth()->user()->id)->where('order_number',$request->order_number)->first();
        if($order){
            if($order->status=="new"){
            request()->session()->flash('success','Your order has been placed. please wait.');
            return redirect()->route('home');

            }
            elseif($order->status=="process"){
                request()->session()->flash('success','Your order is under processing please wait.');
                return redirect()->route('home');
    
            }
            elseif($order->status=="delivered"){
                request()->session()->flash('success','Your order is successfully delivered.');
                return redirect()->route('home');
    
            }
            else{
                request()->session()->flash('error','Your order canceled. please try again');
                return redirect()->route('home');
    
            }
        }
        else{
            request()->session()->flash('error','Invalid order numer please try again');
            return back();
        }
    }

    // PDF generate
    public function pdf(Request $request){
        $order=Order::getAllOrder($request->id);
        // return $order;
        $file_name=$order->order_number.'-'.$order->first_name.'.pdf';
        // return $file_name;
        $pdf=PDF::loadview('backend.order.pdf',compact('order'));
        return $pdf->download($file_name);
    }
    // Income chart
    public function incomeChart(Request $request){
        $year=\Carbon\Carbon::now()->year;
        // dd($year);
        $items=Order::with(['cart_info'])->whereYear('created_at',$year)->where('status','delivered')->get()
            ->groupBy(function($d){
                return \Carbon\Carbon::parse($d->created_at)->format('m');
            });
            // dd($items);
        $result=[];
        foreach($items as $month=>$item_collections){
            foreach($item_collections as $item){
                $amount=$item->cart_info->sum('amount');
                // dd($amount);
                $m=intval($month);
                // return $m;
                isset($result[$m]) ? $result[$m] += $amount :$result[$m]=$amount;
            }
        }
        $data=[];
        for($i=1; $i <=12; $i++){
            $monthName=date('F', mktime(0,0,0,$i,1));
            $data[$monthName] = (!empty($result[$i]))? number_format((float)($result[$i]), 2, '.', '') : 0.0;
        }
        return $data;
    }

    /**
     * Process commission when order is delivered
     */
    private function processCommission($order)
    {
        $user = $order->user;
        $totalCommission = 0;
        $commissionDetails = [];

        // Calculate commission for each product in the order
        foreach($order->cart as $cart) {
            $product = $cart->product;
            if($product && $product->commission > 0) {
                // Calculate commission amount: (product price * quantity * commission percentage / 100)
                $commissionAmount = ($cart->price * $cart->quantity * $product->commission) / 100;
                $totalCommission += $commissionAmount;
                
                $commissionDetails[] = [
                    'product' => $product->title,
                    'quantity' => $cart->quantity,
                    'price' => $cart->price,
                    'commission_rate' => $product->commission,
                    'commission_amount' => $commissionAmount
                ];
            }
        }

        // Add commission to user's wallet if there's any commission
        if($totalCommission > 0) {
            $balanceBefore = $user->wallet_balance ?? 0;
            $balanceAfter = $balanceBefore + $totalCommission;
            
            // Update user's wallet balance
            $user->wallet_balance = $balanceAfter;
            $user->save();

            // Create wallet transaction record
            WalletTransaction::create([
                'user_id' => $user->id,
                'type' => 'commission',
                'amount' => $totalCommission,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => 'Product commission from order #' . $order->order_number . '. Details: ' . json_encode($commissionDetails),
                'status' => 'completed'
            ]);

            // Update sub admin stats if user has parent sub admin
            if($user->parent_sub_admin_id) {
                $this->updateSubAdminCommissionStats($user->parent_sub_admin_id, $totalCommission);
            }
        }
    }

    /**
     * Update sub admin commission statistics
     */
    private function updateSubAdminCommissionStats($subAdminId, $commissionAmount)
    {
        $subAdminStats = \App\Models\SubAdminUserStats::firstOrCreate(
            ['sub_admin_id' => $subAdminId],
            [
                'total_users' => 0,
                'active_users' => 0,
                'total_orders' => 0,
                'total_revenue' => 0,
                'commission_earned' => 0,
                'last_updated' => now()
            ]
        );

        $subAdminStats->commission_earned += $commissionAmount;
        $subAdminStats->last_updated = now();
        $subAdminStats->save();
    }
}
