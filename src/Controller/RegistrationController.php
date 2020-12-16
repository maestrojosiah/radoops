<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/security/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->setResidence('somewhere');
            $user->setCategory('user');
            $user->setAdmin(0);
            $user->setActive(1);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

        /**
     * @Route("/register/helper", name="product_registration_helper")
     */
    public function helperAction(Request $request)
    {

        $array = $this->getArray();
        $em = $this->getDoctrine()->getManager();

        foreach ($array as $key => $presentation_data) {
            $presentation = new Presentation();
            $presentation_user = $em->getRepository('App:User')->find($presentation_data['user_id']);
            $presentation_product = $em->getRepository('App:Product')->find($presentation_data['product_id']);
            $presentation->setProduct($presentation_product);
            $presentation->setPhotoPath($presentation_data['photo_path']);
            $presentation->setDescription($presentation_data['description']);
            $presentation->setDeleted($presentation_data['deleted']);
            $presentation->setUser($presentation_user);

            $em = $this->getDoctrine()->getManager();
            // $em->persist($presentation);
            // $em->flush();
            
        }


        return $this->render(
            'registration/renew.html.twig',
            ['array' => $array]
        );
    }

    /**
     * @Route("/admin/register/list", name="registration_list")
     */
    public function listAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('App:User')->findAll();
        return $this->render(
            'registration/list.html.twig',
            ['users' => $users]
        );
    }

    /**
     * @Route("/admin/update/{id}/{column}", name="update_user")
     */
    public function updateFigureAction(Request $request, $column, $id)
    {

        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('App:User')->find($id);
        $string_get_command = "get".$column;
        $string_set_command = "set".$column;
        if($user->$string_get_command() == 1){
            $change_to = 0;
        } else {
            $change_to = 1;
        }
        $user->$string_set_command($change_to);
        $this->save($user);
        return $this->redirectToRoute('registration_list');
    }

    private function getArray() {
        $array = Array();
        return $array;

    }

    private function em(){
        $em = $this->getDoctrine()->getManager();
        return $em;
    }


    private function save($entity){
        $this->em()->persist($entity);
        $this->em()->flush();
        return true;
    }

}
