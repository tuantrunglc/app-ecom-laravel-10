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

        return view('user.wallet.index', compact('user', 'transactions', 'withdrawals'));
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
                    'description' => 'Yêu cầu nạp tiền: ' . ($request->note ?? 'Không có ghi chú'),
                    'status' => 'pending'
                ]);
            });

            // Check if request came from frontend or dashboard
            $redirectRoute = $request->has('from_frontend') ? 'deposit.request' : 'wallet.index';
            $successMessage = $request->has('from_frontend') 
                ? 'Deposit request submitted successfully! Our customer service team will contact you shortly.'
                : 'Yêu cầu nạp tiền đã được gửi thành công. CSKH sẽ liên hệ với bạn sớm.';

            return redirect()->route($redirectRoute)
                ->with('success', $successMessage);
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra. Vui lòng thử lại.');
        }
    }

    // Form yêu cầu rút tiền
    public function withdrawForm()
    {
        $user = Auth::user();
        return view('user.wallet.withdraw', compact('user'));
    }

    // Xử lý yêu cầu rút tiền
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:50',
            'bank_name' => 'required|string|max:255',
            'bank_account' => 'required|string|max:100',
            'account_name' => 'required|string|max:255',
        ], [
            'amount.required' => 'Please enter amount',
            'amount.numeric' => 'Amount must be a number',
            'amount.min' => 'Minimum withdrawal amount is 50 USD',
            'bank_name.required' => 'Please enter bank name',
            'bank_account.required' => 'Please enter account number',
            'account_name.required' => 'Please enter account holder name',
        ]);

        $user = Auth::user();

        // Kiểm tra số dư
        if ($user->wallet_balance < $request->amount) {
            return back()->with('error', 'Số dư không đủ để thực hiện giao dịch. Số dư hiện tại: ' . $user->formatted_balance);
        }

        try {
            DB::transaction(function () use ($request, $user) {
                // Tạo yêu cầu rút tiền
                WithdrawalRequest::create([
                    'user_id' => $user->id,
                    'amount' => $request->amount,
                    'bank_name' => $request->bank_name,
                    'bank_account' => $request->bank_account,
                    'account_name' => $request->account_name,
                    'status' => 'pending'
                ]);
            });

            return redirect()->route('wallet.index')
                ->with('success', 'Yêu cầu rút tiền đã được gửi thành công. CSKH sẽ xử lý trong 1-3 ngày làm việc.');
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra. Vui lòng thử lại.');
        }
    }
}
