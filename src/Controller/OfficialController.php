<?php

namespace App\Controller;

use App\Entity\Official;
use App\Form\OfficialType;
use App\Repository\OfficialRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/official")
 */
class OfficialController extends AbstractController
{
    /**
     * @Route("/", name="official_index", methods={"GET"})
     */
    public function index(OfficialRepository $officialRepository): Response
    {
        return $this->render('official/index.html.twig', [
            'officials' => $officialRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="official_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $official = new Official();
        $form = $this->createForm(OfficialType::class, $official);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($official);
            $entityManager->flush();

            return $this->redirectToRoute('official_index');
        }

        return $this->render('official/new.html.twig', [
            'official' => $official,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="official_show", methods={"GET"})
     */
    public function show(Official $official): Response
    {
        return $this->render('official/show.html.twig', [
            'official' => $official,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="official_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Official $official): Response
    {
        $form = $this->createForm(OfficialType::class, $official);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('official_index');
        }

        return $this->render('official/edit.html.twig', [
            'official' => $official,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="official_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Official $official): Response
    {
        if ($this->isCsrfTokenValid('delete'.$official->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($official);
            $entityManager->flush();
        }

        return $this->redirectToRoute('official_index');
    }
}
