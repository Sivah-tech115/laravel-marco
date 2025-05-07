<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SeoSetting;
use App\Models\Setting;

class SeoSettingController extends Controller
{

    public function edit()
    {
        $seo = SeoSetting::firstOrCreate([]);
        return view('Admin.seo', compact('seo'));
    }

    public function update(Request $request)
    {
        $seo = SeoSetting::first();
        $seo->update([
            'header_scripts' => $request->header_scripts,
            'footer_scripts' => $request->footer_scripts,
        ]);

        return back()->with('success', 'SEO scripts updated successfully!');
    }

    public function Settingedit()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('Admin.settings', compact('settings'));
    }

    public function Settingupdate(Request $request)
    {
        foreach (['meta_description', 'meta_keywords', 'meta_title'] as $key) {
            Setting::updateOrCreate(['key' => $key], ['value' => $request->input($key)]);
        }

        return back()->with('success', 'Settings updated.');
    }
}
