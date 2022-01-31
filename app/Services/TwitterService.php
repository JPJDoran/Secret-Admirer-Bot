<?php

namespace App\Services;

use App\Events\PostTweet;
use App\Models\Tweet;
use App\Repositories\MessageRepository;
use strlen;

/**
 * Handles the controller logic to send tweets
 */
class TwitterService
{
    /** @var \App\Repositories\MessageRepository $messageRepository */
    protected $messageRepository;

    /**
     * @param \App\Repositories\MessageRepository $messageRepository
     */
    public function __construct(MessageRepository $messageRepository)
    {
        $this->maxCharCount = 260; // A buffer for the actual limit of 280

        $this->messageRepository = $messageRepository;
    }

    /**
     * Generates, stores and sends a tweet via the twitter api.
     *
     * @param  array  $tweetDetails
     * @return bool
     */
    public function sendTweet(array $tweetDetails): bool
    {
        $tweet = $this->generateTweet($tweetDetails);

        if (! $tweet || strlen($tweet) > $this->maxCharCount) {
            return false;
        }

        $tweetDetails = array_merge($tweetDetails, ['message' => $tweet]);

        if (! $tweet = $this->storeTweet($tweetDetails)) {
            return false;
        }

        event(new PostTweet(
            $tweet
        ));

        return true;
    }

    /**
     * Generate a tweet with a random affectionate message of a valid tweet size.
     *
     * @param  array  $tweetDetails
     * @return string|null
     */
    private function generateTweet(array $tweetDetails): ?string
    {
        $charLimit = $this->maxCharCount - $this->calculateCharCount($tweetDetails);

        $messages = $this->messageRepository->getAllMessagesForCategory($tweetDetails['category'], $charLimit);

        if ($messages->isEmpty()) {
            return null;
        }

        $emojis = collect(['💖', '🥰', '😍', '💘', '💝', '😘', '🌹']);

        $to = $tweetDetails['to'];
        $from = $tweetDetails['from'];
        $message = $messages->random()['message'];
        $emoji = $emojis->random();

        return "$to $message $from $emoji";
    }

    /**
     * Calculate the existing character count of the meta fields.
     *
     * @param  array $tweetDetails
     * @return int
     */
    private function calculateCharCount(array $tweetDetails): int
    {
        // One space after the recipient, another after the message and the last
        // after the signature
        $spaces = 3;

        // Pretty sure emojis are only 2 characters but use 5 as a buffer
        $emoji = 5;

        return strlen($tweetDetails['from']) + strlen($tweetDetails['to']) + $spaces + $emoji;
    }

    /**
     * Store the tweet in the database for logging purposes.
     *
     * @param  array  $tweet
     * @return \App\Models\Tweet
     */
    private function storeTweet(array $tweet): Tweet
    {
        return Tweet::create($tweet);
    }

    /**
     * Return the total number of messages sent.
     *
     * @return int
     */
    public function getTotalMessagesSent(): int
    {
        return Tweet::count();
    }
}
