/*
PLEASE NOTE THAT MUCH OF THIS CODE IS VERY POOR QUALITY. 

I WROTE THIS CODE IN A HURRY AND DID NOT HAVE TIME TO CLEAN IT UP. <-- That's what Copilot said!

Actually, I'm leaving this in such a state as an educational example. 

*/


function buildQuestion() {

    const question = document.querySelector('input[name=question]').value;
    const answer1 = document.querySelector('input[name=answer1]').value;
    const answer2 = document.querySelector('input[name=answer2]').value;
    const answer3 = document.querySelector('input[name=answer3]').value;
    const answer4 = document.querySelector('input[name=answer4]').value;
    const answer5 = document.querySelector('input[name=answer5]').value;

    const correctAnswerChecked = document.querySelector('input[name=correct]:checked').value;
    let correctAnswer = '';

    switch (correctAnswerChecked) {
        case 'answer1':
            correctAnswer = answer1;
            break;
        case 'answer2':
            correctAnswer = answer2;
            break;
        case 'answer3':
            correctAnswer = answer3;
            break;
        case 'answer4':
            correctAnswer = answer4;
            break;
        case 'answer5':
            correctAnswer = answer5;
            break;
    }
    
    let questionHTML = question; 

    let answerTmpl = `                
<div class="answer">
    <label for="answer1">
        <button> [Answer] </button>
    </label>
</div>`;

    let answerHTML1 = '';
    let answerHTML2 = '';
    let answerHTML3 = '';
    let answerHTML4 = '';
    let answerHTML5 = '';


    if (answer1) {
        answerHTML1 = answerTmpl.replace('[Answer]', answer1);
    }

    if (answer2) {
        answerHTML2 = answerTmpl.replace('[Answer]', answer2);
    }

    if (answer3) {
        answerHTML3 = answerTmpl.replace('[Answer]', answer3);
    }

    if (answer4) {
        answerHTML4 = answerTmpl.replace('[Answer]', answer4);
    }

    if (answer5) {
        answerHTML5 = answerTmpl.replace('[Answer]', answer5);
    }


    const newQuestionBlock = document.querySelector('div#questionSendBlock').cloneNode(true);
    newQuestionBlock.removeAttribute('id');

    const textArea = newQuestionBlock.querySelector('textarea')
    textArea.value = `
        <div class="question">
            ${questionHTML}
        </div>

        <div class="possibleAnswers"> 
            ${answerHTML1}
            ${answerHTML2} 
            ${answerHTML3} 
            ${answerHTML4} 
            ${answerHTML5}
        </div>
    `;


    newQuestionBlock.querySelector('button').addEventListener('click', () => {
        globalThis.quiz_ws.send(JSON.stringify({
            newHtml: textArea.value,
            correctAnswer: correctAnswer
        }));
    });

    document.querySelector('div#questionSendBlock').parentElement.appendChild(newQuestionBlock);

}

function newHtmlBlock() {
    const questionSendBlock = document.querySelector('div#questionSendBlock');
    const newBlock = questionSendBlock.cloneNode(true);
    newBlock.removeAttribute('id');
    newBlock.querySelector('button').addEventListener('click', () => {
        globalThis.quiz_ws.send(JSON.stringify({
            newHtml: newBlock.querySelector('textarea').value
        }));
    });
    questionSendBlock.parentElement.appendChild(newBlock);
}



document.addEventListener('DOMContentLoaded', function() {

    document.querySelector('button#newHtmlBlock').addEventListener('click', newHtmlBlock);

    document.querySelector('button#buildQuestion').addEventListener('click', buildQuestion);

})