<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
                foreach ($tasks as $task){
                    $mresponse[$task->getId()]=$task->getTaskText();
                }
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

}