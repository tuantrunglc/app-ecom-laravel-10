@extends('backend.layouts.master')

@section('main-content')

<div class="card">
    <h5 class="card-header">Edit Product</h5>
    <div class="card-body">
      <form method="post" action="{{route('product.update',$product->id)}}" enctype="multipart/form-data">
        @csrf 
        @method('PATCH')
        <div class="form-group">
          <label for="inputTitle" class="col-form-label">Title <span class="text-danger">*</span></label>
          <input id="inputTitle" type="text" name="title" placeholder="Enter title"  value="{{$product->title}}" class="form-control">
          @error('title')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="summary" class="col-form-label">Summary <span class="text-danger">*</span></label>
          <textarea class="form-control" id="summary" name="summary">{{$product->summary}}</textarea>
          @error('summary')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="description" class="col-form-label">Description</label>
          <textarea class="form-control" id="description" name="description">{{$product->description}}</textarea>
          @error('description')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>


        <div class="form-group">
          <label for="is_featured">Is Featured</label><br>
          <input type="checkbox" name='is_featured' id='is_featured' value='{{$product->is_featured}}' {{(($product->is_featured) ? 'checked' : '')}}> Yes                        
        </div>
              {{-- {{$categories}} --}}

        <div class="form-group">
          <label for="cat_id">Category <span class="text-danger">*</span></label>
          <select name="cat_id" id="cat_id" class="form-control">
              <option value="">--Select any category--</option>
              @foreach($categories as $key=>$cat_data)
                  <option value='{{$cat_data->id}}' {{(($product->cat_id==$cat_data->id)? 'selected' : '')}}>{{$cat_data->title}}</option>
              @endforeach
          </select>
        </div>
        @php 
          $sub_cat_info=DB::table('categories')->select('title')->where('id',$product->child_cat_id)->get();
        // dd($sub_cat_info);

        @endphp
        {{-- {{$product->child_cat_id}} --}}
        <div class="form-group {{(($product->child_cat_id)? '' : 'd-none')}}" id="child_cat_div">
          <label for="child_cat_id">Sub Category</label>
          <select name="child_cat_id" id="child_cat_id" class="form-control">
              <option value="">--Select any sub category--</option>
              
          </select>
        </div>

        <div class="form-group">
          <label for="price" class="col-form-label">Price(NRS) <span class="text-danger">*</span></label>
          <input id="price" type="number" name="price" placeholder="Enter price"  value="{{$product->price}}" class="form-control">
          @error('price')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="discount" class="col-form-label">Discount(%)</label>
          <input id="discount" type="number" name="discount" min="0" max="100" placeholder="Enter discount"  value="{{$product->discount}}" class="form-control">
          @error('discount')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
          <label for="commission" class="col-form-label">Commission(%)</label>
          <input id="commission" type="number" name="commission" min="0" max="100" step="0.01" placeholder="Enter commission percentage"  value="{{$product->commission ?? 0}}" class="form-control">
          @error('commission')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
        <div class="form-group">
          <label for="size">Size</label>
          <select name="size[]" class="form-control selectpicker"  multiple data-live-search="true">
              <option value="">--Select any size--</option>
              @foreach($items as $item)              
                @php 
                $data=explode(',',$item->size);
                // dd($data);
                @endphp
              <option value="S"  @if( in_array( "S",$data ) ) selected @endif>Small</option>
              <option value="M"  @if( in_array( "M",$data ) ) selected @endif>Medium</option>
              <option value="L"  @if( in_array( "L",$data ) ) selected @endif>Large</option>
              <option value="XL"  @if( in_array( "XL",$data ) ) selected @endif>Extra Large</option>
              @endforeach
          </select>
        </div>
        <div class="form-group">
          <label for="brand_id">Brand</label>
          <select name="brand_id" class="form-control">
              <option value="">--Select Brand--</option>
             @foreach($brands as $brand)
              <option value="{{$brand->id}}" {{(($product->brand_id==$brand->id)? 'selected':'')}}>{{$brand->title}}</option>
             @endforeach
          </select>
        </div>

        <div class="form-group">
          <label for="condition">Condition</label>
          <select name="condition" class="form-control">
              <option value="">--Select Condition--</option>
              <option value="default" {{(($product->condition=='default')? 'selected':'')}}>Default</option>
              <option value="new" {{(($product->condition=='new')? 'selected':'')}}>New</option>
              <option value="hot" {{(($product->condition=='hot')? 'selected':'')}}>Hot</option>
          </select>
        </div>

        <div class="form-group">
          <label for="stock">Quantity <span class="text-danger">*</span></label>
          <input id="quantity" type="number" name="stock" min="0" placeholder="Enter quantity"  value="{{$product->stock}}" class="form-control">
          @error('stock')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
        <div class="form-group">
          <label for="inputPhoto" class="col-form-label">Ảnh Sản Phẩm <span class="text-danger">*</span></label>
          
          <!-- Current Images Display -->
          @if($product->photo)
          <div class="mb-3">
            <label class="form-label">Ảnh hiện tại:</label>
            <div class="row" id="current_images">
              @php
                $photos = explode(',', $product->photo);
              @endphp
              @foreach($photos as $photo)
                @if(trim($photo))
                <div class="col-md-3 col-sm-4 col-6 mb-2">
                  <div class="card current-image-card" style="height: 180px;">
                    <img src="{{asset(trim($photo))}}" class="card-img-top" style="height: 130px; object-fit: cover;" alt="Product Image">
                    <div class="card-body p-2">
                      <small class="text-muted" style="font-size: 10px; word-break: break-all;">{{basename(trim($photo))}}</small>
                    </div>
                  </div>
                </div>
                @endif
              @endforeach
            </div>
          </div>
          @endif
          
          <!-- File Upload Section -->
          <div class="mb-3">
            <label class="form-label">Upload ảnh mới (sẽ thay thế ảnh cũ):</label>
            <input type="file" class="form-control" id="photo_upload" name="photo_upload[]" multiple accept="image/*" onchange="previewImages(this)">
            <small class="text-muted">Chọn nhiều ảnh (JPEG, PNG, GIF). Ảnh sẽ được lưu theo format: /photos/1/Products/xxxxx-filename.jpg</small>
          </div>
          
          <!-- Preview Container for new images -->
          <div id="preview_container" class="row" style="margin-top:15px;"></div>
          
          <!-- Manual Path Input -->
          <div class="mt-3">
            <label class="form-label">Hoặc chỉnh sửa đường dẫn ảnh thủ công:</label>
            <div class="input-group">
                <span class="input-group-btn">
                    <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary text-white">
                    <i class="fas fa-image"></i> Chọn từ Gallery
                    </a>
                </span>
                <input id="thumbnail" class="form-control" type="text" name="photo" value="{{$product->photo}}" placeholder="VD: /photos/1/Products/405b7-pmtk004t.jpg,/photos/1/Products/43f35-2_2.jpg">
            </div>
            <small class="text-muted">Nhiều ảnh cách nhau bằng dấu phẩy</small>
          </div>
          
          <div id="holder" style="margin-top:15px;max-height:100px;"></div>
          @error('photo')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
        
        <div class="form-group">
          <label for="status" class="col-form-label">Status <span class="text-danger">*</span></label>
          <select name="status" class="form-control">
            <option value="active" {{(($product->status=='active')? 'selected' : '')}}>Active</option>
            <option value="inactive" {{(($product->status=='inactive')? 'selected' : '')}}>Inactive</option>
        </select>
          @error('status')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
        <div class="form-group mb-3">
           <button class="btn btn-success" type="submit">Update</button>
        </div>
      </form>
    </div>
</div>

@endsection

@push('styles')
<link rel="stylesheet" href="{{asset('backend/summernote/summernote.min.css')}}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.css" />
<style>
.image-preview-card {
    transition: transform 0.2s;
    border: 2px solid #e9ecef;
}
.image-preview-card:hover {
    transform: translateY(-2px);
    border-color: #007bff;
    box-shadow: 0 4px 8px rgba(0,123,255,0.2);
}
.current-image-card {
    border: 2px solid #28a745;
}
.upload-area {
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    transition: border-color 0.3s;
}
.upload-area:hover {
    border-color: #007bff;
    background-color: #f8f9fa;
}
</style>
@endpush
@push('scripts')
<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<script src="{{asset('backend/summernote/summernote.min.js')}}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>

<script>
    $('#lfm').filemanager('image');

    // Preview uploaded images for edit form
    function previewImages(input) {
        const container = document.getElementById('preview_container');
        container.innerHTML = '';
        
        if (input.files) {
            const files = Array.from(input.files);
            let imagePaths = [];
            const userId = 1; // Default user ID, you can make this dynamic
            
            files.forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    // Create preview column
                    const colDiv = document.createElement('div');
                    colDiv.className = 'col-md-3 col-sm-4 col-6 mb-3';
                    
                    const cardDiv = document.createElement('div');
                    cardDiv.className = 'card image-preview-card';
                    cardDiv.style.cssText = 'height: 200px;';
                    
                    const img = document.createElement('img');
                    img.className = 'card-img-top';
                    img.style.cssText = 'height: 150px; object-fit: cover;';
                    
                    const cardBody = document.createElement('div');
                    cardBody.className = 'card-body p-2';
                    
                    const fileName = document.createElement('small');
                    fileName.className = 'text-muted';
                    fileName.style.cssText = 'font-size: 10px; word-break: break-all;';
                    
                    // Generate expected path with random prefix
                    const randomPrefix = Math.random().toString(36).substr(2, 5);
                    const originalName = file.name.split('.')[0];
                    const extension = file.name.split('.').pop();
                    const expectedFileName = `${randomPrefix}-${originalName}.${extension}`;
                    const expectedPath = `/photos/${userId}/Products/${expectedFileName}`;
                    
                    fileName.textContent = expectedFileName;
                    imagePaths.push(expectedPath);
                    
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        img.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                    
                    cardBody.appendChild(fileName);
                    cardDiv.appendChild(img);
                    cardDiv.appendChild(cardBody);
                    colDiv.appendChild(cardDiv);
                    container.appendChild(colDiv);
                }
            });
            
            // Update the text input with comma-separated paths
            setTimeout(() => {
                const thumbnailInput = document.getElementById('thumbnail');
                if (thumbnailInput && imagePaths.length > 0) {
                    thumbnailInput.value = imagePaths.join(',');
                }
            }, 100);
        }
    }

    $(document).ready(function() {
    $('#summary').summernote({
      placeholder: "Write short description.....",
        tabsize: 2,
        height: 150
    });
    });
    $(document).ready(function() {
      $('#description').summernote({
        placeholder: "Write detail Description.....",
          tabsize: 2,
          height: 150
      });
    });
</script>

<script>
  var  child_cat_id='{{$product->child_cat_id}}';
        // alert(child_cat_id);
        $('#cat_id').change(function(){
            var cat_id=$(this).val();

            if(cat_id !=null){
                // ajax call
                $.ajax({
                    url:"/admin/category/"+cat_id+"/child",
                    type:"POST",
                    data:{
                        _token:"{{csrf_token()}}"
                    },
                    success:function(response){
                        if(typeof(response)!='object'){
                            response=$.parseJSON(response);
                        }
                        var html_option="<option value=''>--Select any one--</option>";
                        if(response.status){
                            var data=response.data;
                            if(response.data){
                                $('#child_cat_div').removeClass('d-none');
                                $.each(data,function(id,title){
                                    html_option += "<option value='"+id+"' "+(child_cat_id==id ? 'selected ' : '')+">"+title+"</option>";
                                });
                            }
                            else{
                                console.log('no response data');
                            }
                        }
                        else{
                            $('#child_cat_div').addClass('d-none');
                        }
                        $('#child_cat_id').html(html_option);

                    }
                });
            }
            else{

            }

        });
        if(child_cat_id!=null){
            $('#cat_id').change();
        }
</script>
@endpush