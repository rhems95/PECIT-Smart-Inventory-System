<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OllamaChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_available_by_type_question_does_not_call_ollama(): void
    {
        config(['psis.ollama.enabled' => true]);
        Http::fake();

        $user = User::factory()->create();
        $user->assignRole('Supply Personnel');

        $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'how many available item by type in inventory'])
            ->assertOk()
            ->assertJsonFragment(['reply' => 'Inventory has 0 item type(s). On hand 0, reserved 0, available 0.']);

        Http::assertNothingSent();
    }

    public function test_listed_question_does_not_call_ollama(): void
    {
        config(['psis.ollama.enabled' => true]);
        Http::fake();

        $user = User::factory()->create();
        $user->assignRole('Supply Personnel');

        $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'Low stock'])
            ->assertOk()
            ->assertJsonStructure(['reply']);

        Http::assertNothingSent();
    }

    public function test_free_text_uses_local_ollama_when_enabled(): void
    {
        config([
            'psis.ollama.enabled' => true,
            'psis.ollama.url' => 'http://127.0.0.1:11434',
            'psis.ollama.model' => 'llama3.2:3b',
        ]);

        Http::fake([
            'http://127.0.0.1:11434/api/chat' => Http::response([
                'message' => ['content' => 'Bond paper has 12 available according to live facts.'],
            ], 200),
        ]);

        $user = User::factory()->create();
        $user->assignRole('Supply Personnel');

        $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'How much bond paper do we have?'])
            ->assertOk()
            ->assertJsonPath('reply', 'Bond paper has 12 available according to live facts.');

        Http::assertSent(function ($request) {
            $payload = $request->data();

            return $request->url() === 'http://127.0.0.1:11434/api/chat'
                && ($payload['model'] ?? null) === 'llama3.2:3b'
                && ($payload['stream'] ?? null) === false
                && str_contains((string) data_get($payload, 'messages.0.content'), 'one item per line')
                && str_contains((string) data_get($payload, 'messages.0.content'), 'Do not pick an item because a word')
                && str_contains((string) data_get($payload, 'messages.1.content'), 'Live PSIS facts')
                && str_contains((string) data_get($payload, 'messages.1.content'), 'Departments in PSIS')
                && str_contains((string) data_get($payload, 'messages.1.content'), 'Units of measurement in PSIS')
                && str_contains((string) data_get($payload, 'messages.1.content'), 'Named items in this question: none')
                && ! str_contains((string) data_get($payload, 'messages.1.content'), 'Sample items (available)');
        });
    }

    public function test_free_text_falls_back_when_ollama_fails(): void
    {
        config([
            'psis.ollama.enabled' => true,
            'psis.ollama.url' => 'http://127.0.0.1:11434',
        ]);

        Http::fake([
            'http://127.0.0.1:11434/api/chat' => Http::response(['error' => 'unavailable'], 503),
        ]);

        $user = User::factory()->create();
        $user->assignRole('Supply Personnel');

        $reply = $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'How much bond paper do we have?'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('The local AI (Ollama) did not reply', $reply);
        $this->assertStringContainsString('Low stock', $reply);
    }

    public function test_non_localhost_ollama_url_is_skipped(): void
    {
        config([
            'psis.ollama.enabled' => true,
            'psis.ollama.url' => 'https://api.openai.com',
        ]);

        Http::fake();

        $user = User::factory()->create();
        $user->assignRole('Supply Personnel');

        $reply = $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'How much bond paper do we have?'])
            ->assertOk()
            ->json('reply');

        $this->assertStringContainsString('The local AI (Ollama) did not reply', $reply);
        Http::assertNothingSent();
    }
}
