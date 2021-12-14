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
use function Symfony\Component\String\s;
use Symfony\Component\Security\Core\Security;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Service\FileNameGenerator;
use App\Service\ResponseWithContentType;

class LuckyController extends AbstractController
{




    /**
     * @Route("api/todo", name="task_list")
     */
    public function tasksList(Request $request, UserRepository $userRepository, TaskRepository $taskRepository): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');




        $entityManager = $this->getDoctrine()->getManager();
        if($request->getMethod() == 'GET') {
            $user = $this->getUser();
            $tasks = $user->getTaskss();
            if(sizeof($tasks) == 0){
                $mresponse["message"] = "There is no tasks";
            } else{
                $items=array();
                foreach ($tasks as $task){
                    $task_item["id"] = $task->getId();
                    $task_item["text"] = $task->getTaskText();
                    array_push($items,$task_item);
                }
                $mresponse["items"] = $items;
            }

            //$mresponse["task_owner"]=$user->getEmail();
            return new Response(
                json_encode($mresponse)
                
            );


        }else if($request->getMethod() == 'POST'){
            $data = json_decode(
                $request->getContent(),
                true
            );
            $user = $this->getUser();


            $taskText = $data['taskText'];

            $task = new Task();
            $task->setTaskText($taskText);

            $user->addTaskss($task);
            $entityManager->persist($task);
            $entityManager->flush();

            $mresponse["message"] = "Task added.";
            $mresponse["task_text"] = $taskText;
            return new Response(
                json_encode($mresponse)
            );

        } else{
            $mresponse["message"] = "Wrong request type.";
            return new Response(
                json_encode($mresponse)
            );
        }
    }


    /**
     * @Route("api/todo/{task_id}", name="task_managment")
     */
    public function taskManagment(Request $request,TaskRepository $taskRepository, int $task_id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');


        $entityManager = $this->getDoctrine()->getManager();
        $checkTask = $taskRepository->find($task_id);
        $user = $this->getUser();
        if($checkTask->getOnwer() == $user){
            if($request->getMethod() == 'DELETE'){

                $entityManager->remove($checkTask);
                $entityManager->flush();

                $mresponse["message"] = "Task deleted.";
                return new Response(
                    json_encode($mresponse)
                );

            }else{
                $data = json_decode(
                    $request->getContent(),
                    true
                );
                $newText = $data['new_text'];
                $checkTask->setTaskText($newText);
                $entityManager->flush();
                $mresponse["message"] = "Task updated.";
                return new Response(
                    json_encode($mresponse)
                );
            }
        } else {
            $mresponse["message"] = "Access denied";
            return new Response(
                json_encode($mresponse)
            );
        }

    }
    /**
     * @Route("api/files/upload", name="files_upload")
     */
    public function uploadFiles(Request $request, FileRepository $fileRepository, FileNameGenerator $nameGenerator){


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
        $mresponse = new JsonResponse(["message" => "OK"], 200);
        return $mresponse;
    }

    /**
     * @Route("api/files/list", name="files_list")
     */
    public function viewFiles(Request $request,FileRepository $fileRepository){
        $listOfFiles = $fileRepository->findAll();
        $array = [];
        foreach ($listOfFiles as $file){
            $array[$file->getId()] = $file->getFileName();
        }
        $mresponse = new JsonResponse($array, 200);
        return $mresponse;
    }
    /**
     * @Route("api/files/{id}", name="files_edit")
     */
    public function editFiles(Request $request, FileRepository $fileRepository, int $id, ResponseWithContentType $contentType){
        if($request->getMethod() == "DELETE"){

            $file = $fileRepository->find($id);
            unlink($this->getParameter("uploads_directory")."/".$file->getFileName());
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($file);
            $entityManager->flush();
            $mresponse = new JsonResponse(["message"=>"file deleted"],200);
            return $mresponse;
        }else if($request->getMethod() == "GET"){

            $file = $fileRepository->find($id);
            return $contentType->makeResponse($file);

        }
        $mresponse = new JsonResponse(["message" => "wrong request type"], 200);
        return $mresponse;
    }




}