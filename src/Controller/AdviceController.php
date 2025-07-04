<?php

namespace App\Controller;

use App\Entity\Advice;
use App\Repository\AdviceRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

final class AdviceController extends AbstractController
{
    #[Route('api/conseil', name: 'conseil', methods: ['GET'])]
    public function getAdviceList(AdviceRepository $adviceRepository, SerializerInterface $serializer, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $today = new \DateTime('today');
        $idCache = "getAdvices-" . $today->format('F');
        $adviceList = $cachePool->get($idCache, function (ItemInterface $item) use ($adviceRepository, $today) {
            echo ("Item mis en cache");
            $item->tag("adviceCahe");
            return $adviceRepository->findByMonth($today);
        });

        $jsonAdviceList = $serializer->serialize($adviceList, 'json',  ["groups" => "advice_user"]);
        return new JsonResponse($jsonAdviceList, Response::HTTP_OK, [], true);
    }
    #[Route('/api/conseil/{mois}', name: 'conseilParMois', methods: ['GET'])]
    public function getAdviceListByMonth(int $mois, SerializerInterface $serializer, AdviceRepository $adviceRepository, TagAwareCacheInterface $cachePool): JsonResponse
    {
        $year = (new \DateTime())->format('Y');

        $date = new \DateTime();
        $date->setDate($year, $mois, 1)->setTime(0, 0, 0);
        $idCache = "getAdvices-" . $date->format('F');
        $adviceList = $cachePool->get($idCache, function (ItemInterface $item) use ($adviceRepository, $date) {
            echo ("Item mis en cache");
            $item->tag("adviceCahe");
            return $adviceRepository->findByMonth($date);
        });

        $jsonAdviceList = $serializer->serialize($adviceList, 'json',  ["groups" => "advice_user"]);
        return new JsonResponse($jsonAdviceList, Response::HTTP_OK, [], true);
    }
    #[Route('/api/conseil', name: 'creatAdvice', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour cette action')]
    public function createAdvice(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, AdviceRepository $adviceRepository, ValidatorInterface $validator, Security $security): JsonResponse
    {

        $user = $security->getUser(); // l'utilisateur connecté
        $content = $request->toArray();

        $mois = $content['mois'] ?? null;
        $arrayMois = explode(",", $mois);

        foreach ($arrayMois as $selectedMois) {
            $advice = $serializer->deserialize($request->getContent(), Advice::class, 'json');
            $advice->setUsers($user);
            // On vérifie les erreurs
            $errors = $validator->validate($advice);

            if ($selectedMois && ctype_digit($selectedMois) && (int)$selectedMois >= 1 && (int)$selectedMois <= 12) {
                $currentYear = (int)(new \DateTime())->format('Y');
                $date = (new \DateTime())->setDate($currentYear, (int)$selectedMois, 1)->setTime(0, 0, 0);
                $advice->setDate($date);
            } else {
                return new JsonResponse(['error' => 'Mois invalide'], 400);
            }
            if ($errors->count() > 0) {
                return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
            }
            $em->persist($advice);
        }




        $em->flush();

        $jsonBook = $serializer->serialize($advice, 'json', ['groups' => 'getBooks']);

        $location = $urlGenerator->generate('creatAdvice', ['id' => $advice->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonBook, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/conseil/{id}', name: "modifyAdvice", methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour cette action')]
    public function updateAdvice(Request $request, SerializerInterface $serializer, Advice $currentAdvice, EntityManagerInterface $em): JsonResponse
    {
        // Récupération du contenu JSON
        $content = $request->toArray();
        $mois = $content['mois'] ?? null;
        if ($mois != null) {
            // Vérifie si le mois est valide
            if ($mois && ctype_digit($mois) && (int)$mois >= 1 && (int)$mois <= 12) {
                $currentYear = (int)(new \DateTime())->format('Y');
                $date = (new \DateTime())->setDate($currentYear, (int)$mois, 1)->setTime(0, 0, 0);
                $currentAdvice->setDate($date);
            } else {
                return new JsonResponse(['error' => 'Mois invalide'], 400);
            }
        }


        // Met à jour les autres champs depuis le JSON (hors "mois")
        $serializer->deserialize(
            $request->getContent(),
            Advice::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $currentAdvice]
        );

        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }

    #[Route('/api/conseil/{id}', name: 'deleteAdvice', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour cette action')]
    public function deleteAdvice(Advice $advice, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($advice);
        $em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
