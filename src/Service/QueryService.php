<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class QueryService 
{
	private function makeNodeDef($arg)
	{
		return '('.$arg.')';
		
	}
	
	private function makeRelationDef($arg,$dir)
	{
		$front='';
		$back='';
		if ($dir=='l') {$front='<';} else 
			{
				if ($dir=='r') {$back='>';}
			}
		return $front.'-['.$arg.']-'.$back;
	}	

	private function createQueryWhereClause($whearr)
	{
		$alfabet = 'abcdefghijklmnopqrstuvwxyz';
		$theReturn = 'WHERE ';
		$asName='';
		$pre ='';		
		$wrappedattribute='';
		$length = count($whearr); // number of return definitions			
			for ($i = 0; $i < $length; $i++) {		
				//create return string
				$item = $whearr[$i];
				$wrappedattribute = $alfabet[$item[0]].'.'.$item[1].' '.$item[2].' \''.$item[3].'\'';
				$theReturn = $theReturn.$pre.$wrappedattribute.$asName;						
				$pre=' AND ';
			}		
		return $theReturn;
	}
	
	private function createQueryReturnClause($resarr)
	{
		$alfabet = 'abcdefghijklmnopqrstuvwxyz';
		$theReturn = 'RETURN ';
		$asName='';
		$pre ='';		
		$wrappedattribute='';
		$length = count($resarr); // number of return definitions			
			for ($i = 0; $i < $length; $i++) {		
				//create return string
				$item = $resarr[$i];
				$wrappedattribute = $alfabet[$item[0]].'.'.$item[1]; 			 
				if ($item[2]!='') {$asName = ' as '.$item[2];} else {$asName='';} // define "as" name
				if ($item[3]!='') {
					switch ($item[3])
					{
						case "intsum" : 
							$wrappedattribute='sum(toInt('.$wrappedattribute.'))';
						break;
						case "max" : 
							$wrappedattribute='max('.$wrappedattribute.')';
						break;
						case "intmax" : 
							$wrappedattribute='max(toInt('.$wrappedattribute.'))';
						break;
						case "distinct" :
							$wrappedattribute='DISTINCT '.$wrappedattribute.'';
						break;
					}
				}
				$theReturn = $theReturn.$pre.$wrappedattribute.$asName;						
				$pre=', ';
			}		
		return $theReturn;
	}
	
	private function createQueryOrderClause($ordarr)
	{
		$theOrder='';
		$length = count($ordarr); // number of return definitions	
		$pre ='';
		if ($length!=0)
		{
			$theOrder = 'ORDER BY ';
			for ($i = 0; $i < $length; $i++) {	
				$item = $ordarr[$i];
				if ($item[1]) {$asName=' DESC';} else {$asName='';}
				$theOrder = $theOrder.$pre.$item[0].$asName;
				$pre=', ';
			}			
		}	
		return $theOrder;
	}
	
	public function createInQuery($inQueryArr)
	{
		$matcharr = $inQueryArr['path'];
		$whearr = $inQueryArr['where'];
		$resarr = $inQueryArr['result'];
		$ordarr = $inQueryArr['order'];
		
		$alfabet = 'abcdefghijklmnopqrstuvwxyz';
		$theQuery = 'MATCH ';

		$label = '';
		$pre ='';
		$length = count($matcharr); // number of node definitions
			for ($i = 0; $i < $length; $i++) {
				// Create Match string (array contains Node-relation-Node-...)
				if ($i % 2 == 0){
					// Node
					if ($matcharr[$i][0]=='') {$label = '';} else {$label = ':'.$matcharr[$i][0];} 
					$theQuery = $theQuery.$this->makeNodeDef($alfabet[$i].$label);
				} else {
					// Relation
					if ($matcharr[$i][0]!='')
					{
						$label = ':'.$matcharr[$i][0];
						$theQuery = $theQuery.$this->makeRelationDef($alfabet[$i].$label,$matcharr[$i][1]);
					}
				}
			}
		$theWhere = $this->createQueryWhereClause($whearr);
		$theReturn = $this->createQueryReturnClause($resarr);	
		$theOrder = $this->createQueryOrderClause($ordarr);
		return $theQuery.' '.$theWhere.' '.$theReturn.' '.$theOrder;
	}
	
	public function getInQuery($neocl, $instanceID)
	{
		$query = 'MATCH (n:inQuery) WHERE n.domain=\'FunctionalType\' AND n.in_id={instanceID} RETURN n.description';
		$result = $neocl->run($query, ['instanceID' => $instanceID]);
		$record = $result->records()[0];
		return $this->createInQuery((array)json_decode($record->get('n.description')));
	}
	
	public function executeInQuery($neocl, $instanceID, $lookupID)
	{
		$query=$this->getInQuery($neocl,$instanceID);
		$query=str_replace('{SourceNodeID}',$lookupID,$query);
		return $neocl->run($query);
	}	
}
