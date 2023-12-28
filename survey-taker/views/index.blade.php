<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <title>{{$config['survey_name']}}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="data:;base64,iVBORw0KGgo="> <!-- https://stackoverflow.com/a/13416784 -->
    <link rel="stylesheet" href="css/quiz.css"/>
</head>

<body>
<div id="mainContainer">
    <Heading><h1> Welcome to Quiz beta! </h1></Heading>

    <div id="acceptTerms" class="guidance" style="display: none;">
        <p>
            This website is under active development.
        </p>
        <p>
            Please be aware that no guarantees are made about the security or privacy of any information entered on this website.
        </p>
        <p>
            If you do not know what this site is, you should probably close this tab.
        </p>
        <button id="accept">Accept</button>
    </div>

    <div id="guidance" class="guidance" style="display: none;">
        <p>This is a survey system under development </p>
        <div id="connectionStatus">
            Connection Status: <span> Not yet connected </span>
        </div>
    </div>

    <div id="yourName" class="guidance" style="display: none;">
        <p>
            Please enter a name, ideally something anonymous! This is just so we can track your answers.
        </p>
        <input type="text" id="name" placeholder="Your name"/>
        <button id="setName">Save</button>
    </div>


    <div id="container" style="display: none;"> 
        <div class="sliderContainer" >

            <div class="sliderGuidance">
                <p>How much do you like this website?</p>
            </div>

            <div class="sliderLabels">
                <div class="left">
                    <p>Not much at all</p>
                </div>
                <div class="right">
                    <p>Totally</p>
                </div>
            </div>

            <div class="sliderInputContainer">         
                <input type="range" min="0" max="100" value="50" class="slider" id="mySlider" data-slider-name="firstSlider" >
            </div>

        </div>
    </div>

    <div id="surveyButtons">
        <button disabled="disabled" id="previousQuestion">Previous</button>
        <button id="nextQuestion">Next</button>
    </div>

</div>
</body>

<script>



function sliderChange(event) {
        console.log("Slider changed");
        console.log(event.target.value);
        console.log(event.target.dataset.sliderName);
        
        
        globalThis.quiz_ws.send(JSON.stringify({
            type: 'slider',
            value: event.target.value
        }));
    }

    document.querySelector('#mySlider').addEventListener('change', sliderChange);

</script>

<script type="module">
    let ws;
    let currentSurveryQuestion = 0;
    let currentQuestionType = '';


    const conStat = document.querySelector('#connectionStatus span');
    const htmlContainer = document.querySelector('#container');
    let selectedAnswer = '';

    function connect() {
        ws = new WebSocket("{{$config['WS_URL']}}");
        globalThis.quiz_ws = ws;


        ws.onopen = () => {
            conStat.innerHTML = 'Connected';

            ws.send(JSON.stringify({
                authorize: 'identify',
                key: "{{$config['SURVEY_KEY']}}", 
            }));
        };

        // {{$config['COOKIE_DOMAIN']}}

        const messageContainerDiv = document.querySelector('.websocketMessages');

        ws.onmessage = (msgBuffer) => {

            console.log(msgBuffer);

            const msg = JSON.parse(msgBuffer.data);

            if (msg.surveyDone) {
                showAllDone();
            }

            if (msg.nextQuestion) {
                console.log("Next question", msg.nextQuestion);
                setupNextQuestion(msg);
            }

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

    function setupNextQuestion(questionData) {

        currentSurveryQuestion = questionData.position + 1;

        if (questionData.type == 'slider') {
            setupSliderQuestion(questionData);
            setNextButtonNext();
            currentQuestionType = 'slider';
            return;
        } else if (questionData.type == 'radio') {
            setupRadioQuestion(questionData);
            setNextButtonSkip();
            currentQuestionType = 'radio';
            return;
        } else if (questionData.type == 'freetext') {
            setupFreeTextQuestion(questionData);
            setNextButtonNext();
            currentQuestionType = 'freetext';
            return;
        }
    }

    function setupSliderQuestion(questionData) {
        document.querySelector('#container').innerHTML = `
        <div class="sliderContainer" >

            <div class="sliderGuidance">
                <p>${questionData.text}</p>
            </div>

            <div class="sliderLabels">
                <div class="left">
                    <p>${questionData.left}</p>
                </div>
                <div class="right">
                    <p>${questionData.right}</p>
                </div>
            </div>

            <div class="sliderInputContainer">         
                <input type="range" min="0" max="100" value="50" class="slider" id="mySlider" data-slider-name="firstSlider" >
            </div>

        </div>        
        `;
    }

    function setupFreeTextQuestion(questionData) {
        document.querySelector('#container').innerHTML = `
        <div class="freeTextContainer" >

            <div class="freeTextGuidance">
                <p>${questionData.text}</p>
            </div>


            <div class="freeTextInputContainer">         
                <textarea id="freeTextAnswer" rows="4" cols="50"></textarea>
            </div>

        </div>        
        `;
    }

    function showAllDone() {
        document.querySelector('#container').innerHTML = `
        <div class="freeTextContainer" >

            That's it! Thanks for taking part!

            <button id="showAnswers">Show Answers</button>

        </div>        
        `;
    }

    function setupRadioQuestion(questionData) {


        let buttons = questionData.answers.map((answer) => {
            return `<button class="dynamicRadioAnswer">${answer}</button>`;
        }).join('');


        document.querySelector('#container').innerHTML = `
        <div class="radioContainer" >

            <div class="radioGuidance">
                <p>${questionData.text}</p>
            </div>

            <div class="radioAnswers">
                ${buttons}
            </div>

        </div>        
        `;

        document.querySelectorAll('.dynamicRadioAnswer').forEach((button) => {
            button.addEventListener('click', () => {
                ws.send(JSON.stringify({
                    getNextQuestion: currentSurveryQuestion,
                    type: 'answer',
                    questionNo: currentSurveryQuestion,
                    answer: button.innerHTML.trim()
                }));
            });
        });
    }

    function getNextQuestion() {

        if (currentQuestionType === 'radio') {
            ws.send(JSON.stringify({
                type: 'answer',
                answer: "skipped",
                questionNo: currentSurveryQuestion
            }));
        } else if (currentQuestionType === 'slider') {
            const sliderValue = document.querySelector('#mySlider').value;

            ws.send(JSON.stringify({
                type: 'answer',
                answer: sliderValue,
                questionNo: currentSurveryQuestion
            }));
        } else if (currentQuestionType === 'freetext') {
            const freeTextAnswer = document.querySelector('#freeTextAnswer').value;

            ws.send(JSON.stringify({
                type: 'answer',
                answer: freeTextAnswer,
                questionNo: currentSurveryQuestion
            }));
        }

        ws.send(JSON.stringify({
            getNextQuestion: currentSurveryQuestion
        }));
    }

    const nextButton = document.querySelector('#nextQuestion');

    function setNextButtonSkip() {
        nextButton.innerHTML = 'Skip'; 
    }

    function setNextButtonNext() {
        nextButton.innerHTML = 'Next'; 
    }

    document.querySelector('#nextQuestion').addEventListener('click', getNextQuestion);


</script>
</html>
