<?php

// src/Controller/LuckyController.php

namespace App\Controller;

use App\Entity\File;
use App\Repository\FileRepository;
use PhpParser\Node\Name;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Entity\Task;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Repository\UserRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Service\FileNameGenerator;
use App\Service\ResponseWithContentType;
use function Symfony\Component\String\s;
use App\Service\ShowEnv;

class LuckyController extends AbstractController
{
    /**
     * @Route("api/todo", name="task_list")
     */
    public function tasksList(Request $request, UserRepository $userRepository, TaskRepository $taskRepository): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');




        $entityManager = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'GET') {
            $user = $this->getUser();
            $tasks = $user->getTaskss();
            if (sizeof($tasks) == 0) {
                $mresponse["message"] = "There is no tasks";
            } else {
                $items=[];
                foreach ($tasks as $task) {
                    $task_item["id"] = $task->getId();
                    $task_item["text"] = $task->getTaskText();
                    array_push($items, $task_item);
                }
                $mresponse["items"] = $items;
            }
            return json_encode($mresponse) ?
                new Response(json_encode($mresponse)) :
                new Response(
                    "mresponse error",
                    200
                );
        } elseif ($request->getMethod() == 'POST') {
            if (gettype($request->getContent()) == "string") {
                $content = (string)$request->getContent();
                $data = json_decode(
                    $content,
                    true
                );
            } else {
                $mresponse["message"] = "Bad Request content";
                return json_encode($mresponse) ?
                    new Response(json_encode($mresponse)) :
                    new Response(
                        "mresponse error",
                        200
                    );
            }

            $user = $this->getUser();


            $taskText = $data['taskText'];

            $task = new Task();
            $task->setTaskText($taskText);

            $user->addTaskss($task);
            $entityManager->persist($task);
            $entityManager->flush();

            $mresponse["message"] = "Task added.";
            $mresponse["task_text"] = $taskText;
            return json_encode($mresponse) ?
                new Response(json_encode($mresponse)) :
                new Response(
                    "mresponse error",
                    200
                );
        } else {
            $mresponse["message"] = "Wrong request type.";
            return json_encode($mresponse) ?
                new Response(json_encode($mresponse)) :
                new Response(
                    "mresponse error",
                    200
                );
        }
    }


    /**
     * @Route("api/todo/{task_id}", name="task_managment")
     */
    public function taskManagment(Request $request, TaskRepository $taskRepository, int $task_id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');


        $entityManager = $this->getDoctrine()->getManager();
        $checkTask = $taskRepository->find($task_id);
        $user = $this->getUser();
        if ($checkTask->getOnwer() == $user) {
            if ($request->getMethod() == 'DELETE') {
                $entityManager->remove($checkTask);
                $entityManager->flush();

                $mresponse["message"] = "Task deleted.";
                return json_encode($mresponse) ?
                    new Response(json_encode($mresponse)) :
                    new Response(
                        "mresponse error",
                        200
                    );
            } else {
                $data = json_decode(
                    (string)$request->getContent(),
                    true
                );
                $newText = $data['new_text'];
                $checkTask->setTaskText($newText);
                $entityManager->flush();
                $mresponse["message"] = "Task updated.";
                return json_encode($mresponse) ?
                    new Response(json_encode($mresponse)) :
                    new Response(
                        "mresponse error",
                        200
                    );
            }
        } else {
            $mresponse["message"] = "Access denied";
            return json_encode($mresponse) ?
                new Response(json_encode($mresponse)) :
                new Response(
                    "mresponse error",
                    200
                );
        }
    }
    /**
     * @Route("api/files/upload", name="files_upload")
     */
    public function uploadFiles(Request $request, FileRepository $fileRepository, FileNameGenerator $nameGenerator): Response
    {
        $newFile = $request->files->get("uploaded_file");
        $destination = $this->getParameter('uploads_directory');
        $fileForDoctrine = new File();

        $fileDescribe = $nameGenerator->uploadFile($newFile);
        $fileForDoctrine->setFileName($fileDescribe["name"]);
        $fileForDoctrine->setFilePath($fileDescribe["path"]);
        $fileForDoctrine->setFileType($fileDescribe["type"]);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($fileForDoctrine);
        $entityManager->flush();
        $mresponse = new JsonResponse(
            ["message" => "OK"],
            200
        );
        return $mresponse;
    }

    /**
     * @Route("api/files/list", name="files_list")
     */
    public function viewFiles(Request $request, FileRepository $fileRepository): Response
    {
        $listOfFiles = $fileRepository->findAll();
        $array = [];
        foreach ($listOfFiles as $file) {
            $array[$file->getId()] = $file->getFileName();
        }
        $mresponse = new JsonResponse($array, 200);
        return $mresponse;
    }
    /**
     * @Route("api/files/{id}", name="files_edit")
     */
    public function editFiles(Request $request, FileRepository $fileRepository, int $id, ResponseWithContentType $contentType): Response
    {
        if ($request->getMethod() == "DELETE") {
            $file = $fileRepository->find($id);
            unlink((string)$this->getParameter(
                "uploads_directory")."/".$file->getFileName()
            );
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($file);
            $entityManager->flush();
            $mresponse = new JsonResponse(
                ["message"=>"file deleted"],
                200
            );
            return $mresponse;
        } elseif ($request->getMethod() == "GET") {
            $file = $fileRepository->find($id);
            return $contentType->makeResponse($file);
        }
        $mresponse = new JsonResponse(
            ["message" => "wrong request type"],
            200
        );
        return $mresponse;
    }
    /**
     * @Route("api/health", name="show_enviroment")
     */
    public function new(ShowEnv $mEnv):Response
    {

        $mresponse = new JsonResponse(
            ["message" => $mEnv->getEnv()],
            200
        );
        return $mresponse;
    }
}
