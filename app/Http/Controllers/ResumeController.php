<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class ResumeController extends Controller
{
    public function __invoke(): Response
    {
        $path = SiteSetting::get('resume_path');

        if (! $path || ! Storage::disk('public')->exists($path)) {
            abort(404);
        }

        return response()->download(
            Storage::disk('public')->path($path),
            SiteSetting::get('resume_filename', 'resume.pdf')
        );
    }
}
