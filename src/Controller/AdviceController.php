<?php

namespace App\Controller;

use App\Repository\AdviceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;


final class AdviceController extends AbstractController
{
    #[Route('api/advice', name: 'advice', methods: ['GET'])]
    public function getAdviceList(AdviceRepository $adviceRepository, SerializerInterface $serializer): JsonResponse
    {
        $adviceList = $adviceRepository->findAll();
        $jsonAdviceList = $serializer->serialize($adviceList, 'json',  ["groups" => "advice_user"]);
        return new JsonResponse($jsonAdviceList, Response::HTTP_OK, [], true);
    }
}
