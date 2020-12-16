<?php

namespace App\Controller;

use App\Entity\Orda;
use App\Form\OrdaType;
use App\Repository\OrdaRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/orda")
 */
class OrdaController extends AbstractController
{
    /**
     * @Route("/sales", name="orda_index", methods={"GET"})
     */
    public function index(OrdaRepository $ordaRepository): Response
    {
        return $this->render('orda/index.html.twig', [
            'ordas' => $ordaRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="orda_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $orda = new Orda();
        $form = $this->createForm(OrdaType::class, $orda);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($orda);
            $entityManager->flush();

            return $this->redirectToRoute('orda_index');
        }

        return $this->render('orda/new.html.twig', [
            'orda' => $orda,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="orda_show", methods={"GET"})
     */
    public function show(Orda $orda): Response
    {
        return $this->render('orda/show.html.twig', [
            'orda' => $orda,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="orda_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Orda $orda): Response
    {
        $form = $this->createForm(OrdaType::class, $orda);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('orda_index');
        }

        return $this->render('orda/edit.html.twig', [
            'orda' => $orda,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="orda_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Orda $orda): Response
    {
        if ($this->isCsrfTokenValid('delete'.$orda->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($orda);
            $entityManager->flush();
        }

        return $this->redirectToRoute('orda_index');
    }
}
