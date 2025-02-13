<?php

namespace App\Entity;

use App\Repository\TestResultRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TestResultRepository::class)]
class TestResult
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'testresults')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quiz $qcm = null;

    #[ORM\Column]
    private ?int $score = null;

    #[ORM\Column]
    private ?int $num_questions = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getQcm(): ?Quiz
    {
        return $this->qcm;
    }

    public function setQcm(?Quiz $qcm): static
    {
        $this->qcm = $qcm;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(int $score): static
    {
        $this->score = $score;

        return $this;
    }

    public function getNumQuestions(): ?int
    {
        return $this->num_questions;
    }

    public function setNumQuestions(int $num_questions): static
    {
        $this->num_questions = $num_questions;

        return $this;
    }
}
