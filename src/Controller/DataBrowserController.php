<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\NeoService;

class DataBrowserController extends Controller
{
	
    /**
     * @Route("/databrowser", name="data_browser")
     */
    public function index()
    {
		$neolib = new NeoService();			
		$data = $neolib->get_domains($this->getNeo4jClient(),'TopNavTree');
        return $this->render('data_browser/index.html.twig',array(
			'domains' => $data,
			'subtit' => 'Data browser') );
    }
	
	/**
     * @return \GraphAware\Neo4j\Client\Client
     */
    public function getNeo4jClient()
    {
		return $this->get('my.neo4j');
    }	
}
