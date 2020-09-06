<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\NeoService;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

use Symfony\Component\Validator\Constraints\DateTime;


class DataEditorController extends Controller
{
	private $redirecturl = '/frameN/databrowser/';
	private $metaredirecturl = '/frameN/metaeditor/';
	private $metaNames = ['MetaAttr' => 'an Attribute', 'MetaLookupAttr' => 'a Lookup Attribute', 'MetaRel' => 'a Relation'];
	private $neolib = null;
	
    public function __construct()
    {
        $this->neolib = new NeoService();
    }	
	
	// Delete an instance of a metaType (Person, Company, etc...)
	/**
	 * @Route("/dataeditor/delete/{domain}/{metaType}/{instanceID}", defaults={"redir" = ""})
	 */	 
	public function deleteInstance($domain,$metaType,$instanceID,$redir)
	{
		$redir = $redir=="" ? $this->redirecturl.$domain.'/'.$metaType : $redir;
		$neocl = $this->getNeo4jClient();
		$this->neolib->delete_instance($neocl, $metaType, $instanceID);
		return $this->redirect($redir);
	}

	/**
	 * @Route("/metaeditor/delete/{domain}/{metaType}/{instanceID}")
	 */	 
	public function deleteMetaThing(Request $request, $domain,$metaType,$instanceID)
	{
		$refererdomain = $request->query->get('referer');
		return $this->deleteInstance($domain,$metaType,$instanceID,$this->metaredirecturl.$refererdomain.'/');
	}	
	
    // add a structure with attributes and relations
	/**
	 * @Route("/dataeditor/addtype/{domain}")
	 */
	public function addMetaType(Request $request, $domain)
	{
		$neocl = $this->getNeo4jClient();
		// handle submitted data
		if ($request->getMethod() == 'POST')
		{
			$data = $request->request->all();
			$this->neolib-> add_metatype($neocl, $domain, $data);

			//return new Response('<html><body> asdf</body>'.$redirect.'</html>' );
            return $this->redirect($this->metaredirecturl.$domain.'/'.$data['typename']);
		}
		$redirect = $request->getUri();
		return $this->render('data_editor/type/addtype.html.twig', array(
			'domain' => $domain,
			'listitems' => $this->neolib->get_allMetaTypes($neocl)->records()));
	}
	
	// Remove a metaType (Person, Company, etc...)
	/**
	 * @Route("/dataeditor/deletetype/{domain}/{metaType}")
	 */	 
	public function removeMetaType($domain,$metaType)
	{
		$neocl = $this->getNeo4jClient();
		$this->neolib->remove_MetaType($neocl, $domain, $metaType);
		return $this->redirect($this->metaredirecturl.$domain);
	}
	
	/**
     * @Route("/dataeditor/edit/{domain}/{instanceType}/{instanceID}", defaults={"instanceID" = "", "redir" = ""})
     */
    public function AddEditInstance(Request $request, $domain, $instanceType, $instanceID, $redir)
    {
		$redir = $redir=="" ? $this->redirecturl.$domain.'/'.$instanceType.'/' : $redir;
		$titprefix = $instanceID != "" ? 'Edit ' : 'Add ';
		$neocl = $this->getNeo4jClient();
	
		$metaData = $this->getMetaData($domain, $instanceType);

		$instanceData = $instanceID != "" ? $this->neolib->getInstanceEditData($neocl, $instanceType, $instanceID) : [];

		$form = $this->getAddEditForm($metaData,$instanceData);
	
		$form->handleRequest($request);			
					
		if ($form->isSubmitted() && $form->isValid()) {
			// get Return values
			$returnValues = $form->getData();
			// Write to Database
			
			$this->neolib->CreateOrMergeInstance($neocl, $domain, $instanceType, $metaData, $returnValues, $instanceID, []);
			
			//return new Response('<html><body>|done</body></html>' );
			return $this->redirect($redir);
		}
		
        return $this->render('data_editor/instance/form.html.twig', [
            'form' => $form->createView(),
			'title'=> $titprefix.$instanceType,
			'redir'=> $redir,
        ]);
    }
	
	/**
     * @Route("/metaeditor/edit/{domain}/{instanceType}/{instanceID}", defaults={"instanceID" = ""})
     */
    public function AddEditMetaThing(Request $request, $domain, $instanceType, $instanceID)
    {
		$refererdomain = $request->query->get('referer');
		return $this->AddEditInstance($request, $domain, $instanceType, $instanceID, $this->metaredirecturl.$refererdomain.'/');
	}
	
	/**
     * @Route("/dataeditor/metaadd/{domain}/{instanceType}/{metaTypeToAdd}")
     */
	public function AddMetaThing(Request $request, $domain, $instanceType, $metaTypeToAdd)
	{
		$titprefix = 'Add '.$this->metaNames[$metaTypeToAdd].' to ';
		$neocl = $this->getNeo4jClient();
	
		$metaData = $this->getMetaData('FunctionalType', $metaTypeToAdd);

		$form = $this->getAddEditForm($metaData,[]);
	
		$form->handleRequest($request);			
					
		if ($form->isSubmitted() && $form->isValid()) {
			// get Return values
			$returnValues = $form->getData();
			// Write to Database
			$this->neolib->CreateOrMergeInstance($neocl, $domain, $instanceType, $metaData, $returnValues, "", array('makedomain' => 'FunctionalType', 'makeMT' => $metaTypeToAdd));
			
			//return new Response('<html><body>|end of stuff</body></html>' );
			return $this->redirect($this->metaredirecturl.$domain.'/'.$instanceType.'/');
		}
		
        return $this->render('data_editor/instance/form.html.twig', [
            'form' => $form->createView(),
			'title'=> $titprefix.$instanceType,
			'redir'=> $this->metaredirecturl.$domain.'/'.$instanceType.'/',
        ]);	
	}
	
	private function getMetaData($domain, $instanceType)
	{
		$neocl = $this->getNeo4jClient();		
		$metaData = [];
		// get attribute and relation data
		$metaData['Attr'] = $this->queryResultToArr( $this->neolib-> get_typeattr($neocl, $instanceType, $domain) );
		//Add Name and Description in first position
		array_unshift($metaData['Attr'],['type'=>'textarea','name'=>'description','desc'=>'The description of the '.$instanceType]);
		array_unshift($metaData['Attr'],['type'=>'text','name'=>'name','desc'=>'The name of the '.$instanceType]);		
		//Find Relations
		$metaData['Rel'] = $this->queryResultToArr( $this->neolib-> get_metaRels($neocl, $domain, $instanceType ) );			
		// Find Lookup Attributes
		$metaData['LoAttr'] = $this->queryResultToArr(  $this->neolib-> get_metaLookupAttrs($neocl, $domain, $instanceType) );		

		return $metaData;
	}
	
	private function getAddEditForm($metaData,$instanceData)
	{
		//Create Form Fields	
		$returnValues = [];		
        $form = $this->createFormBuilder($returnValues)
            ->add('save', SubmitType::class, ['label' => 'Save']);

		$tetc = [
			'text' => TextType::class,
			'textarea' => TextAreaType::class,
			'relation' => ChoiceType::class,
			'datetime-local' => DateTimeType::class,
			'date' => DateType::class,
			'time' => TimeType::class,
			'bool' => ChoiceType::class,
			'number' => IntegerType::class,
			'lookupattr' => ChoiceType::class,
		];
		
		$tetcDisplay = [
			'Text' => 'text',
			'Text (multi-line)' => 'textarea',
			'Datetime' => 'datetime-local',
			'Date' => 'date',
			'Yes/No' => 'bool',
			'Value' => 'number',
			'Time' => 'time',
		];
	
		// Add Attributes to the form	
		foreach($metaData['Attr'] as $item)
		{
			// fill myData with record value
			$myData = '';
			if (array_key_exists('entity',$instanceData)) {
				if (array_key_exists($item['name'],$instanceData['entity'])) { $myData= $instanceData['entity'][$item['name']]; }
			} 
			
			// ... if record value is empty and default value exists, fill that in myData
			if ($myData=='' && array_key_exists('value',$item)) {
				$myData=$item['value'];
			}
			
			if ($item['type']=='bool')
				{
					$form = $form->add($item['name'], $tetc[$item['type']], [ 'required' => true,'label_attr' => ['class' =>'input-group-addon'], 'choices' => array('Yes' => true,'No' => false), 'empty_data' => false]);
				} else
				if ($item['name']=='attrtype')
				{	//cheating a little here :-) by making the field named 'attrtype' a combo with all valid types
					$form = $form->add($item['name'], $tetc['lookupattr'], [ 'required' => true,'label_attr' => ['class' =>'input-group-addon'], 'choices' => $tetcDisplay, 'empty_data' => false]);
				}
				else {
					// this is where the all the normal items are actually added
					$req = $item['name']=='name' ? true : false;
					$form = $form->add($item['name'], $tetc[$item['type']], [ 'required' => $req,'label_attr' => ['class' =>'input-group-addon']]);
				}
				
			if ($item['type']=='datetime-local')
			{
				$date = new \DateTime(str_replace('T',' ',$myData));
				$form->get($item['name'])->setData($date);
			} else				
			if ($item['type']=='bool')
			{
				$tf = false;
				if (gettype($myData)=='string')
				{
					switch ($myData)
					{
						case 'false': $tf = false;
						break;
						case 'true' : $tf = true;
						break;
					}
				} else {$tf = boolval($myData);}
				$form->get($item['name'])->setData($tf);
			} else 
			if ($item['type']=='number')
			{
				$form->get($item['name'])->setData(intval($myData));
			} else
			{ $form->get($item['name'])->setData($myData); }
		}
		
		// Add Lookups to the form
		foreach($metaData['LoAttr'] as $item)
		{
			// Get choices
			$arr = [];
			$arr = $this->retrieverelnameid($item['relid'], $item['domain'], $item['fromtype'], true, false);
			$vals = array();
			if (array_key_exists('relprops',$instanceData) && array_key_exists($item['name'],$instanceData['relprops']))
			{
				$chosen = $instanceData['relprops'][$item['name']];
			}
			// Add choice
			$form = $form->add($item['name'], $tetc['lookupattr'], [ 'required' => false,'label_attr' => ['class' =>'input-group-addon'], 'choices' => $arr]);
			
			$myData = '';
			if (array_key_exists('entity',$instanceData)) {
				if (array_key_exists($item['name'],$instanceData['entity'])) { $myData= $instanceData['entity'][$item['name']]; }	
			}
			
			if ($myData!='') { $form->get($item['name'])->setData($myData); }
		}		
		
		
		// Add Relations to the form
		foreach($metaData['Rel'] as $item)
		{
			// Get choices
			$arr = [];
			$arr = $this->retrieverelnameid($item['relid'], $item['domain'], $item['totype'], false, $item['isTaxo']);
			$chosen = [];
			$vals = array();
			if (array_key_exists('relprops',$instanceData) && array_key_exists($item['name'],$instanceData['relprops']))
			{
				$chosen = $instanceData['relprops'][$item['name']];
			}
			// Add choice
			$form = $form->add($item['name'], $tetc['relation'], [ 'required' => false,'multiple' => $item['multi'],'label_attr' => ['class' =>'input-group-addon'], 'choices' => $arr]);
			if ($item['multi'] == false) // this setData only works for single value selection
			{
				if (sizeof($chosen)>0) { $form->get($item['name'])->setData($chosen[0]); }
			} else // multiple value selection is more difficult (needs array of explicitly created strings)
			{
				if (sizeof($chosen)>0) { $form->get($item['name'])->setData($chosen); }
			}
		}
		
        return $form->getForm();
	}
	
	private function retrieverelnameid($relid, $domain, $instanceType, $isLoAttr, $isTaxo)
	{
		$neocl = $this->getNeo4jClient();		
		$tti2 = [];
		if ($instanceType != 'instanceType')
		{
			if ($isTaxo == false)
			{
				//$attrArray = $this->neolib-> get_relattr($neocl,$relid);
				if (($instanceType=='MetaType') && ($domain =='FunctionalType'))
				{	$totypeinstances = $this->neolib-> get_allMetaTypes($neocl);	}
				else
				{	$totypeinstances = $this->neolib-> get_instancenames($neocl, $domain, $instanceType);	}
				if ($isLoAttr) { $tti2 = $this->queryResultToKeyKey($totypeinstances); }
						  else { $tti2 = $this->queryResultToKeyVal($totypeinstances); }
			} else
			{
				// get taxonomy values as list of strings (heavily simplified on purpose to fit current automated form generation!)
				$totypeinstances = $this->neolib->get_taxoinstancenames($neocl, $domain, $instanceType);
				$tti2 = $this->queryResultToKeyVal($totypeinstances);
			}
		} else
		{   // instanceType triggers return of all types in all domains
			$tti2 = $this->queryResultToKeyVal($this->neolib->get_allinstanceTypes($neocl));
		}

		return $tti2;
	}	
		
	
	// Restructure key,value of the $record into $tti[$key]=$value, with empty values and add that to $attA.
	public function queryResultToArr($queryResult)
	{
		$attA = array();
		$j = 0;
		foreach ($queryResult->records() as $record)
		{
			$thistype = '';
			$thisdefval = '';
			for ($i = 0; $i < count($record->keys()); $i++) 
			{
				if ($record->keys()[$i] != 'defval')
				{
					$tti[$record->keys()[$i]]=$record->value($record->keys()[$i]);
				} else {
					$thisdefval = $record->value($record->keys()[$i]);
				}
				if ($record->keys()[$i] == 'type')
				{
					$thistype = $record->value($record->keys()[$i]);
				}
			}
			
			// set default value
			$tti["value"] = "";
			if ($thisdefval != "")
			{
				if ($thistype == 'Date')
				{
					if ($thisdefval = 'today') { $thisdefval = date('Y-m-d'); }
				}
				if ($thistype == 'datetime-local')
				{
					if ($thisdefval = 'today') { $thisdefval = (new \DateTime())->format('Y-m-d\TH:i:s'); }
				}
				$tti["value"] = $thisdefval;	
			} 
			$attA[$j] = $tti;
			$j++;
		}		
		return $attA;
	}	
	
	// Restructure key,value of the $record into $tti[$key]=$value, with empty values and add that to $attA.
	public function queryResultToKeyVal($queryResult)
	{
		$attA = array();
		$j = 0;
		foreach ($queryResult->records() as $record)
		{
			$name = '';
			$id = '';
			for ($i = 0; $i < count($record->keys()); $i++) 
			{
				if ($record->keys()[$i]=='name') {$name=$record->value($record->keys()[$i]);}
				if ($record->keys()[$i]=='id') {$id=$record->value($record->keys()[$i]);}
			}
			
			$attA[$name.' - ('.$id.')'] = $id;
			$j++;
		}		
		return $attA;
	}
	
	// Restructure key,value of the $record into $tti[$key]=$key, so the name of the relation is returned for LookupAttributes
	public function queryResultToKeyKey($queryResult)
	{
		$attA = array();
		$j = 0;
		foreach ($queryResult->records() as $record)
		{
			$name = '';
			$id = '';
			for ($i = 0; $i < count($record->keys()); $i++) 
			{
				if ($record->keys()[$i]=='name') {$name=$record->value($record->keys()[$i]); $id=$record->value($record->keys()[$i]);}
			}
			
			$attA[$name] = $name;
			$j++;
		}		
		return $attA;
	}	

	/**
     * @return \GraphAware\Neo4j\Client\Client
     */
    public function getNeo4jClient()
    {
		return $this->get('my.neo4j');
    }
}
