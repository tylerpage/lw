@php $companies = \App\Models\CareerCompany::query()->with('roles')->orderBy('sort_order')->get(); @endphp
@if(!empty($block['heading']))<h2 class="font-display text-3xl font-semibold">{{ $block['heading'] }}</h2>@endif
<div class="mt-8 space-y-8">
    @foreach($companies as $company)
        <div>
            <h3 class="font-display text-xl font-semibold">{{ $company->name }}</h3>
            @if($company->location)<p class="text-sm text-charcoal/60">{{ $company->location }}</p>@endif
            <div class="mt-4 space-y-4 border-l-2 border-golden/40 pl-6">
                @foreach($company->roles as $role)
                    <div>
                        <p class="font-semibold">{{ $role->title }}</p>
                        <p class="text-sm text-charcoal/60">{{ $role->dateRangeLabel() }}</p>
                        @if($role->summary)<p class="mt-2 text-sm text-charcoal/80">{{ $role->summary }}</p>@endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
