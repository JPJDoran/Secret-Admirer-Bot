<!DOCTYPE html>
<html lang="en" dir="ltr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Secret Admirer Bot</title>

        <!-- Fonts -->
        <link rel="dns-prefetch" href="//fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet">

        <!-- Styles -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    </head>
    <body class="bg-light">
        <div id="vue-container">
            <div class="container">
                <div class="py-4 text-center">
                    <img class="d-block mx-auto mb-4" src="/imgs/secret-admirer-bot-logo.png" alt="" width="72" height="72">
                    <h2>Secret Admirer Bot 🤖</h2>
                    <p class="intro-text">
                        Tweet someone you know a random message of adoration, admiration or friendship anonymously or publicly!
                        <br> Tweet your <strong>partner</strong>, your <strong>crush</strong> or your <strong>BFF</strong>. Try it out and make someone's day today!
                        <br> <small> Bot created by <a href="https://twitter.com/DeveloperDoran" target="_blank">@developerdoran</a> </small>
                    </p>
                </div>
            </div>

            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-sm-12 col-md-3">
                        <div class="pt-0 pb-5 text-center">
                            <div class="card counter">
                                <div class="card-body">
                                    <h5 class="card-title">Tweets Published</h5>
                                    <p class="card-text counter-text"><strong>{{ $sentMessages }}</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-3">
                        <div class="pt-0 pb-5 text-center">
                            <div class="card counter">
                                <div class="card-body">
                                    <h5 class="card-title">Possible Messages</h5>
                                    <p class="card-text counter-text"><strong>{{ $messageCount }}</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tweet-container">
                <div class="row">
                    <div class="col">
                        <div>
                            <form class="form-signin" autocomplete="off" @submit.prevent="sendTweet">
                                <div class="text-center mb-4">
                                    <h1 class="h3 mb-3 font-weight-normal">💖 Compose Tweet 💖</h1>
                                    <p class="description-text">Choose your message theme, add your recipient, select your signature, then press "Tweet" to publish.</p>
                                    <p>Your tweet will be sent to your recipient via <a href="https://twitter.com/YourAdmirerBot" target="_blank">@YourAdmirerBot</a>.</p>
                                </div>

                                <div class="container">
                                    <div class="row">
                                        <div class="col">
                                            <p class="text-center">
                                                {!! session('access_token') ? "Logged in as <span id='twitter-handle'>$twitterLogin</span>" : '<a href="'.$twitterLogin.'"><img src="/imgs/sign-in-with-twitter-link.png"></a>' !!}

                                                @if (session('access_token'))
                                                    (<a href="/logout">Logout</a>)
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-label-group">
                                    <input
                                        type="text"
                                        id="recipient"
                                        class="form-control"
                                        placeholder="@developerdoran"
                                        v-model="recipient"
                                        pattern="[A-Za-z0-9@_]{4,15}"
                                        required
                                        autofocus
                                        oninvalid="this.setCustomValidity('Twitter handles are alphanumeric between 4-15 characters long.')"
                                        oninput="this.setCustomValidity('')"
                                    >
                                    <label for="recipient">To</label>
                                </div>

                                <div class="form-label-group">
                                    <select class="form-select" aria-label="Message Theme" v-model="theme" required>
                                        <option selected value="">Theme</option>
                                        <option value="admiration">Admiration</option>
                                        <option value="adoration">Adoration</option>
                                        <option value="fancy">Crush</option>
                                        <option value="friendship">Friendship</option>

                                        @if (date('M-d') === 'Feb-14')
                                            <option value="valentines">Valentines</option>
                                        @endif
                                    </select>
                                </div>

                                <div class="form-label-group">
                                    <select class="form-select" aria-label="Signature" v-model="signature" required>
                                        <option selected value="">Signature</option>
                                        <option value="From An Admirer...">From An Admirer</option>
                                        <option value="From ???">From ???</option>
                                        <option value="From Anon...">From Anon</option>
                                        <option value="From Anonymous...">From Anonymous</option>
                                        <option value="From A Friend...">From A Friend</option>

                                        @if (session('access_token'))
                                            <option value="Twitter User">From {{ $twitterLogin }}</option>
                                        @endif
                                    </select>
                                </div>

                                <button class="btn btn-lg btn-primary btn-block" type="submit" id="form-submit-button">
                                    Tweet 💌
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="col my-auto">
                        <div class="form-container">
                            <div class="text-center mb-4">
                                <h1 class="h3 mb-3 font-weight-normal">Example Tweet</h1>
                                <p class="description-text">Below is an example of the type of message you might send.</p>
                                <hr>
                            </div>

                            <div class="text-center mb-4">
                                <p class="lead" v-model="preview"><strong>@{{ preview }}</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="position-fixed top-0 end-0 p-3 toast-container" style="z-index: 11">
                <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-autohide="true">
                    <div class="toast-header">
                        <strong class="me-auto">Secret Admirer Bot 🤖</strong>
                        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body lead"></div>
                </div>
            </div>
        </div>

        <!-- Stick this to the bottom of the page? -->
        <footer class="my-5 py-3 text-muted text-center text-small">
            <p class="mb-1">&copy; {{ date("Y") }} Secret Admirer Bot</p>
            <!-- <ul class="list-inline">
                <li class="list-inline-item"><a href="#">Privacy</a></li>
                <li class="list-inline-item"><a href="#">Terms</a></li>
            </ul> -->
        </footer>

        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>

        <script>
            var vue = new Vue({
                el: '#vue-container',
                data: {
                    recipient: '',
                    signature: '',
                    theme: '',
                    messages: {
                        'adoration': "I didn't know it was possible to love everything about someone until I met you.",
                        'admiration': "I admire your honesty, your kindness and above all, your heart.",
                        'fancy': "When I think about you I end up having a stupid grin on my face.",
                        'friendship': "I just wanted to remind you of how much I value our friendship.",
                        'valentines': "The best things in life are better with you. Happy Valentines Day!",
                    },
                    statusText: '',
                    toasts: 0,
                },
                computed: {
                    preview: function() {
                        let recipient = this.recipient.replace('@', '');

                        if (recipient !== "") {
                            recipient = `@${recipient}`;
                        }

                        recipient = this.sanitizeString(recipient);

                        let message = this.themeMessage;

                        if (message == null) {
                            message = "";
                        }

                        message = this.sanitizeString(message);

                        let signature = this.signature;

                        if (signature == null) {
                            signature = "";
                        }

                        if (signature === "Twitter User") {
                            signature = document.getElementById("twitter-handle").innerHTML;
                        }

                        signature = this.sanitizeString(signature);

                        if (recipient === "" || signature === "" || message === "") {
                            return "Start filling out the form to generate your preview..."
                        }

                        return `@${recipient} ${message} @${signature} ${this.getEmoji()}`;
                    },
                    themeMessage: function() {
                        return this.messages[this.theme];
                    },
                },
                methods: {
                    sanitizeString(str){
                        str = str.replace(/[^a-z0-9áéíóúñü \.,_-]/gim,"");
                        return str.trim();
                    },
                    getEmoji() {
                        let emojis = ['💖', '🥰', '😍', '💘', '💝', '😘', '🌹'];
                        return emojis[Math.floor(Math.random()*emojis.length)];
                    },
                    async sendTweet() {
                        const button = document.getElementById("form-submit-button");

                        button.classList.add('disabled');
                        button.disabled = true;
                        button.innerHTML = '<div class="spinner-border" role="status"></div>';

                        let response = await this.postTweet();

                        this.statusText = ' Success! Your tweet has been scheduled!';

                        if (response.error) {
                            if (response.message.hasOwnProperty('content')) {
                                this.statusText = response.message.content;
                            } else if (! response.message.hasOwnProperty('to')) {
                                this.statusText = 'Oops! Looks like there is an issue with your tweet 🤔';
                            } else {
                                this.statusText = response.message.to[0];
                            }
                        }

                        let bgClass = response.error ? 'status-danger' : 'status-success';

                        button.innerHTML = 'Tweet 💌';
                        button.disabled = false;
                        button.classList.remove('disabled');

                        this.displayResult(bgClass);
                    },
                    async postTweet() {
                        const response = await fetch('/tweet', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                             },
                            body: JSON.stringify({
                                to: `@${this.recipient.replace('@', '')}`,
                                from: this.signature,
                                category: this.theme,
                            })
                        });

                        return {
                            'error': response.status == 200 ? false : true,
                            'message': await response.json(),
                        };
                    },
                    displayResult(bgClass) {
                        let toastEl;

                        if (this.toasts === 0) {
                            toastEl = document.querySelector('#liveToast');
                            toastEl.classList.add(bgClass);
                        } else {
                            let toastCount = this.toasts - 1;

                            if (toastCount === 0) {
                                toastCount = '';
                            }

                            toastEl = document.querySelector(`#liveToast${toastCount}`);

                            let clone = toastEl.cloneNode(true);

                            clone.id = `liveToast${this.toasts}`;
                            clone.classList.remove('status-danger');
                            clone.classList.remove('status-success');
                            clone.classList.add(bgClass);

                            toastEl.after(clone);
                            toastEl = clone;
                        }

                        let toastContentEl = toastEl.getElementsByClassName("toast-body")[0];
                        toastContentEl.innerHTML = this.statusText;

                        const toast = new bootstrap.Toast(toastEl);

                        toast.show();

                        this.toasts += 1;
                    }
                }
            });
    </script>
    </body>
</html>
