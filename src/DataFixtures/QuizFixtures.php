<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Quiz;
use App\Entity\Question;
use App\Entity\Option;
use App\Enum\QuestionType;
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

// Question 1
        $question1 = new Question();
        $question1->setTitle('First Question Title');
        $question1->setContent('First Question Content');
        $question1->setType(QuestionType::SINGLE);

        $option1 = new Option();
        $option1->setContent('First Option Content');
        $option1->setCorrect(true);

        $option2 = new Option();
        $option2->setContent('Second Option Content');
        $option2->setCorrect(false);

        $question1->addOption($option1);
        $question1->addOption($option2);

        $quiz1->addQuestion($question1);

// Question 2
        $question2 = new Question();
        $question2->setTitle('Second Question Title');
        $question2->setContent('Second Question Content');
        $question2->setType(QuestionType::SINGLE);

        $option3 = new Option();
        $option3->setContent('Third Option Content');
        $option3->setCorrect(false);

        $option4 = new Option();
        $option4->setContent('Fourth Option Content');
        $option4->setCorrect(true);

        $question2->addOption($option3);
        $question2->addOption($option4);

        $quiz1->addQuestion($question2);

// Question 3
        $question3 = new Question();
        $question3->setTitle('Third Question Title');
        $question3->setContent('Third Question Content');
        $question3->setType(QuestionType::SINGLE);

        $option5 = new Option();
        $option5->setContent('Fifth Option Content');
        $option5->setCorrect(true);

        $option6 = new Option();
        $option6->setContent('Sixth Option Content');
        $option6->setCorrect(false);

        $question3->addOption($option5);
        $question3->addOption($option6);

        $quiz1->addQuestion($question3);

// Question 4
        $question4 = new Question();
        $question4->setTitle('Fourth Question Title');
        $question4->setContent('Fourth Question Content');
        $question4->setType(QuestionType::SINGLE);

        $option7 = new Option();
        $option7->setContent('Seventh Option Content');
        $option7->setCorrect(false);

        $option8 = new Option();
        $option8->setContent('Eighth Option Content');
        $option8->setCorrect(true);

        $question4->addOption($option7);
        $question4->addOption($option8);

        $quiz1->addQuestion($question4);

// Question 5
        $question5 = new Question();
        $question5->setTitle('Fifth Question Title');
        $question5->setContent('Fifth Question Content');
        $question5->setType(QuestionType::SINGLE);

        $option9 = new Option();
        $option9->setContent('Ninth Option Content');
        $option9->setCorrect(true);

        $option10 = new Option();
        $option10->setContent('Tenth Option Content');
        $option10->setCorrect(false);

        $question5->addOption($option9);
        $question5->addOption($option10);

        $quiz1->addQuestion($question5);

        $manager->persist($quiz1);
        $manager->persist($question1);
        $manager->persist($option1);
        $manager->persist($option2);
        $manager->persist($question2);
        $manager->persist($option3);
        $manager->persist($option4);
        $manager->persist($question3);
        $manager->persist($option5);
        $manager->persist($option6);
        $manager->persist($question4);
        $manager->persist($option7);
        $manager->persist($option8);
        $manager->persist($question5);
        $manager->persist($option9);
        $manager->persist($option10);

        // Second Quiz
        $quiz2 = new Quiz();
        $quiz2->setUser($user);
        $quiz2->setPrompt('Second Quiz Prompt');

        $question2 = new Question();
        $question2->setTitle('Second Question Title');
        $question2->setContent('Second Question Content');
        $question2->setType(QuestionType::MULTIPLE);

        $option3 = new Option();
        $option3->setContent('Third Option Content');
        $option3->setCorrect(true);

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