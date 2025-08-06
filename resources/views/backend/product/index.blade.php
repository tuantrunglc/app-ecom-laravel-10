@extends('backend.layouts.master')

@section('main-content')
 <!-- DataTales Example -->
 <div class="card shadow mb-4">
     <div class="row">
         <div class="col-md-12">
            @include('backend.layouts.notification')
         </div>
     </div>
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary float-left">Danh Sách Sản Phẩm</h6>
      <a href="{{route('product.create')}}" class="btn btn-primary btn-sm float-right" data-toggle="tooltip" data-placement="bottom" title="Thêm Sản Phẩm"><i class="fas fa-plus"></i> Thêm Sản Phẩm</a>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        @if(count($products)>0)
        <table class="table table-bordered" id="product-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>STT</th>
              <th>Tiêu Đề</th>
              <th>Danh Mục</th>
              <th>Nổi Bật</th>
              <th>Giá</th>
              <th>Giảm Giá</th>
              <th>Hoa Hồng</th>
              <th>Kích Thước</th>
              <th>Tình Trạng</th>
              <th>Thương Hiệu</th>
              <th>Tồn Kho</th>
              <th>Hình Ảnh</th>
              <th>Trạng Thái</th>
              <th>Hành Động</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th>STT</th>
              <th>Tiêu Đề</th>
              <th>Danh Mục</th>
              <th>Nổi Bật</th>
              <th>Giá</th>
              <th>Giảm Giá</th>
              <th>Hoa Hồng</th>
              <th>Kích Thước</th>
              <th>Tình Trạng</th>
              <th>Thương Hiệu</th>
              <th>Tồn Kho</th>
              <th>Hình Ảnh</th>
              <th>Trạng Thái</th>
              <th>Hành Động</th>
            </tr>
          </tfoot>
          <tbody>

            @foreach($products as $product)
              @php
              $sub_cat_info=DB::table('categories')->select('title')->where('id',$product->child_cat_id)->get();
              // dd($sub_cat_info);
              $brands=DB::table('brands')->select('title')->where('id',$product->brand_id)->get();
              @endphp
                <tr>
                    <td>{{$product->id}}</td>
                    <td>{{$product->title}}</td>
                    <td>{{$product->cat_info['title'] ?? 'Không có danh mục'}}
                      <sub>
                          {{$product->sub_cat_info->title ?? ''}}
                      </sub>
                    </td>
                    <td>{{(($product->is_featured==1)? 'Có': 'Không')}}</td>
                    <td>${{$product->price}}</td>
                    <td>  {{$product->discount}}% GIẢM</td>
                    <td>{{$product->commission ?? 0}}% HOA HỒNG</td>
                    <td>{{$product->size}}</td>
                    <td>{{$product->condition}}</td>
                    <td> {{$product->brand ? ucfirst($product->brand->title) : 'Không có thương hiệu'}}</td>
                    <td>
                      @if($product->stock>0)
                      <span class="badge badge-primary">{{$product->stock}}</span>
                      @else
                      <span class="badge badge-danger">{{$product->stock}}</span>
                      @endif
                    </td>
                    <td>
                        @if($product->photo)
                            @php
                              $photo=explode(',',$product->photo);
                              // dd($photo);
                            @endphp
                            <img src="{{$photo[0]}}" class="img-fluid zoom" style="max-width:80px" alt="{{$product->photo}}">
                        @else
                            <img src="https://demoeshop.online/public/backend/img/thumbnail-default.jpg" class="img-fluid" style="max-width:80px" alt="avatar.png">
                        @endif
                    </td>
                    <td>
                        @if($product->status=='active')
                            <span class="badge badge-success">{{$product->status}}</span>
                        @else
                            <span class="badge badge-warning">{{$product->status}}</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{route('product.edit',$product->id)}}" class="btn btn-primary btn-sm float-left mr-1" style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" title="edit" data-placement="bottom"><i class="fas fa-edit"></i></a>
                    <form method="POST" action="{{route('product.destroy',[$product->id])}}">
                      @csrf
                      @method('delete')
                          <button class="btn btn-danger btn-sm dltBtn" data-id={{$product->id}} style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" data-placement="bottom" title="Delete"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
          </tbody>
        </table>
        <span style="float:right">{{$products->links()}}</span>
        @else
          <h6 class="text-center">Không tìm thấy sản phẩm nào!!! Vui lòng tạo sản phẩm</h6>
        @endif
      </div>
    </div>
</div>
@endsection

@push('styles')
  <link href="https://demoeshop.online/public/backend/vendor/datatables/dataTables.bootstrap4.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
  <style>
      div.dataTables_wrapper div.dataTables_paginate{
          display: none;
      }
      .zoom {
        transition: transform .2s; /* Animation */
      }

      .zoom:hover {
        transform: scale(5);
      }
  </style>
@endpush

@push('scripts')

  <!-- Page level plugins -->
  <script src="https://demoeshop.online/public/backend/vendor/datatables/jquery.dataTables.min.js"></script>
  <script src="https://demoeshop.online/public/backend/vendor/datatables/dataTables.bootstrap4.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>

  <!-- Page level custom scripts -->
  <script src="https://demoeshop.online/public/backend/js/demo/datatables-demo.js"></script>
  <script>

      $('#product-dataTable').DataTable( {
        "scrollX": false
            "columnDefs":[
                {
                    "orderable":false,
                    "targets":[10,11,12]
                }
            ]
        } );

        // Sweet alert

        function deleteData(id){

        }
  </script>
  <script>
      $(document).ready(function(){
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
          $('.dltBtn').click(function(e){
            var form=$(this).closest('form');
              var dataID=$(this).data('id');
              // alert(dataID);
              e.preventDefault();
              swal({
                    title: "Bạn có chắc chắn?",
                    text: "Sau khi xóa, bạn sẽ không thể khôi phục dữ liệu này!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                       form.submit();
                    } else {
                        swal("Dữ liệu của bạn an toàn!");
                    }
                });
          })
      })
  </script>
@endpush
