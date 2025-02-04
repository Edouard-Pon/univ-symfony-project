<?php

namespace App\Controller;

use App\Entity\Option;
use App\Entity\Quiz;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class QuizController extends AbstractController
{
    #[Route('/quiz/{id}', name: 'app_quiz_show')]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
        $quiz = $entityManager->getRepository(Quiz::class)->find($id);

        if (!$quiz) {
            throw $this->createNotFoundException('The quiz does not exist');
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
            $selectedOptionId = $request->request->get('question' . $question->getId());
            $selectedOption = $entityManager->getRepository(Option::class)->find($selectedOptionId);

            if ($selectedOption && $selectedOption->isCorrect()) {
                $correctAnswers++;
            }
        }

        return $this->render('quiz/result.html.twig', [
            'quiz' => $quiz,
            'correctAnswers' => $correctAnswers,
            'totalQuestions' => $totalQuestions,
        ]);
    }
}