<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileNameGenerator
{
    private $_uploadsPath;

    public function __construct(string $uploadsPath)
    {
        $this->_uploadsPath = $uploadsPath;
    }

    /**
     * @param UploadedFile $uploadedFile
     * @return array<string,mixed>
     */
    public function uploadFile(UploadedFile $uploadedFile): array
    {
        $destination = $this->_uploadsPath;
        $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
        $type = $uploadedFile->guessExtension();
        $newFilename = $originalFilename.'-'.uniqid().'.'.$type;
        $uploadedFile->move(
            $destination,
            $newFilename
        );
        $fileDescribe = ["name"=>$newFilename, "path"=>$destination, "type"=>$type];
        return $fileDescribe;
    }
}
