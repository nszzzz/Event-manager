<?php

namespace Database\Seeders;

use App\Models\Faq_entries;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FaqEntriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faqEntries = [
            [
                'title' => 'Password reset',
                'category' => 'Authentication',
                'answer' => 'Use the Forgot Password option on the login page. After submitting your email address, you will receive a password reset link.',
                'keywords' => ['password', 'forgot password', 'reset password', 'login problem'],
                'is_active' => true,
            ],
            [
                'title' => 'Login issue',
                'category' => 'Authentication',
                'answer' => 'Make sure you are using the correct email address and password. If the problem persists, use the password reset option or contact a human agent.',
                'keywords' => ['login', 'sign in', 'cannot log in', 'access problem'],
                'is_active' => true,
            ],
            [
                'title' => 'Create event',
                'category' => 'Events',
                'answer' => 'To create a new event, open the Events page and click the Create Event button. Fill in the title and occurrence date/time, then save the form.',
                'keywords' => ['create event', 'new event', 'add event'],
                'is_active' => true,
            ],
            [
                'title' => 'View events',
                'category' => 'Events',
                'answer' => 'You can view your events on the Events page after logging in. The list is refreshed automatically at regular intervals.',
                'keywords' => ['view events', 'list events', 'my events', 'see events'],
                'is_active' => true,
            ],
            [
                'title' => 'Update event',
                'category' => 'Events',
                'answer' => 'You can update an event by opening it from the events list, editing the available fields, and saving your changes.',
                'keywords' => ['update event', 'edit event', 'change event'],
                'is_active' => true,
            ],
            [
                'title' => 'Delete event',
                'category' => 'Events',
                'answer' => 'To delete an event, open it from your events list and use the Delete action. Deleted events are removed from the active list.',
                'keywords' => ['delete event', 'remove event'],
                'is_active' => true,
            ],
            [
                'title' => 'Event date and time',
                'category' => 'Events',
                'answer' => 'Each event must have a title and an occurrence date and time. The occurrence field determines when the event is scheduled to happen.',
                'keywords' => ['occurrence', 'event date', 'event time', 'schedule event'],
                'is_active' => true,
            ],
            [
                'title' => 'Unauthorized access',
                'category' => 'Security',
                'answer' => 'If you cannot access a protected page, your session may have expired or your account may not have the required permissions. Please sign in again or contact support.',
                'keywords' => ['unauthorized', 'forbidden', 'access denied', 'permission'],
                'is_active' => true,
            ],
            [
                'title' => 'Talk to a human agent',
                'category' => 'Helpdesk',
                'answer' => 'If you need additional help, you can request a human agent directly from the support chat. Your conversation will be transferred to the next available agent.',
                'keywords' => ['human', 'agent', 'support', 'operator', 'real person'],
                'is_active' => true,
            ],
            [
                'title' => 'Previous conversations',
                'category' => 'Helpdesk',
                'answer' => 'Your previous support conversations are available in the helpdesk section, where you can review earlier messages and responses.',
                'keywords' => ['previous chats', 'old conversations', 'chat history', 'past messages'],
                'is_active' => true,
            ],
        ];

        foreach ($faqEntries as $faqEntry) {
            Faq_entries::updateOrCreate(
                ['title' => $faqEntry['title']],
                $faqEntry
            );
        }
    }
}
