<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Trang ví chính - hiển thị số dư và lịch sử
    public function index()
    {
        $user = Auth::user();
        $transactions = $user->walletTransactions()->paginate(10);
        $withdrawals = $user->withdrawalRequests()->paginate(5);
        
        // Kiểm tra xem user đã liên kết ngân hàng chưa
        $hasBankInfo = !empty($user->bank_name) && !empty($user->bank_account_number) && !empty($user->bank_account_name);

        return view('user.wallet.index', compact('user', 'transactions', 'withdrawals', 'hasBankInfo'));
    }

    // Form yêu cầu nạp tiền (frontend)
    public function frontendDepositForm()
    {
        return view('frontend.pages.deposit');
    }

    // Form yêu cầu nạp tiền (user dashboard)
    public function depositForm()
    {
        return view('user.wallet.deposit');
    }

    // Xử lý yêu cầu nạp tiền
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10|max:50000',
            'note' => 'nullable|string|max:500'
        ], [
            'amount.required' => 'Please enter amount',
            'amount.numeric' => 'Amount must be a number',
            'amount.min' => 'Minimum amount is 10 USD',
            'amount.max' => 'Maximum amount is 50,000 USD',
            'note.max' => 'Note cannot exceed 500 characters'
        ]);

        $user = Auth::user();

        try {
            DB::transaction(function () use ($request, $user) {
                // Tạo yêu cầu nạp tiền với trạng thái pending
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'deposit',
                    'amount' => $request->amount,
                    'balance_before' => $user->wallet_balance,
                    'balance_after' => $user->wallet_balance, // Chưa thay đổi vì chờ duyệt
                    'description' => 'Deposit request: ' . ($request->note ?? 'No note'),
                    'status' => 'pending'
                ]);
            });

            // Check if request came from frontend or dashboard
            $redirectRoute = $request->has('from_frontend') ? 'deposit.request' : 'wallet.index';
            $successMessage = $request->has('from_frontend') 
                ? 'Deposit request submitted successfully! Our customer service team will contact you shortly.'
                : 'Deposit request submitted successfully. Customer service will contact you soon.';

            return redirect()->route($redirectRoute)
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred. Please try again.');
        }
    }

    // Form yêu cầu rút tiền
    public function withdrawForm()
    {
        $user = Auth::user();
        
        // Kiểm tra xem user đã liên kết ngân hàng chưa
        $hasBankInfo = !empty($user->bank_name) && !empty($user->bank_account_number) && !empty($user->bank_account_name);
        
        return view('user.wallet.withdraw', compact('user', 'hasBankInfo'));
    }

    // Xử lý yêu cầu rút tiền
    public function withdraw(Request $request)
    {
        $user = Auth::user();
        
        // Kiểm tra xem user đã liên kết ngân hàng chưa
        $hasBankInfo = !empty($user->bank_name) && !empty($user->bank_account_number) && !empty($user->bank_account_name);
        
        if (!$hasBankInfo) {
            return redirect()->route('user-profile')
                ->with('error', 'You need to link your bank information before withdrawing money. Please update your information in the Profile page.');
        }

        $request->validate([
            'amount' => 'required|numeric|min:50',
        ], [
            'amount.required' => 'Please enter amount',
            'amount.numeric' => 'Amount must be a number',
            'amount.min' => 'Minimum withdrawal amount is 50 USD',
        ]);

        // Check balance
        if ($user->wallet_balance < $request->amount) {
            return back()->with('error', 'Insufficient balance to complete the transaction. Current balance: ' . $user->formatted_balance);
        }

        try {
            DB::transaction(function () use ($request, $user) {
                // Tạo yêu cầu rút tiền sử dụng thông tin ngân hàng từ profile
                WithdrawalRequest::create([
                    'user_id' => $user->id,
                    'amount' => $request->amount,
                    'bank_name' => $user->bank_name,
                    'bank_account' => $user->bank_account_number,
                    'account_name' => $user->bank_account_name,
                    'status' => 'pending'
                ]);
            });

            return redirect()->route('wallet.index')
                ->with('success', 'Withdrawal request submitted successfully. Customer service will process within 1-3 business days.');
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred. Please try again.');
        }
    }
}
