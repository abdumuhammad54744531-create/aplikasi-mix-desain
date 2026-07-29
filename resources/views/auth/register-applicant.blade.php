<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Daftar Pemohon — Desain Campuran Beton</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{min-height:100vh;background:linear-gradient(135deg,#071e2d,#0a5d59);font-family:system-ui;padding:35px 15px}
.register-card{max-width:760px;margin:auto;background:#fff;border-radius:20px;box-shadow:0 25px 80px #00101866;overflow:hidden}
.heading{background:#0b2c3d;color:#fff;padding:30px 38px;border-bottom:4px solid #16a394}.form-area{padding:34px 38px}
</style>
</head>
<body>
<main class="register-card">
 <div class="heading"><div class="small text-white-50">UNIVERSITAS MUHAMMADIYAH BUTON</div><h2 class="fw-bold mb-1">Pendaftaran Akun Pemohon</h2><p class="mb-0 text-white-50">Buat akun untuk mengirim dan memantau permohonan pengujian laboratorium.</p></div>
 <div class="form-area">
  @if($errors->any())<div class="alert alert-danger"><b>Periksa kembali data:</b><ul class="mb-0">@foreach($errors->all() as $error)<li>{{$error}}</li>@endforeach</ul></div>@endif
  <form method="post" action="{{route('applicant.register.store')}}" class="row g-3">@csrf
   <div class="col-md-6"><label class="form-label">Nama lengkap</label><input name="name" class="form-control" value="{{old('name')}}" required></div>
   <div class="col-md-6"><label class="form-label">Instansi/Perusahaan</label><input name="institution" class="form-control" value="{{old('institution')}}"></div>
   <div class="col-md-6"><label class="form-label">Nama pengguna</label><input name="username" class="form-control" value="{{old('username')}}" required><div class="form-text">Gunakan huruf, angka, garis bawah, atau tanda hubung.</div></div>
   <div class="col-md-6"><label class="form-label">Alamat surel</label><input type="email" name="email" class="form-control" value="{{old('email')}}" required></div>
   <div class="col-md-6"><label class="form-label">Kata sandi</label><input type="password" name="password" minlength="8" class="form-control" required></div>
   <div class="col-md-6"><label class="form-label">Konfirmasi kata sandi</label><input type="password" name="password_confirmation" minlength="8" class="form-control" required></div>
   <div class="col-12 d-flex justify-content-between align-items-center mt-4"><a href="{{route('lab-services.brochure')}}" class="text-decoration-none">Kembali ke brosur layanan</a><button class="btn btn-success px-4">Daftar dan Buat Permohonan</button></div>
  </form>
 </div>
</main>
</body>
</html>
