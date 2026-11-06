@extends('layouts.app')

@section('title', 'Kelola Admin')

@section('content')
@include('partials.page-hero', [
    'crumb' => '<a href="'.route('admin.dashboard').'">Dasbor</a> / Admin',
    'title' => 'Kelola Admin',
    'subtitle' => 'Hanya admin yang sudah login yang bisa menambah admin baru.',
])

<div class="page-body">
    <div class="container">
        <div class="row">
            <div class="col-lg-7 mb-4">
                <div class="card-soft pad-lg" data-reveal="left">
                    <div class="card-head"><h5><i class="fas fa-user-shield text-primary mr-2"></i>Tambah admin baru</h5></div>
                    @if ($errors->any())
                        <div class="alert alert-danger">@foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>
                    @endif
                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" value="{{ old('username') }}" class="form-control" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="password">Password</label>
                                <input type="password" id="password" name="password" class="form-control" required minlength="6">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="password_confirmation">Konfirmasi password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-plus mr-1"></i>Simpan Admin</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-5 mb-4">
                <div class="card-soft pad-lg" data-reveal="right">
                    <div class="card-head"><h5>Daftar admin</h5><span class="badge badge-soft">{{ $admins->count() }}</span></div>
                    @foreach ($admins as $admin)
                        <div class="appt">
                            <div class="avatar-3d sm">{{ strtoupper(mb_substr($admin->username, 0, 2)) }}</div>
                            <div class="appt-info"><h6>{{ $admin->username }}</h6><small>{{ $admin->email }}</small></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
