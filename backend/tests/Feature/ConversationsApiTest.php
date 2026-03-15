<?php

namespace Tests\Feature;

use App\Models\Conversations;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ConversationsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_conversations_store_creates_bot_active_conversation(): void
    {
        $user = $this->createUser();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/conversations', [
            'channel' => 'web_chat',
            'subject' => 'Need help',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('conversation.status', Conversations::STATUS_BOT_ACTIVE)
            ->assertJsonPath('conversation.user.id', $user->id)
            ->assertJsonPath('conversation.subject', 'Need help');
    }

    public function test_conversations_index_returns_only_owners_conversations_for_user_role(): void
    {
        $user = $this->createUser();
        $other = $this->createUser(['email' => 'other@example.com']);

        Conversations::query()->create([
            'user_id' => $user->id,
            'channel' => 'web_chat',
            'status' => Conversations::STATUS_BOT_ACTIVE,
            'subject' => 'Mine',
        ]);

        Conversations::query()->create([
            'user_id' => $other->id,
            'channel' => 'web_chat',
            'status' => Conversations::STATUS_BOT_ACTIVE,
            'subject' => 'Not mine',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/conversations');

        $response
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.subject', 'Mine');
    }

    public function test_conversations_index_returns_only_assigned_conversations_for_helpdesk_agent(): void
    {
        $owner = $this->createUser();
        $agent = $this->createUser(['role' => 'helpdesk_agent', 'email' => 'agent@example.com']);
        $otherAgent = $this->createUser(['role' => 'helpdesk_agent', 'email' => 'agent2@example.com']);

        Conversations::query()->create([
            'user_id' => $owner->id,
            'assigned_agent_id' => $agent->id,
            'channel' => 'web_chat',
            'status' => Conversations::STATUS_AGENT_ACTIVE,
            'subject' => 'Assigned to agent',
        ]);

        Conversations::query()->create([
            'user_id' => $owner->id,
            'assigned_agent_id' => $otherAgent->id,
            'channel' => 'web_chat',
            'status' => Conversations::STATUS_AGENT_ACTIVE,
            'subject' => 'Assigned to other',
        ]);

        Sanctum::actingAs($agent);

        $response = $this->getJson('/api/conversations');

        $response
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.subject', 'Assigned to agent');
    }

    public function test_conversations_show_returns_conversation_with_messages_for_owner(): void
    {
        $owner = $this->createUser();
        $conversation = Conversations::query()->create([
            'user_id' => $owner->id,
            'channel' => 'web_chat',
            'status' => Conversations::STATUS_BOT_ACTIVE,
            'subject' => 'Show me',
        ]);

        $conversation->messages()->create([
            'sender_type' => 'user',
            'sender_user_id' => $owner->id,
            'content' => 'Hello',
            'message_type' => 'text',
        ]);

        Sanctum::actingAs($owner);

        $response = $this->getJson("/api/conversations/{$conversation->id}");

        $response
            ->assertOk()
            ->assertJsonPath('id', $conversation->id)
            ->assertJsonPath('messages.0.content', 'Hello');
    }

    public function test_conversation_owner_can_update_conversation(): void
    {
        $owner = $this->createUser();
        $conversation = Conversations::query()->create([
            'user_id' => $owner->id,
            'channel' => 'web_chat',
            'status' => Conversations::STATUS_BOT_ACTIVE,
            'subject' => 'Old Subject',
        ]);

        Sanctum::actingAs($owner);

        $response = $this->putJson("/api/conversations/{$conversation->id}", [
            'channel' => 'voice',
            'subject' => 'Updated Subject',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('subject', 'Updated Subject')
            ->assertJsonPath('channel', 'voice');
    }

    public function test_conversation_owner_can_delete_conversation(): void
    {
        $owner = $this->createUser();
        $conversation = Conversations::query()->create([
            'user_id' => $owner->id,
            'channel' => 'web_chat',
            'status' => Conversations::STATUS_BOT_ACTIVE,
            'subject' => 'Delete conversation',
        ]);

        Sanctum::actingAs($owner);

        $response = $this->deleteJson("/api/conversations/{$conversation->id}");

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Conversation deleted successfully.',
            ]);

        $this->assertDatabaseMissing('conversations', [
            'id' => $conversation->id,
        ]);
    }

    public function test_owner_can_request_agent_handoff(): void
    {
        $owner = $this->createUser();
        $conversation = Conversations::query()->create([
            'user_id' => $owner->id,
            'channel' => 'web_chat',
            'status' => Conversations::STATUS_BOT_ACTIVE,
            'subject' => 'Need human',
        ]);

        Sanctum::actingAs($owner);

        $response = $this->postJson("/api/conversations/{$conversation->id}/request-agent");

        $response
            ->assertOk()
            ->assertJsonPath('conversation.status', Conversations::STATUS_WAITING_FOR_AGENT);

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_type' => 'system',
            'message_type' => 'system',
        ]);
    }

    public function test_owner_can_resolve_conversation_after_bot_answer(): void
    {
        $owner = $this->createUser();
        $conversation = Conversations::query()->create([
            'user_id' => $owner->id,
            'channel' => 'web_chat',
            'status' => Conversations::STATUS_BOT_ACTIVE,
            'subject' => 'Resolved by user',
        ]);

        Sanctum::actingAs($owner);

        $response = $this->postJson("/api/conversations/{$conversation->id}/resolve");

        $response
            ->assertOk()
            ->assertJsonPath('conversation.status', Conversations::STATUS_CLOSED);

        $this->assertNotNull($conversation->fresh()->closed_at);
        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'sender_type' => 'system',
            'message_type' => 'system',
        ]);
    }

    public function test_helpdesk_queue_endpoint_returns_waiting_conversations(): void
    {
        $owner = $this->createUser();
        $agent = $this->createUser([
            'role' => 'helpdesk_agent',
            'email' => 'queue-agent@example.com',
        ]);

        Conversations::query()->create([
            'user_id' => $owner->id,
            'channel' => 'web_chat',
            'status' => Conversations::STATUS_WAITING_FOR_AGENT,
            'subject' => 'Queued conversation',
        ]);

        Sanctum::actingAs($agent);

        $response = $this->getJson('/api/helpdesk/conversations/queue');

        $response
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.status', Conversations::STATUS_WAITING_FOR_AGENT);
    }

    public function test_helpdesk_agent_can_accept_waiting_conversation(): void
    {
        $owner = $this->createUser();
        $agent = $this->createUser([
            'role' => 'helpdesk_agent',
            'email' => 'accept-agent@example.com',
        ]);

        $conversation = Conversations::query()->create([
            'user_id' => $owner->id,
            'channel' => 'web_chat',
            'status' => Conversations::STATUS_WAITING_FOR_AGENT,
            'subject' => 'Accept me',
        ]);

        Sanctum::actingAs($agent);

        $response = $this->postJson("/api/conversations/{$conversation->id}/accept");

        $response
            ->assertOk()
            ->assertJsonPath('conversation.status', Conversations::STATUS_AGENT_ACTIVE)
            ->assertJsonPath('conversation.assigned_agent_id', $agent->id);
    }

    public function test_assigned_helpdesk_agent_can_close_conversation(): void
    {
        $owner = $this->createUser();
        $agent = $this->createUser([
            'role' => 'helpdesk_agent',
            'email' => 'close-agent@example.com',
        ]);

        $conversation = Conversations::query()->create([
            'user_id' => $owner->id,
            'assigned_agent_id' => $agent->id,
            'channel' => 'web_chat',
            'status' => Conversations::STATUS_AGENT_ACTIVE,
            'subject' => 'Close this',
        ]);

        Sanctum::actingAs($agent);

        $response = $this->postJson("/api/conversations/{$conversation->id}/close");

        $response
            ->assertOk()
            ->assertJsonPath('conversation.status', Conversations::STATUS_CLOSED);

        $this->assertNotNull($conversation->fresh()->closed_at);
    }

    public function test_user_role_can_access_help_panel_endpoint(): void
    {
        $user = $this->createUser(['role' => 'user']);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/home/help-panel');
        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Help panel data placeholder.',
            ]);
    }

    public function test_helpdesk_role_can_access_helpdesk_chat_panel_endpoint(): void
    {
        $agent = $this->createUser([
            'role' => 'helpdesk_agent',
            'email' => 'chat-agent@example.com',
        ]);
        Sanctum::actingAs($agent);

        $response = $this->getJson('/api/home/helpdesk-chat-panel');
        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Helpdesk chat panel data placeholder.',
            ]);
    }

    private function createUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ], $attributes));
    }
}
