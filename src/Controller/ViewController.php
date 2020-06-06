<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\DateTime;
use App\Service\NeoService;

class ViewController extends Controller
{	
	private $neolib = null;
	
    public function __construct()
    {
        $this->neolib = new NeoService();
    }
	/**
	 * @Route("/frameN/{frameName}/{domain}/{metaType}/{instanceID}", defaults={"instanceID" = "", "metaType" = "", "domain" = ""})
	 */
	 public function loadframeByName($frameName, $domain, $metaType, $instanceID)
	 {
		$frame = $this->neolib->getFrameByName($this->getNeo4jClient(), $frameName);
		return $this->getFrame($frame,$domain,$metaType,$instanceID);
	 }		 
	 
	/**
	 * @Route("/frame/{frameID}/{domain}/{metaType}/{instanceID}")
	 */
	 public function loadframe($frameID, $domain, $metaType, $instanceID)
	 {
		$frame = $this->neolib->getFrame($this->getNeo4jClient(), $frameID);
		return $this->getFrame($frame,$domain,$metaType,$instanceID);
	 }	 
	 
	 // gets the actual Frame (originated from ID or Name)
	 public function getFrame($frame,$domain,$metaType,$instanceID)
	 {
		$ViewID = $frame['view']['in_id'];
		$renderedView = $this->HTMLView($domain, $metaType, $ViewID, $instanceID, -1);
		$paramArray = array(
			'subtit' => 'Data viewer',
			'domain' => $domain,
			'metaType' => $metaType,
			'instanceID' => $instanceID,
			'ViewContent' => $renderedView,			
			'ViewID' => $ViewID,
			);
		return $this->render('view/frames/'.$frame['frame']['templateName'], $paramArray);	
	 } 	
	
	// Get the render of one view
	/**
	 * @Route("/j_htmlview/{domain}/{metaType}/{viewID}/{instanceID}/{page}", defaults={"page" = -1})
	 */
	 // JSONifies the HTML render for transport to frontend
    public function getHTMLViewData(Request $request, $domain, $metaType, $viewID, $instanceID, $page)
    {
		return new JsonResponse(
					array("code" => 200, 
						  "response" => $this->HTMLView($domain, $metaType, $viewID, $instanceID, $page)));
	}
	
	// returns the actual HTML rendered response (allowing injection of view with pre-rendered HTML)
	public function HTMLView($domain, $metaType, $viewID, $instanceID, $page)
	{
			function endsWith($haystack, $needle) {
				return substr_compare($haystack, $needle, -strlen($needle)) === 0;
			}
			
			function nodeToArr($theData) {
				$theResult = [];
				$row = 0;
				$start = 0;
				foreach ($theData as $record) {
					for ($i = $start; $i < count($record->keys()); $i++) 
					{
						if (is_array($record->values()))
						{
							if (is_object($record->values()[$i]))
							{
								$nodeArr = $record->values()[$i]->values();
								$nodeArr['in_metaType'] = $record->values()[$i]->labels()[0];
								
								if ($row==0 && $i==0){
									$theResult[$record->keys()[$i]]=$nodeArr;
								} else
									$theResult[$row][$record->keys()[$i]]=$nodeArr;
							} else
								if (!is_array($record->values()[$i]) and !is_null($record->values()[$i]))
								{
									$theResult[$row][$record->keys()[$i]]=$record->values()[$i];
								}
						}
					}
					$start = 1;
					$row++;				
				}
				return $theResult;
			}
		
		$neocl = $this->getNeo4jClient();
		$theData = $this->neolib->getView($neocl, $viewID, false);
		// $defaultTwigTemplate = 'view//'+$theView
		
		if ($page!=-1)
		{
			// get record count for paginated view
			$countq='MATCH (n:{metaType}) where n.domain={domain} return count(n)';
			$countq=str_replace('{domain}','\''.$domain.'\'',$countq);		
			$countq=str_replace('{metaType}',$metaType, $countq);		
			$metaTypeRecCount = nodeToArr($neocl->run($countq)->records())[0]['count(n)'];
		} else{
			$metaTypeRecCount = -1;
		}
		if (array_key_exists('RecordsPerPage', $theData['view']))
		{
			$maxRecords = $theData['view']['RecordsPerPage'];
		} else 
		{
			$maxRecords = 100;
		}
		
		$atRecord = max(($page * $maxRecords),0);
		
		$query=str_replace('{SourceNodeID}',$instanceID,$theData['view']['query']);
		$query=str_replace('{instanceID}',$instanceID,$query);
		$query=str_replace('{domain}',$domain,$query);		
		$query=str_replace('{metaType}',$metaType, $query);
		$query=str_replace('{maxRecords}',$maxRecords,$query);
		$query=str_replace('{atRecord}',$atRecord,$query);		

		$theQueryData = $neocl->run($query)->records();
		
		if ($theData['view']['ViewKind'] == 'Node' || $theData['view']['ViewKind'] == 'FullNode')
		{
			$theResult = nodeToArr($theQueryData);
		}
		// for table structures (keys [0] -> Round, values [0] -> 1, etc...)
		if ($theData['view']['ViewKind'] == 'Table')
		{
			$theResult = [];		
			$keys = [];
			$count = 0;
			foreach ($theQueryData as $record) {
				$res = [];
				for ($i = 0; $i < count($record->keys()); $i++) 
				{	$thiskey = $record->keys()[$i];
					$res[] = $record->values()[$i];
					if ($count == 0 && !in_array($thiskey,$keys)) $keys[] = $record->keys()[$i];
				}
				$theResult[$count] = $res;
				$count++;
			}
			$theResult['keys']=$keys;
		}
		
		if ($theData['view']['description'] != '')
		{
			$arr = explode('|',$theData['view']['description']);
			$res = [];
			foreach ($arr as $rec){
				$temp = explode(',',$rec);
				$name = array_shift($temp);
				$res[$name] = $temp;
			}
			$theData['view']['behavior']=$res;
		}

		$resultArray = array(
			'instanceID' => $instanceID,
			'domain' => $domain, // needed for "add" button, amongst others
			'metaType' => $metaType,
			'theResult' => $theResult,
			'recCount' => $metaTypeRecCount,
			'pageNum' => $page,
			'theView' => $theData['view']);
		
        return $this->render('view/templates/'.$theData['view']['templateName'], $resultArray)->getContent();
    }



	/**
     * @return \GraphAware\Neo4j\Client\Client
     */
    public function getNeo4jClient()
    {
		return $this->get('my.neo4j');
    }	
}
