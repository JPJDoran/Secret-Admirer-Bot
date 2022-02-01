<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendTweetRequest;
use App\Repositories\MessageRepository;
use App\Services\TwitterService;
use App\Services\TwitterApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class BotController extends Controller
{
    /** @var \App\Services\TwitterService $twitterService */
    protected $twitterService;

    /** @var \App\Services\TwitterApiService $twitterApiService */
    protected $twitterApiService;

    /** @var \App\Repositories\MessageRepository $messageRepository */
    protected $messageRepository;

    /**
     * @param  \App\Services\TwitterService  $twitterService
     * @param  \App\Services\TwitterApiService  $twitterApiService
     * @param  \App\Repositories\MessageRepository  $messageRepository
     */
    public function __construct(
        TwitterService $twitterService,
        TwitterApiService $twitterApiService,
        MessageRepository $messageRepository
    ) {
        $this->twitterService = $twitterService;
        $this->twitterApiService = $twitterApiService;
        $this->messageRepository = $messageRepository;
    }

    /**
     * Load the frontend for the user
     *
     * @return
     */
    public function landingPage()
    {
        $counts = $this->messageRepository->getCountOfAllMessagesByCategory();
        $messageCount = $this->messageRepository->getCountOfAllMessages();
        $sentMessages = $this->twitterService->getTotalMessagesSent();
        $twitterLogin = $this->twitterApiService->loginViaTwitter();

        return view('landing', compact('counts', 'messageCount', 'sentMessages', 'twitterLogin'));
    }

    /**
     * Respond to tweet post requests
     *
     * @param  \App\Http\Requests\SendTweetRequest  $request
     * @return
     */
    public function sendTweet(SendTweetRequest $request)
    {
        $from = $request->input('from');

        // User twitter user handle if requested
        $from = $from === "Twitter User" ? session('twitter_user') : $from;

        $success = $this->twitterService->sendTweet([
            'to' => $request->input('to'),
            "from" => $from,
            "category" => $request->input('category'),
        ]);

        if ($success) {
            return response()->json([], 200);
        }

        return response()->json([
            'content' => 'An unexpected error occurred. Please try again later or tweet @developerdoran.'
        ], 422);
    }

    /**
     * Twitter OAuth callback endpoint
     *
     * @param  Request $request
     * @return
     */
    public function twitterOAuth(Request $request)
    {
        $this->twitterApiService->handleOAuthCallback($request);

        return redirect('/');
    }
}
