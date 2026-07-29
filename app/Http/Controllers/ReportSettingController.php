<?php
namespace App\Http\Controllers;
use App\Models\ReportSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class ReportSettingController extends Controller {
 private function admin():void{abort_unless(in_array(auth()->user()->role,['admin','administrator']),403);}
 public function edit(){ $this->admin();return view('report-settings.edit',['setting'=>ReportSetting::firstOrCreate([])]); }
 public function update(Request $r){
  $this->admin();
  $d=$r->validate(['margin_top'=>'required|numeric|between:5,40','margin_right'=>'required|numeric|between:5,40','margin_bottom'=>'required|numeric|between:5,40','margin_left'=>'required|numeric|between:5,40','font_family'=>'required|in:Arial,Calibri,Times New Roman,Verdana','font_size'=>'required|numeric|between:8,16','signer_name'=>'required|max:255','signer_position'=>'required|max:255','preface_template'=>'nullable|max:5000','logo_left'=>'nullable|image|max:4096','logo_right'=>'nullable|image|max:4096','logo_left_position'=>'required|in:left,center,right','logo_right_position'=>'required|in:left,center,right','logo_left_width'=>'required|numeric|between:8,35','logo_right_width'=>'required|numeric|between:8,35']);
  $setting=ReportSetting::firstOrCreate([]);
  foreach(['logo_left','logo_right'] as $field){
   if(!$r->hasFile($field)){unset($d[$field]);continue;}
   $file=$r->file($field);
   if(!$file->isValid())return back()->withInput()->withErrors([$field=>'Berkas gagal diterima oleh server. Pilih ulang gambar PNG/JPG maksimal 4 MB.']);
   $extension=strtolower($file->extension()?:'png');
   $path=$file->storeAs('report-settings',Str::uuid().'.'.$extension,['disk'=>'public']);
   if(!$path)return back()->withInput()->withErrors([$field=>'Logo gagal disimpan. Pastikan folder penyimpanan dapat ditulis.']);
   if($setting->$field)Storage::disk('public')->delete($setting->$field);
   $d[$field]=$path;
  }
  $setting->update($d);return back()->with('success','Pengaturan dan posisi logo berhasil disimpan.');
 }
}
