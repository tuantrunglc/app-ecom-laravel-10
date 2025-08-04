<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Banner;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $banners = Banner::latest('id')->paginate(10);
        return view('backend.banner.index', compact('banners'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.banner.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'title' => 'required|string|max:50',
                'description' => 'nullable|string',
                'photo' => 'nullable|string',
                'photo_upload' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'status' => 'required|in:active,inactive',
            ]);

            // Handle file upload
            if ($request->hasFile('photo_upload')) {
                $photoFile = $request->file('photo_upload');
                $photoFileName = 'banner_' . time() . '.' . $photoFile->getClientOriginalExtension();
                $photoFile->move(public_path('photos'), $photoFileName);
                $validatedData['photo'] = 'photos/' . $photoFileName;
            }

            // Ensure photo field has a value
            if (empty($validatedData['photo'])) {
                return redirect()->back()
                    ->withErrors(['photo' => 'Vui lòng chọn ảnh banner.'])
                    ->withInput();
            }

            $slug = $this->generateUniqueSlug($request->title);
            $validatedData['slug'] = $slug;

            $banner = Banner::create($validatedData);

            $message = $banner
                ? 'Banner successfully added'
                : 'Error occurred while adding banner';

            return redirect()->route('banner.index')->with(
                $banner ? 'success' : 'error',
                $message
            );
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Implement if needed
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $banner = Banner::findOrFail($id);
        return view('backend.banner.edit', compact('banner'));
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
        try {
            $banner = Banner::findOrFail($id);

            $validatedData = $request->validate([
                'title' => 'required|string|max:50',
                'description' => 'nullable|string',
                'photo' => 'nullable|string',
                'photo_upload' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'status' => 'required|in:active,inactive',
            ]);

            // Handle file upload
            if ($request->hasFile('photo_upload')) {
                $photoFile = $request->file('photo_upload');
                $photoFileName = 'banner_' . time() . '.' . $photoFile->getClientOriginalExtension();
                $photoFile->move(public_path('photos'), $photoFileName);
                $validatedData['photo'] = 'photos/' . $photoFileName;
            }

            // Ensure photo field has a value
            if (empty($validatedData['photo']) && empty($banner->photo)) {
                return redirect()->back()
                    ->withErrors(['photo' => 'Vui lòng chọn ảnh banner.'])
                    ->withInput();
            }

            $status = $banner->update($validatedData);

            $message = $status
                ? 'Banner successfully updated'
                : 'Error occurred while updating banner';

            return redirect()->route('banner.index')->with(
                $status ? 'success' : 'error',
                $message
            );
            
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Có lỗi xảy ra: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        $status = $banner->delete();

        $message = $status
            ? 'Banner successfully deleted'
            : 'Error occurred while deleting banner';

        return redirect()->route('banner.index')->with(
            $status ? 'success' : 'error',
            $message
        );
    }

    /**
     * Generate a unique slug for the banner.
     *
     * @param  string  $title
     * @return string
     */
    private function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $count = Banner::where('slug', $slug)->count();

        if ($count > 0) {
            $slug = $slug . '-' . date('ymdis') . '-' . rand(0, 999);
        }

        return $slug;
    }
}
