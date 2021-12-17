<?php

namespace App\Service;

use App\Entity\File;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ResponseWithContentType
{
    private string $uploadsPath;

    public function __construct(string $uploadsPath)
    {
        $this->uploadsPath = $uploadsPath;
    }
    public function makeResponse(File $file): Response
    {
        $MemeTypes = [
           "jpeg"=>"image/jpeg",
           "jpg"=>"image/jpeg",
           "png"=>"image/png",
           "mp4"=>"video/mp4",
           "doc"=>"application/msword",
           "pdf"=>"application/pdf"
        ];
        $response=new BinaryFileResponse(
            $this -> uploadsPath."/".$file->getFileName()
        );
        $response->headers->set(
            'Content-Type',
            $MemeTypes[$file->getFileType()]
        );
        return $response;
    }
}
