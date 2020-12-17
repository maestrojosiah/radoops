<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use App\Repository\CategoryRepository;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/list", name="product_index", methods={"GET"})
     */
    public function index(ProductRepository $productRepository): Response
    {
        $products = $productRepository->findBy(
            array('deleted' => 0),
            array('id' => 'ASC')
        );
        return $this->render('product/index.html.twig', [
            'products' => $products,
        ]);
    }

    /**
     * Lists all deleted product entities.
     *
     * @Route("/admin/deleted", name="product_deleted")
     * @Method("GET")
     */
    public function deletedAction(ProductRepository $productRepository)
    {
        $products = $productRepository->findBy(
            array('deleted' => 1),
            array('id' => 'ASC')
        );
        return $this->render('product/restore.html.twig', [
            'products' => $products,
        ]);
    }

    /**
     * Lists all product entities under a category.
     *
     * @Route("/category/{category}", name="product_category")
     * @Method("GET")
     */
    public function categoryAction(ProductRepository $productRepository, CategoryRepository $categoryRepository, $category)
    {
        $categoryEntity = $categoryRepository->findOneByName($category);
        $category_id = $categoryEntity->getId();
        $products = $productRepository->findByCategory($category_id);
        return $this->render('product/category.html.twig', [
            'products' => $products,
            'category' => $category,
        ]);
    }

    /**
     * Lists all product entities by availability.
     *
     * @Route("/availability/{availability}", name="product_availability")
     * @Method("GET")
     */
    public function availabilityAction($availability)
    {
        $products = $productRepository->findBy(
            array('availability' => $availability),
            array('id' => 'ASC')
        );
        return $this->render('product/index.html.twig', [
            'products' => $products,
            'availability' => $availability,
        ]);
    }

    /**
     * @Route("/new", name="product_new", methods={"GET","POST"})
     */
    public function new(Request $request, CategoryRepository $categoryRepository): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);
        $user = $this->getUser();
        $categories = $categoryRepository->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
            $category = $form->get('category')->getData();
            $now = date("d-m-Y h:i:s");
            $product->setUploaded(new \DateTime($now));            
            $product->setDeleted(0);            
            $product_title = $form->get('title')->getData();
            $trimmed_title = str_replace(" ", "_", $product_title);
            $originalName = $image->getClientOriginalName();;
            $filepath = $this->getParameter('prod_img_directory')."/$category/$trimmed_title/";
            $image->move($filepath, $originalName);
            $simple_filepath = "/img/products/$category/$trimmed_title/";
            $product->setImage($simple_filepath . $originalName);
            $product->setUser($user);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'categories' => $categories,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/{title}", name="product_show", methods={"GET"})
     */
    public function show(Product $product): Response
    {
        $deleteForm = $this->createDeleteForm($product);

        $related_products = $this->getRandomDoctrineItem('App\Entity\Product', $product->getCategory(), 3);

        return $this->render('product/show.html.twig', array(
            'product' => $product,
            'delete_form' => $deleteForm->createView(),
            'related_products' => $related_products,
        ));
        // return $this->render('product/show.html.twig', [
        //     'product' => $product,
        // ]);
    }

    /**
     * @Route("/{id}/edit", name="product_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Product $product): Response
    {
        $editForm = $this->createForm(ProductType::class, $product);
        $editForm->handleRequest($request);
        $formerFileName = $product->getImage();
        $formerTitle = $product->getTitle();

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $image = $editForm->get('image')->getData();
            $category = $editForm->get('category')->getData();
            $now = date("d-m-Y h:i:s");
            $product->setUploaded(new \DateTime($now));            
            $product_title = $editForm->get('title')->getData();
            $old_product_title = $_POST['old_title'];
            $trimmed_title = $old_product_title;
            // var_dump($old_product_title);
             // if (is_object($image)){
                $originalName = $image->getClientOriginalName();;
                $filepath = $this->getParameter('prod_img_directory')."/$category/$trimmed_title/";
                $simple_filepath = "/img/products/$category/$trimmed_title/";
                $image->move($filepath, $originalName);
                $product->setImage($simple_filepath . $originalName);
            // } else {
            //     $config->setImage($formerFileName);
            // }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('product_index');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, Product $product): Response
    {
        $form = $this->createDeleteForm($product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setDeleted(1);
            $em = $this->getDoctrine()->getManager();
            $em->persist($product);
            $em->flush();
        }

        return $this->redirectToRoute('product_index');        
        
        // if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
        //     $entityManager = $this->getDoctrine()->getManager();
        //     $entityManager->remove($product);
        //     $entityManager->flush();
        // }

        // return $this->redirectToRoute('product_index');
    }

    /**
     * Restores a deleted product entity.
     *
     * @Route("/admin/restore/{id}", name="product_restore")
     */
    public function restoreAction(Request $request, Product $product)
    {

        $product->setDeleted(0);
        $em = $this->getDoctrine()->getManager();
        $em->persist($product);
        $em->flush();

        return $this->redirectToRoute('product_deleted');
    }

        /**
     * Creates a form to delete a product entity.
     *
     * @param Product $product The product entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Product $product)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('product_delete', array('id' => $product->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

        /**
     * Retrieve one random item of given class from ORM repository.
     * 
     * @param EntityManager $em    The Entity Manager instance to use
     * @param string        $class The class name to retrieve items from
     * @return object
     */ 
    function getRandomDoctrineItem($class, $like, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        static $counters = [];
        if (!isset($counters[$class])) {
            $counters[$class] = (int) $em->createQuery(
                'SELECT COUNT(c) FROM '. $class .' c' 
            )->getSingleScalarResult();
        }
        // return $counters;
        return $em
            ->createQuery('SELECT c FROM ' . $class .' c WHERE c.category = :like ORDER BY c.id ASC')
            ->setMaxResults($limit)
            ->setParameter('like', $like)
            ->setFirstResult(mt_rand(0, 5))
            ->getResult()
        ;
    }

    private function em(){
        $em = $this->getDoctrine()->getManager();
        return $em;
    }

    private function find($entity, $id){
        $entity = $this->em()->getRepository("App:$entity")->find($id);
        return $entity;
    }

    private function findby($entity, $by, $actual){
        $query_string = "findBy$by";
        $entity = $this->em()->getRepository("App:$entity")->$query_string($actual);
        return $entity;
    }

    private function findandlimit($entity, $by, $actual, $limit, $order){
        $entity = $this->em()->getRepository("App:$entity")
            ->findBy(
                array($by => $actual),
                array('id' => $order),
                $limit
            );
        return $entity;
    }
    
    private function findbyandlimit($entity, $by, $actual, $by2, $actual2, $limit, $offset){
        $entity = $this->em()->getRepository("App:$entity")
            ->findBy(
                array($by => $actual, $by2 => $actual2),
                array('id' => 'ASC'),
                $limit,
                $offset
            );
        return $entity;
    }

    private function save($entity){
        $this->em()->persist($entity);
        $this->em()->flush();
        return true;
    }

    private function delete($entity){
        $this->em()->remove($entity);
        $this->em()->flush();
        return true;
    }

}
