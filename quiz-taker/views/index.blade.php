<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>Confirm Access Request</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:;base64,iVBORw0KGgo="> <!-- https://stackoverflow.com/a/13416784 -->
    <link rel="stylesheet" href="css/quiz.css"/>
</head>

<body>
<div id="mainContainer">
    <Heading><h1> Welcome to the Live Quiz beta! </h1></Heading>

    <div id="acceptTerms" class="guidance" style="display: none;">
        <p>
            By continuing to use this website, you accept that we use cookies and other technologies to track usage.
            This website is used for educational purposes and currently under active development.
        </p>
        <p>
            Please be aware that no guarantees are made about the security or privacy of any information entered on this website.
        </p>
        <p>
            If you do not know what this site is, you should probably close this tab.
        </p>
        <button id="accept">Accept</button>
    </div>

    <div id="yourName" class="guidance" style="display: none;">
        <p>
            Please enter a name, ideally something recognisable from your Discord username.
        </p>
        <input type="text" id="name" placeholder="Your name"/>
        <button id="setName">Save</button>
    </div>

    <div id="guidance" class="guidance" style="display: none;">
        <p>This is a live quiz system, used along side Discord.</p>
        <p>If you have found this website but are not on the Discord server, please join The Language Sloth Discord server.</p>
        <p>Please also be aware that this site will be completely inactive outside of Discord classes.</p>
        <div id="connectionStatus">
            Connection Status: <span> Not yet connected </span>
        </div>
    </div>

    <div id="container" style="display: none;">
        When the quiz is active, this section will be replaced by the current quiz question.
    </div>
</div>
</body>

<script type="module">
    let ws;

    const conStat = document.querySelector('#connectionStatus span');
    const htmlContainer = document.querySelector('#container');
    let selectedAnswer = '';

    function connect() {
        ws = new WebSocket("{{$config['WS_URL']}}");

        ws.onopen = () => {
            conStat.innerHTML = 'Connected';
        };

        const messageContainerDiv = document.querySelector('.websocketMessages');

        ws.onmessage = (msgBuffer) => {

            console.log(msgBuffer);

            const msg = JSON.parse(msgBuffer.data);

            if (msg.newHtml) {
                console.log("Setting new html", msg.newHtml);
                htmlContainer.innerHTML = msg.newHtml;
                setupContainerButtons();
            }

            if (msg.directControl) {
                handleDirectControl(msg.directControl.trim().toLowerCase());
            }

            if (msg.correctAnswer) {

                let message = "The correct answer was: " + msg.correctAnswer;

                if (selectedAnswer === '') {
                    return;
                }

                if (selectedAnswer === msg.correctAnswer) {
                    htmlContainer.querySelector('button.selected').classList.add('correct');
                } else {
                    htmlContainer.querySelector('button.selected').classList.add('incorrect');

                    htmlContainer.querySelectorAll('button').forEach((button) => {
                        if (button.innerHTML.trim() === msg.showCorrectAnswer) {
                            button.classList.add('actualCorrect');
                        }

                        button.disabled = true;
                    });
                }
            }
        };
    }

    function setupContainerButtons() {
        htmlContainer.querySelectorAll('button').forEach((button) => {
            button.addEventListener('click', async () => {
                ws.send(JSON.stringify({
                    type: 'answer',
                    answer: button.innerHTML.trim()
                }));

                htmlContainer.querySelectorAll('button').forEach((button) => {
                    button.classList = [];
                });

                button.classList.add('selected');
                selectedAnswer = button.innerHTML.trim();
            });
        });
    }

    function handleDirectControl(control) {
        console.log("Handling direct control", control);

        switch (control) {
            case "lock":
                htmlContainer.querySelectorAll('button').forEach((button) => {
                    button.disabled = true;
                });
                break;
        }
    }

    document.addEventListener('DOMContentLoaded', () => {

        document.querySelector('#accept').addEventListener('click', () => {
            console.log("Accepted terms");
            document.querySelector('#acceptTerms').style.display = 'none';
            document.cookie = "acceptTerms=" + Date.now() + "; domain={{$config['COOKIE_DOMAIN']}}; path=/; expires=Fri, 31 Dec 9999 23:59:59 GMT";
            document.querySelector('#yourName').style = '';
        });

        document.querySelector('#setName').addEventListener('click', () => {
            console.log("Set name");
            document.querySelector('#yourName').style.display = 'none';
            document.cookie = "usersName=" + document.querySelector('#name').value + "; domain={{$config['COOKIE_DOMAIN']}}; path=/; expires=Fri, 31 Dec 9999 23:59:59 GMT";
            document.querySelector('#guidance').style = '';
            document.querySelector('#container').style = '';

            connect();
        });

        if (document.cookie.indexOf("acceptTerms") === -1) {
            document.querySelector('#acceptTerms').style = '';
        } else {

            if (document.cookie.indexOf("usersName") === -1) {
                document.querySelector('#yourName').style = '';
            } else {
                document.querySelector('#guidance').style = '';
                document.querySelector('#container').style = '';

                setTimeout(() => {
                    connect();
                    conStat.innerHTML = "Connecting to server";
                }, 250);
            }
        }
    });
</script>
</html>
