<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Firebase\JWT\JWT;

/**
 * @Route("/api")
 */


class ApiLoginController extends AbstractController
{
    /**
     * @Route("/login", name="api_login", methods={"POST"})
     */
    public function login(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $encoder): Response
    {
        $data = json_decode(
            $request->getContent(),
            true
        );



        $user = $userRepository->findOneBy([
            'email'=>$data['email'],
        ]);
        if (!$user || !$encoder->isPasswordValid($user, $data['password'])) {
            return $this->json([
                'message' => 'email or password is wrong.',
            ]);
        }
        $payload = [
            "user" => $user->getEmail(),
            "exp"  => (new \DateTime())->modify("+5 minutes")->getTimestamp(),
        ];


        $jwt = JWT::encode($payload, $this->getParameter('jwt_secret'), 'HS256');
        return $this->json([
            'message' => 'success!',
            'token' => sprintf('Bearer %s', $jwt),
        ]);
    }

    /**
     * @Route("/register", name="register", methods={"POST"})
     */
    public function register(Request $request, UserRepository $userRepository, UserPasswordHasherInterface $encoder): Response
    {
        $data = json_decode(
            $request->getContent(),
            true
        );


        $entityManager = $this->getDoctrine()->getManager();

        $password = $data['password'];
        $email = $data['email'];
        $user = new User();
        $user->setPassword($encoder->hashPassword($user, $password));
        $user->setEmail($email);

        //$checkUser = $userRepository->findOneBy(['email'=>$email]);

        if ($userRepository->findOneBy(['email'=>$email])) {
            return $this->json([
               'error' => 'This name is already used'
            ]);
        } else {
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->json([
                "message"=>"success"
            ]);
        }
    }
}
