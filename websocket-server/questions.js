import { XMLParser } from "fast-xml-parser";
import { readFileSync } from "fs";

let questions = [];

export function loadQuestions() {

    const xml = readFileSync('questions.xml', 'utf-8');

    const parser = new XMLParser({
        ignoreAttributes : false
    });
    const questionsParsed = parser.parse(xml);

    globalThis.questionsDebug = questionsParsed;

    for (const q of questionsParsed['survey']['question']) {

        const currentQ = {text: q.text, position: questions.length};

        if (q?.['@_type'] == 'slider') {
            
            currentQ.type = 'slider';
            currentQ.left = q.left;
            currentQ.right = q.right;

        } else if (q?.['@_type'] == 'radio') {

            currentQ.type = 'radio';
            currentQ.answers = q['options'].option;

        } else if (q?.['@_type'] == 'freetext') {

            currentQ.type = 'freetext';

        }else {
            currentQ.type = 'radio';
            currentQ.answers = q['options'].option;

        }

        questions.push(currentQ);
    }

    console.log(questions);
    //console.log(questions.quiz.question[0].answer[0].correct);

}

export function getNextQuestion(position = 0) {
    return questions[position];
}