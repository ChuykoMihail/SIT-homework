<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 */
class Task
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(type="text")
     */
    private string $task_text;



    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="taskss")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $onwer;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTaskText(): ?string
    {
        return $this->task_text;
    }

    public function setTaskText(string $task_text): self
    {
        $this->task_text = $task_text;

        return $this;
    }


    public function getOnwer(): ?User
    {
        return $this->onwer;
    }

    public function setOnwer(?User $onwer): self
    {
        $this->onwer = $onwer;

        return $this;
    }
}
