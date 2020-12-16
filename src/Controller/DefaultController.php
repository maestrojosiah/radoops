<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends AbstractController
{
    /**
     * @Route("/{page}", name="homepage")
     */
    public function index(ProductRepository $productRepository, CategoryRepository $categoryRepository, $page = 1): Response
    {

        $limit = 12;
        $offset = $page * $limit - $limit;
        $data = [];

        $products = $productRepository
            ->findBy(
                array('deleted' => 0),
                array('id' => 'DESC'),
                $limit,
                $offset
            );

        if($products){
            $data['nextPage'] = $page + 1;
            $data['prevPage'] = $page - 1;
        } else {
            $data['nextPage'] = "blank";
            $data['prevPage'] = $page - 1;
        }
        $data['numberOfProducts'] = count($products);
        $data['categories'] = $categoryRepository->findOrderedByPriority();

        if(count($products) > 0){
            // $display_products = $this->getRandomDoctrineItem('App\Entity\Product', 8);    
            $display_products = $products;    
            shuffle($display_products);
        } else {
            $display_products = NULL;
        }

        return $this->render('default/index.html.twig', [
            'display_products' => $display_products,
            'data' => $data,
        ]);
    }
        /**
     * @Route("/radoops/about", name="about")
     */
    public function aboutAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/about.html.twig');
    }

    /**
     * @Route("/radoops/agent/find", name="find_agent")
     */
    public function findAction(Request $request)
    {
        $agents = $this->em()->getRepository('App:User')
            ->findBy(
                array('active' => true, 'category' => 'le'),
                array('residence' => 'ASC')
            );
        $compressed_list = [];
        foreach ($agents as $key => $agent) {
            $compressed_list[$agent->getResidence()] = $agent;
        }
        // replace this example code with whatever you need
        return $this->render('default/find_agent.html.twig', ['agents' => $compressed_list]);
    }

    /**
     * @Route("/radoops/agent/list", name="list_agent")
     */
    public function listAgentsAction(Request $request)
    {
        $residence = $request->request->get('residence');
    
        $agents = $this->em()->getRepository('App:User')
            ->searchMatchingResidents($residence);

        $agents_list = [];
        foreach ($agents as $key => $agent) {
            $agents_list[] = [
                $agent->getFName()." ".$agent->getLName(), 
                $agent->getPhone(), 
                $agent->getEmail(), 
                $agent->getUsername().".radoopskenya.net",
                $agent->getResidence() 
             ];
        }
        return new JsonResponse($agents_list);
    }

    /**
     * @Route("/radoops/products/list", name="search_products")
     */
    public function searchProductsAction(Request $request)
    {
        $result_text = $request->request->get('search_text');
    
        $products = $this->em()->getRepository('App:Product')
            ->searchMatchingProducts($result_text);

        $result_text = [];
        foreach ($products as $key => $product) {
            $result_text[] = [
                $product->getTitle(), 
                $product->getAuthor(), 
                $product->getCategory(), 
                substr($product->getDescription(), 0, 100)."...",
                $this->generateUrl("product_show", ['id' => $product->getId()])
             ];
        }
        return new JsonResponse($result_text);
    }

    /**
     * Retrieve one random item of given class from ORM repository.
     */ 
    function getRandomDoctrineItem($class, $limit)
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
            ->createQuery('SELECT c FROM ' . $class .' c ORDER BY c.id ASC')
            ->setMaxResults($limit)
            ->setFirstResult(mt_rand(0, $counters[$class] - 1))
            ->getResult()
        ;
    }
    
    private function em(){
        $em = $this->getDoctrine()->getManager();
        return $em;
    }

}
