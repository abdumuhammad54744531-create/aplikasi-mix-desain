@extends('layouts.app')
@section('title','Akun Pengguna')
@section('subtitle','Kelola akun petugas laboratorium dan pemohon')
@section('content')
<div class="row g-4">
 <div class="col-lg-4">
  <div class="card p-4">
   <h5 class="fw-bold mb-3">Tambah Akun</h5>
   <form method="post" action="{{route('accounts.store')}}">@csrf
    <label class="form-label">Jenis akun</label><select name="role" class="form-select mb-2" id="new-account-role"><option value="teknisi">Petugas laboratorium</option><option value="pemohon">Pemohon pekerjaan laboratorium</option></select>
    <label class="form-label">Nama</label><input name="name" class="form-control mb-2" required>
    <label class="form-label">Nama pengguna</label><input name="username" class="form-control mb-2" required>
    <label class="form-label">Alamat surel</label><input type="email" name="email" class="form-control mb-2" required>
    <label class="form-label">Instansi/Perusahaan</label><input name="institution" class="form-control mb-2">
    <label class="form-label">Hak akses petugas</label><select name="access_level" class="form-select mb-2"><option value="read">Baca saja</option><option value="edit">Baca dan ubah</option></select>
    <label class="form-label">Kata sandi awal</label><input type="password" name="password" minlength="8" class="form-control mb-2" required>
    <label class="form-label">Konfirmasi kata sandi</label><input type="password" name="password_confirmation" minlength="8" class="form-control mb-3" required>
    <button class="btn btn-primary w-100">Tambah Akun</button>
   </form>
  </div>
 </div>
 <div class="col-lg-8">
  <div class="card overflow-hidden">
   <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Pengguna/Identitas</th><th>Nama pengguna</th><th>Jenis/Akses</th><th>Status</th><th></th></tr></thead><tbody>
   @foreach($users as $user)
   <tr><form method="post" action="{{route('accounts.update',$user)}}">@csrf @method('patch')
    <td><input name="name" class="form-control form-control-sm" value="{{$user->name}}" placeholder="Nama lengkap"><input type="email" name="email" class="form-control form-control-sm mt-1" value="{{$user->email}}" placeholder="Alamat surel"><input name="institution" class="form-control form-control-sm mt-1" value="{{$user->institution}}" placeholder="Instansi/perusahaan"><input name="employee_number" class="form-control form-control-sm mt-1" value="{{$user->employee_number}}" placeholder="NIP/Identitas"><input name="position" class="form-control form-control-sm mt-1" value="{{$user->position}}" placeholder="Jabatan"><input name="approval_authority" class="form-control form-control-sm mt-1" value="{{$user->approval_authority}}" placeholder="Kewenangan persetujuan"></td>
    <td><input name="username" class="form-control form-control-sm" value="{{$user->username}}"></td>
    <td>
     @if(in_array($user->role,['admin','administrator'],true))
      <span class="badge text-bg-dark">Administrator</span><input type="hidden" name="access_level" value="edit">
     @else
      <select name="role" class="form-select form-select-sm mb-1"><option value="teknisi" @selected($user->role==='teknisi')>Petugas laboratorium</option><option value="pemohon" @selected($user->role==='pemohon')>Pemohon</option></select>
      <select name="access_level" class="form-select form-select-sm" @disabled($user->role==='pemohon')><option value="read" @selected($user->access_level==='read')>Baca saja</option><option value="edit" @selected($user->access_level==='edit')>Baca dan ubah</option></select>
      @if($user->role==='pemohon')<input type="hidden" name="access_level" value="read">@endif
     @endif
    </td>
    <td><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($user->is_active) @disabled($user->id===auth()->id())><label class="small">Aktif</label></div></td>
    <td><button class="btn btn-sm btn-outline-primary">Perbarui</button></td>
   </form></tr>
   @endforeach
   </tbody></table></div>
  </div>
 </div>
</div>
@endsection
