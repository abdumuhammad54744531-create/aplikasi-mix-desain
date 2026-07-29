<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Masuk — Desain Campuran Beton</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
body{min-height:100vh;background:linear-gradient(135deg,#071e2d,#0a5d59);display:grid;place-items:center;font-family:system-ui}
.login{width:min(920px,92vw);background:#fff;border-radius:20px;overflow:hidden;box-shadow:0 25px 80px #00101866}
.hero{background:#0b2c3d;color:#fff;padding:48px}
.creator{border-left:3px solid #39c0ad;padding-left:14px;margin-bottom:34px}
.creator span,.creator small{display:block;color:#a9c6ce}
.creator strong{display:block;font-size:1.04rem;letter-spacing:.02em}
.hero .icon{font-size:50px;color:#39c0ad}
.form-side{padding:58px}.form-control{padding:.8rem}.btn{padding:.75rem}
.creator-mobile{background:#edf8f6;border-left:3px solid #198f80;padding:10px 12px;margin-bottom:24px}
@media(max-width:767.98px){.form-side{padding:38px 30px}}
</style>
</head>
<body>
<div class="login row g-0">
 <div class="hero col-md-6 d-none d-md-flex flex-column justify-content-between">
  <div>
   <div class="creator">
    <span>Dikembangkan oleh</span>
    <strong>MUHAMMAD ABDU, S.T., M.T.</strong>
    <small>Universitas Muhammadiyah Buton</small>
   </div>
   <i class="bi bi-buildings icon"></i>
   <h1 class="mt-4 fw-bold">DESAIN CAMPURAN<br>BETON</h1>
   <p class="text-white-50">Sistem laboratorium beton terintegrasi berdasarkan SNI 7656:2012.</p>
  </div>
  <small>Perencanaan • Pengujian • Evaluasi • Pelaporan</small>
 </div>
 <div class="form-side col-md-6">
  <div class="creator-mobile d-md-none">
   <small class="text-secondary d-block">Dikembangkan oleh</small>
   <strong>MUHAMMAD ABDU, S.T., M.T.</strong>
   <small class="text-secondary d-block">Universitas Muhammadiyah Buton</small>
  </div>
  <h3 class="fw-bold">Selamat datang</h3>
  <p class="text-secondary mb-4">Masuk ke ruang kerja laboratorium.</p>
  @if($errors->any())<div class="alert alert-danger">{{$errors->first()}}</div>@endif
  <form method="post" action="/login">
   @csrf
   <label class="form-label">Nama pengguna</label>
   <input class="form-control mb-3" name="username" value="{{old('username')}}" required autofocus>
   <label class="form-label">Kata sandi</label>
   <input class="form-control mb-3" type="password" name="password" required>
   <div class="form-check mb-4"><input class="form-check-input" type="checkbox" name="remember" id="r"><label class="form-check-label" for="r">Ingat saya</label></div>
   <button class="btn btn-success w-100">Masuk <i class="bi bi-arrow-right ms-2"></i></button>
  </form>
  <div class="small text-secondary mt-4">Akun awal: <b>admin</b> • kata sandi sementara wajib diganti.</div>
  <div class="border-top mt-4 pt-3 text-center"><span class="small text-secondary">Memerlukan pengujian laboratorium?</span><br><a href="{{route('lab-services.brochure')}}" class="fw-semibold text-decoration-none">Buka brosur dan ajukan permohonan</a></div>
 </div>
</div>
</body>
</html>
