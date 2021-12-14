<?php

namespace App\Entity;

use App\Repository\FileRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FileRepository::class)
 */
class File
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $FileName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $FileType;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $FilePath;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFileName(): ?string
    {
        return $this->FileName;
    }

    public function setFileName(string $FileName): self
    {
        $this->FileName = $FileName;

        return $this;
    }

    public function getFileType(): ?string
    {
        return $this->FileType;
    }

    public function setFileType(string $FileType): self
    {
        $this->FileType = $FileType;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->FilePath;
    }

    public function setFilePath(string $FilePath): self
    {
        $this->FilePath = $FilePath;

        return $this;
    }
}
