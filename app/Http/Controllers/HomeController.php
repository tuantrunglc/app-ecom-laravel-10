<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Models\Order;
use App\Models\ProductReview;
use App\Models\PostComment;
use App\Rules\MatchOldPassword;
use App\Rules\WithdrawalPinRule;
use Hash;
use App\Jobs\DeliverOrderJob;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */


    public function index(){
        return view('user.index');
    }

    public function profile(){
        $profile=Auth()->user();
        // return $profile;
        return view('user.users.profile')->with('profile',$profile);
    }

    public function profileUpdate(Request $request,$id){
        // return $request->all();
        $request->validate([
            'name' => 'required|string|max:255',
            'birth_date' => 'nullable|date|before:today',
            'age' => 'nullable|integer|min:1|max:120',
            'gender' => 'nullable|in:male,female,other',
            'address' => 'nullable|string|max:500',
            'bank_name' => 'nullable|string|max:255',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:255',
            'photo' => 'nullable|string'
        ], [
            'name.required' => 'Name is required',
            'name.max' => 'Name cannot exceed 255 characters',
            'birth_date.date' => 'Please enter a valid date',
            'birth_date.before' => 'Birth date must be before today',
            'age.integer' => 'Age must be a number',
            'age.min' => 'Age must be at least 1',
            'age.max' => 'Age cannot exceed 120',
            'gender.in' => 'Please select a valid gender',
            'address.max' => 'Address cannot exceed 500 characters',
            'bank_name.max' => 'Bank name cannot exceed 255 characters',
            'bank_account_number.max' => 'Account number cannot exceed 50 characters',
            'bank_account_name.max' => 'Account holder name cannot exceed 255 characters'
        ]);

        $user=User::findOrFail($id);
        $data=$request->all();
        $status=$user->fill($data)->save();
        if($status){
            request()->session()->flash('success','Successfully updated your profile');
        }
        else{
            request()->session()->flash('error','Please try again!');
        }
        return redirect()->back();
    }

    // Order
    public function orderIndex(){
        $orders=Order::with('shipping')->orderBy('id','DESC')->where('user_id',auth()->user()->id)->paginate(10);
        
        // Get commission data for delivered orders
        $orderNumbers = $orders->where('status', 'delivered')->pluck('order_number')->toArray();
        $commissions = [];
        
        if (!empty($orderNumbers)) {
            $walletTransactions = \App\Models\WalletTransaction::where('user_id', auth()->user()->id)
                ->where('type', 'commission')
                ->where('status', 'completed')
                ->get();
            
            foreach ($walletTransactions as $transaction) {
                foreach ($orderNumbers as $orderNumber) {
                    if (strpos($transaction->description, $orderNumber) !== false) {
                        $commissions[$orderNumber] = $transaction->amount;
                        break;
                    }
                }
            }
        }
        
        return view('user.order.index')->with('orders',$orders)->with('commissions', $commissions);
    }


    public function orderShow($id)
    {
        $order=Order::with('shipping')->find($id);
        // return $order;
        return view('user.order.show')->with('order',$order);
    }

    /**
     * Advance order status: New -> Processing immediately, then auto -> Delivered after ~10 minutes.
     */
    public function advanceOrderStatus(Request $request, $id)
    {
        $user = auth()->user();
        $order = Order::where('id', $id)->where('user_id', $user->id)->first();
        
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        if (!in_array($order->status, ['new', 'process'])) {
            return response()->json(['success' => false, 'message' => 'Invalid order status for advance'], 422);
        }

        // Wallet deduction must be atomic
        try {
            DB::transaction(function () use ($user, $order) {
                $user->refresh(); // get latest balance
                $amount = (float) ($order->total_amount ?? 0);
                $currentBalance = (float) ($user->wallet_balance ?? 0);

                if ($amount <= 0) {
                    throw new \Exception('Invalid order amount');
                }

                if ($currentBalance < $amount && $order->payment_status == 'unpaid') {
                    throw new \Exception('Insufficient wallet balance');
                }

                // Only allow advancing if payment is unpaid
                if ($order->payment_status == 'unpaid') {
                    // Deduct balance
                    $newBalance = $currentBalance - $amount;
                    $user->wallet_balance = $newBalance;
                    $user->save();
                    // Record wallet transaction
                    WalletTransaction::create([
                        'user_id' => $user->id,
                        'type' => 'purchase',
                        'amount' => $amount,
                        'balance_before' => $currentBalance,
                        'balance_after' => $newBalance,
                        'description' => 'Payment for order #' . $order->order_number,
                        'status' => 'completed',
                    ]);
                      // Update order payment status immediately
                    $order->payment_status = 'paid';
                    $order->save();
                }
              
                 // Move to processing now if currently new
                if ($order->status == 'new') {
                    $order->status = 'process';
                    $order->save();
                }

                // Dispatch a delayed job to deliver after 10 minutes
                DeliverOrderJob::dispatch($order->id)->delay(now()->addMinutes(10));

                return response()->json([
                    'success' => true,
                    'message' => 'Payment completed. Order moved to Processing.',
                    'order' => [
                        'id' => $order->id,
                        'status' => $order->status,
                        'payment_status' => $order->payment_status,
                    ]
                ]);
            });
        } catch (\Exception $e) {
            $code = $e->getMessage() === 'Insufficient wallet balance' ? 402 : 422;
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $code);
        }

       
    }
    // Product Review
    public function productReviewIndex(){
        $reviews=ProductReview::getAllUserReview();
        return view('user.review.index')->with('reviews',$reviews);
    }

    public function productReviewEdit($id)
    {
        $review=ProductReview::find($id);
        // return $review;
        return view('user.review.edit')->with('review',$review);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function productReviewUpdate(Request $request, $id)
    {
        $review=ProductReview::find($id);
        if($review){
            $data=$request->all();
            $status=$review->fill($data)->update();
            if($status){
                request()->session()->flash('success','Review Successfully updated');
            }
            else{
                request()->session()->flash('error','Something went wrong! Please try again!!');
            }
        }
        else{
            request()->session()->flash('error','Review not found!!');
        }

        return redirect()->route('user.productreview.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function productReviewDelete($id)
    {
        $review=ProductReview::find($id);
        $status=$review->delete();
        if($status){
            request()->session()->flash('success','Successfully deleted review');
        }
        else{
            request()->session()->flash('error','Something went wrong! Try again');
        }
        return redirect()->route('user.productreview.index');
    }

    public function userComment()
    {
        $comments=PostComment::getAllUserComments();
        return view('user.comment.index')->with('comments',$comments);
    }
    public function userCommentDelete($id){
        $comment=PostComment::find($id);
        if($comment){
            $status=$comment->delete();
            if($status){
                request()->session()->flash('success','Post Comment successfully deleted');
            }
            else{
                request()->session()->flash('error','Error occurred please try again');
            }
            return back();
        }
        else{
            request()->session()->flash('error','Post Comment not found');
            return redirect()->back();
        }
    }
    public function userCommentEdit($id)
    {
        $comments=PostComment::find($id);
        if($comments){
            return view('user.comment.edit')->with('comment',$comments);
        }
        else{
            request()->session()->flash('error','Comment not found');
            return redirect()->back();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function userCommentUpdate(Request $request, $id)
    {
        $comment=PostComment::find($id);
        if($comment){
            $data=$request->all();
            // return $data;
            $status=$comment->fill($data)->update();
            if($status){
                request()->session()->flash('success','Comment successfully updated');
            }
            else{
                request()->session()->flash('error','Something went wrong! Please try again!!');
            }
            return redirect()->route('user.post-comment.index');
        }
        else{
            request()->session()->flash('error','Comment not found');
            return redirect()->back();
        }

    }

    public function changePassword(){
        return view('user.layouts.userPasswordChange');
    }
    public function changPasswordStore(Request $request)
    {
        $request->validate([
            'current_password' => ['required', new MatchOldPassword],
            'new_password' => ['required'],
            'new_confirm_password' => ['same:new_password'],
        ]);
   
        User::find(auth()->user()->id)->update(['password'=> Hash::make($request->new_password)]);
   
        return redirect()->route('user')->with('success','Password successfully changed');
    }

    /**
     * Tạo mật khẩu rút tiền cho user
     */
    public function createWithdrawalPassword(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'withdrawal_password' => ['required', new WithdrawalPinRule],
            'withdrawal_password_confirmation' => 'required|same:withdrawal_password'
        ]);
        
        // Kiểm tra user đã có mật khẩu rút tiền chưa
        if ($user->hasWithdrawalPassword()) {
            return response()->json([
                'success' => false, 
                'message' => 'You already have a withdrawal password. Please contact admin if you need to change it.'
            ]);
        }
        
        $user->setWithdrawalPassword($request->withdrawal_password);
        
        return response()->json([
            'success' => true, 
            'message' => 'Withdrawal password created successfully!'
        ]);
    }

    /**
     * Thay đổi mật khẩu rút tiền - CHỈ ADMIN MỚI ĐƯỢC PHÉP
     * User không thể tự thay đổi mật khẩu rút tiền
     */
    public function changeWithdrawalPassword(Request $request)
    {
        return response()->json([
            'success' => false, 
            'message' => 'You do not have permission to change withdrawal password. Please contact admin if you need assistance.'
        ], 403);
    }

    /**
     * Xác thực mật khẩu rút tiền
     */
    public function verifyWithdrawalPassword(Request $request)
    {
        $user = auth()->user();
        
        $request->validate([
            'withdrawal_password' => 'required'
        ]);
        
        if (!$user->hasWithdrawalPassword()) {
            return response()->json([
                'success' => false, 
                'message' => 'You have not created a withdrawal password yet!',
                'need_create' => true
            ]);
        }
        
        if (!$user->checkWithdrawalPassword($request->withdrawal_password)) {
            return response()->json([
                'success' => false, 
                'message' => 'Incorrect withdrawal password!'
            ]);
        }
        
        return response()->json([
            'success' => true, 
            'message' => 'Verification successful!'
        ]);
    }
}
