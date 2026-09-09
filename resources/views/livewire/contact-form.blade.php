<div>
    @if($submitted)
        <div class="rounded-2xl bg-lavender/30 p-6" role="status">
            <h2 class="font-display text-xl font-semibold">Thanks for reaching out!</h2>
            <p class="mt-2 text-charcoal/80">Your message has been received. I'll get back to you soon.</p>
        </div>
    @else
        <form wire:submit="submit" class="space-y-6">
            @error('form')
                <div class="rounded-lg bg-blush/40 p-4 text-sm" role="alert">{{ $message }}</div>
            @enderror

            <div>
                <label for="name" class="block text-sm font-medium">Name</label>
                <input wire:model="name" type="text" id="name" required class="mt-1 w-full rounded-lg border border-blush/50 px-3 py-2">
                @error('name')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium">Email</label>
                <input wire:model="email" type="email" id="email" required class="mt-1 w-full rounded-lg border border-blush/50 px-3 py-2">
                @error('email')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="organization" class="block text-sm font-medium">Organization <span class="text-charcoal/50">(optional)</span></label>
                <input wire:model="organization" type="text" id="organization" class="mt-1 w-full rounded-lg border border-blush/50 px-3 py-2">
            </div>

            <div>
                <label for="reason" class="block text-sm font-medium">Reason for reaching out <span class="text-charcoal/50">(optional)</span></label>
                <input wire:model="reason" type="text" id="reason" class="mt-1 w-full rounded-lg border border-blush/50 px-3 py-2">
            </div>

            <div>
                <label for="message" class="block text-sm font-medium">Message</label>
                <textarea wire:model="message" id="message" rows="5" required class="mt-1 w-full rounded-lg border border-blush/50 px-3 py-2"></textarea>
                @error('message')<p class="mt-1 text-sm text-red-700">{{ $message }}</p>@enderror
            </div>

            <div class="hidden" aria-hidden="true">
                <label for="website">Website</label>
                <input wire:model="website" type="text" id="website" tabindex="-1" autocomplete="off">
            </div>

            <input type="hidden" wire:model="form_started_at">

            <button type="submit" class="rounded-full bg-golden px-6 py-3 font-semibold text-charcoal hover:bg-golden/90" wire:loading.attr="disabled">
                Send message
            </button>
        </form>
    @endif
</div>
