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

class LuckyController extends AbstractController
{


    /**
     * @Route("/user", name="registrate")
     */
    public function registration(Request $request, ValidatorInterface $validator, UserRepository $userRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $name = $request->request->get('name');
        $password = $request->request->get('password');
        if($name==""||$password=="") return new Response('Введите корректные данные');
        $user = new User();
        $user->setName($name);
        $user->setPassword($password);

        $errors = $validator->validate($user);
        $checkUser = $userRepository
            ->findOneBy(['name' => $name]);
        if(($checkUser!==null)&&($checkUser->getPassword()===$password)){
            return new Response('С возвращением, '.$name);

        }else{
            if (count($errors) > 0) {
                /*
                 * Uses a __toString method on the $errors variable which is a
                 * ConstraintViolationList object. This gives us a nice string
                 * for debugging.
                 */
                $errorsString = (string) $errors;
                return new Response($errorsString);
            }
            else{
                $entityManager->persist($user);
                $entityManager->flush();
                $mresponse = array('name'=>$name, 'password'=>$password);
                return new Response(
                    json_encode($mresponse)
                );
            }
        }



    }


    /**
     * @Route("/todo", name="task_list")
     */
    public function tasksList(Request $request, UserRepository $userRepository, TaskRepository $taskRepository): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        if($request->getMethod() == 'GET') {
            $name = $request->query->get('name');
            $checkUser = $userRepository->findOneBy(['name' => $name]);
            $tasks = $checkUser->getTasks();

            foreach ($tasks as $task){
                $mresponse[$task->getId()]=$task->getTaskText();
            }


            return new Response(
                json_encode($mresponse)
            );
        }else{
            $name = $request->request->get('name');
            $checkUser = $userRepository->findOneBy(['name' => $name]);


            $taskText = $request->request->get('text');

            $task = new Task();
            $task->setTaskText($taskText);

            $checkUser->addTask($task);
            $entityManager->persist($task);
            $entityManager->flush();

            $mresponse = array('Owner'=>$name, 'Text'=>$taskText);
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
        $entityManager = $this->getDoctrine()->getManager();
        $checkTask = $taskRepository->find($task_id);
        if($request->getMethod() == 'DELETE'){

            $entityManager->remove($checkTask);
            $entityManager->flush();
            return new Response(
                'ваша задача удалена'
            );

        }else{
            $newText = $request->get('new_text');
            $checkTask->setTaskText($newText);
            $entityManager->flush();
            return new Response(
                'ваша задача обновлена'
            );
        }

        return new Response(
            'error'
        );
    }

}