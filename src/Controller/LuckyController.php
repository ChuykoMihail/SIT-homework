<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Entity\Task;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Repository\UserRepository;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\String\s;

class LuckyController extends AbstractController
{




    /**
     * @Route("/todo", name="task_list")
     */
    public function tasksList(Request $request, UserRepository $userRepository, TaskRepository $taskRepository): Response
    {

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $entityManager = $this->getDoctrine()->getManager();
        if($request->getMethod() == 'GET') {
            $tasks = $user->getTasks();
            if(sizeof($tasks) == 0){
                $mresponse["message"] = "There is no tasks";
            } else{
                foreach ($tasks as $task){
                    $mresponse[$task->getId()]=$task->getTaskText();
                }
            }

            $mresponse["task_owner"]=$user;
            return new Response(
                json_encode($mresponse)
            );
        }else if($request->getMethod() == 'POST'){

            $taskText = $request->request->get('text');

            $task = new Task();
            $task->setTaskText($taskText);

            $user->addTask($task);
            //$entityManager->persist($task);
            //$entityManager->flush();

            $mresponse["message"] = "Task added.";
            $mresponse["task_text"] = $taskText;
            $mresponse["task_owner"] = $user;
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
     * @Route("/todo/{task_id}", name="task_managment")
     */
    public function taskManagment(Request $request,TaskRepository $taskRepository, int $task_id): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        $entityManager = $this->getDoctrine()->getManager();

        $checkTask = $taskRepository->find($task_id);
        if($checkTask->getOwner() == $user){
            if($request->getMethod() == 'DELETE'){

                $entityManager->remove($checkTask);
                $entityManager->flush();

                $mresponse["message"] = "Task deleted.";
                return new Response(
                    json_encode($mresponse)
                );

            }else{
                $newText = $request->get('new_text');
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