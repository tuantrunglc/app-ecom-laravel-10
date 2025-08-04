<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Settings;
use App\User;
use App\Rules\MatchOldPassword;
use Hash;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
class AdminController extends Controller
{
    public function index(){
        $data = User::select(\DB::raw("COUNT(*) as count"), \DB::raw("DAYNAME(created_at) as day_name"), \DB::raw("DAY(created_at) as day"))
        ->where('created_at', '>', Carbon::today()->subDay(6))
        ->groupBy('day_name','day')
        ->orderBy('day')
        ->get();
     $array[] = ['Name', 'Number'];
     foreach($data as $key => $value)
     {
       $array[++$key] = [$value->day_name, $value->count];
     }
    //  return $data;
     return view('backend.index')->with('users', json_encode($array));
    }

    public function profile(){
        $profile=Auth()->user();
        // return $profile;
        return view('backend.users.profile')->with('profile',$profile);
    }

    public function profileUpdate(Request $request,$id){
        // return $request->all();
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

    public function settings(){
        $data=Settings::first();
        return view('backend.setting')->with('data',$data);
    }

    public function settingsUpdate(Request $request){
        try {
            $this->validate($request,[
                'short_des'=>'required|string',
                'description'=>'required|string',
                'photo'=>'nullable|string',
                'logo'=>'nullable|string',
                'logo_upload' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'photo_upload' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'address'=>'required|string',
                'email'=>'required|email',
                'phone'=>'required|string',
            ]);
            
            $data = $request->all();
            
            // Handle logo upload
            if ($request->hasFile('logo_upload')) {
                $logoFile = $request->file('logo_upload');
                $logoFileName = 'logo_' . time() . '.' . $logoFile->getClientOriginalExtension();
                $logoFile->move(public_path('photos'), $logoFileName);
                $data['logo'] = 'photos/' . $logoFileName;
            }
            
            // Handle photo upload
            if ($request->hasFile('photo_upload')) {
                $photoFile = $request->file('photo_upload');
                $photoFileName = 'photo_' . time() . '.' . $photoFile->getClientOriginalExtension();
                $photoFile->move(public_path('photos'), $photoFileName);
                $data['photo'] = 'photos/' . $photoFileName;
            }
            
            // Ensure required fields have values
            $settings = Settings::first();
            if (empty($data['logo']) && empty($settings->logo)) {
                return redirect()->back()
                    ->withErrors(['logo' => 'Vui lòng chọn logo.'])
                    ->withInput();
            }
            if (empty($data['photo']) && empty($settings->photo)) {
                return redirect()->back()
                    ->withErrors(['photo' => 'Vui lòng chọn hình ảnh.'])
                    ->withInput();
            }
            
            $status = $settings->fill($data)->save();
            if($status){
                request()->session()->flash('success','Setting successfully updated');
            }
            else{
                request()->session()->flash('error','Please try again');
            }
            return redirect()->route('admin');
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function changePassword(){
        return view('backend.layouts.changePassword');
    }
    public function changPasswordStore(Request $request)
    {
        $request->validate([
            'current_password' => ['required', new MatchOldPassword],
            'new_password' => ['required'],
            'new_confirm_password' => ['same:new_password'],
        ]);

        User::find(auth()->user()->id)->update(['password'=> Hash::make($request->new_password)]);

        return redirect()->route('admin')->with('success','Password successfully changed');
    }

    // Pie chart
    public function userPieChart(Request $request){
        // dd($request->all());
        $data = User::select(\DB::raw("COUNT(*) as count"), \DB::raw("DAYNAME(created_at) as day_name"), \DB::raw("DAY(created_at) as day"))
        ->where('created_at', '>', Carbon::today()->subDay(6))
        ->groupBy('day_name','day')
        ->orderBy('day')
        ->get();
     $array[] = ['Name', 'Number'];
     foreach($data as $key => $value)
     {
       $array[++$key] = [$value->day_name, $value->count];
     }
    //  return $data;
     return view('backend.index')->with('course', json_encode($array));
    }

    // public function activity(){
    //     return Activity::all();
    //     $activity= Activity::all();
    //     return view('backend.layouts.activity')->with('activities',$activity);
    // }

    public function storageLink(){
        // check if the storage folder already linked;
        if(File::exists(public_path('storage'))){
            // removed the existing symbolic link
            File::delete(public_path('storage'));

            //Regenerate the storage link folder
            try{
                Artisan::call('storage:link');
                request()->session()->flash('success', 'Successfully storage linked.');
                return redirect()->back();
            }
            catch(\Exception $exception){
                request()->session()->flash('error', $exception->getMessage());
                return redirect()->back();
            }
        }
        else{
            try{
                Artisan::call('storage:link');
                request()->session()->flash('success', 'Successfully storage linked.');
                return redirect()->back();
            }
            catch(\Exception $exception){
                request()->session()->flash('error', $exception->getMessage());
                return redirect()->back();
            }
        }
    }
}
