<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Rules\WithdrawalPinRule;
class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // Nếu là AJAX request từ DataTable
        if ($request->ajax()) {
            $query = User::query();
            
            // Xử lý tìm kiếm
            if ($request->has('search') && $request->search['value'] != '') {
                $search = $request->search['value'];
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('role', 'LIKE', "%{$search}%")
                      ->orWhere('status', 'LIKE', "%{$search}%");
                });
            }
            
            // Xử lý sắp xếp
            if ($request->has('order')) {
                $orderColumn = $request->order[0]['column'];
                $orderDir = $request->order[0]['dir'];
                
                $columns = ['id', 'name', 'email', 'photo', 'created_at', 'role', 'status', 'action'];
                if (isset($columns[$orderColumn])) {
                    $query->orderBy($columns[$orderColumn], $orderDir);
                }
            } else {
                $query->orderBy('id', 'DESC');
            }
            
            // Tổng số bản ghi
            $totalRecords = User::count();
            $filteredRecords = $query->count();
            
            // Phân trang
            $start = $request->start ?? 0;
            $length = $request->length ?? 10;
            $users = $query->skip($start)->take($length)->get();
            
            // Format dữ liệu cho DataTable
            $data = [];
            foreach ($users as $user) {
                $statusBadge = $user->status == 'active' 
                    ? '<span class="badge badge-success">'.$user->status.'</span>'
                    : '<span class="badge badge-warning">'.$user->status.'</span>';
                
                $photo = $user->photo 
                    ? '<img src="'.$user->photo.'" class="img-fluid rounded-circle" style="max-width:50px" alt="'.$user->photo.'">'
                    : '<img src="'.asset('backend/img/avatar.png').'" class="img-fluid rounded-circle" style="max-width:50px" alt="avatar.png">';
                
                $nameWithVip = '<div>'.$user->name.'</div>';
                if ($user->vip_level_name) {
                    $nameWithVip .= '<div><span class="badge" style="background-color: '.($user->vip_color ?? '#007bff').'; color: #fff;">VIP: '.$user->vip_level_name.'</span>';
                    $nameWithVip .= '<small class="text-muted ml-1">Remaining today: '.($user->remaining_purchases_today ?? 0).' / Limit: '.($user->daily_purchase_limit ?? 0).'</small></div>';
                }
                
                // Action buttons
                $actionButtons = '<div class="d-flex flex-wrap">';
                $actionButtons .= '<button class="btn btn-info btn-sm mr-1 mb-1 edit-user-btn" data-id="'.$user->id.'" data-toggle="tooltip" title="Chỉnh sửa thông tin"><i class="fas fa-user-edit"></i></button>';
                $actionButtons .= '<button class="btn btn-warning btn-sm mr-1 mb-1 change-password-btn" data-id="'.$user->id.'" data-toggle="tooltip" title="Đổi mật khẩu"><i class="fas fa-key"></i></button>';
                
                $statusBtnClass = $user->status == 'active' ? 'btn-secondary' : 'btn-success';
                $statusIcon = $user->status == 'active' ? 'fa-lock' : 'fa-unlock';
                $statusTitle = $user->status == 'active' ? 'Khóa tài khoản' : 'Mở khóa tài khoản';
                $actionButtons .= '<button class="btn btn-sm mr-1 mb-1 toggle-status-btn '.$statusBtnClass.'" data-id="'.$user->id.'" data-status="'.$user->status.'" data-toggle="tooltip" title="'.$statusTitle.'"><i class="fas '.$statusIcon.'"></i></button>';
                
                if ($user->withdrawal_password) {
                    $actionButtons .= '<button class="btn btn-success btn-sm mr-1 mb-1 change-withdrawal-password-btn" data-id="'.$user->id.'" data-toggle="tooltip" title="Thay đổi mật khẩu rút tiền"><i class="fas fa-shield-alt"></i></button>';
                } else {
                    $actionButtons .= '<button class="btn btn-warning btn-sm mr-1 mb-1 create-withdrawal-password-btn" data-id="'.$user->id.'" data-toggle="tooltip" title="Tạo mật khẩu rút tiền"><i class="fas fa-plus-circle"></i></button>';
                }
                
                // Add wallet edit button for admin only
                $actionButtons .= '<a href="'.route('admin.wallet.edit-balance', $user->id).'" class="btn btn-success btn-sm mr-1 mb-1" data-toggle="tooltip" title="Chỉnh sửa số dư ví"><i class="fas fa-wallet"></i></a>';
                
                $actionButtons .= '<a href="'.route('users.edit', $user->id).'" class="btn btn-primary btn-sm mr-1 mb-1" data-toggle="tooltip" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>';
                $actionButtons .= '<form method="POST" action="'.route('users.destroy', $user->id).'" style="display: inline;">';
                $actionButtons .= csrf_field();
                $actionButtons .= method_field('delete');
                $actionButtons .= '<button class="btn btn-danger btn-sm mb-1 dltBtn" data-id="'.$user->id.'" data-toggle="tooltip" title="Xóa"><i class="fas fa-trash-alt"></i></button>';
                $actionButtons .= '</form></div>';
                
                $data[] = [
                    'id' => $user->id,
                    'name' => $nameWithVip,
                    'email' => $user->email,
                    'photo' => $photo,
                    'created_at' => $user->created_at ? $user->created_at->diffForHumans() : '',
                    'role' => $user->role,
                    'status' => $statusBadge,
                    'action' => $actionButtons
                ];
            }
            
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredRecords,
                'data' => $data
            ]);
        }
        
        // Nếu không phải AJAX, trả về view thường
        $users = User::orderBy('id','ASC')->paginate(10);
        return view('backend.users.index')->with('users',$users);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,
        [
            'name'=>'string|required|max:30',
            'email'=>'string|required|unique:users',
            'password'=>'string|required',
            'role'=>'required|in:admin,user',
            'status'=>'required|in:active,inactive',
            'photo'=>'nullable|string',
        ]);
        // dd($request->all());
        $data=$request->all();
        $data['password']=Hash::make($request->password);
        // dd($data);
        $status=User::create($data);
        // dd($status);
        if($status){
            request()->session()->flash('success','Successfully added user');
        }
        else{
            request()->session()->flash('error','Error occurred while adding user');
        }
        return redirect()->route('users.index');

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user=User::findOrFail($id);
        return view('backend.users.edit')->with('user',$user);
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
        $user=User::findOrFail($id);
        $this->validate($request,
        [
            'name'=>'string|required|max:30',
            'email'=>'string|required',
            'role'=>'required|in:admin,user',
            'status'=>'required|in:active,inactive',
            'photo'=>'nullable|string',
        ]);
        // dd($request->all());
        $data=$request->all();
        // dd($data);
        
        $status=$user->fill($data)->save();
        if($status){
            request()->session()->flash('success','Successfully updated');
        }
        else{
            request()->session()->flash('error','Error occured while updating');
        }
        return redirect()->route('users.index');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $delete=User::findorFail($id);
        $status=$delete->delete();
        if($status){
            request()->session()->flash('success','User Successfully deleted');
        }
        else{
            request()->session()->flash('error','There is an error while deleting users');
        }
        return redirect()->route('users.index');
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Check permission
        if (!auth()->user()->canManageUser($id)) {
            return response()->json(['error' => 'Bạn không có quyền thay đổi mật khẩu user này'], 403);
        }

        $this->validate($request, [
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user->password = Hash::make($request->new_password);
        $status = $user->save();

        if ($status) {
            return response()->json(['success' => 'Đã thay đổi mật khẩu thành công']);
        } else {
            return response()->json(['error' => 'Có lỗi xảy ra khi thay đổi mật khẩu'], 500);
        }
    }

    /**
     * Toggle user account status (lock/unlock)
     */
    public function toggleStatus(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Check permission
        if (!auth()->user()->canManageUser($id)) {
            return response()->json(['error' => 'Bạn không có quyền thay đổi trạng thái user này'], 403);
        }

        // Toggle status
        $newStatus = $user->status === 'active' ? 'inactive' : 'active';
        $user->status = $newStatus;
        $status = $user->save();

        if ($status) {
            $message = $newStatus === 'active' ? 'Đã mở khóa tài khoản' : 'Đã khóa tài khoản';
            return response()->json([
                'success' => $message,
                'new_status' => $newStatus,
                'status_badge' => $newStatus === 'active' 
                    ? '<span class="badge badge-success">active</span>' 
                    : '<span class="badge badge-warning">inactive</span>'
            ]);
        } else {
            return response()->json(['error' => 'Có lỗi xảy ra khi thay đổi trạng thái'], 500);
        }
    }

    /**
     * Show user details for editing
     */
    public function showDetails($id)
    {
        $user = User::findOrFail($id);
        
        // Check permission
        if (!auth()->user()->canManageUser($id)) {
            return response()->json(['error' => 'Bạn không có quyền xem thông tin user này'], 403);
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'status' => $user->status,
                'photo' => $user->photo,
                'wallet_balance' => $user->wallet_balance,
                'birth_date' => $user->birth_date,
                'age' => $user->age,
                'gender' => $user->gender,
                'address' => $user->address,
                'bank_name' => $user->bank_name,
                'bank_account_number' => $user->bank_account_number,
                'bank_account_name' => $user->bank_account_name,
                'created_at' => $user->created_at->format('d/m/Y H:i'),
            ]
        ]);
    }

    /**
     * Update user information via AJAX
     */
    public function updateInfo(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Check permission
        if (!auth()->user()->canManageUser($id)) {
            return response()->json(['error' => 'Bạn không có quyền chỉnh sửa user này'], 403);
        }

        $this->validate($request, [
            'name' => 'string|required|max:30',
            'email' => 'string|required|email|unique:users,email,' . $id,
            'birth_date' => 'nullable|date',
            'age' => 'nullable|integer|min:1|max:120',
            'gender' => 'nullable|in:male,female,other',
            'wallet_balance' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:100',
        ]);

        $data = $request->only([
            'name', 'email', 'birth_date', 'age', 'gender', 'wallet_balance',
            'address', 'bank_name', 'bank_account_number', 'bank_account_name'
        ]);

        $status = $user->fill($data)->save();

        if ($status) {
            return response()->json(['success' => 'Đã cập nhật thông tin thành công']);
        } else {
            return response()->json(['error' => 'Có lỗi xảy ra khi cập nhật thông tin'], 500);
        }
    }

    /**
     * Tạo mật khẩu rút tiền
     */
    public function createWithdrawalPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Kiểm tra quyền
        if (!auth()->user()->canManageUser($id) && auth()->id() != $id) {
            return response()->json(['success' => false, 'message' => 'Không có quyền truy cập'], 403);
        }
        
        $request->validate([
            'withdrawal_password' => ['required', new WithdrawalPinRule],
            'withdrawal_password_confirmation' => 'required|same:withdrawal_password'
        ]);
        
        $user->setWithdrawalPassword($request->withdrawal_password);
        
        return response()->json([
            'success' => true, 
            'message' => 'Tạo mật khẩu rút tiền thành công!'
        ]);
    }

    /**
     * Thay đổi mật khẩu rút tiền
     */
    public function changeWithdrawalPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Kiểm tra quyền
        if (!auth()->user()->canManageUser($id) && auth()->id() != $id) {
            return response()->json(['success' => false, 'message' => 'Không có quyền truy cập'], 403);
        }
        
        $rules = [
            'new_withdrawal_password' => ['required', new WithdrawalPinRule],
            'new_withdrawal_password_confirmation' => 'required|same:new_withdrawal_password'
        ];
        
        // Nếu user tự thay đổi, yêu cầu mật khẩu cũ
        if (auth()->id() == $id) {
            $rules['current_withdrawal_password'] = 'required';
        }
        
        $request->validate($rules);
        
        // Kiểm tra mật khẩu cũ nếu user tự thay đổi
        if (auth()->id() == $id) {
            if (!$user->checkWithdrawalPassword($request->current_withdrawal_password)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Mật khẩu rút tiền hiện tại không đúng!'
                ]);
            }
        }
        
        $user->setWithdrawalPassword($request->new_withdrawal_password);
        
        return response()->json([
            'success' => true, 
            'message' => 'Thay đổi mật khẩu rút tiền thành công!'
        ]);
    }

    /**
     * Xác thực mật khẩu rút tiền
     */
    public function verifyWithdrawalPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Kiểm tra quyền
        if (auth()->id() != $id) {
            return response()->json(['success' => false, 'message' => 'Không có quyền truy cập'], 403);
        }
        
        $request->validate([
            'withdrawal_password' => 'required'
        ]);
        
        if (!$user->hasWithdrawalPassword()) {
            return response()->json([
                'success' => false, 
                'message' => 'Bạn chưa tạo mật khẩu rút tiền!',
                'need_create' => true
            ]);
        }
        
        if (!$user->checkWithdrawalPassword($request->withdrawal_password)) {
            return response()->json([
                'success' => false, 
                'message' => 'Mật khẩu rút tiền không đúng!'
            ]);
        }
        
        return response()->json([
            'success' => true, 
            'message' => 'Xác thực thành công!'
        ]);
    }
}
