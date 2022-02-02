<?php

namespace Tests\Unit\Services;

use App\Repositories\MessageRepository;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use Tests\CreatesApplication;

/**
 * @covers defaultClass \App\Repositories\MessageRepository
 * @group MessageRepository
 */
class MessageRepositoryTest extends TestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        $this->createApplication();
    }

    protected function resolveService(): MessageRepository
    {
        return resolve(MessageRepository::class);
    }

    public function test_can_resolve_message_repository(): void
    {
        // Setup the test
        // ==========================================

        $messageRepository = $this->resolveService();

        // Test the result
        // ==========================================

        $this->assertInstanceOf(MessageRepository::class, $messageRepository);
    }

    /**
     * @covers getAllMessages
     * @return void
     */
    public function test_getAllMessages(): void
    {
        // Setup the test
        // ==========================================

        $messageRepository = $this->resolveService();

        // Make the change
        // ==========================================

        $result = $messageRepository->getAllMessages();

        // Test the result
        // ==========================================

        $this->assertIsArray($result);
    }

    /**
     * @covers getAllCategories
     * @return void
     */
    public function test_getAllCategories(): void
    {
        // Setup the test
        // ==========================================

        $messageRepository = $this->resolveService();

        // Make the change
        // ==========================================

        $result = $messageRepository->getAllCategories();

        // Test the result
        // ==========================================

        $this->assertIsArray($result);
    }

    /**
     * @covers getCountOfAllMessagesByCategory
     * @return void
     */
    public function test_getCountOfAllMessagesByCategory(): void
    {
        // Setup the test
        // ==========================================

        $messageRepository = $this->resolveService();

        // Make the change
        // ==========================================

        $result = $messageRepository->getCountOfAllMessagesByCategory();

        // Test the result
        // ==========================================

        $this->assertIsArray($result);
    }

    /**
     * @covers getAllMessagesForCategory
     * @return void
     */
    public function test_getAllMessagesForCategory_with_valid_category(): void
    {
        // Setup the test
        // ==========================================

        $messageRepository = $this->resolveService();
        $messageCount = $messageRepository->getCountOfAllMessagesByCategory();
        $category = 'admiration';

        // Make the change
        // ==========================================

        $result = $messageRepository->getAllMessagesForCategory($category, 150);

        // Test the result
        // ==========================================

        $this->assertInstanceOf(Collection::class, $result);
        $this->assertSame($messageCount[$category], $result->count());
    }

    /**
     * @covers getAllMessagesForCategory
     * @return void
     */
    public function test_getAllMessagesForCategory_with_invalid_category(): void
    {
        // Setup the test
        // ==========================================

        $messageRepository = $this->resolveService();

        // Make the change
        // ==========================================

        $result = $messageRepository->getAllMessagesForCategory('', 150);

        // Test the result
        // ==========================================

        $this->assertNull($result);
    }
}
