@extends('backend.layouts.master')

@section('title','User VIP Management')

@section('main-content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5>User VIP Management</h5>
    <a href="{{ route('admin.vip.levels') }}" class="btn btn-sm btn-secondary">Manage VIP Levels</a>
  </div>
  <div class="card-body">
    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
      <table class="table table-bordered">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>VIP</th>
            <th>Today</th>
            <th>Limit</th>
            <th>Remaining</th>
            <th>Change VIP</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($users as $u)
            <tr>
              <td>#{{ $u->id }}</td>
              <td>{{ $u->name }}</td>
              <td>{{ $u->email }}</td>
              <td>
                <span class="badge" style="background-color: {{ $u->vip_color }}; color: #fff">{{ $u->vip_level_name }}</span>
              </td>
              <td>{{ $u->today_purchases_count }}</td>
              <td>{{ $u->daily_purchase_limit }}</td>
              <td>{{ $u->remaining_purchases_today }}</td>
              <td>
                <form method="POST" action="{{ route('admin.vip.change-user', $u) }}" class="form-inline">
                  @csrf
                  <select name="vip_level_id" class="form-control form-control-sm mr-2" required>
                    @foreach($vipLevels as $level)
                      <option value="{{ $level->id }}" {{ $u->vip_level_id == $level->id ? 'selected' : '' }}>
                        L{{ $level->level }} - {{ $level->name }} ({{ $level->daily_purchase_limit }}/day)
                      </option>
                    @endforeach
                  </select>
                  <button type="submit" class="btn btn-sm btn-primary">Update</button>
                </form>
              </td>
              <td>
                <form method="POST" action="{{ route('admin.vip.reset-today', $u) }}">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-warning">Reset Today</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-center mt-3">
      {{ $users->links() }}
    </div>
  </div>
</div>
@endsection