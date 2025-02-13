<?php

namespace App\Controller;

use App\Entity\Option;
use App\Entity\Question;
use App\Entity\Quiz;
use App\Enum\QuestionType;
use App\Utils\QuizValidationUtility;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class QuizController extends AbstractController
{
    #[Route('/{_locale<%app.supported_locales%>}/quiz/{id}', name: 'app_quiz_show')]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
        $motivationApiUrl = $this->getParameter('MOTIVATION_API_URL');
        $quiz = $entityManager->getRepository(Quiz::class)->find($id);
        $randomNumberApiUrl = $this->getParameter('RANDOM_NUMBER_API_URL');

        if (!$quiz) {
            throw $this->createNotFoundException('The quiz does not exist');
        }
        foreach ($quiz->getQuestions() as $question) {
            $options = $question->getOptions()->toArray();
            shuffle($options);
            $question->getOptions()->clear();
            foreach ($options as $option) {
                $question->addOption($option);
            }
            $quiz->addQuestion($question);
        }

        return $this->render('quiz/index.html.twig', [
            'quiz' => $quiz,
            'random_number_api_url' => $randomNumberApiUrl,
            'motivational_api_url' => $motivationApiUrl,
        ]);
    }


    #[Route('/{_locale<%app.supported_locales%>}/submit-quiz', name: 'app_quiz_submit', methods: ['POST'])]
    public function submit(Request $request, EntityManagerInterface $entityManager): Response
    {
        $quizId = $request->request->get('quiz_id');
        $quiz = $entityManager->getRepository(Quiz::class)->find($quizId);

        if (!$quiz) {
            throw $this->createNotFoundException('The quiz does not exist');
        }

        $correctAnswers = 0;
        $totalQuestions = count($quiz->getQuestions());

        foreach ($quiz->getQuestions() as $question) {
            if ($question->getType() === QuestionType::MULTIPLE) {
                $selectedOptionIds = $request->request->all('question' . $question->getId());
                $correctOptions = $question->getOptions()->filter(function($option) {
                    return $option->isCorrect();
                });

                $correctOptionIds = $correctOptions->map(function($option) {
                    return $option->getId();
                })->toArray();

                if (count($selectedOptionIds) === count($correctOptionIds) && !array_diff($selectedOptionIds, $correctOptionIds)) {
                    $correctAnswers++;
                }
            } else {
                $selectedOptionId = $request->request->get('question' . $question->getId());
                $selectedOption = $entityManager->getRepository(Option::class)->find($selectedOptionId);
                if ($selectedOption && $selectedOption->isCorrect()) {
                    $correctAnswers++;
                }
            }
        }

        return $this->redirectToRoute('app_quiz_result', [
            'quizId' => $quizId,
            'correctAnswers' => $correctAnswers,
            'totalQuestions' => $totalQuestions,
        ]);
    }

    // TODO - check this route and fix it if needed
    #[Route('/{_locale<%app.supported_locales%>}/quiz-result/{quizId}/{correctAnswers}/{totalQuestions}', name: 'app_quiz_result')]
    public function result(int $quizId, int $correctAnswers, int $totalQuestions, EntityManagerInterface $entityManager): Response
    {
        $quiz = $entityManager->getRepository(Quiz::class)->find($quizId);

        if (!$quiz) {
            throw $this->createNotFoundException('The quiz does not exist');
        }

        return $this->render('quiz/result.html.twig', [
            'quiz' => $quiz,
            'correctAnswers' => $correctAnswers,
            'totalQuestions' => $totalQuestions,
        ]);
    }

    #[Route('/{_locale<%app.supported_locales%>}/quiz-generate', name: 'app_quiz_generate', methods: ['POST'])]
    public function create(Request $request, HttpClientInterface $httpClient, EntityManagerInterface $entityManager): Response
    {
        $apiUrl = $this->getParameter('API_URL');
        $llmModel = $this->getParameter('LLM_MODEL');

        $userPrompt = $request->request->get('prompt');
        $numberOfQuestions = $request->request->get('number_of_questions');
        $optionsType = $request->request->get('options_type');
        $language = $request->request->get('language');

        if ($optionsType === 'mixed') {
            $optionsType = 'single or multiple';
        }
        $jsonTemplate = '
            {
                "quiz": {
                    "title": "Quiz Title",
                    "language": "en" or "fr",
                    "questions": [
                        {
                            "id": 1,
                            "type": "single" or "multiple",
                            "question": "Question text?",
                            "options": [
                                {"id": "a", "text": "Option 1", "correct": true},
                                {"id": "b", "text": "Option 2", "correct": false},
                                {"id": "c", "text": "Option 3", "correct": false},
                                {"id": "d", "text": "Option 4", "correct": false}
                            ],
                        }
                    ]
                }
            }
        ';

        $prompt = "
            GENERATE A $userPrompt QUIZ WITH $numberOfQuestions QUESTIONS. $optionsType CHOICE QUESTIONS.
            THE QUIZ MUST BE IN $language LANGUAGE.
            ENSURE THAT THE QUESTIONS MATCH THE THEME OF THE QUIZ.
            FOLLOW THESE RULES:
            1. Return valid JSON matching this structure:
            ```json
            $jsonTemplate
            ```
            2. Use lowercase for all keys.
            3. Ensure that the number of questions matches the number requested.
            4. Ensure that the theme of the quiz matches the questions.
            5. Ensure that if user requests single or multiple choice questions, the quiz contains both.
            6. Ensure that if user requests single choice questions, the quiz contains only single choice questions.
            7. Ensure that if user requests multiple choice questions, the quiz contains only multiple choice questions.
            8. Ensure that the correct type of questions is returned.
            9. Ensure that the single type questions have only one correct answer.
            10. Ensure that the multiple type questions have more than one correct answer.
        ";

        $jsonPayload = [
            'model' => $llmModel,
            'prompt' => $prompt,
            'stream' => false,
            'keep_alive' => 0,
            'format' => [
                'type' => 'object',
                'properties' => [
                    'quiz' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => ['type' => 'string'],
                            'language' => ['type' => 'string'],
                            'questions' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'id' => ['type' => 'integer'],
                                        'type' => ['type' => 'string'],
                                        'question' => ['type' => 'string'],
                                        'options' => [
                                            'type' => 'array',
                                            'items' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'id' => ['type' => 'string'],
                                                    'text' => ['type' => 'string'],
                                                    'correct' => ['type' => 'boolean']
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        try {
            $response = $httpClient->request('POST', $apiUrl . '/generate', [
                'json' => $jsonPayload,
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'timeout' => 1000,
            ]);

            if ($response->getStatusCode() !== 200) {
                return new JsonResponse(['error' => 'Failed to fetch data from API'], Response::HTTP_BAD_REQUEST);
            }

            $data = $response->toArray();
            $quizData = json_decode($data['response'], true);

            if (!QuizValidationUtility::validateJsonData($quizData)) {
                return new JsonResponse(['error' => 'Invalid JSON data'], Response::HTTP_BAD_REQUEST);
            }

            $user = $this->getUser();

            $quiz = new Quiz();
            $quiz->setPrompt($quizData['quiz']['title']);
            $quiz->setUser($user);
            $quiz->setLanguage($quizData['quiz']['language']);

            foreach ($quizData['quiz']['questions'] as $questionData) {
                $question = new Question();
                $question->setType($questionData['type'] === QuestionType::SINGLE->value ? QuestionType::SINGLE : QuestionType::MULTIPLE);
                $question->setTitle('Question: ' . $questionData['id']);
                $question->setContent($questionData['question']);
                $question->setQuiz($quiz);

                foreach ($questionData['options'] as $optionData) {
                    $option = new Option();
                    $option->setContent($optionData['text']);
                    $option->setCorrect($optionData['correct']);
                    $option->setQuestion($question);
                    $question->addOption($option);

                    $entityManager->persist($option);
                }

                $entityManager->persist($question);
            }

            $entityManager->persist($quiz);
            $entityManager->flush();
        } catch (TransportExceptionInterface $e) {
            return new JsonResponse(['error' => 'Failed to fetch data from API'], Response::HTTP_BAD_REQUEST);
        }

        return $this->redirectToRoute('app_home');
    }
}