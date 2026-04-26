<?php

namespace App\Controller;

use App\Entity\Menu;
use App\Entity\Restaurant;
use App\Repository\MenuRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/menu', name: 'app_api_menu_')]
class MenuController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private MenuRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route(methods: 'GET')]
    /** @OA\Get(
     *     path="/api/menu",
     *     summary="Récupérer tous les menus",
     *     @OA\Response(response=200, description="Liste des menus retournée avec succès")
     * )
     */
    public function index(): JsonResponse
    {
        $menus = $this->repository->findAll();
        $responseData = $this->serializer->serialize($menus, 'json', ['groups' => 'menu:read']);
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    /** @OA\Get(
     *     path="/api/menu/{id}",
     *     summary="Afficher un menu par ID",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Menu trouvé"),
     *     @OA\Response(response=404, description="Menu non trouvé")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $menu = $this->repository->findOneBy(['id' => $id]);
        if ($menu) {
            $responseData = $this->serializer->serialize($menu, 'json', ['groups' => 'menu:read']);
            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route(methods: 'POST')]
    /** @OA\Post(
     *     path="/api/menu",
     *     summary="Créer un menu",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", example="Menu du jour"),
     *             @OA\Property(property="description", type="string", example="Description du menu"),
     *             @OA\Property(property="price", type="number", example=29.90),
     *             @OA\Property(property="restaurant", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Menu créé avec succès")
     * )
     */
    public function new(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $menu = new Menu();
        $menu->setTitle($data['title']);
        $menu->setDescription($data['description'] ?? null);
        $menu->setPrice($data['price']);
        $menu->setCreatedAt(new DateTimeImmutable());

        $restaurant = $this->manager->getRepository(Restaurant::class)->find($data['restaurant']);
        if (!$restaurant) {
            return new JsonResponse(['error' => 'Restaurant non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $menu->setRestaurant($restaurant);
        $this->manager->persist($menu);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($menu, 'json', ['groups' => 'menu:read']);
        $location = $this->urlGenerator->generate(
            'app_api_menu_show',
            ['id' => $menu->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    /** @OA\Delete(
     *     path="/api/menu/{id}",
     *     summary="Supprimer un menu par ID",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Menu supprimé"),
     *     @OA\Response(response=404, description="Menu non trouvé")
     * )
     */
    public function delete(int $id): JsonResponse
    {
        $menu = $this->repository->findOneBy(['id' => $id]);
        if ($menu) {
            $this->manager->remove($menu);
            $this->manager->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}

