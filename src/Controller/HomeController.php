<?php

namespace App\Controller;

use App\Entity\Quiz;
use App\Entity\TestResult;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

#[Route('{_locale<%app.supported_locales%>}')]
final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager, Request $request ): Response
    {
        $locale = $request->getLocale();
        $user = $this->getUser();
        $quizzes = $entityManager->getRepository(Quiz::class)->findBy(['language' => $locale]);
        $testResults = $entityManager->getRepository(TestResult::class)->findBy(['user' => $user]);

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'quizzes' => $quizzes,
            'testResults' => $testResults,
        ]);
    }
}
