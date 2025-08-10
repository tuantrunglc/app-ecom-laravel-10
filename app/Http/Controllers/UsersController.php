<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users=User::orderBy('id','ASC')->paginate(10);
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
            'address' => 'nullable|string|max:255',
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:100',
        ]);

        $data = $request->only([
            'name', 'email', 'birth_date', 'age', 'gender', 
            'address', 'bank_name', 'bank_account_number', 'bank_account_name'
        ]);

        $status = $user->fill($data)->save();

        if ($status) {
            return response()->json(['success' => 'Đã cập nhật thông tin thành công']);
        } else {
            return response()->json(['error' => 'Có lỗi xảy ra khi cập nhật thông tin'], 500);
        }
    }
}
