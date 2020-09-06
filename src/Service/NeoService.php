<?php

namespace App\Service;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class NeoService 
{
	public function postArrayToInSet($arrayResult)
	{
		$res = '';
		$first = '';
		for ($i = 0; $i < count($arrayResult); $i++) 
		{
			if ($arrayResult[$i]!='dummy')
			{
				$res = $res.$first.'"'.$arrayResult[$i].'"';
				if ($first=='') { $first = ',';}
			}
		}		
		return $res;
	}
	
	// returns a list of all MetaTypes, with the corresponding Domain between ()
	public function get_allMetaTypes($neocl){
		$q = 'match (n:Domain) unwind n.name as domain MATCH (m) where domain in labels(m) return m.name+\' (\'+n.name+\')\' as name,m.in_id as id ORDER BY m.name,n.name';
		return $neocl->run($q);		
	}
	
	// Adds a new node with label $label. This basically creates a new datatype that can be instantiated with the user interface.
	// $data holds all information that was input via the addtype.html.twig form.
	public function add_metatype($neocl, $label, $data)
	{
		$q = 'MERGE (newtype:'.$label.' {in_id:\''.uniqid($data['typename'],true).'\', name: \''.$data['typename'].'\', isabstract:'.$data['isabstract'].'} ) ';
		
		// Add all attributes
		if (array_key_exists('attr_name', $data)) {
			$length = count($data['attr_name']);
			for ($i = 0; $i < $length; $i++) { // as of now, every MetaAttr is unique to the $label, so no re-use for attributes (merge the whole pattern)
				$q = $q.'MERGE (newtype)-[:HASATTR]->(:MetaAttr {name:\''.$data['attr_name'][$i].'\', attrtype:\''.$data['attr_type'][$i].'\', domain:\'FunctionalType\', in_id:\''.uniqid($data['attr_name'][$i][0],true).'\' }) ';
			}
		}
		// Add inheritance relation if needed
		if (array_key_exists('inheritsFrom',$data) && $data['inheritsFrom']!='None')
		{
			$q = $q.' WITH newtype MATCH (basetype {in_id:\''.$data['inheritsFrom'].'\'}) MERGE (newtype)-[:INHERITS]->(basetype)';
		}
		
		// Add all relations, if present
		if (array_key_exists('rel_name', $data)) {
			$lmr = 0; // unique query id for attributes of relation
			$nattrs = 0; // counter for number of attributes (removes the gaps in the 'relcomp' array)
			$attrs = ''; // container for all attributes (to be added last)
			$length = count($data['rel_name']); // number of relations
			for ($i = 0; $i < $length; $i++) {
				if (is_array($data['rel_name'][$i]))
				{ // relname[$i][0] contains the attrname, reltype[$i][0] contains the attrtype
					$attrs = $attrs.' MERGE (lmr'.$lmr.')-[:HASATTR]->(:MetaAttr {in_id:\''.uniqid($data['rel_name'][$i][0],true).'\', name:\''.$data['rel_name'][$i][0].'\', attrtype:\''.$data['rel_type'][$i][0].'\', domain:\'FunctionalType\' }) ';
					$nattrs++;
				}
				else
				{
					$lmr++;
					$lmarr[$lmr] = uniqid($data['rel_name'][$i],true);
					if ($data['rel_type'][$i]==='Self'){
						$q = $q.'WITH newtype MERGE (newtype)-[:HASREL]->(lmr'.$lmr.':MetaRel {in_id:\''.$lmarr[$lmr].'\', name:\''.$data['rel_name'][$i].'\', iscomp:\''.$data['rel_comp'][$i-$nattrs].'\', multi:'.$data['rel_multi'][$i-$nattrs].', domain:\'FunctionalType\' })-[:TOTYPE]->(newtype) ';
					}
					else{
						$q = $q.'WITH newtype MATCH (t) WHERE t.in_id=\''.$data['rel_type'][$i].'\' MERGE (newtype)-[:HASREL]->(lmr'.$lmr.':MetaRel {in_id:\''.$lmarr[$lmr].'\', name:\''.$data['rel_name'][$i].'\', iscomp:\''.$data['rel_comp'][$i-$nattrs].'\', multi:'.$data['rel_multi'][$i-$nattrs].', domain:\'FunctionalType\' })-[:TOTYPE]->(t) ';
					}
				}
			}
			// add all attributes last!
			$q = $q.$attrs;
		}
		if (array_key_exists('loattr_name', $data)) {
			$lmr = 0; // unique query id for attributes of relation
			$length = count($data['loattr_name']); // number of relations
			for ($i = 0; $i < $length; $i++) {
					$lmr++;
					$lmarr[$lmr] = uniqid($data['loattr_name'][$i],true);
					$q = $q.'WITH newtype MATCH (t) WHERE t.in_id=\''.$data['loattr_type'][$i].'\' MERGE (newtype)-[:HASLOATTR]->(lmr'.$lmr.':MetaLookupAttr {in_id:\''.$lmarr[$lmr].'\', name:\''.$data['loattr_name'][$i].'\', domain:\'FunctionalType\' })-[:FROMTYPE]->(t) ';
				}
		}
		return $neocl->run($q);
	}
	
	// Removes a MetaType from the meta model. This will also delete all relations, attributes and lookup attributes attached to the metatype.
	// Will return an ERROR if there are incoming relationships. (THIS IS DELIBERATE!)
	public function remove_MetaType($neocl, $domain, $metaType)
	{
		$query = 'MATCH (n:'.$domain.') WHERE n.name={metaType} OPTIONAL MATCH (n)-[r]->(m) WHERE not (n)-[r:INHERITS]->(m) DETACH DELETE m,n';
		$result = $neocl->run($query,["metaType" => $metaType]);
	}
	
	//
	// Routines needed to add a typed instance to the Neo4j persistence. (Add/Edit pages)
	//
	
	// METADATA: Retrieve all attributes MetaAttr of $metaType in domain $domain. Returns name, type. orders by name.
	public function get_typeattr($neocl,$metaType,$domain)
	{
		// no inheritance:
		//$q = 'MATCH (n:'.$domain.')-[:HASATTR]->(a:MetaAttr) WHERE n.name = \''.$metaType.'\' RETURN a.name AS name,a.attrtype AS type,a.defval as defval, a.description as desc ORDER BY a.name';
		// with inheritance:
		$retstatement = 'RETURN a.name AS name,a.attrtype AS type,a.defval as defval, a.description as desc ORDER BY a.name DESC';
		$q = 'MATCH (n:'.$domain.' {name:\''.$metaType.'\'})-[:HASATTR]->(a:MetaAttr) '.$retstatement.' UNION MATCH (n:'.$domain.' {name:\''.$metaType.'\'})-[:INHERITS*]->(nn)-[:HASATTR]->(a:MetaAttr) '.$retstatement;
		return $neocl->run($q);
	}	
	
	// METADATA: Retrieve all attributes MetaAttr of MetaRel $relid. Returns name and type. orders by name.
	public function get_relattr($neocl,$relid)
	{
		$q = 'MATCH (n:MetaRel)-[:HASATTR]->(a:MetaAttr) WHERE n.in_id = \''.$relid.'\' RETURN a.name AS name,a.attrtype AS type,a.defval as defval ORDER BY a.name';
		return $neocl->run($q);
	}
	
	// METADATA: Retrieve all MetaLookupAttr of $label $typeName (Person). Returns name, fromtype, relid. orders by name.
	public function get_metaLookupAttrs($neocl,$label, $instData)
	{
		$attrName = 'name';
		// no inheritance:
		//$q = 'MATCH (n:'.$label.')-[:HASLOATTR]->(a:MetaLookupAttr)-[:FROMTYPE]-(t) WHERE n.'.$attrName.'= \''.$instData.'\' RETURN a.name AS name, t.name as fromtype, a.in_id as relid, labels(t)[0] as domain, a.description as desc ORDER BY a.name';
		// with inheritance:
		$retstatement = 'RETURN a.name AS name, t.name as fromtype, a.in_id as relid, labels(t)[0] as domain, a.description as desc ORDER BY a.name';		
		$q = 'MATCH (n:'.$label.' {name:\''.$instData.'\'})-[:HASLOATTR]->(a:MetaLookupAttr)-[:FROMTYPE]-(t) '.$retstatement.' UNION MATCH (n:'.$label.' {name:\''.$instData.'\'})-[:INHERITS*]->()-[:HASLOATTR]->(a:MetaLookupAttr)-[:FROMTYPE]-(t) '.$retstatement;		
		return $neocl->run($q);
	}	
	
	// METADATA: Retrieve all MetaRel of $label $typeName (Person). Returns name, totype, relid, iscomp. orders by name.
	public function get_metaRels($neocl,$label, $instData)
	{
		$attrName = 'name';
		if ($label == 'Action') {$attrName = 'in_id';}
		// no inheritance:
		// $q = 'MATCH (n:'.$label.')-[:HASREL]->(a:MetaRel)-[:TOTYPE]-(t) WHERE n.'.$attrName.'= \''.$instData.'\' RETURN a.name AS name, t.name as totype, a.in_id as relid, a.iscomp as iscomp, a.multi as multi, labels(t)[0] as domain,a.description as desc ORDER BY a.name';
		// with inheritance:
		$istaxo = ' OPTIONAL MATCH (t)-[:INHERITS]->(tax:FunctionalType {name:\'TaxonomyItem\'}) ';
		$retstatement = 'RETURN a.name AS name, t.name as totype, a.in_id as relid, a.iscomp as iscomp, a.multi as multi, labels(t)[0] as domain, count(tax)>0 as isTaxo, a.description as desc ORDER BY a.name';
		$q = 'MATCH (n:'.$label.' {name:\''.$instData.'\'})-[:HASREL]->(a:MetaRel)-[:TOTYPE]-(t) '.$istaxo.$retstatement.' UNION MATCH (n:'.$label.' {name:\''.$instData.'\'})-[:INHERITS*]->()-[:HASREL]->(a:MetaRel)-[:TOTYPE]-(t) '.$istaxo.$retstatement;		
		return $neocl->run($q);
	}	
	
	// Retrieve all instances (TaxonomyItems) in Taxonomy $label. Must return same stuff as get_instancenames for form compatibility (for now)
	public function get_taxoinstancenames($neocl, $domain, $label)
	{
		$q = 'MATCH (a:'.$label.') WHERE NOT((a)-->()) WITH a RETURN a.name AS name, a.in_id as id, a.domain as domain UNION MATCH p = (a:'.$label.')-[*]->(b) WHERE a.domain={domain} AND NOT((b)-->()) WITH a, b, NODES(p) AS pts, LENGTH(p) AS n RETURN substring(REDUCE(s = \'\', i IN RANGE(0, n-1) | pts[i].name +  \', \' + s),0,255) AS name, a.in_id as id, a.domain as domain ORDER BY name, n';
		return $neocl->run($q, ["label" => $label, "domain" => $domain]);
	}
	
	
	// Retrieve all instances of a $label. (Domain or Person) Returns name and id. orders by name.
	public function get_instancenames($neocl, $domain, $label)
	{
		// no inheritance:
		// $q = 'MATCH (n:'.$label.') where n.domain={domain} RETURN n.name AS name, n.in_id as id, n.domain as domain ORDER BY name';
		// with inheritance:
		$q = 'MATCH (n:'.$domain.' {name:{label}}) OPTIONAL MATCH (n)<-[:INHERITS*]-(r) With n.name+COLLECT(r.name) as collect MATCH (n) where labels(n)[0] IN collect AND n.domain={domain} RETURN n.name AS name, n.in_id as id, n.domain as domain ORDER BY name';
		return $neocl->run($q, ["label" => $label, "domain" => $domain]);
	}
	
	// Compiles retrieved data into a Neo4j query to create/merge a typed instance.
	public function CreateOrMergeInstance($neocl, $domain, $metaType, $metaData, $resultData, $instanceID, $isMetaThing)
	{
		$q = '';
		// if in_id is returned, then it is an edited node!!!
		if ($instanceID=="") { $instanceID = $isMetaThing != [] ? uniqid($isMetaThing['makeMT'],true) : uniqid($metaType,true); }
		
		// Start of Query 
		// Create or find node
		
		if ($isMetaThing != [])
		{
			$toRel = ['MetaAttr' => 'HASATTR', 'MetaLookupAttr' => 'HASLOATTR', 'MetaRel' => 'HASREL'];
			// for meta things do:
			$q = $q.'MATCH (cTo:'.$domain.' {name:\''.$metaType.'\'}) WITH cTo ';
			$q = $q.'MERGE (cTo)-[:'.$toRel[$isMetaThing['makeMT']].']->(nti:'.$isMetaThing['makeMT'].' {in_id:\''.$instanceID.'\'}) ';
			// swap makeMT & for metaType
			$domain = $isMetaThing['makedomain'];
			$metaType = $isMetaThing['makeMT'];
		}
		else {
			// for instances do:
			$q = $q.'MERGE (nti:'.$metaType.' {in_id:\''.$instanceID.'\'}) ';
		}
		
		// Set standard attributes
		$q = $q.'SET nti.domain="'.$domain.'" ';		
		
		// Set custom attributes
		foreach($metaData['Attr'] as $field) {
			if (is_array($field)) {
				if ($field['type']=='number')
				{
					$q = $q.'SET nti.'.$field['name'].' = '.$resultData[$field['name']].' ';
				} else 
				if ($field['type']=='bool') {
					$tempres = $resultData[$field['name']]=='1' ? 'true' : 'false';
					$q = $q.'SET nti.'.$field['name'].' = '.$tempres.' ';
				}
				else
				if ($field['type']=='date' || $field['type']=='datetime-local')
				{
					$q = $q.'SET nti.'.$field['name'].' = "'.$resultData[$field['name']]->format('Y-m-d\TH:i:s').'" ';
				} else
				{
					$q = $q.'SET nti.'.$field['name'].' = "'.$resultData[$field['name']].'" ';
				}
			}
		}
		
		// Set Lookup Attributes - NOT IMPLEMENTED YET
		foreach($metaData['LoAttr'] as $field) {
			$q = $q.'SET nti.'.$field['name'].' = "'.$resultData[$field['name']].'" ';
		}		
		
		$result = '';
		// Commit

		$result = $neocl->run($q);
		
		// Add all relations (if they exist)
		self::CreateOrMergeRelationData($neocl,$metaType,$instanceID,$metaData['Rel'],$resultData);

		return $result;
	}
	
		// Add 1 or more relations to an instance
	public function CreateOrMergeRelationData($neocl, $metaType, $instanceID, $relationData, $resultData)
	{
		$result = '';
		foreach ($relationData as $item)
		{
			$destIds='';
			//the $relname is the relationType
			$cyrelAdd = '[:'.$item['name'].']';
			$cyrelRem = '[r:'.$item['name'].']';
			if ($item['multi']==1) {$destIds = self::postArrayToInSet($resultData[$item['name']]);} 
							  else {$destIds = '"'.$resultData[$item['name']].'"';}
			
			//Remove old relations...
			$query = 'MATCH (n:'.$metaType.') WHERE n.in_id = {instanceID} OPTIONAL MATCH (n)-'.$cyrelRem.'->(o) DELETE r';
			// ... and create the new ones.
			if ($destIds!="") {
				$query = $query.' WITH n MATCH (m) WHERE m.in_id IN ['.$destIds.'] MERGE (n)-'.$cyrelAdd.'->(m) ';
			}
			$result = $neocl->run($query, ['instanceID' => $instanceID]);
		}
		return $result;
	}
	


	// Returns everything related to $metaType (all labels Person) -- New Style!
	public function getInstanceEditData($neocl, $metaType, $instanceID)
    {		
		if ($metaType<>'') {$metaType=':'.$metaType;}
        //$query = 'MATCH (n'.$metaType.') WHERE n.in_id={instanceID} OPTIONAL MATCH (n)-[r]->(m) RETURN DISTINCT n,COLLECT(r) as r2,m.in_id as m';
		$query = 'MATCH (n'.$metaType.') WHERE n.in_id={instanceID} OPTIONAL MATCH (n)-[r]->(m) RETURN n,TYPE(r) as r2,COLLECT(m.in_id) as m';
        $result = $neocl->run($query, ['instanceID' => $instanceID]);
        $relations = [];
		$first = false;
        foreach ($result->records() as $record) {
			
            if ($first == false) { 
				$entityNode = $record->get('n'); 
				$first = true;
				$relations['entity'] = $entityNode->values();
				
			} // only once
			
			// if (!empty($relationNode)) { $relationData = $relationNode;}
			$relationRel = $record->get('r2');
			$relationNode = $record->get('m');
			$returnValues = [];
			foreach($relationNode as $key => $value) { $returnValues[] = (string)$value; }
			$relations['relprops'][$relationRel] = $returnValues;
        }

        return $relations;
    }			

	public function delete_instance($neocl, $metaType, $instanceID)
	{
		$query = 'Match (n:'.$metaType.') WHERE n.in_id = {instanceID} DETACH DELETE n';
		$result = $neocl->run($query, ['instanceID' => $instanceID]);
	}
	
	// Get Frame by ID
	public function getFrame($neocl, $instanceID)
	{
		return $this->getFrameRes($neocl, 'match (f:Frame)-[:startView]->(v:View) WHERE f.in_id={param} RETURN f,v', $instanceID);
	}	
	// Get Frame by Name
	public function getFrameByName($neocl, $instanceName)
	{
		return $this->getFrameRes($neocl, 'match (f:Frame)-[:startView]->(v:View) WHERE replace(toLower(f.name)," ","")={param} RETURN f,v',$instanceName);
	}	
	
	// Get the actual result (originated from ID or Name)
	public function getFrameRes($neocl, $query, $param)
	{
		$result = $neocl->run($query, ['param' => $param]);
		$res = [];
		$res['frame']  = $result->records()[0]->get('f')->values();		
		$res['view'] = $result->records()[0]->get('v')->values();	
		return $res;
	}	
	
	public function getView($neocl, $instanceID)
	{
		$query = 'match (v:View) WHERE v.in_id={instanceID} RETURN v';
		$result = $neocl->run($query, ['instanceID' => $instanceID]);
		$res = [];
		$res['view']  = $result->records()[0]->get('v')->values();		
		return $res;
	}
	
	public function get_domains($neocl,$navTreeName)
	{
		$query = 'MATCH (n:DomainNavTree)-[:hasDomain]->(m:Domain) WHERE n.name={navTreeName} RETURN m';
		$result = $neocl->run($query, ['navTreeName' => $navTreeName]);
		return $result->getRecords('m');
	}

}
