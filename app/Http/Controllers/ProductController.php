<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products = Product::getAllProduct();
        return view('backend.product.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $brands = Brand::get();
        $categories = Category::where('is_parent', 1)->get();
        return view('backend.product.create', compact('categories', 'brands'));
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
                'title' => 'required|string',
                'summary' => 'required|string',
                'description' => 'nullable|string',
                'photo' => 'nullable|string',
                'photo_upload.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'size' => 'nullable',
                'stock' => 'required|numeric',
                'cat_id' => 'required|exists:categories,id',
                'brand_id' => 'nullable|exists:brands,id',
                'child_cat_id' => 'nullable|exists:categories,id',
                'is_featured' => 'sometimes|in:1',
                'status' => 'required|in:active,inactive',
                'condition' => 'required|in:default,new,hot',
                'price' => 'required|numeric',
                'discount' => 'nullable|numeric',
                'commission' => 'nullable|numeric|min:0|max:100',
            ]);

            // Handle file uploads with improved error handling
            if ($request->hasFile('photo_upload')) {
                $uploadedPaths = [];
                $userId = auth()->id() ?? 1;
                
                try {
                    foreach ($request->file('photo_upload') as $file) {
                        // Validate file
                        if (!$file->isValid()) {
                            Log::error('Invalid file upload', ['file' => $file->getClientOriginalName()]);
                            continue;
                        }
                        
                        // Generate unique filename with random prefix
                        $randomPrefix = substr(md5(uniqid()), 0, 5);
                        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $extension = $file->getClientOriginalExtension();
                        $fileName = $randomPrefix . '-' . $originalName . '.' . $extension;
                        
                        // Use Laravel Storage for better file handling
                        $relativePath = "photos/{$userId}/Products/{$fileName}";
                        
                        // Store file using Laravel Storage (public_photos disk)
                        $storedPath = $file->storeAs("{$userId}/Products", $fileName, 'public_photos');
                        
                        if ($storedPath) {
                            // Use photos URL for consistent path generation
                            $uploadedPaths[] = "/photos/" . $storedPath;
                            
                            // Debug: Check if file actually exists
                            $fullPath = public_path('photos/' . $storedPath);
                            $fileExists = file_exists($fullPath);
                            
                            Log::info('File upload attempt', [
                                'stored_path' => $storedPath,
                                'full_path' => $fullPath,
                                'file_exists' => $fileExists,
                                'file_size' => $fileExists ? filesize($fullPath) : 'N/A',
                                'permissions' => $fileExists ? substr(sprintf('%o', fileperms($fullPath)), -4) : 'N/A'
                            ]);
                        } else {
                            Log::error('Failed to store file', [
                                'filename' => $fileName,
                                'user_id' => $userId,
                                'disk_root' => config('filesystems.disks.public_photos.root')
                            ]);
                        }
                    }
                    
                    // If files were uploaded, use them instead of manual input
                    if (!empty($uploadedPaths)) {
                        $validatedData['photo'] = implode(',', $uploadedPaths);
                    }
                    
                } catch (\Exception $e) {
                    Log::error('File upload error: ' . $e->getMessage());
                    return redirect()->back()
                        ->withErrors(['photo_upload' => 'Lỗi khi upload ảnh: ' . $e->getMessage()])
                        ->withInput();
                }
            }

            // Ensure photo field has a value
            if (empty($validatedData['photo'])) {
                return redirect()->back()
                    ->withErrors(['photo' => 'Vui lòng chọn ít nhất một ảnh sản phẩm.'])
                    ->withInput();
            }

            $slug = generateUniqueSlug($request->title, Product::class);
            $validatedData['slug'] = $slug;
            $validatedData['is_featured'] = $request->input('is_featured', 0);

            if ($request->has('size')) {
                $validatedData['size'] = implode(',', $request->input('size'));
            } else {
                $validatedData['size'] = '';
            }

            $product = Product::create($validatedData);

            $message = $product
                ? 'Product Successfully added'
                : 'Please try again!!';

            return redirect()->route('product.index')->with(
                $product ? 'success' : 'error',
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
        $brands = Brand::get();
        $product = Product::findOrFail($id);
        $categories = Category::where('is_parent', 1)->get();
        $items = Product::where('id', $id)->get();

        return view('backend.product.edit', compact('product', 'brands', 'categories', 'items'));
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
            $product = Product::findOrFail($id);

            $validatedData = $request->validate([
                'title' => 'required|string',
                'summary' => 'required|string',
                'description' => 'nullable|string',
                'photo' => 'nullable|string',
                'photo_upload.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'size' => 'nullable',
                'stock' => 'required|numeric',
                'cat_id' => 'required|exists:categories,id',
                'child_cat_id' => 'nullable|exists:categories,id',
                'is_featured' => 'sometimes|in:1',
                'brand_id' => 'nullable|exists:brands,id',
                'status' => 'required|in:active,inactive',
                'condition' => 'required|in:default,new,hot',
                'price' => 'required|numeric',
                'discount' => 'nullable|numeric',
                'commission' => 'nullable|numeric|min:0|max:100',
            ]);

            // Handle new file uploads with improved error handling
            if ($request->hasFile('photo_upload')) {
                $uploadedPaths = [];
                $userId = auth()->id() ?? 1;
                
                try {
                    foreach ($request->file('photo_upload') as $file) {
                        // Validate file
                        if (!$file->isValid()) {
                            Log::error('Invalid file upload', ['file' => $file->getClientOriginalName()]);
                            continue;
                        }
                        
                        // Generate unique filename with random prefix
                        $randomPrefix = substr(md5(uniqid()), 0, 5);
                        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                        $extension = $file->getClientOriginalExtension();
                        $fileName = $randomPrefix . '-' . $originalName . '.' . $extension;
                        
                        // Store file using Laravel Storage (public_photos disk)
                        $storedPath = $file->storeAs("{$userId}/Products", $fileName, 'public_photos');
                        
                        if ($storedPath) {
                            // Use photos URL for consistent path generation
                            $uploadedPaths[] = "/photos/" . $storedPath;
                            
                            // Debug: Check if file actually exists
                            $fullPath = public_path('photos/' . $storedPath);
                            $fileExists = file_exists($fullPath);
                            
                            Log::info('File upload attempt', [
                                'stored_path' => $storedPath,
                                'full_path' => $fullPath,
                                'file_exists' => $fileExists,
                                'file_size' => $fileExists ? filesize($fullPath) : 'N/A',
                                'permissions' => $fileExists ? substr(sprintf('%o', fileperms($fullPath)), -4) : 'N/A'
                            ]);
                        } else {
                            Log::error('Failed to store file', [
                                'filename' => $fileName,
                                'user_id' => $userId,
                                'disk_root' => config('filesystems.disks.public_photos.root')
                            ]);
                        }
                    }
                    
                    // If new files were uploaded, replace existing photos
                    if (!empty($uploadedPaths)) {
                        $validatedData['photo'] = implode(',', $uploadedPaths);
                    }
                    
                } catch (\Exception $e) {
                    Log::error('File upload error: ' . $e->getMessage());
                    return redirect()->back()
                        ->withErrors(['photo_upload' => 'Lỗi khi upload ảnh: ' . $e->getMessage()])
                        ->withInput();
                }
            }

            // If no new files uploaded and no manual photo input, keep existing photos
            if (empty($validatedData['photo']) && empty($request->file('photo_upload'))) {
                $validatedData['photo'] = $product->photo;
            }

            // Ensure photo field has a value
            if (empty($validatedData['photo'])) {
                return redirect()->back()
                    ->withErrors(['photo' => 'Vui lòng chọn ít nhất một ảnh sản phẩm.'])
                    ->withInput();
            }

            $validatedData['is_featured'] = $request->input('is_featured', 0);

            if ($request->has('size')) {
                $validatedData['size'] = implode(',', $request->input('size'));
            } else {
                $validatedData['size'] = '';
            }

            $status = $product->update($validatedData);

            $message = $status
                ? 'Product Successfully updated'
                : 'Please try again!!';

            return redirect()->route('product.index')->with(
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
        $product = Product::findOrFail($id);
        $status = $product->delete();

        $message = $status
            ? 'Product successfully deleted'
            : 'Error while deleting product';

        return redirect()->route('product.index')->with(
            $status ? 'success' : 'error',
            $message
        );
    }
}
