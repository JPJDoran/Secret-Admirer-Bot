<?php

namespace App\Http\Requests;

use App\Models\Tweet;
use App\Repositories\MessageRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;

class SendTweetRequest extends FormRequest
{
    protected function failedValidation(Validator $validator) {
        throw new HttpResponseException(response()->json($validator->errors(), 422));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'to' => [
                'required',
                'min:4',
                'max:16',
                'regex:/(^[a-zA-Z0-9@_]*$)/u',
                function ($attribute, $value, $fail) {
                    if (strtolower($value) !== '@developerdoran') {
                        // Check target hasn't received a tweet today
                        $target = Tweet::whereDate('created_at', Carbon::today())
                            ->where('to', '=', $value)
                            ->get();

                        if (! $target->isEmpty()) {
                            return $fail("That twitter user has already received a tweet today.");
                        }
                    }
                }
            ],
            'from' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value !== "Twitter User") {
                        // Check signature is supported
                        $signatures = [
                            'From An Admirer...',
                            'From ???',
                            'From Anon...',
                            'From Anonymous...',
                            'From A Friend...',
                        ];

                        if (! in_array($value, $signatures)) {
                            return $fail("The chosen signature is not supported.");
                        }

                        return;
                    }

                    if (null === session('twitter_user')) {
                        return $fail("The chosen signature is not supported.");
                    }
                }
            ],
            'category' => [
                'required',
                function ($attribute, $value, $fail) {
                    // Check category is supported
                    $messageRepository = new MessageRepository;

                    if (! in_array($value, $messageRepository->getAllCategories())) {
                        return $fail("The chosen message theme isn't supported.");
                    }
                }
            ],
        ];
    }
}
