@extends('backend.layouts.master')
@section('title','Wallmart88 || Banner Edit')
@section('main-content')

<div class="card">
    <h5 class="card-header">Edit Banner</h5>
    <div class="card-body">
      <form method="post" action="{{route('banner.update',$banner->id)}}" enctype="multipart/form-data">
        @csrf 
        @method('PATCH')
        <div class="form-group">
          <label for="inputTitle" class="col-form-label">Title <span class="text-danger">*</span></label>
        <input id="inputTitle" type="text" name="title" placeholder="Enter title"  value="{{$banner->title}}" class="form-control">
        @error('title')
        <span class="text-danger">{{$message}}</span>
        @enderror
        </div>

        <div class="form-group">
          <label for="inputDesc" class="col-form-label">Description</label>
          <textarea class="form-control" id="description" name="description">{{$banner->description}}</textarea>
          @error('description')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
        <label for="inputPhoto" class="col-form-label">Photo <span class="text-danger">*</span></label>
        
        <!-- Option 1: File Upload -->
        <div class="mb-3">
          <input type="file" class="form-control" id="photo_upload" name="photo_upload" accept="image/*" onchange="previewBannerImage(this)">
          <small class="text-muted">Chọn ảnh banner mới (JPEG, PNG, GIF). Sẽ được lưu vào public/photos/</small>
        </div>
        
        <!-- Option 2: Manual Path Input -->
        <div class="input-group">
            <span class="input-group-btn">
                <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                <i class="fa fa-picture-o"></i> Chọn từ Gallery
                </a>
            </span>
          <input id="thumbnail" class="form-control" type="text" name="photo" value="{{$banner->photo}}" placeholder="Hoặc nhập đường dẫn ảnh">
        </div>
        
        <div id="banner_preview_container" style="margin-top:15px;"></div>
        <div id="holder" style="margin-top:15px;max-height:100px;"></div>
          @error('photo')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
        
        <div class="form-group">
          <label for="status" class="col-form-label">Status <span class="text-danger">*</span></label>
          <select name="status" class="form-control">
            <option value="active" {{(($banner->status=='active') ? 'selected' : '')}}>Active</option>
            <option value="inactive" {{(($banner->status=='inactive') ? 'selected' : '')}}>Inactive</option>
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
@endpush
@push('scripts')
<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<script src="{{asset('backend/summernote/summernote.min.js')}}"></script>
<script>
    $('#lfm').filemanager('image', {prefix: '/laravel-filemanager'});

    // Preview banner image upload
    function previewBannerImage(input) {
        const container = document.getElementById('banner_preview_container');
        container.innerHTML = '';
        
        if (input.files && input.files[0]) {
            const file = input.files[0];
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.cssText = 'height: 80px; width: 120px; object-fit: cover; margin: 5px; border: 1px solid #ddd; border-radius: 4px;';
                    container.appendChild(img);
                };
                reader.readAsDataURL(file);
                
                // Update the text input
                const fileName = file.name;
                setTimeout(() => {
                    const thumbnailInput = document.getElementById('thumbnail');
                    if (thumbnailInput) {
                        thumbnailInput.value = 'photos/' + fileName;
                    }
                }, 100);
            }
        }
    }

    $(document).ready(function() {
    $('#description').summernote({
      placeholder: "Write short description.....",
        tabsize: 2,
        height: 150
    });
    });
</script>
@endpush