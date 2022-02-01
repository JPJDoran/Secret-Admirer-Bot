var brd = document.createElement("DIV");
document.body.insertBefore(brd, document.getElementById("board"));

const duration = 3000;
const speed = 3;
const cursorXOffset = 0;
const cursorYOffset = -5;

var hearts = [];

function generateHeart(x, y, xBound, xStart, scale) {
    var heart = document.createElement("DIV");

    heart.setAttribute('class', 'heart');
    brd.appendChild(heart);
    heart.time = duration;
    heart.x = x;
    heart.y = y;
    heart.bound = xBound;
    heart.direction = xStart;
    heart.style.left = heart.x + "px";
    heart.style.top = heart.y + "px";
    heart.scale = scale;
    heart.style.transform = "scale(" + scale + "," + scale + ")";

    if (hearts == null)
        hearts = [];

    hearts.push(heart);

    return heart;
}

var event = null;

document.onmousedown = function(e) {
    event = e;
}

document.ontouchstart = function(e) {
    event = e.touches[0];
}

var before = Date.now();
var id = setInterval(frame, 5);

function frame() {
    var current = Date.now();
    var deltaTime = current - before;
    before = current;

    for (i in hearts) {
        var heart = hearts[i];
        heart.time -= deltaTime;

        if (heart.time > 0) {
            heart.y -= speed;
            heart.style.top = heart.y + "px";
            heart.style.left = heart.x + heart.direction * heart.bound * Math.sin(heart.y * heart.scale / 30) / heart.y * 150 + "px";
        } else {
            heart.parentNode.removeChild(heart);
            hearts.splice(i, 1);
        }
    }
}

async function showTheLove()
{
    for (var x = 0; x < 5; x++) {
        await new Promise(r => setTimeout(r, 100));

        var start = 1 - Math.round(Math.random()) * 2;
        var scale = Math.random() * Math.random() * 0.8 + 0.2;
        var bound = 30 + Math.random() * 20;

        generateHeart(event.pageX - brd.offsetLeft + cursorXOffset, event.pageY + cursorYOffset, bound, start, scale);
    }
}

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
                signature = "@"+this.sanitizeString(document.getElementById("twitter-handle").innerHTML);
            } else {
                signature = this.sanitizeString(signature);
            }

            if (recipient === "" || signature === "" || message === "") {
                return "Start filling out the form to generate your preview..."
            }

            return `@${recipient} ${message} ${signature} ${this.getEmoji()}`;
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
            // Make some hearts <3
            showTheLove();

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
