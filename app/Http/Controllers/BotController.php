<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendTweetRequest;
use App\Repositories\MessageRepository;
use App\Services\TwitterService;
use Illuminate\Http\Request;

class BotController extends Controller
{
    /** @var \App\Services\TwitterService $twitterService */
    protected $twitterService;

    /** @var \App\Repositories\MessageRepository $messageRepository */
    protected $messageRepository;

    /**
     * @param \App\Services\TwitterService $twitterService
     */
    public function __construct(TwitterService $twitterService, MessageRepository $messageRepository)
    {
        $this->twitterService = $twitterService;
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

        return view('landing', compact('counts', 'messageCount', 'sentMessages'));
    }

    /**
     * Respond to tweet post requests
     *
     * @param  \App\Http\Requests\SendTweetRequest  $request
     * @return
     */
    public function sendTweet(SendTweetRequest $request)
    {
        $success = $this->twitterService->sendTweet([
            'to' => $request->input('to'),
            "from" => $request->input('from'),
            "category" => $request->input('category'),
        ]);

        if ($success) {
            return response()->json([], 200);
        }

        return response()->json([
            'content' => 'An unexpected error occurred. Please try again later or tweet @developerdoran.'
        ], 422);
    }
}
