<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Quiz;
use App\Entity\Question;
use App\Entity\Option;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class QuizFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create admin user
        $user = new User();
        $user->setEmail('admin_quizmaker@example.com');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));

        $manager->persist($user);
        $manager->flush();

        // First Quiz
        $quiz1 = new Quiz();
        $quiz1->setUser($user);
        $quiz1->setPrompt('First Quiz Prompt');

        $question1 = new Question();
        $question1->setTitle('First Question Title');
        $question1->setContent('First Question Content');

        $option1 = new Option();
        $option1->setContent('First Option Content');
        $option1->setCorrect(true);

        $option2 = new Option();
        $option2->setContent('Second Option Content');
        $option2->setCorrect(false);

        $question1->addOption($option1);
        $question1->addOption($option2);

        $quiz1->addQuestion($question1);

        $manager->persist($quiz1);
        $manager->persist($question1);
        $manager->persist($option1);
        $manager->persist($option2);

        // Second Quiz
        $quiz2 = new Quiz();
        $quiz2->setUser($user);
        $quiz2->setPrompt('Second Quiz Prompt');

        $question2 = new Question();
        $question2->setTitle('Second Question Title');
        $question2->setContent('Second Question Content');

        $option3 = new Option();
        $option3->setContent('Third Option Content');
        $option3->setCorrect(false);

        $option4 = new Option();
        $option4->setContent('Fourth Option Content');
        $option4->setCorrect(true);

        $option5 = new Option();
        $option5->setContent('Fifth Option Content');
        $option5->setCorrect(false);

        $question2->addOption($option3);
        $question2->addOption($option4);
        $question2->addOption($option5);

        $quiz2->addQuestion($question2);

        $manager->persist($quiz2);
        $manager->persist($question2);
        $manager->persist($option3);
        $manager->persist($option4);
        $manager->persist($option5);

        $manager->flush();
    }
}