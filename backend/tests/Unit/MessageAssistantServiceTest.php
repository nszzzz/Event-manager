<?php

namespace Tests\Unit;

use App\Models\Faq_entries;
use App\Services\MessageAssistantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessageAssistantServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_generate_reply_result_returns_matching_answer_when_keyword_is_found(): void
    {
        Faq_entries::query()->create([
            'title' => 'Password reset',
            'category' => 'Auth',
            'answer' => 'Use forgot password to reset your account.',
            'keywords' => ['forgot password', 'reset password', 'login'],
            'is_active' => true,
        ]);

        $service = new MessageAssistantService();
        $result = $service->generateReplyResult('I cannot login and need reset password help');

        $this->assertTrue($result['matched']);
        $this->assertSame('Use forgot password to reset your account.', $result['content']);
    }

    public function test_generate_reply_result_returns_fallback_when_no_match_exists(): void
    {
        Faq_entries::query()->create([
            'title' => 'Create event',
            'category' => 'Events',
            'answer' => 'Open events page and click create.',
            'keywords' => ['create event', 'new event'],
            'is_active' => true,
        ]);

        $service = new MessageAssistantService();
        $result = $service->generateReplyResult('How can I update my billing profile?');

        $this->assertFalse($result['matched']);
        $this->assertSame(
            'I could not find a direct FAQ match. Please rephrase your question, or request a human agent.',
            $result['content']
        );
    }
}
