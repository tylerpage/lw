<?php

namespace App\Http\Controllers;

use App\Models\Discipline;
use App\Models\Industry;
use App\Models\Project;
use App\Services\PageBlockRenderer;
use App\Services\SeoService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkController extends Controller
{
    public function index(Request $request, SeoService $seo): View
    {
        $query = Project::query()->published()->orderBy('sort_order');

        if ($discipline = $request->string('discipline')->toString()) {
            $query->whereHas('disciplines', fn ($q) => $q->where('slug', $discipline));
        }

        if ($industry = $request->string('industry')->toString()) {
            $query->whereHas('industries', fn ($q) => $q->where('slug', $industry));
        }

        $projects = $query->paginate(12);

        return view('work.index', [
            'projects' => $projects,
            'disciplines' => Discipline::query()->has('projects')->orderBy('name')->get(),
            'industries' => Industry::query()->has('projects')->orderBy('name')->get(),
            'seo' => [
                'title' => 'Work',
                'description' => 'Selected case studies and project work.',
                'canonical' => url('/work'),
                'robots' => 'index, follow',
            ],
        ]);
    }

    public function show(string $slug, PageBlockRenderer $renderer, SeoService $seo): View
    {
        $project = Project::query()->published()->where('slug', $slug)->firstOrFail();

        return view('work.show', [
            'project' => $project,
            'content' => $renderer->render($project->blocks ?? []),
            'seo' => $seo->forProject($project),
            'related' => Project::query()
                ->published()
                ->where('id', '!=', $project->id)
                ->limit(3)
                ->get(),
        ]);
    }
}
