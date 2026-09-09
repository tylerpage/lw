@php $projects = \App\Models\Project::query()->published()->where('featured', true)->limit($block['limit'] ?? 3)->get(); @endphp
@if(!empty($block['heading']))<h2 class="font-display text-3xl font-semibold">{{ $block['heading'] }}</h2>@endif
<div class="mt-8 grid gap-6 md:grid-cols-3">
    @forelse($projects as $project)
        <a href="{{ route('work.show', $project->slug) }}" class="group rounded-2xl bg-warm-white p-6 shadow-sm hover:shadow-md">
            <h3 class="font-display text-xl font-semibold group-hover:text-plum">{{ $project->title }}</h3>
            <p class="mt-2 text-sm text-charcoal/70">{{ $project->card_summary }}</p>
        </a>
    @empty
        <p class="text-charcoal/60">Featured projects will appear here when published.</p>
    @endforelse
</div>
