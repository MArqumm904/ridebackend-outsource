<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class AppController extends Controller
{
    // Public app configuration used by splash screen and initial app load
    public function config(Request $request)
    {
        $data = [
            'app_name' => Setting::get('app.name', config('app.name')),
            'support_email' => Setting::get('support.email', config('mail.from.address')),
            'support_phone' => Setting::get('support.phone', null),
            'terms_version' => Setting::get('terms.version', '1.0.0'),
            'privacy_version' => Setting::get('privacy.version', '1.0.0'),
            'min_app_version' => [
                'android' => Setting::get('app.min_version.android', '1.0.0'),
                'ios' => Setting::get('app.min_version.ios', '1.0.0'),
            ],
            'force_update' => Setting::get('app.force_update', false, 'bool'),
            'assets' => [
                'splash_logo_url' => Setting::get('assets.splash_logo_url', null),
                'primary_color' => Setting::get('theme.primary_color', '#1E88E5'),
                'secondary_color' => Setting::get('theme.secondary_color', '#FFC107'),
            ],
        ];

        return response()->json(['status' => true, 'data' => $data]);
    }

    // Static pages content: terms
    public function terms()
    {
        $content = Setting::get('content.terms', 'Terms and conditions will be provided soon.');
        $version = Setting::get('terms.version', '1.0.0');
        return response()->json(['status' => true, 'version' => $version, 'content' => $content]);
    }

    // Static pages content: privacy
    public function privacy()
    {
        $content = Setting::get('content.privacy', 'Privacy policy will be provided soon.');
        $version = Setting::get('privacy.version', '1.0.0');
        return response()->json(['status' => true, 'version' => $version, 'content' => $content]);
    }
}
