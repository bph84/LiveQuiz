<?php
    require_once("config.php");

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <title>Confirm Access Request</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:;base64,iVBORw0KGgo="> <!-- https://stackoverflow.com/a/13416784 -->
  <link rel="stylesheet" href="master.css" />


</head>

    <body>

    <div id="mainContainer">

        <div id="controls" class="aBlock">

            <div class="control" id="connect">
                <button> Connect Websocket </button>
            </div>

            <div class="control" id="identify">
                <button> Identify</button>
                <div class="result"></div>
            </div>

            <div class="control" id="showCorrect">
                <button> Show Correct Answer </button>
            </div>

            <div class="control" id="next">
                <button> Next</button>
                <div class="result"></div>
            </div>

            <div class="directControl" id="lock">
                <button> Lock </button>
            </div>

        </div>

        <div class="websocketMessages">

        </div>

        <div class="questionSendBlock" id="questionSendBlock"> 
            <div class="htmlContent">
                <textarea rows="8" cols="60"></textarea>
            </div>
            <div class="control" id="sendHtml">
                <button> Send HTML</button>
            <div class="result"></div>
        </div>


    </div>

    <div class="aBlock"> 
        Duplicate  a block
        <button id="newHtmlBlock"> Duplicate </button>
    </div>

    <div class="aBlock">
        Question Builder

        <div class="question">
            <label for="question">
                Question:
                <input type="text" name="question" id="question" />
            </label>
        </div>

        <div class="answerOptions">
            <label for="answer1">
                Answer 1:
                <input type="text" name="answer1" id="answer1" />
                <input type="radio" name="correct" value="answer1" />
            </label>

            <label for="answer2">
                Answer 2:
                <input type="text" name="answer2" id="answer2" />
                <input type="radio" name="correct" value="answer2" />
            </label>

            <label for="answer3">
                Answer 3:
                <input type="text" name="answer3" id="answer3" />
                <input type="radio" name="correct" value="answer3" />

            </label>

            <label for="answer4">
                Answer 4:
                <input type="text" name="answer4" id="answer4" />
                <input type="radio" name="correct" value="answer4" />

            </label>

            <label for="answer5">
                Answer 5:
                <input type="text" name="answer5" id="answer5" />
                <input type="radio" name="correct" value="answer5" />

            </label>

            <button id="buildQuestion">Build Question</button>
        </div>


    </div>

</div>
    </body>

    <script type="module">

        let ws;
        

        {
            const button = document.querySelector('#connect button');
            const result = document.querySelector('#connect .result');

            button.addEventListener('click', async () => {
                
                ws = new WebSocket("<?php echo WS_URL; ?>");
                globalThis.quiz_ws = ws;

                ws.onopen = () => {
                    result.innerHTML = 'Connected';
                };

                const messageContainerDiv = document.querySelector('.websocketMessages');
                ws.onmessage = (msg) => {
                    const div = document.createElement('div');
                    div.innerHTML = msg.data;
                    messageContainerDiv.appendChild(div);
                };
                
            });
        }

        {
            const button = document.querySelector('#identify button');
            const result = document.querySelector('#identify .result');

            button.addEventListener('click', async () => {
                console.log("Sending...");

                ws.send(JSON.stringify({
                    authorize: "please",
                    key: '<?php echo MASTER_KEY; ?>'
                }));
            });
        }

        {
            const button = document.querySelector('#sendHtml button');
            const result = document.querySelector('#sendHtml .result');

            button.addEventListener('click', async () => {
                ws.send(JSON.stringify({
                    newHtml: document.querySelector('.htmlContent textarea').value
                }));
            });
        }

        {
            const button = document.querySelector('#showCorrect button');

            button.addEventListener('click', async () => {
                ws.send(JSON.stringify({
                    showCorrectAnswer: true
                }));
            });
        }

        {
            document.querySelectorAll('.directControl button').forEach((button) => {
                button.addEventListener('click', async () => {
                    console.log("Sending...");

                    ws.send(JSON.stringify({
                        [button.parentElement.id]: true,
                        directControl: button.innerHTML
                    }));
                });
            });
        }

        


    </script>

    <script type="module" src="master.js"></script>

</html>