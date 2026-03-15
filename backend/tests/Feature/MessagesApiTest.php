<?php

namespace Tests\Feature;

use App\Models\Conversations;
use App\Models\Faq_entries;
use App\Models\Messages;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MessagesApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_messages_index_returns_messages_for_accessible_conversation(): void
    {
        $owner = $this->createUser();
        $conversation = $this->createConversationFor($owner);

        $conversation->messages()->create([
            'sender_type' => 'user',
            'sender_user_id' => $owner->id,
            'content' => 'First message',
            'message_type' => 'text',
        ]);

        Sanctum::actingAs($owner);

        $response = $this->getJson("/api/messages?conversation_id={$conversation->id}");

        $response
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.content', 'First message');
    }

    public function test_messages_show_returns_single_message_when_user_has_access(): void
    {
        $owner = $this->createUser();
        $conversation = $this->createConversationFor($owner);

        $message = $conversation->messages()->create([
            'sender_type' => 'user',
            'sender_user_id' => $owner->id,
            'content' => 'Direct message',
            'message_type' => 'text',
        ]);

        Sanctum::actingAs($owner);

        $response = $this->getJson("/api/messages/{$message->id}");

        $response
            ->assertOk()
            ->assertJsonPath('id', $message->id)
            ->assertJsonPath('content', 'Direct message');
    }

    public function test_messages_store_creates_user_and_bot_messages_when_bot_is_active_and_match_found(): void
    {
        $owner = $this->createUser();
        $conversation = $this->createConversationFor($owner, [
            'status' => Conversations::STATUS_BOT_ACTIVE,
        ]);

        Faq_entries::query()->create([
            'title' => 'Login help',
            'category' => 'Auth',
            'answer' => 'Try resetting your password.',
            'keywords' => ['login', 'sign in'],
            'is_active' => true,
        ]);

        Sanctum::actingAs($owner);

        $response = $this->postJson('/api/messages', [
            'conversation_id' => $conversation->id,
            'content' => 'I have a login issue',
            'message_type' => 'text',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message.sender_type', 'user')
            ->assertJsonPath('assistant_message.sender_type', 'bot')
            ->assertJsonPath('needs_human_handoff', false);

        $this->assertDatabaseCount('messages', 2);
    }

    public function test_messages_store_sets_handoff_flag_when_no_faq_match_found(): void
    {
        $owner = $this->createUser();
        $conversation = $this->createConversationFor($owner, [
            'status' => Conversations::STATUS_BOT_ACTIVE,
        ]);

        Sanctum::actingAs($owner);

        $response = $this->postJson('/api/messages', [
            'conversation_id' => $conversation->id,
            'content' => 'zxqv random unsupported query',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message.sender_type', 'user')
            ->assertJsonPath('assistant_message.sender_type', 'bot')
            ->assertJsonPath('needs_human_handoff', true);
    }

    public function test_messages_store_does_not_create_bot_message_while_waiting_for_agent(): void
    {
        $owner = $this->createUser();
        $conversation = $this->createConversationFor($owner, [
            'status' => Conversations::STATUS_WAITING_FOR_AGENT,
        ]);

        Sanctum::actingAs($owner);

        $response = $this->postJson('/api/messages', [
            'conversation_id' => $conversation->id,
            'content' => 'Still waiting',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('assistant_message', null)
            ->assertJsonPath('needs_human_handoff', true);

        $this->assertDatabaseCount('messages', 1);
    }

    public function test_assigned_agent_can_send_message_in_agent_active_conversation(): void
    {
        $owner = $this->createUser();
        $agent = $this->createUser([
            'role' => 'helpdesk_agent',
            'email' => 'assigned-agent@example.com',
        ]);

        $conversation = $this->createConversationFor($owner, [
            'assigned_agent_id' => $agent->id,
            'status' => Conversations::STATUS_AGENT_ACTIVE,
        ]);

        Sanctum::actingAs($agent);

        $response = $this->postJson('/api/messages', [
            'conversation_id' => $conversation->id,
            'content' => 'Agent reply',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('message.sender_type', 'agent')
            ->assertJsonPath('assistant_message', null);
    }

    public function test_closed_conversation_rejects_new_messages(): void
    {
        $owner = $this->createUser();
        $conversation = $this->createConversationFor($owner, [
            'status' => Conversations::STATUS_CLOSED,
            'closed_at' => now(),
        ]);

        Sanctum::actingAs($owner);

        $response = $this->postJson('/api/messages', [
            'conversation_id' => $conversation->id,
            'content' => 'Cannot send this',
        ]);

        $response->assertStatus(403);
    }

    private function createUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ], $attributes));
    }

    private function createConversationFor(User $owner, array $attributes = []): Conversations
    {
        return Conversations::query()->create(array_merge([
            'user_id' => $owner->id,
            'channel' => 'web_chat',
            'status' => Conversations::STATUS_BOT_ACTIVE,
            'subject' => 'Support',
        ], $attributes));
    }
}
