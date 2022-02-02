<?php

namespace Tests\Unit\Services;

use App\Services\TwitterService;
use PHPUnit\Framework\TestCase;
use Tests\CreatesApplication;

/**
 * @covers defaultClass \App\Services\TwitterService
 * @group TwitterService
 */
class TwitterServiceTest extends TestCase
{
    use CreatesApplication;

    /**
     * Setup the tests
     */
    protected function setUp(): void
    {
        $this->createApplication();
    }

    /**
     * Get an instance of the TwitterService
     *
     * @return TwitterService [description]
     */
    protected function resolveService(): TwitterService
    {
        return resolve(TwitterService::class);
    }

    /**
     * Common data to be used across tests
     *
     * @return array
     */
    protected function getTweetDetails(): array
    {
        return [
            'to' => '@developerdoran',
            'from' => '@developerdoran',
            'category' => 'admiration',
        ];
    }

    /**
     * @covers ::__construct
     */
    public function test_can_resolve_twitter_service(): void
    {
        // Setup the test
        // ==========================================

        $twitterService = $this->resolveService();

        // Test the result
        // ==========================================

        $this->assertInstanceOf(TwitterService::class, $twitterService);
    }

    /**
     * @covers ::generateTweet
     * @return void
     */
    public function test_generateTweet(): void
    {
        // Setup the test
        // ==========================================

        $twitterService = $this->resolveService();

        // Make the change
        // ==========================================

        $result = $twitterService->generateTweet($this->getTweetDetails());

        // Test the result
        // ==========================================

        $this->assertIsString($result);
    }

    /**
     * @covers ::calculateCharCount
     * @return void
     */
    public function test_calculateCharCount(): void
    {
        // Setup the test
        // ==========================================

        $twitterService = $this->resolveService();

        // Make the change
        // ==========================================

        $result = $twitterService->calculateCharCount($this->getTweetDetails());

        // Test the result
        // ==========================================

        $this->assertIsInt($result);
    }
}
