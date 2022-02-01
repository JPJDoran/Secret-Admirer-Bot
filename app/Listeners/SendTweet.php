<?php

namespace App\Listeners;

use App\Events\PostTweet;
use App\Models\Tweet;
use App\Services\TwitterApiService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Cache\RateLimiter;

class SendTweet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var \App\Services\TwitterService $twitterService */
    protected $twitterApiService;

    /**
     * Create the event listener.
     *
     * @param \App\Services\TwitterApiService $twitterApiService
     * @return void
     */
    public function __construct(TwitterApiService $twitterApiService)
    {
        $this->twitterApiService = $twitterApiService;
        $this->limiter = $this->limiter();
        $this->throttleKey = 'send-tweet';
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\PostTweet  $event
     * @return void
     */
    public function handle(PostTweet $event): void
    {
        // Event retry count not initiated so default it
        if (! isset($event->retries)) {
            $event->retries = 0;
        }

        // Attempt to send the tweet via rate limiter - 1 per minute
        // as we can only send 100 tweets an hour
        $sent = $this->limiter->attempt(
            $this->throttleKey,
            1,
            function() use ($event) {
                if (! $response = $this->twitterApiService->sendTweetOverApi($event->tweet)) {
                    return false;
                }

                $this->markTweetAsSent($event->tweet);

                return true;
            }
        );

        $event->retries++;

        // Failed to tweet, try again if we can
        if (! $sent && $event->retries < 5) {
            sleep($this->limiter->availableIn($this->throttleKey));

            event($event);
        }

        return;
    }

    /**
     * Get the rate limiter instance.
     *
     * @return \Illuminate\Cache\RateLimiter
     */
    protected function limiter(): RateLimiter
    {
        return app(RateLimiter::class);
    }

    /**
     * Update the tweet to show as published
     *
     * @param  Tweet $tweet
     * @return void
     */
    private function markTweetAsSent(Tweet $tweet): void
    {
        $tweet->sent = Carbon::now();
        $tweet->save();

        return;
    }
}
