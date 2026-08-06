<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield('title','Dasbor') — Desain Campuran Beton</title>
<meta name="csrf-token" content="{{csrf_token()}}">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
@vite('resources/js/app.js')
<style>
:root{--navy:#0b2638;--teal:#0a796f;--pale:#edf5f3;--ink:#18323e}html,body{height:100%}body{background:#f3f6f7;color:var(--ink);font-family:Inter,system-ui,sans-serif;overflow:hidden}
.sidebar{width:260px;background:var(--navy);height:100vh;position:fixed;top:0;left:0;color:#fff;padding:24px 14px;overflow-y:auto;overscroll-behavior:contain}.brand{border-bottom:1px solid #ffffff24;padding:0 10px 22px;margin-bottom:18px}.brand-mark{width:42px;height:42px;background:#16a394;border-radius:12px;display:grid;place-items:center;font-size:22px}.sidebar .nav-link{color:#bfd0d7;border-radius:8px;padding:9px 12px;margin:2px 0;font-size:.9rem}.sidebar .nav-link:hover,.sidebar .nav-link.active{background:#ffffff16;color:#fff}.sidebar small{color:#6f929e;letter-spacing:.12em;font-size:.65rem;margin:18px 12px 6px;display:block}.content{margin-left:260px;height:100vh;overflow-y:auto;overscroll-behavior:contain}.topbar{background:#fff;border-bottom:1px solid #dfe7e9;padding:11px 28px;position:sticky;top:0;z-index:10;min-height:67px}.app-author{margin-left:auto;margin-right:24px;padding-left:14px;border-left:3px solid #16a394;line-height:1.2}.app-author .label{font-size:.64rem;color:#71838a;text-transform:uppercase;letter-spacing:.08em}.app-author .name{font-size:.78rem;font-weight:700;color:#18323e}.app-author .institution{font-size:.67rem;color:#71838a}.page{padding:28px}.card{border:0;box-shadow:0 3px 14px #122c3810;border-radius:12px}.metric-icon{width:44px;height:44px;background:var(--pale);color:var(--teal);border-radius:10px;display:grid;place-items:center;font-size:20px}.btn-primary{background:var(--teal);border-color:var(--teal)}.badge-soft{background:#e2f3ef;color:#06776c}.form-label{font-weight:600;font-size:.84rem}.table thead th{font-size:.75rem;text-transform:uppercase;color:#6b7d84;background:#f7f9fa}.excel-paste-notice{position:fixed;right:24px;bottom:24px;z-index:2000;margin:0}.excel-paste-hint{font-size:.75rem;color:#6b7d84}
@media(max-width:640px){body{overflow:auto}.sidebar{position:relative;width:100%;height:auto;max-height:55vh}.sidebar .nav{display:flex}.content{margin:0;height:auto;overflow:visible}.topbar{position:sticky}.page{padding:18px}}
</style>@stack('head')</head><body>
@php($isApplicant=auth()->user()->role==='pemohon')
<aside class="sidebar"><div class="brand d-flex gap-3 align-items-center"><div class="brand-mark"><i class="bi bi-buildings"></i></div><div><b>DESAIN CAMPURAN</b><div class="small text-white-50">BETON • SNI 7656:2012</div></div></div>
<nav class="nav flex-column">
@if($isApplicant)
<small>LAYANAN PEMOHON</small>
<a class="nav-link {{request()->routeIs('lab-requests.*')?'active':''}}" href="{{route('lab-requests.index')}}"><i class="bi bi-file-earmark-text me-2"></i>Permohonan Uji Lab</a>
<a class="nav-link" href="{{route('lab-services.brochure')}}" target="_blank"><i class="bi bi-layout-text-window me-2"></i>Brosur Pengujian</a>
@else
<a class="nav-link {{request()->routeIs('dashboard')?'active':''}}" href="{{route('dashboard')}}"><i class="bi bi-grid me-2"></i>Dasbor</a>
<small>DATA UTAMA</small><a class="nav-link {{request()->routeIs('projects.*')?'active':''}}" href="{{route('projects.index')}}"><i class="bi bi-briefcase me-2"></i>Data Proyek</a>
<a class="nav-link {{request()->routeIs('materials.*')?'active':''}}" href="{{route('materials.index')}}"><i class="bi bi-box-seam me-2"></i>Sumber Material</a>
<small>PERENCANAAN</small><a class="nav-link {{request()->routeIs('material-tests.*')?'active':''}}" href="{{route('material-tests.index')}}"><i class="bi bi-clipboard2-pulse me-2"></i>Pemeriksaan Material</a><a class="nav-link {{request()->routeIs('jmd.material-tests.*')?'active':''}}" href="{{route('jmd.material-tests.projects')}}"><i class="bi bi-flask me-2"></i>Pengujian Material JMD</a><a class="nav-link {{request()->routeIs('mix-design-2012.*')?'active':''}}" href="{{route('mix-design-2012.create')}}"><i class="bi bi-calculator me-2"></i>Desain Campuran 2012</a>
<a class="nav-link {{request()->routeIs('mix-design-2012-combined.*')?'active':''}}" href="{{route('mix-design-2012-combined.create')}}"><i class="bi bi-bezier2 me-2"></i>Desain Campuran 2012 (Gradasi Gabungan)</a>
<a class="nav-link {{request()->routeIs('documentation.*')?'active':''}}" href="{{route('documentation.index')}}"><i class="bi bi-images me-2"></i>Dokumentasi</a>
@foreach([
['Kuat Tekan','compressive-strength']
] as [$item,$slug])<a class="nav-link {{request()->is('workflow/'.$slug)?'active':''}}" href="{{route('workflow.index',$slug)}}"><i class="bi bi-chevron-right me-2"></i>{{$item}}</a>@endforeach
<a class="nav-link {{request()->routeIs('workflow.reports')?'active':''}}" href="{{route('workflow.reports')}}"><i class="bi bi-chevron-right me-2"></i>Laporan</a>
<small>ADMINISTRASI</small>
@if(in_array(auth()->user()->role,['admin','administrator']))<a class="nav-link {{request()->routeIs('accounts.*')?'active':''}}" href="{{route('accounts.index')}}"><i class="bi bi-people me-2"></i>Akun Pengguna</a><a class="nav-link {{request()->routeIs('report-settings.*')?'active':''}}" href="{{route('report-settings.edit')}}"><i class="bi bi-file-earmark-gear me-2"></i>Pengaturan Laporan</a>@endif
<a class="nav-link {{request()->routeIs('archive.*')?'active':''}}" href="{{route('archive.index')}}"><i class="bi bi-archive me-2"></i>Arsip</a>
<a class="nav-link" href="{{route('audit')}}"><i class="bi bi-clock-history me-2"></i>Riwayat Audit</a>
<small>PELAYANAN</small><a class="nav-link {{request()->routeIs('lab-requests.*')?'active':''}}" href="{{route('lab-requests.index')}}"><i class="bi bi-file-earmark-text me-2"></i>Permohonan Uji Lab</a><a class="nav-link" href="{{route('lab-services.brochure')}}" target="_blank"><i class="bi bi-layout-text-window me-2"></i>Brosur Pengujian</a>
@endif
</nav></aside>
<main class="content"><div class="topbar d-flex align-items-center"><div><b>@yield('title')</b><div class="text-secondary small">@yield('subtitle','Laboratorium teknik sipil')</div></div>
<div class="app-author d-none d-xl-block"><div class="label">Dikembangkan oleh</div><div class="name">MUHAMMAD ABDU, S.T., M.T.</div><div class="institution">Universitas Muhammadiyah Buton</div></div>
<div class="d-flex align-items-center gap-2"><span class="badge badge-soft">{{$isApplicant?'Pemohon':(in_array(auth()->user()->role,['admin','administrator'])?'Administrator':(auth()->user()->access_level==='read'?'Baca saja':'Dapat mengubah'))}}</span><div class="text-end"><b class="small">{{auth()->user()->name}}</b><div class="text-secondary" style="font-size:.7rem">{{auth()->user()->username}}</div></div><button class="btn btn-sm btn-outline-secondary" title="Ganti kata sandi" aria-label="Ganti kata sandi" data-bs-toggle="modal" data-bs-target="#passwordModal"><i class="bi bi-key"></i></button><form method="post" action="{{route('logout')}}">@csrf<button class="btn btn-sm btn-outline-secondary" title="Keluar" aria-label="Keluar"><i class="bi bi-box-arrow-right"></i></button></form></div></div>
<div class="page">@if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
@if($errors->any())<div class="alert alert-danger"><b>Periksa kembali data:</b><ul class="mb-0">@foreach($errors->all() as $e)<li>{{$e}}</li>@endforeach</ul></div>@endif @yield('content')</div></main>
<div class="modal fade" id="passwordModal"><div class="modal-dialog"><form method="post" action="{{route('account.password')}}" class="modal-content">@csrf @method('patch')<div class="modal-header"><h5 class="modal-title">Ganti Kata Sandi</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body"><label class="form-label">Kata sandi lama</label><input type="password" name="current_password" class="form-control mb-3" required><label class="form-label">Kata sandi baru</label><input type="password" name="password" class="form-control mb-3" minlength="8" required><label class="form-label">Konfirmasi kata sandi baru</label><input type="password" name="password_confirmation" class="form-control" minlength="8" required></div><div class="modal-footer"><button class="btn btn-primary">Simpan Kata Sandi</button></div></form></div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
<script>
(() => {
 const sidebar=document.querySelector('.sidebar'),content=document.querySelector('.content');
 if(!sidebar)return;
 const sidebarKey='mix-design-sidebar-scroll',contentKey='mix-design-content-scroll:'+location.pathname+location.search;
 const savedSidebar=Number(sessionStorage.getItem(sidebarKey)||0);
 const savedContent=Number(sessionStorage.getItem(contentKey)||0);
 requestAnimationFrame(()=>{sidebar.scrollTop=savedSidebar;if(content)content.scrollTop=savedContent});
 sidebar.addEventListener('scroll',()=>sessionStorage.setItem(sidebarKey,String(sidebar.scrollTop)),{passive:true});
 content?.addEventListener('scroll',()=>sessionStorage.setItem(contentKey,String(content.scrollTop)),{passive:true});
 document.querySelectorAll('.sidebar a').forEach(link=>link.addEventListener('click',()=>sessionStorage.setItem(sidebarKey,String(sidebar.scrollTop))));
})();
</script>@stack('scripts')</body></html>
