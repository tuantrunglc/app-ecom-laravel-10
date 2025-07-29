<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    // Danh sách yêu cầu nạp tiền
    public function deposits()
    {
        $deposits = WalletTransaction::where('type', 'deposit')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.wallet.deposits', compact('deposits'));
    }

    // Duyệt nạp tiền
    public function approveDeposit(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:500'
        ]);

        $transaction = WalletTransaction::findOrFail($id);
        
        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
        }

        $user = $transaction->user;

        try {
            DB::transaction(function () use ($transaction, $user, $request) {
                // Cập nhật số dư user
                $newBalance = $user->wallet_balance + $transaction->amount;
                $user->update(['wallet_balance' => $newBalance]);

                // Cập nhật transaction
                $transaction->update([
                    'status' => 'completed',
                    'balance_after' => $newBalance,
                    'admin_note' => $request->admin_note ?? 'Đã duyệt nạp tiền'
                ]);
            });

            return back()->with('success', 'Đã duyệt nạp tiền thành công cho user: ' . $user->name);
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi duyệt nạp tiền.');
        }
    }

    // Danh sách yêu cầu rút tiền
    public function withdrawals()
    {
        $withdrawals = WithdrawalRequest::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.wallet.withdrawals', compact('withdrawals'));
    }

    // Duyệt rút tiền
    public function approveWithdrawal(Request $request, $id)
    {
        $request->validate([
            'admin_note' => 'nullable|string|max:500'
        ]);

        $withdrawal = WithdrawalRequest::findOrFail($id);
        
        if ($withdrawal->status !== 'pending') {
            return back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
        }

        $user = $withdrawal->user;

        // Kiểm tra số dư user
        if ($user->wallet_balance < $withdrawal->amount) {
            return back()->with('error', 'Số dư user không đủ để thực hiện rút tiền.');
        }

        try {
            DB::transaction(function () use ($withdrawal, $user, $request) {
                // Trừ tiền từ ví user
                $newBalance = $user->wallet_balance - $withdrawal->amount;
                $user->update(['wallet_balance' => $newBalance]);

                // Tạo transaction rút tiền
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'withdraw',
                    'amount' => $withdrawal->amount,
                    'balance_before' => $user->wallet_balance + $withdrawal->amount,
                    'balance_after' => $newBalance,
                    'description' => 'Rút tiền về ' . $withdrawal->bank_name . ' - STK: ' . $withdrawal->bank_account,
                    'status' => 'completed',
                    'admin_note' => $request->admin_note ?? 'Đã duyệt rút tiền'
                ]);

                // Cập nhật trạng thái withdrawal
                $withdrawal->update([
                    'status' => 'completed',
                    'admin_note' => $request->admin_note ?? 'Đã duyệt rút tiền'
                ]);
            });

            return back()->with('success', 'Đã duyệt rút tiền thành công cho user: ' . $user->name);
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi duyệt rút tiền.');
        }
    }

    // Từ chối yêu cầu (deposit hoặc withdrawal)
    public function reject(Request $request, $type, $id)
    {
        $request->validate([
            'admin_note' => 'required|string|max:500'
        ], [
            'admin_note.required' => 'Vui lòng nhập lý do từ chối'
        ]);

        try {
            if ($type === 'deposit') {
                $item = WalletTransaction::findOrFail($id);
            } elseif ($type === 'withdrawal') {
                $item = WithdrawalRequest::findOrFail($id);
            } else {
                return back()->with('error', 'Loại yêu cầu không hợp lệ.');
            }

            if ($item->status !== 'pending') {
                return back()->with('error', 'Yêu cầu này đã được xử lý trước đó.');
            }

            $item->update([
                'status' => 'rejected',
                'admin_note' => $request->admin_note
            ]);

            $typeName = $type === 'deposit' ? 'nạp tiền' : 'rút tiền';
            return back()->with('success', 'Đã từ chối yêu cầu ' . $typeName . ' của user: ' . $item->user->name);
        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra khi từ chối yêu cầu.');
        }
    }
}
