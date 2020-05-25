<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\NeoService;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // Fetch TopNavTree here and stream into index (fill sidebar)
		$neolib = new NeoService();
		$data = $neolib->get_domains($this->getNeo4jClient(),'TopNavTree');
        return $this->render('default/index.html.twig',array(
			'domains' => $data,
			'subtit' => 'Main Menu') );
    }
	
	/**
     * @return \GraphAware\Neo4j\Client\Client
     */
    public function getNeo4jClient()
    {
		return $this->get('my.neo4j');
    }
}
