@extends('user.layouts.master')

@section('title','User Profile')

@section('main-content')

<div class="card shadow mb-4">
    <div class="row">
        <div class="col-md-12">
           @include('backend.layouts.notification')
        </div>
    </div>
   <div class="card-header py-3">
     <h4 class=" font-weight-bold">Profile</h4>
     <ul class="breadcrumbs">
         <li><a href="{{route('user')}}" style="color:#999">Dashboard</a></li>
         <li><a href="" class="active text-primary">Profile Page</a></li>
     </ul>
   </div>
   <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="image">
                        @if($profile->photo)
                        <img class="card-img-top img-fluid roundend-circle mt-4" style="border-radius:50%;height:80px;width:80px;margin:auto;" src="{{$profile->photo}}" alt="profile picture">
                        @else 
                        <img class="card-img-top img-fluid roundend-circle mt-4" style="border-radius:50%;height:80px;width:80px;margin:auto;" src="{{asset('backend/img/avatar.png')}}" alt="profile picture">
                        @endif
                    </div>
                    <div class="card-body mt-4 ml-2">
                      <h5 class="card-title text-left"><small><i class="fas fa-user"></i> {{$profile->name}}</small></h5>
                      <p class="card-text text-left"><small><i class="fas fa-envelope"></i> {{$profile->email}}</small></p>
                      @if($profile->age)
                      <p class="card-text text-left"><small><i class="fas fa-birthday-cake"></i> {{$profile->age}} years old</small></p>
                      @endif
                      @if($profile->gender)
                      <p class="card-text text-left"><small><i class="fas fa-venus-mars"></i> 
                        @if($profile->gender == 'male') Male
                        @elseif($profile->gender == 'female') Female
                        @else Other
                        @endif
                      </small></p>
                      @endif
                    </div>
                  </div>
            </div>
            <div class="col-md-8">
                <form class="border px-4 pt-2 pb-3" method="POST" action="{{route('user-profile-update',$profile->id)}}">
                    @csrf
                    <div class="form-group">
                        <label for="inputTitle" class="col-form-label">Name</label>
                      <input id="inputTitle" type="text" name="name" placeholder="Enter name"  value="{{$profile->name}}" class="form-control">
                      @error('name')
                      <span class="text-danger">{{$message}}</span>
                      @enderror
                      </div>
              
                      <div class="form-group">
                          <label for="inputEmail" class="col-form-label">Email</label>
                        <input id="inputEmail" disabled type="email" name="email" placeholder="Enter email"  value="{{$profile->email}}" class="form-control">
                        @error('email')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                      </div>
              
                      <div class="form-group">
                      <label for="inputPhoto" class="col-form-label">Photo</label>
                      <div class="input-group">
                          <span class="input-group-btn">
                              <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                              <i class="fa fa-picture-o"></i> Choose
                              </a>
                          </span>
                          <input id="thumbnail" class="form-control" type="text" name="photo" value="{{$profile->photo}}">
                      </div>
                        @error('photo')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                      </div>
                      <div class="form-group">
                          <label for="birth_date" class="col-form-label">Birth Date</label>
                          <input id="birth_date" type="date" name="birth_date" value="{{$profile->birth_date}}" class="form-control">
                          @error('birth_date')
                          <span class="text-danger">{{$message}}</span>
                          @enderror
                      </div>

                      <div class="form-group">
                          <label for="age" class="col-form-label">Age</label>
                          <input id="age" type="number" name="age" placeholder="Enter age" value="{{$profile->age}}" class="form-control" min="1" max="120">
                          @error('age')
                          <span class="text-danger">{{$message}}</span>
                          @enderror
                      </div>

                      <div class="form-group">
                          <label for="gender" class="col-form-label">Gender</label>
                          <select name="gender" class="form-control">
                              <option value="">-----Select Gender-----</option>
                              <option value="male" {{(($profile->gender=='male')? 'selected' : '')}}>Male</option>
                              <option value="female" {{(($profile->gender=='female')? 'selected' : '')}}>Female</option>
                              <option value="other" {{(($profile->gender=='other')? 'selected' : '')}}>Other</option>
                          </select>
                          @error('gender')
                          <span class="text-danger">{{$message}}</span>
                          @enderror
                      </div>

                      <div class="form-group">
                          <label for="address" class="col-form-label">Address</label>
                          <textarea id="address" name="address" placeholder="Enter your address" class="form-control" rows="3">{{$profile->address}}</textarea>
                          @error('address')
                          <span class="text-danger">{{$message}}</span>
                          @enderror
                      </div>

                      <hr>
                      <h5 class="mb-3"><i class="fas fa-university"></i> Bank Information</h5>
                      
                      <div class="form-group">
                          <label for="bank_name" class="col-form-label">Bank Name</label>
                          <input id="bank_name" type="text" name="bank_name" placeholder="Enter bank name" value="{{$profile->bank_name}}" class="form-control">
                          @error('bank_name')
                          <span class="text-danger">{{$message}}</span>
                          @enderror
                      </div>

                      <div class="form-group">
                          <label for="bank_account_number" class="col-form-label">Account Number</label>
                          <input id="bank_account_number" type="text" name="bank_account_number" placeholder="Enter account number" value="{{$profile->bank_account_number}}" class="form-control">
                          @error('bank_account_number')
                          <span class="text-danger">{{$message}}</span>
                          @enderror
                      </div>

                      <div class="form-group">
                          <label for="bank_account_name" class="col-form-label">Account Holder Name</label>
                          <input id="bank_account_name" type="text" name="bank_account_name" placeholder="Enter account holder name" value="{{$profile->bank_account_name}}" class="form-control">
                          @error('bank_account_name')
                          <span class="text-danger">{{$message}}</span>
                          @enderror
                      </div>

                      <button type="submit" class="btn btn-success btn-sm">Update Profile</button>
                </form>
            </div>
        </div>
   </div>
</div>

<!-- Withdrawal Password Management Section -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h4 class="font-weight-bold">
            <i class="fas fa-shield-alt"></i> Withdrawal Password Management
        </h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                @if($profile->hasWithdrawalPassword())
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> 
                        <strong>Withdrawal password is set</strong>
                        <br><small>Created: {{ $profile->withdrawal_password_created_at ? $profile->withdrawal_password_created_at->format('d/m/Y H:i') : 'N/A' }}</small>
                        @if($profile->withdrawal_password_updated_at)
                        <br><small>Last updated: {{ $profile->withdrawal_password_updated_at->format('d/m/Y H:i') }}</small>
                        @endif
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-lock"></i> 
                        <strong>Security Notice:</strong> Withdrawal password cannot be changed by user for security reasons. 
                        Contact admin if you need assistance.
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>No withdrawal password set</strong>
                        <br><small>You need to create a withdrawal password to withdraw money from your wallet. <strong>Note: You can only create it once!</strong></small>
                    </div>
                    
                    <button class="btn btn-primary" id="createWithdrawalPasswordBtn">
                        <i class="fas fa-plus-circle"></i> Create Withdrawal Password (One Time Only)
                    </button>
                @endif
            </div>
            <div class="col-md-6">
                <div class="card bg-light">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="fas fa-info-circle text-info"></i> About Withdrawal Password
                        </h6>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> 4-6 digit PIN for security</li>
                            <li><i class="fas fa-check text-success"></i> Required for all withdrawals</li>
                            <li><i class="fas fa-check text-success"></i> Different from login password</li>
                            <li><i class="fas fa-exclamation-triangle text-warning"></i> <strong>Can only be created once</strong></li>
                            <li><i class="fas fa-shield-alt text-info"></i> Contact admin to change</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Withdrawal Password Modal -->
<div class="modal fade" id="createWithdrawalPasswordModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Withdrawal Password</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form id="createWithdrawalPasswordForm">
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Important:</strong> You can only create withdrawal password ONCE! Make sure to remember it as you cannot change it later.
                    </div>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Create a 4-6 digit PIN for withdrawal security
                    </div>
                    <div class="form-group">
                        <label for="withdrawal_password">Withdrawal Password</label>
                        <input type="password" class="form-control" id="withdrawal_password" 
                               name="withdrawal_password" required pattern="[0-9]{4,6}" maxlength="6" 
                               placeholder="Enter 4-6 digits">
                    </div>
                    <div class="form-group">
                        <label for="withdrawal_password_confirmation">Confirm Password</label>
                        <input type="password" class="form-control" id="withdrawal_password_confirmation" 
                               name="withdrawal_password_confirmation" required pattern="[0-9]{4,6}" maxlength="6" 
                               placeholder="Confirm 4-6 digits">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Password</button>
                </div>
            </form>
        </div>
    </div>
</div>



@endsection

<style>
    .breadcrumbs{
        list-style: none;
    }
    .breadcrumbs li{
        float:left;
        margin-right:10px;
    }
    .breadcrumbs li a:hover{
        text-decoration: none;
    }
    .breadcrumbs li .active{
        color:red;
    }
    .breadcrumbs li+li:before{
      content:"/\00a0";
    }
    .image{
        background:url('{{asset('backend/img/background.jpg')}}');
        height:150px;
        background-position:center;
        background-attachment:cover;
        position: relative;
    }
    .image img{
        position: absolute;
        top:55%;
        left:35%;
        margin-top:30%;
    }
    i{
        font-size: 14px;
        padding-right:8px;
    }
  </style> 

@push('scripts')
<script src="/vendor/laravel-filemanager/js/stand-alone-button.js"></script>
<script>
    $('#lfm').filemanager('image');
    
    // Auto calculate age when birth date is selected
    $('#birth_date').on('change', function() {
        var birthDate = new Date($(this).val());
        var today = new Date();
        var age = today.getFullYear() - birthDate.getFullYear();
        var monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        if (age >= 0 && age <= 120) {
            $('#age').val(age);
        }
    });

    // Withdrawal Password Management
    $(document).ready(function() {
        // CSRF Token setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Create Withdrawal Password Button
        $('#createWithdrawalPasswordBtn').on('click', function() {
            $('#createWithdrawalPasswordModal').modal('show');
        });



        // Create Withdrawal Password Form Submit
        $('#createWithdrawalPasswordForm').on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            
            $.ajax({
                url: '{{ route("user.create-withdrawal-password") }}',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#createWithdrawalPasswordModal').modal('hide');
                        alert('Success: ' + response.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.message);
                    }
                },
                error: function(xhr) {
                    var errors = xhr.responseJSON.errors;
                    var errorMessage = '';
                    
                    if (errors) {
                        $.each(errors, function(key, value) {
                            errorMessage += value[0] + '\n';
                        });
                    } else {
                        errorMessage = 'An error occurred!';
                    }
                    
                    alert('Error: ' + errorMessage);
                }
            });
        });



        // Clear form when modal is hidden
        $('#createWithdrawalPasswordModal').on('hidden.bs.modal', function() {
            $('#createWithdrawalPasswordForm')[0].reset();
        });
    });
</script>
@endpush