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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
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


    #[Route('/api/user/{id}', name: "modifyUser", methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour cette action')]
    public function updateUser(Request $request, SerializerInterface $serializer, User $currentUser, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {

        // Désérialisation dans l'objet existant
        $serializer->deserialize(
            $request->getContent(),
            User::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]
        );

        // Hachage du mot de passe uniquement s'il est fourni
        if ($currentUser->getPassword() !== null) {
            $plainPassword = $currentUser->getPassword();
            $hashedPassword = $passwordHasher->hashPassword($currentUser, $plainPassword);
            $currentUser->setPassword($hashedPassword);
        }


        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/api/user/{id}', name: 'deleteuser', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour cette action')]
    public function deleteAdvice(User $user, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($user);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
