<?php

namespace App\Livewire;

use App\Enums\ContactSubmissionStatus;
use App\Http\Requests\ContactFormRequest;
use App\Models\ContactSubmission;
use App\Models\SiteSetting;
use App\Notifications\NewContactSubmission;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Component;

class ContactForm extends Component
{
    public string $name = '';

    public string $email = '';

    public ?string $organization = null;

    public ?string $reason = null;

    public string $message = '';

    public string $website = '';

    public int $form_started_at;

    public bool $submitted = false;

    public function mount(): void
    {
        $this->form_started_at = now()->timestamp;
    }

    public function submit(): void
    {
        $key = 'contact-form:'.request()->ip();
        $emailKey = 'contact-form:'.strtolower($this->email);

        if (RateLimiter::tooManyAttempts($key, 5) || RateLimiter::tooManyAttempts($emailKey, 3)) {
            $this->addError('form', 'Too many submissions. Please try again later.');

            return;
        }

        $validated = $this->validate((new ContactFormRequest)->rules());

        if (filled($this->website)) {
            RateLimiter::hit($key, 3600);

            return;
        }

        if (now()->timestamp - $this->form_started_at < 3) {
            $this->addError('form', 'Please wait a moment before submitting.');

            return;
        }

        $submission = ContactSubmission::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'organization' => $validated['organization'] ?? null,
            'reason' => $validated['reason'] ?? null,
            'message' => $validated['message'],
            'status' => ContactSubmissionStatus::New,
            'ip_address' => request()->ip(),
            'user_agent' => (string) request()->userAgent(),
        ]);

        $recipient = SiteSetting::get('contact_email', config('mail.from.address'));

        if ($recipient) {
            Notification::route('mail', $recipient)->notify(new NewContactSubmission($submission));
        }

        RateLimiter::hit($key, 3600);
        RateLimiter::hit($emailKey, 3600);

        $this->reset(['name', 'email', 'organization', 'reason', 'message', 'website']);
        $this->form_started_at = now()->timestamp;
        $this->submitted = true;

        $this->dispatch('analytics-event', ['event' => 'contact_form_submit', 'properties' => ['placement' => 'contact_page']]);
    }

    public function updated(): void
    {
        if ($this->name || $this->email || $this->message) {
            $this->dispatch('analytics-event', ['event' => 'contact_form_start', 'properties' => ['placement' => 'contact_page']]);
        }
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
