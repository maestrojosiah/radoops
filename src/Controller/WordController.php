<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WordController extends AbstractController
{

    /**
     * @Route("/catalog/word/{name}", name="download_word_catalog")
     */
    public function indexAction($name)
    {

    	$products_all = $this->em()->getRepository('AppBundle:Product')
    		->findGroupedProducts();

    	$products = [];
    	foreach($products_all as $product){
    		$products[$product->getTitle()] = $product;
    	}
        // ask the service for a Word2007
        $phpWordObject = $this->get('phpword')->createPHPWordObject();

        // Create a new Page
        $section = $phpWordObject->addSection();
		$header = array('size' => 16, 'bold' => true);

        // // Adding Text element to the Section having font styled by default...
        // $section->addText(
        //     '"Learn from yesterday, live for today, hope for tomorrow. '
        //         . 'The important thing is not to stop questioning." '
        //         . '(Albert Einstein)'
        // );

		$section->addTextBreak(1);
		$section->addText('Home Health Education Service', $header);

		$fancyTableStyleName = 'Fancy Table';
		$fancyTableStyle = array('borderSize' => 6, 'borderColor' => '006699', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
		$fancyTableFirstRowStyle = array('borderBottomSize' => 18, 'borderBottomColor' => '0000FF', 'bgColor' => '66BBFF');
		$fancyTableCellStyle = array('valign' => 'center');
		$fancyTableCellBtlrStyle = array('valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR);
		$fancyTableCellStyle = array('settings' => \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true));
		$fancyTableFontStyle = array('bold' => true);
		$phpWordObject->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
		$table = $section->addTable($fancyTableStyleName);
		$table->addRow(900);
		$table->addCell(2500, $fancyTableCellStyle)->addText('Image', $fancyTableFontStyle);
		$table->addCell(1500, $fancyTableCellStyle)->addText('Title', $fancyTableFontStyle);
		$table->addCell(4000, $fancyTableCellStyle)->addText('Description', $fancyTableFontStyle);
		$table->addCell(1000, $fancyTableCellStyle)->addText('Price', $fancyTableFontStyle);
		// Remote image
		foreach($products as $product){
			$product_link = $product->getImage();
			$table->addRow(900);
		    $table->addCell(2500)->addImage("$product_link", array('width' => 110, 'height' => 180, ));
		    $table->addCell(1500)->addText($product->getTitle());
		    $table->addCell(4000)->addText($product->getDescription());
		    $table->addCell(1000)->addText($product->getCost());
		}


        // create the writer
        $writer = $this->get('phpword')->createWriter($phpWordObject, 'Word2007');
        // create the response
        $response = $this->get('phpword')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            "$name.doc"
        );
        $response->headers->set('Content-Type', 'application/msword');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;        
    }

    private function em(){
        $em = $this->getDoctrine()->getManager();
        return $em;
    }


}
