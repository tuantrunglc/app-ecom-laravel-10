@extends('backend.layouts.master')

@section('title','VIP Levels Management')

@section('main-content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5>VIP Levels Configuration</h5>
    <a href="{{ route('admin.vip.user-management') }}" class="btn btn-sm btn-secondary">Manage Users VIP</a>
  </div>
  <div class="card-body">
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <div class="table-responsive">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>Level</th>
            <th>Name</th>
            <th>Product Limit</th>
            <th>Price</th>
            <th>Color</th>
            <th>Active</th>
            <th>Users</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($vipLevels as $level)
            <tr>
              <td><span class="badge badge-info">{{ $level->level }}</span></td>
              <td><strong style="color: {{ $level->color }}">{{ $level->name }}</strong></td>
              <td>{{ $level->daily_purchase_limit }} / day</td>
              <td>${{ number_format((float)$level->price, 2) }}</td>
              <td>
                <span class="badge" style="background-color: {{ $level->color }}; color: #fff;">{{ $level->color }}</span>
              </td>
              <td>
                @if($level->is_active)
                  <span class="badge badge-success">Active</span>
                @else
                  <span class="badge badge-secondary">Inactive</span>
                @endif
              </td>
              <td>{{ $level->users_count }}</td>
              <td>
                <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editLevel{{ $level->id }}">
                  <i class="fas fa-edit"></i> Edit
                </button>
              </td>
            </tr>

            <div class="modal fade" id="editLevel{{ $level->id }}" tabindex="-1" role="dialog">
              <div class="modal-dialog" role="document">
                <div class="modal-content">
                  <form method="POST" action="{{ route('admin.vip.update-level', $level) }}">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                      <h5 class="modal-title">Edit {{ $level->name }}</h5>
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                      </button>
                    </div>
                    <div class="modal-body">
                      <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $level->name) }}" required>
                      </div>
                      <div class="form-group">
                        <label>Daily Purchase Limit</label>
                        <input type="number" name="daily_purchase_limit" class="form-control" min="1" value="{{ old('daily_purchase_limit', $level->daily_purchase_limit) }}" required>
                      </div>
                      <div class="form-group">
                        <label>Price</label>
                        <input type="number" step="0.01" name="price" class="form-control" min="0" value="{{ old('price', $level->price) }}" required>
                      </div>
                      <div class="form-group">
                        <label>Color</label>
                        <input type="text" name="color" class="form-control" value="{{ old('color', $level->color) }}" required>
                      </div>
                      <div class="form-group form-check">
                        <input type="checkbox" name="is_active" id="active{{ $level->id }}" class="form-check-input" value="1" {{ $level->is_active ? 'checked' : '' }}>
                        <label for="active{{ $level->id }}" class="form-check-label">Active</label>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                      <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection