<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Entity\Restaurant;
use App\Repository\PictureRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/picture', name: 'app_api_picture_')]
class PictureController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private PictureRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route(methods: 'GET')]
    /** @OA\Get(
     *     path="/api/picture",
     *     summary="Récupérer toutes les images",
     *     @OA\Response(response=200, description="Liste des images retournée avec succès")
     * )
     */
    public function index(): JsonResponse
    {
        $pictures = $this->repository->findAll();
        $responseData = $this->serializer->serialize($pictures, 'json', ['groups' => 'picture:read']);
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    /** @OA\Get(
     *     path="/api/picture/{id}",
     *     summary="Afficher une image par ID",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Image trouvée"),
     *     @OA\Response(response=404, description="Image non trouvée")
     * )
     */
    public function show(int $id): JsonResponse
    {
        $picture = $this->repository->findOneBy(['id' => $id]);
        if ($picture) {
            $responseData = $this->serializer->serialize($picture, 'json', ['groups' => 'picture:read']);
            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route(methods: 'POST')]
    /** @OA\Post(
     *     path="/api/picture",
     *     summary="Créer une image",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="title", type="string", example="Salle du restaurant"),
     *             @OA\Property(property="slug", type="string", example="salle-restaurant.jpg"),
     *             @OA\Property(property="restaurant", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Image créée avec succès")
     * )
     */
    public function new(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $picture = new Picture();
        $picture->setTitle($data['title']);
        $picture->setSlug($data['slug']);
        $picture->setCreatedAt(new DateTimeImmutable());

        $restaurant = $this->manager->getRepository(Restaurant::class)->find($data['restaurant']);

        if (!$restaurant) {
            return new JsonResponse(['error' => 'Restaurant non trouvé'], Response::HTTP_NOT_FOUND);
        }

        $picture->setRestaurant($restaurant);

        $this->manager->persist($picture);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($picture, 'json', ['groups' => 'picture:read']);
        $location = $this->urlGenerator->generate(
            'app_api_picture_show',
            ['id' => $picture->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    /** @OA\Delete(
     *     path="/api/picture/{id}",
     *     summary="Supprimer une image par ID",
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Image supprimée"),
     *     @OA\Response(response=404, description="Image non trouvée")
     * )
     */
    public function delete(int $id): JsonResponse
    {
        $picture = $this->repository->findOneBy(['id' => $id]);
        if ($picture) {
            $this->manager->remove($picture);
            $this->manager->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}
