<?php

namespace App\Http\Controllers;

use App\Models\LaboratoryProfile;
use App\Models\Project;
use App\Models\ReportSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ReportSettingController extends Controller
{
    public function edit()
    {
        return view('report-settings.edit', [
            'setting' => ReportSetting::firstOrCreate([]),
            'laboratory' => LaboratoryProfile::first(),
        ]);
    }

    public function preview()
    {
        return view('report-settings.preview', [
            'setting' => ReportSetting::firstOrCreate([]),
            'laboratory' => LaboratoryProfile::first(),
            'project' => Project::latest()->first(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'margin_top' => 'required|numeric|between:5,40', 'margin_right' => 'required|numeric|between:5,40',
            'margin_bottom' => 'required|numeric|between:5,40', 'margin_left' => 'required|numeric|between:5,40',
            'font_family' => ['required', Rule::in(ReportSetting::FONT_FAMILIES)], 'font_size' => 'required|numeric|between:8,16',
            'report_heading_size' => 'required|numeric|between:10,24', 'report_subheading_size' => 'required|numeric|between:9,20',
            'report_table_size' => 'required|numeric|between:8,14', 'report_caption_size' => 'required|numeric|between:8,14',
            'report_line_height' => 'required|numeric|between:1,2',
            'signer_name' => 'required|max:255', 'signer_position' => 'required|max:255', 'signer_identity' => 'nullable|max:255',
            'examiner_address' => 'nullable|max:5000', 'examiner_city' => 'nullable|max:255', 'examiner_province' => 'nullable|max:255',
            'examiner_postal_code' => 'nullable|max:20', 'examiner_phone' => 'nullable|max:100',
            'examiner_email' => 'nullable|email|max:255', 'examiner_website' => 'nullable|max:255',
            'preface_template' => 'nullable|max:5000',
            'logo_left' => 'nullable|image|max:4096', 'logo_right' => 'nullable|image|max:4096',
            'signature_image' => 'nullable|image|max:4096', 'stamp_image' => 'nullable|image|max:4096',
            'logo_left_position' => 'required|in:left,center,right', 'logo_right_position' => 'required|in:left,center,right',
            'logo_left_width' => 'required|numeric|between:8,50', 'logo_right_width' => 'required|numeric|between:8,50',
            'logo_left_height' => 'nullable|numeric|between:5,50', 'logo_right_height' => 'nullable|numeric|between:5,50',
            'logo_left_x' => 'required|numeric|between:-50,50', 'logo_left_y' => 'required|numeric|between:-50,50',
            'logo_right_x' => 'required|numeric|between:-50,50', 'logo_right_y' => 'required|numeric|between:-50,50',
            'header_lines_enabled' => 'nullable|boolean', 'header_line_1_width' => 'required|numeric|between:0,5',
            'header_line_2_width' => 'required|numeric|between:0,5', 'header_line_gap' => 'required|numeric|between:0,10',
            'header_to_line_gap' => 'required|numeric|between:0,20', 'line_to_content_gap' => 'required|numeric|between:0,25',
            'header_lines' => 'required|array|size:5', 'header_lines.*.text' => 'nullable|max:255',
            'header_lines.*.size' => 'required|numeric|between:7,32', 'header_lines.*.font' => ['required', Rule::in(ReportSetting::FONT_FAMILIES)],
            'header_lines.*.align' => 'required|in:left,center,right', 'header_lines.*.margin_top' => 'required|numeric|between:0,20',
            'header_lines.*.margin_bottom' => 'required|numeric|between:0,20', 'header_lines.*.line_height' => 'required|numeric|between:0.8,2',
            'header_lines.*.bold' => 'nullable|boolean', 'header_lines.*.italic' => 'nullable|boolean', 'header_lines.*.uppercase' => 'nullable|boolean',
        ]);
        $data['header_lines_enabled'] = $request->boolean('header_lines_enabled');
        foreach ($data['header_lines'] as $index => &$line) {
            foreach (['bold', 'italic', 'uppercase'] as $flag) {
                $line[$flag] = $request->boolean("header_lines.$index.$flag");
            }
        }
        unset($line);

        $setting = ReportSetting::firstOrCreate([]);
        foreach (['logo_left', 'logo_right', 'signature_image', 'stamp_image'] as $field) {
            if (! $request->hasFile($field)) {
                unset($data[$field]);
                continue;
            }
            $file = $request->file($field);
            $path = $file->storeAs('report-settings', Str::uuid().'.'.strtolower($file->extension() ?: 'png'), 'public');
            if ($setting->$field) {
                Storage::disk('public')->delete($setting->$field);
            }
            $data[$field] = $path;
        }
        $setting->update($data);

        return back()->with('success', 'Pengaturan laporan, kop, tipografi, dan penandatangan berhasil disimpan.');
    }
}
