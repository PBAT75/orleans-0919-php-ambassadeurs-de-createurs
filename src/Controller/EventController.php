<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\User;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use App\Service\CoordinateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/event")
 */
class EventController extends AbstractController
{
    /**
     * @Route("/", name="event_index", methods={"GET"})
     */
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('event/index.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="event_new", methods={"GET","POST"})
     */
    public function new(
        Request $request,
        CoordinateService $coordinateService,
        UserRepository $userRepository
    ): Response {
        $event = new Event();
        $event->setUser($this->getUser());
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $city = $request->request->get('event')['place'];
            $coordinates = $coordinateService->getCoordinates($city);
            if (!is_null($coordinates)) {
                $event->setLatitude($coordinates[0]);
                $event->setLongitude($coordinates[1]);
            }
            $entityManager->persist($event);
            $entityManager->flush();
            $this->addFlash('success', 'Votre événement a été créé');
            return $this->redirectToRoute('user_show', ['id' => $this->getUser()]);
        }

        return $this->render('event/new.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="event_show", methods={"GET"})
     */
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="event_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Event $event, CoordinateService $coordinateService): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $city = $request->request->get('event')['place'];
            $coordinates = $coordinateService->getCoordinates($city);
            if (!is_null($coordinates)) {
                $event->setLatitude($coordinates[0]);
                $event->setLongitude($coordinates[1]);
            }
            $entityManager->persist($event);
            $entityManager->flush();
            $this->addFlash('success', 'Votre événement a été mofifié');
            return $this->redirectToRoute('event_index');
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="event_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Event $event): Response
    {
        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($event);
            $entityManager->flush();
            $this->addFlash('danger', 'Votre événement a été supprimé');
        }

        return $this->redirectToRoute('event_index');
    }
}
