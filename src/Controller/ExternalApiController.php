<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\AdviceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ExternalApiController extends AbstractController
{
    /**
     * Cette méthode fait appel à la route https://api.github.com/repos/symfony/symfony-docs
     * récupère les données et les transmets telles quelles.
     *
     * Pour plus d'information sur le client http:
     * https://symfony.com/doc/current/http_client.html
     *
     * @param HttpClientInterface $httpClient
     * @return JsonResponse
     */
    #[Route('/api/meteo', name: 'current_city_weather', methods: 'GET')]
    public function getCurrentCityWeather(HttpClientInterface $httpClient, Security $security, TagAwareCacheInterface $cachePool)
    {
        $currentUser = $security->getUser();


        $city = $currentUser->getCity();
        $idCache = "getCityWeather-" . $city;

        // Appel à l'API météo

        $weatherResponse = $cachePool->get($idCache, function (ItemInterface $item) use ($httpClient, $city) {
            echo ("Item mis en cache");
            $item->tag("weatherCache");
            $response = $httpClient->request(
                'GET',
                'https://api.openweathermap.org/data/2.5/weather?q=' . urlencode($city) . '&appid=' . $_ENV["API_METEO"] . '&units=metric'
            );
            return $response->toArray();
        });

        return new JsonResponse($weatherResponse);
    }

    #[Route('/api/meteo/{city}', name: 'city_weather', methods: 'GET')]
    public function getCityWeather(string $city, HttpClientInterface $httpClient, TagAwareCacheInterface $cachePool)
    {

        // Appel à l'API météo
        // $weatherResponse = $httpClient->request(
        //     'GET',
        //     'https://api.openweathermap.org/data/2.5/weather?q=' . urlencode($city) . '&appid=' . $_ENV["API_METEO"] . '&units=metric'
        // );

        // return new JsonResponse($weatherResponse->getContent(), $weatherResponse->getStatusCode(), [], true);
        $idCache = "getCityWeather-" . $city;
        $weatherResponse = $cachePool->get($idCache, function (ItemInterface $item) use ($httpClient, $city) {
            echo ("Item mis en cache");
            $item->tag("weatherCache");
            $response = $httpClient->request(
                'GET',
                'https://api.openweathermap.org/data/2.5/weather?q=' . urlencode($city) . '&appid=' . $_ENV["API_METEO"] . '&units=metric'
            );
            return $response->toArray();
        });

        return new JsonResponse($weatherResponse);
    }
}
