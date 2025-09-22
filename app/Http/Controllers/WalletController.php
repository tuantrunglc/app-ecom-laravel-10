<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WalletTransaction;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\StatusNotification;
use App\User;

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
            $transaction = null;
            DB::transaction(function () use ($request, $user, &$transaction) {
                // Tạo yêu cầu nạp tiền với trạng thái pending
                $transaction = WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => 'deposit',
                    'amount' => $request->amount,
                    'balance_before' => $user->wallet_balance,
                    'balance_after' => $user->wallet_balance, // Chưa thay đổi vì chờ duyệt
                    'description' => 'Deposit request: ' . ($request->note ?? 'No note'),
                    'status' => 'pending'
                ]);
            });

            // Send notification to admins and sub-admins
            $this->sendDepositNotification($transaction, $user);

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
            'withdrawal_password' => 'required',
        ], [
            'amount.required' => 'Please enter amount',
            'amount.numeric' => 'Amount must be a number',
            'amount.min' => 'Minimum withdrawal amount is 50 USD',
            'withdrawal_password.required' => 'Withdrawal password is required',
        ]);

        // Kiểm tra có mật khẩu rút tiền chưa
        if (!$user->hasWithdrawalPassword()) {
            return back()->with('error', 'You need to create a withdrawal password first. Please contact admin or create one in your profile.');
        }

        // Xác thực mật khẩu rút tiền
        if (!$user->checkWithdrawalPassword($request->withdrawal_password)) {
            return back()->with('error', 'Invalid withdrawal password. Please try again.');
        }

        // Check balance
        if ($user->wallet_balance < $request->amount) {
            return back()->with('error', 'Insufficient balance to complete the transaction. Current balance: ' . $user->formatted_balance);
        }

        try {
            $withdrawalRequest = null;
            DB::transaction(function () use ($request, $user, &$withdrawalRequest) {
                // Tạo yêu cầu rút tiền sử dụng thông tin ngân hàng từ profile
                $withdrawalRequest = WithdrawalRequest::create([
                    'user_id' => $user->id,
                    'amount' => $request->amount,
                    'bank_name' => $user->bank_name,
                    'bank_account' => $user->bank_account_number,
                    'account_name' => $user->bank_account_name,
                    'status' => 'pending'
                ]);
            });

            // Send notification to admins and sub-admins
            $this->sendWithdrawalNotification($withdrawalRequest, $user);

            return redirect()->route('wallet.index')
                ->with('success', 'Withdrawal request submitted successfully. Customer service will process within 1-3 business days.');
        } catch (\Exception $e) {
            return back()->with('error', 'An error occurred. Please try again.');
        }
    }

    /**
     * Get list of users who should receive deposit notifications
     */
    private function getDepositNotificationRecipients($user)
    {
        $recipients = collect();

        // Always notify all admins
        $admins = User::where('role', 'admin')->where('status', 'active')->get();
        $recipients = $recipients->merge($admins);

        // If user has a parent sub-admin, notify them if they have deposit notification enabled
        if ($user->parent_sub_admin_id) {
            $subAdmin = User::where('id', $user->parent_sub_admin_id)
                           ->where('role', 'sub_admin')
                           ->where('status', 'active')
                           ->first();
            
            if ($subAdmin && $subAdmin->subAdminSettings && $subAdmin->subAdminSettings->notification_new_deposit) {
                $recipients->push($subAdmin);
            }
        }

        return $recipients;
    }

    /**
     * Get list of users who should receive withdrawal notifications
     */
    private function getWithdrawalNotificationRecipients($user)
    {
        $recipients = collect();

        // Always notify all admins
        $admins = User::where('role', 'admin')->where('status', 'active')->get();
        $recipients = $recipients->merge($admins);

        // If user has a parent sub-admin, notify them if they have withdrawal notification enabled
        if ($user->parent_sub_admin_id) {
            $subAdmin = User::where('id', $user->parent_sub_admin_id)
                           ->where('role', 'sub_admin')
                           ->where('status', 'active')
                           ->first();
            
            if ($subAdmin && $subAdmin->subAdminSettings && $subAdmin->subAdminSettings->notification_new_withdrawal) {
                $recipients->push($subAdmin);
            }
        }

        return $recipients;
    }

    /**
     * Send deposit notification to admins and sub-admins
     */
    private function sendDepositNotification($transaction, $user)
    {
        $recipients = $this->getDepositNotificationRecipients($user);

        if ($recipients->isNotEmpty()) {
            $details = [
                'title' => 'New Deposit Request - $' . number_format($transaction->amount, 2) . ' from ' . $user->name,
                'actionURL' => route('admin.wallet.deposits'),
                'fas' => 'fas fa-plus-circle'
            ];

            Notification::send($recipients, new StatusNotification($details));
        }
    }

    /**
     * Send withdrawal notification to admins and sub-admins
     */
    private function sendWithdrawalNotification($withdrawalRequest, $user)
    {
        $recipients = $this->getWithdrawalNotificationRecipients($user);

        if ($recipients->isNotEmpty()) {
            $details = [
                'title' => 'New Withdrawal Request - $' . number_format($withdrawalRequest->amount, 2) . ' from ' . $user->name,
                'actionURL' => route('admin.wallet.withdrawals'),
                'fas' => 'fas fa-minus-circle'
            ];

            Notification::send($recipients, new StatusNotification($details));
        }
    }

    /**
     * Admin: Form chỉnh sửa số dư ví user
     */
    public function adminEditBalance($userId)
    {
        // Chỉ admin mới có quyền
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        $user = User::findOrFail($userId);
        return view('backend.users.edit-balance', compact('user'));
    }

    /**
     * Admin: Xử lý chỉnh sửa số dư ví
     */
    public function adminUpdateBalance(Request $request, $userId)
    {
        // Chỉ admin mới có quyền
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        $request->validate([
            'action' => 'required|in:add,subtract',
            'amount' => 'required|numeric|min:0.01|max:999999',
            'reason' => 'required|string|max:500'
        ], [
            'action.required' => 'Vui lòng chọn hành động',
            'action.in' => 'Hành động không hợp lệ',
            'amount.required' => 'Vui lòng nhập số tiền',
            'amount.numeric' => 'Số tiền phải là số',
            'amount.min' => 'Số tiền tối thiểu là 0.01',
            'amount.max' => 'Số tiền tối đa là 999,999',
            'reason.required' => 'Vui lòng nhập lý do',
            'reason.max' => 'Lý do không được vượt quá 500 ký tự'
        ]);

        $user = User::findOrFail($userId);
        $admin = auth()->user();
        $amount = $request->amount;
        $action = $request->action;
        $reason = $request->reason;

        try {
            DB::transaction(function () use ($user, $admin, $amount, $action, $reason) {
                $balanceBefore = $user->wallet_balance;
                
                if ($action === 'add') {
                    // Thêm tiền vào ví
                    $balanceAfter = $balanceBefore + $amount;
                    $transactionType = 'deposit';
                    $description = "Admin deposit by {$admin->name}: {$reason}";
                } else {
                    // Trừ tiền khỏi ví
                    if ($balanceBefore < $amount) {
                        throw new \Exception('Số dư không đủ để thực hiện giao dịch');
                    }
                    $balanceAfter = $balanceBefore - $amount;
                    $transactionType = 'withdraw';
                    $description = "Admin withdrawal by {$admin->name}: {$reason}";
                }

                // Cập nhật số dư ví user
                $user->update(['wallet_balance' => $balanceAfter]);

                // Tạo bản ghi giao dịch
                WalletTransaction::create([
                    'user_id' => $user->id,
                    'type' => $transactionType,
                    'amount' => $amount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'description' => $description,
                    'status' => 'completed'
                ]);
            });

            // Gửi thông báo cho user
            $this->sendBalanceUpdateNotification($user, $action, $amount, $reason);

            return redirect()->route('users.index')
                ->with('success', "Đã {$this->getActionText($action)} ${$amount} vào ví của {$user->name}");

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Gửi thông báo cho user khi admin thay đổi số dư
     */
    private function sendBalanceUpdateNotification($user, $action, $amount, $reason)
    {
        $actionText = $action === 'add' ? 'added' : 'deducted';
        $details = [
            'title' => "Wallet Update: ${$amount} {$actionText} by Admin",
            'actionText' => $reason,
            'actionURL' => route('wallet.index'),
            'fas' => $action === 'add' ? 'fas fa-plus-circle' : 'fas fa-minus-circle'
        ];

        $user->notify(new StatusNotification($details));
    }

    /**
     * Helper: Lấy text action
     */
    private function getActionText($action)
    {
        return $action === 'add' ? 'thêm' : 'trừ';
    }
}
