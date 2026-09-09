<?php

namespace Tests\Feature;

use App\Livewire\ContactForm;
use App\Notifications\NewContactSubmission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Notification::fake();
    }

    public function test_contact_form_submits_successfully(): void
    {
        Livewire::test(ContactForm::class)
            ->set('name', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('message', 'Hello, I would like to connect about a role.')
            ->set('form_started_at', now()->subSeconds(10)->timestamp)
            ->call('submit')
            ->assertSet('submitted', true);

        $this->assertDatabaseHas('contact_submissions', [
            'email' => 'jane@example.com',
            'name' => 'Jane Doe',
        ]);

        Notification::assertSentOnDemand(NewContactSubmission::class);
    }

    public function test_honeypot_blocks_submission(): void
    {
        Livewire::test(ContactForm::class)
            ->set('name', 'Spammer')
            ->set('email', 'spam@example.com')
            ->set('message', 'Buy now')
            ->set('website', 'http://spam.test')
            ->set('form_started_at', now()->subSeconds(10)->timestamp)
            ->call('submit');

        $this->assertDatabaseCount('contact_submissions', 0);
    }

    public function test_fast_submission_is_rejected(): void
    {
        Livewire::test(ContactForm::class)
            ->set('name', 'Jane Doe')
            ->set('email', 'jane@example.com')
            ->set('message', 'Too fast')
            ->set('form_started_at', now()->timestamp)
            ->call('submit')
            ->assertHasErrors(['form']);

        $this->assertDatabaseCount('contact_submissions', 0);
    }
}
