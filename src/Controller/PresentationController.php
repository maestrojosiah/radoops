<?php

namespace App\Controller;

use App\Entity\Presentation;
use App\Form\PresentationType;
use App\Repository\PresentationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/presentation")
 */
class PresentationController extends AbstractController
{
    /**
     * Lists all presentation entities.
     *
     * @Route("/admin/", name="presentation_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $presentations = $em->getRepository('App:Presentation')->findAll();

        return $this->render('presentation/index.html.twig', array(
            'presentations' => $presentations,
        ));
    }

    /**
     * Creates a new presentation entity.
     *
     * @Route("/admin/new/{bk_id}", name="presentation_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, $bk_id)
    {
        $presentation = new Presentation();
        $form = $this->createForm('App\Form\PresentationType', $presentation);
        $form->handleRequest($request);
        $product = $this->em()->getRepository('App:Product')->find($bk_id);
        $presentation->setProduct($product);
        $presentation->setUser($this->getUser());
        $presentation->setDeleted(0);

        if ($form->isSubmitted() && $form->isValid()) {
            $photo_path = $form->get('photoPath')->getData();
            $category = $product->getCategory();
            $presentation->setDeleted(0);            
            $product_title = $product->getTitle();
            $trimmed_title = str_replace(" ", "_", $product_title);
            $originalName = $photo_path->getClientOriginalName();
            $filepath = $this->getParameter('prod_img_directory')."/$category/$trimmed_title/";
            $photo_path->move($filepath, $originalName);
            $simple_filepath = "/img/products/$category/$trimmed_title/";
            $presentation->setPhotoPath($simple_filepath . $originalName);
            $em = $this->getDoctrine()->getManager();
            $em->persist($presentation);
            $em->flush();

            return $this->redirectToRoute('presentation_new', array('bk_id' => $bk_id));
        }

        return $this->render('presentation/new.html.twig', array(
            'presentation' => $presentation,
            'product' => $product,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a presentation entity.
     *
     * @Route("/{id}", name="presentation_show")
     * @Method("GET")
     */
    public function showAction(Presentation $presentation)
    {
        $deleteForm = $this->createDeleteForm($presentation);

        return $this->render('presentation/show.html.twig', array(
            'presentation' => $presentation,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * @Route("/{id}/unlink", name="presentation_del_photo")
     */
    public function deleteOldFile(Request $request, Presentation $presentation): Response
    {
        $formerFileName = $presentation->getPhotoPath();
        $old_filepath = $this->getParameter('public_directory').$formerFileName;
        if(file_exists($old_filepath)){ unlink($old_filepath); }
        return $this->redirectToRoute('presentation_edit', array('id' => $presentation->getId(), 'del' => $old_filepath));

    }

    /**
     * Displays a form to edit an existing presentation entity.
     *
     * @Route("/admin/{id}/edit", name="presentation_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Presentation $presentation)
    {
        $deleteForm = $this->createDeleteForm($presentation);
        $editForm = $this->createForm('App\Form\PresentationType', $presentation);
        $editForm->handleRequest($request);
        $product = $presentation->getProduct();

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $photo_path = $editForm->get('photoPath')->getData();
            $category = $product->getCategory();
            $presentation->setDeleted(0);            
            $product_title = $product->getTitle();
            $trimmed_title = str_replace(" ", "_", $product_title);
            $originalName = $photo_path->getClientOriginalName();
            $filepath = $this->getParameter('prod_img_directory')."/$category/$trimmed_title/";
            $photo_path->move($filepath, $originalName);
            $simple_filepath = "/img/products/$category/$trimmed_title/";
            $presentation->setPhotoPath($simple_filepath . $originalName);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('presentation_edit', array('id' => $presentation->getId()));
        }

        return $this->render('presentation/edit.html.twig', array(
            'presentation' => $presentation,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a presentation entity.
     *
     * @Route("/admin/{id}", name="presentation_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Presentation $presentation)
    {
        $form = $this->createDeleteForm($presentation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($presentation);
            $em->flush();
        }

        return $this->redirectToRoute('product_index');
    }

    /**
     * Creates a form to delete a presentation entity.
     *
     * @param Presentation $presentation The presentation entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Presentation $presentation)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('presentation_delete', array('id' => $presentation->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    private function em(){
        $em = $this->getDoctrine()->getManager();
        return $em;
    }

    private function user(){
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        return $user;
    }

}
