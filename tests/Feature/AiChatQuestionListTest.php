<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiChatQuestionListTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_chat_page_lists_questions_and_has_a_type_box(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Supply Personnel');

        $this->actingAs($user)
            ->get(route('ai.chat'))
            ->assertOk()
            ->assertSee('Choose a question')
            ->assertSee('Reorder recommendations')
            ->assertSee('Type a question');
    }

    public function test_ask_accepts_a_listed_question(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Supply Personnel');

        $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'Low stock'])
            ->assertOk()
            ->assertJsonStructure(['reply']);
    }

    public function test_ask_accepts_free_typed_questions(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Supply Personnel');

        $this->actingAs($user)
            ->postJson(route('ai.ask'), ['message' => 'What is the meaning of life?'])
            ->assertOk()
            ->assertJsonStructure(['reply']);
    }
}
