<?php

namespace Tests\Feature;

use App\Models\Events;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_events_index_returns_available_events(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Events::query()->create([
            'user_id' => $user->id,
            'title' => 'Planning',
            'occurrence_at' => now()->addDay(),
            'description' => 'Sprint planning',
        ]);

        $response = $this->getJson('/api/events');

        $response
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.title', 'Planning')
            ->assertJsonPath('0.user.id', $user->id);
    }

    public function test_events_store_creates_event_for_authenticated_user(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/events', [
            'title' => 'Retrospective',
            'occurrence_at' => now()->addDays(2)->toIso8601String(),
            'description' => 'Retro notes',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('event.title', 'Retrospective')
            ->assertJsonPath('event.user.id', $user->id);

        $this->assertDatabaseHas('events', [
            'user_id' => $user->id,
            'title' => 'Retrospective',
        ]);
    }

    public function test_events_show_returns_single_event(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $event = Events::query()->create([
            'user_id' => $user->id,
            'title' => 'Demo',
            'occurrence_at' => now()->addDays(3),
            'description' => 'Demo session',
        ]);

        $response = $this->getJson("/api/events/{$event->id}");

        $response
            ->assertOk()
            ->assertJsonPath('id', $event->id)
            ->assertJsonPath('title', 'Demo');
    }

    public function test_events_update_modifies_owned_event(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $event = Events::query()->create([
            'user_id' => $user->id,
            'title' => 'Old Title',
            'occurrence_at' => now()->addDays(4),
            'description' => 'Old',
        ]);

        $response = $this->putJson("/api/events/{$event->id}", [
            'title' => 'Updated Title',
            'occurrence_at' => now()->addDays(5)->toIso8601String(),
            'description' => 'Updated',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('title', 'Updated Title');

        $this->assertDatabaseHas('events', [
            'id' => $event->id,
            'title' => 'Updated Title',
            'description' => 'Updated',
        ]);
    }

    public function test_events_destroy_deletes_owned_event(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $event = Events::query()->create([
            'user_id' => $user->id,
            'title' => 'Delete Me',
            'occurrence_at' => now()->addDays(2),
            'description' => null,
        ]);

        $response = $this->deleteJson("/api/events/{$event->id}");

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Event deleted successfully.',
            ]);

        $this->assertDatabaseMissing('events', [
            'id' => $event->id,
        ]);
    }
}
