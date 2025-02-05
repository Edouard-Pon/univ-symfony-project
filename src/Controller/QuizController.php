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
    #[Route('/quiz/{id}', name: 'app_quiz_show')]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
        $quiz = $entityManager->getRepository(Quiz::class)->find($id);

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
        ]);
    }


    #[Route('/submit-quiz', name: 'app_quiz_submit', methods: ['POST'])]
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

        return $this->render('quiz/result.html.twig', [
            'quiz' => $quiz,
            'correctAnswers' => $correctAnswers,
            'totalQuestions' => $totalQuestions,
        ]);
    }

    #[Route('/quiz-generate', name: 'app_quiz_generate', methods: ['POST'])]
    public function create(Request $request, HttpClientInterface $httpClient, EntityManagerInterface $entityManager): Response
    {
        $apiUrl = $this->getParameter('API_URL');
        $llmModel = $this->getParameter('LLM_MODEL');

        $userPrompt = $request->request->get('prompt');
        $numberOfQuestions = $request->request->get('number_of_questions');
        $optionsType = $request->request->get('options_type');
        $jsonTemplate = '
            {
                "quiz": {
                    "title": "Quiz Title",
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
            FOLLOW THESE RULES:
            1. Return valid JSON matching this structure:
            $jsonTemplate
            2. Use lowercase for all keys
            3. Ensure correct_answer is always an array
            4. Ensure that the number of questions matches the number requested
            5. Ensure that the theme of the quiz matches the questions
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

            foreach ($quizData['quiz']['questions'] as $questionData) {
                $question = new Question();
                $question->setType($questionData['type'] === QuestionType::SINGLE ? QuestionType::SINGLE : QuestionType::MULTIPLE);
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