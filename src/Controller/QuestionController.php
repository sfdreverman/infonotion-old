<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\NeoService;

class QuestionController extends Controller
{
	private $neolib = null;
	
    public function __construct()
    {
        $this->neolib = new NeoService();
    }	
    /**
     * @Route("/questionnaire/{instanceID}")
     */
    public function AnswerQuestions(Request $request, $instanceID)
    {
        // Redirect to the frame that answers the question
		return $this->redirect('/frameN/Questionnaire/Questions/ListOfQuestions/'.$instanceID);
    }
	
	
	/**
	 * The Json calls that load repsonses (StoreRespondent, GetNextQuestion)
	 */
	 
	 /**
	 * @Route("/j_getRespondentInfo/{RespondentName}/{LoQID}", defaults={"LoQID" = -1})
	 */
	 // JSONifies the HTML render for transport to frontend
    public function getRespondentInfo(Request $request, $RespondentName, $LoQID)
	{
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
		$query = 'MATCH (q:Question)<-[:contains]-(loq:ListOfQuestions) where loq.in_id={LoQID} WITH loq,q,count(q) as TotalQuestions MATCH (r:Respondent) where r.name={RespondentName} OPTIONAL MATCH (r)-[rw:respondedWith]->(a:Answer)-[:isAnswerTo]->(q) return r.in_id as RespondentID, r.name as RespondentName ,count(rw) as QuestionsAnswered, count(q) as TotalQuestions';
		$theData = $neocl->run($query, ['RespondentName' => $RespondentName,'LoQID' => $LoQID]);
		$tabledData = nodeToArr($theData->records());
		return new JsonResponse( $tabledData );
					//array("code" => 200, 
						//  "response" => nodeToArr($theData->records())));
	}
	 
	// Get the render of one view
	/**
	 * @Route("/j_getnextquestion/{RespondentID}/{LoQID}/{AnswerID}", defaults={"AnswerID" = ""})
	 */
	 // JSONifies the HTML render for transport to frontend	 
 
    public function getHTMLQuestionData(Request $request, $RespondentID, $LoQID, $AnswerID)
    {
		return new JsonResponse(
					array("code" => 200, 
						  "response" => $this->getNextQuestion($RespondentID, $LoQID, $AnswerID)));
	}	 
	 
	public function getNextQuestion($RespondentID, $LoQID, $AnswerID)
	{
			function nodeToArr($theData) {
				$theResult = [];
				$row = 0;
				$start = 0;
				foreach ($theData as $record) {
					for ($i = $start; $i < count($record->keys()); $i++) 
					{
						if (is_array($record->values()))
						{
							//print_r($record->values());
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
								} else {
									$na = 0;
									foreach ($record->values()[$i] as $answer) {
										$nodeArr = $answer->values();
										$theResult[$record->keys()[$i]][$na]=$nodeArr;
										//print_r($theResult);
										$na++;
									}
								}
						}
					}
					$start = 1;
					$row++;				
				}
				return $theResult;
			}
			
		$neocl = $this->getNeo4jClient();
		// if an AnswerID is given, store it.
		if (!$AnswerID=="")
		{
			$query = 'MATCH (r:Respondent),(a:Answer) where r.in_id={RespondentID} and a.in_id={AnswerID} MERGE (r)-[:respondedWith]->(a)';
			$neocl->run($query, ['RespondentID' => $RespondentID,'AnswerID' => $AnswerID]);
		}
		$ViewData = $this->neolib->getView($neocl, 'View5ee536863ee7e7.13505371', false);
		$query = $ViewData['view']['query'];
		$theData = $neocl->run($query, ['RespondentID' => $RespondentID,'LoQID' => $LoQID]);
		$tabledData = nodeToArr($theData->records());		
		
		$resultArray = array(
			'RespondentID' => $RespondentID,
			'LoQID' => $LoQID,
			'Question' => $tabledData,
			'theView' => $ViewData['view']);
		
        return $this->render('view/templates/'.$ViewData['view']['templateName'], $resultArray)->getContent();		
		
		return new JsonResponse( $tabledData );
	}
	 
	
	/**
     * @return \GraphAware\Neo4j\Client\Client
     */
    public function getNeo4jClient()
    {
		return $this->get('my.neo4j');
    }
}