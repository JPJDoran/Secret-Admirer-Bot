<?php

namespace App\Repositories;

use Illuminate\Support\Collection;

class MessageRepository
{
    /**
     * Get all messages from the config file
     *
     * @return array
     */
    public function getAllMessages(): array
    {
        return config("messages");
    }

    /**
     * Get all the types of message the app supports
     *
     * @return array
     */
    public function getAllCategories(): array
    {
        return array_keys($this->getAllMessages());
    }

    /**
     * Return a key value pair of the count of all messsages per category
     *
     * @return array
     */
    public function getCountOfAllMessagesByCategory(): array
    {
        $messages = $this->getAllMessages();

        $messagesCount = [];

        foreach ($messages as $category => $contents) {
            $messagesCount[$category] = collect($contents)->count();
        }

        return $messagesCount;
    }

    /**
     * Get all the messages for a given category
     *
     * @param  string  $category
     * @return \Illuminate\Support\Collection|null
     */
    public function getAllMessagesForCategory(string $category, int $charLimit): ?collection
    {
        $messages = $this->getAllMessages();

        if (! in_array($category, array_keys($messages))) {
            return null;
        }

        return collect($messages[$category])->filter(function ($value, $key) use ($charLimit) {
            return $value['charCount'] < $charLimit;
        });
    }

    /**
     * Get a total count of all the different messages
     *
     * @return int
     */
    public function getCountOfAllMessages(): int
    {
        $messages = $this->getAllMessages();
        $keys = array_keys($messages);
        $count = 0;

        foreach ($keys as $key) {
            $count +=  count($messages[$key]);
        }

        return $count;
    }
}
