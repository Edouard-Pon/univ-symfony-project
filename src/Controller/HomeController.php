<?php

namespace App\Controller;

use App\Entity\Quiz;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager, Request $request ): Response
    {
        $locale = $request->get('_locale', $request->getDefaultLocale());
        $request->getSession()->set('_locale', $locale);
        $request->setLocale($locale);

        $quizzes = $entityManager->getRepository(Quiz::class)->findAll();

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'quizzes' => $quizzes,
        ]);
    }
}
