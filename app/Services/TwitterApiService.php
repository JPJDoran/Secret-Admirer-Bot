<?php

namespace App\Services;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Models\Tweet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use strlen;

/**
 * Handles the api logic to send tweets via the twitter api
 */
class TwitterApiService
{

    public function __construct()
    {
        $this->consumerKey = env("TWITTER_CONSUMER_KEY");
        $this->consumerKeySecret = env("TWITTER_CONSUMER_SECRET");
        $this->accessKey = env("TWITTER_ACCESS_TOKEN");
        $this->accessKeySecret = env("TWITTER_ACCESS_TOKEN_SECRET");
    }

    /**
     * Creates a new twitter api connection
     *
     * @param  string|null  $oauthToken
     * @param  string|null  $oauthSecret
     * @return \Abraham\TwitterOAuth\TwitterOAuth
     */
    public function createConnection(
        ?string $oauthToken = null,
        ?string $oauthSecret = null
    ): TwitterOAuth {
        if ($oauthToken && $oauthSecret) {
            return new TwitterOAuth(
                $this->consumerKey,
                $this->consumerKeySecret,
                $oauthToken,
                $oauthSecret
            );
        }

        return new TwitterOAuth(
            $this->consumerKey,
            $this->consumerKeySecret
        );
    }

    /**
     * Attempt to log in a user via twitter OAuth
     *
     * @return string
     */
    public function loginViaTwitter(): string
    {
        // User already logged in so return their details
        if (null !== session('access_token')) {
            return $this->getLoggedInUser();
        }

        $connection = $this->createConnection();
        $request_token = $connection->oauth('oauth/request_token', array('oauth_callback' => 'https://www.secretadmirerbot.com/oAuth'));

        Session::put('oauth_token', $request_token['oauth_token']);
        Session::put('oauth_token_secret', $request_token['oauth_token_secret']);
        Session::save();

        return $connection->url('oauth/authorize', array('oauth_token' => $request_token['oauth_token']));
    }

    /**
     * Get the twitter handle of the logged in user
     *
     * @return string
     */
    public function getLoggedInUser(): string
    {
        $access_token = session('access_token');
        $connection = $this->createConnection($access_token['oauth_token'], $access_token['oauth_token_secret']);
        $user = $connection->get("account/verify_credentials", []);

        Session::put('twitter_user', "@".$user->screen_name);
        Session::save();

        return "@".$user->screen_name;
    }

    /**
     * Handles the callback from twitter OAuth when logining in a user
     *
     * @param Request $request [description]
     * @return void
     */
    public function handleOAuthCallback(Request $request): void
    {
        $connection = $this->createConnection(session('oauth_token'), session('oauth_token_secret'));

        $access_token = $connection->oauth("oauth/access_token", array("oauth_verifier" => $request->input('oauth_verifier')));

        Session::put('access_token', $access_token);
        Session::save();

        return;
    }

    /**
     * Attempt to publish a tweet over the api
     *
     * @param  Tweet $tweet
     * @return bool
     */
    public function sendTweetOverApi(Tweet $tweet): bool
    {
        $connection = $this->createConnection($this->accessKey, $this->accessKeySecret);
        $response = $connection->post("statuses/update", ['status' => $tweet->message]);

        if (! isset($response->errors)) {
            return true;
        }

        if (empty($response->errors)) {
            return true;
        }

        /** @todo log the $response->errors */

        return false;
    }
}
