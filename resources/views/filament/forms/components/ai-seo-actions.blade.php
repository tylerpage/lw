<div class="ai-seo-actions">
    <div>
        <p class="ai-seo-actions__title">AI SEO quick actions</p>
        <p class="ai-seo-actions__description">
            Generate suggestions from the current title and content. Review before saving.
        </p>
    </div>

    <div class="ai-seo-actions__buttons">
        <x-filament::button
            type="button"
            size="sm"
            wire:click="generateSeo('from_content')"
            wire:loading.attr="disabled"
            wire:target="generateSeo"
        >
            Generate all SEO
        </x-filament::button>

        <x-filament::button
            type="button"
            color="gray"
            size="sm"
            wire:click="generateSeo('meta_only')"
            wire:loading.attr="disabled"
            wire:target="generateSeo"
        >
            Meta title &amp; description
        </x-filament::button>

        <x-filament::button
            type="button"
            color="gray"
            size="sm"
            wire:click="generateSeo('open_graph_only')"
            wire:loading.attr="disabled"
            wire:target="generateSeo"
        >
            Open Graph only
        </x-filament::button>

        <x-filament::button
            type="button"
            color="gray"
            size="sm"
            wire:click="generateSeo('improve_existing')"
            wire:loading.attr="disabled"
            wire:target="generateSeo"
        >
            Improve existing SEO
        </x-filament::button>
    </div>

    <p class="ai-seo-actions__hint" wire:loading wire:target="generateSeo">
        Generating SEO suggestions…
    </p>
</div>
