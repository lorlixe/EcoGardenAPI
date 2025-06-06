<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class UserController extends AbstractController
{
    #[Route('/api/user', name: 'create_user', methods: ['POST'])]
    public function createUser(
        Request $request,
        SerializerInterface $serializer,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
    ): JsonResponse {

        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        $plainPassword = $user->getPassword();
        $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);
        $em->persist($user);
        $em->flush();
        $jsonUser = $serializer->serialize($user, 'json', ['groups' => ['advice_user']]);
        return new JsonResponse($jsonUser, Response::HTTP_CREATED, [], true);
    }
}
