<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\User;
use App\Repository\ReservationRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/reservation', name: 'app_api_reservation_')]
class ReservationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $manager,
        private ReservationRepository $repository,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route(methods: 'GET')]
    /** @OA\Get(
     *     path="/api/reservation",
     *     summary="Récupérer les réservations de l'utilisateur connecté",
     *     @OA\Response(response=200, description="Liste des réservations")
     * )
     */
    public function index(#[CurrentUser] ?User $user): JsonResponse
    {
        $reservations = $this->repository->findBy(['user' => $user]);
        $responseData = $this->serializer->serialize($reservations, 'json', ['groups' => 'reservation:read']);
        return new JsonResponse($responseData, Response::HTTP_OK, [], true);
    }

    #[Route(methods: 'POST')]
    /** @OA\Post(
     *     path="/api/reservation",
     *     summary="Créer une réservation",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="date", type="string", example="2026-05-15"),
     *             @OA\Property(property="time", type="string", example="20:00"),
     *             @OA\Property(property="nbGuests", type="integer", example=4),
     *             @OA\Property(property="allergies", type="string", example="Cacahouètes")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Réservation créée")
     * )
     */
    public function new(Request $request, #[CurrentUser] ?User $user): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $reservation = new Reservation();
        $reservation->setDate(new \DateTime($data['date']));
        $reservation->setTime($data['time']);
        $reservation->setNbGuests($data['nbGuests']);
        $reservation->setAllergies($data['allergies'] ?? null);
        $reservation->setCreatedAt(new DateTimeImmutable());
        $reservation->setUser($user);

        $this->manager->persist($reservation);
        $this->manager->flush();

        $responseData = $this->serializer->serialize($reservation, 'json', ['groups' => 'reservation:read']);
        $location = $this->urlGenerator->generate(
            'app_api_reservation_show',
            ['id' => $reservation->getId()],
            UrlGeneratorInterface::ABSOLUTE_URL,
        );

        return new JsonResponse($responseData, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/{id}', name: 'show', methods: 'GET')]
    public function show(int $id): JsonResponse
    {
        $reservation = $this->repository->findOneBy(['id' => $id]);
        if ($reservation) {
            $responseData = $this->serializer->serialize($reservation, 'json', ['groups' => 'reservation:read']);
            return new JsonResponse($responseData, Response::HTTP_OK, [], true);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/{id}', name: 'delete', methods: 'DELETE')]
    public function delete(int $id): JsonResponse
    {
        $reservation = $this->repository->findOneBy(['id' => $id]);
        if ($reservation) {
            $this->manager->remove($reservation);
            $this->manager->flush();
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }
}

