<?php

namespace App\Listeners;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Events\PostTweet;
use App\Models\Tweet;
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

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        $this->consumerKey = env("TWITTER_CONSUMER_KEY");
        $this->consumerKeySecret = env("TWITTER_CONSUMER_SECRET");
        $this->accessKey = env("TWITTER_ACCESS_TOKEN");
        $this->accessKeySecret = env("TWITTER_ACCESS_TOKEN_SECRET");
        // $this->connection = $this->createConnection();
        $this->limiter = $this->limiter();
        $this->throttleKey = 'send-tweet';
    }

    /**
     * Creates a new twitter api connection
     *
     * @return \Abraham\TwitterOAuth\TwitterOAuth
     */
    private function createConnection()
    {
        return new TwitterOAuth(
            $this->consumerKey,
            $this->consumerKeySecret,
            $this->accessKey,
            $this->accessKeySecret
        );
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\PostTweet  $event
     * @return void
     */
    public function handle(PostTweet $event)
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
                if (! $response = $this->sendTweetOverApi($event->tweet)) {
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

    private function sendTweetOverApi(Tweet $tweet): bool
    {
        if (env("DEBUG_MODE")) {
            return true;
        }

        $response = $this->connection->post("statuses/update", ['status' => 'testing']);

        if (! isset($response->errors)) {
            return true;
        }

        if (empty($response->errors)) {
            return true;
        }

        /** @todo log the $response->errors */

        return false;
    }

    /**
     *
     *
     * @param  Tweet $tweet
     * @return void
     */
    private function markTweetAsSent(Tweet $tweet): void
    {
        $tweet->sent = Carbon::now();
        $tweet->save();
    }
}
