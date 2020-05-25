<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\NeoService;

use Symfony\Component\Form\Extension\Core\Type\DateType;
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
	private $neolib = null;
	
    public function __construct()
    {
        $this->neolib = new NeoService();	
    }	
	
	// Delete an instance of a metaType (Person, Company, etc...)
	/**
	 * @Route("/dataeditor/delete/{domain}/{metaType}/{instanceID}")
	 */	 
	public function deleteInstance($domain,$metaType,$instanceID)
	{
		$neocl = $this->getNeo4jClient();
		$this->neolib->delete_instance($neocl, $metaType, $instanceID);
		return $this->redirect($this->redirecturl.$domain.'/'.$metaType);
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
            return $this->redirect($this->redirecturl.$domain.'/'.$data['typename']);
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
	public function removeMetaType(Request $request, $domain,$metaType)
	{
		$neocl = $this->getNeo4jClient();
		$this->neolib->remove_MetaType($neocl, $domain, $metaType);
		return $this->redirect($this->redirecturl.$domain);
	}
	
	/**
     * @Route("/dataeditor/edit/{domain}/{instanceType}/{instanceID}", name="query_buildertest", defaults={"instanceID" = ""})
     */
    public function AddEditInstance(Request $request, $domain, $instanceType, $instanceID)
    {
		$titprefix = 'Add '; // assume add
		$neocl = $this->getNeo4jClient();
	
		// get attribute and relation data
		$metaAttrData = $this->queryResultToArr( $this->neolib-> get_typeattr($neocl, $instanceType, $domain) );
		//Add Name and Description in first position
		array_unshift($metaAttrData,['type'=>'textarea','name'=>'description','desc'=>'The description of the '.$instanceType]);
		array_unshift($metaAttrData,['type'=>'text','name'=>'name','desc'=>'The name of the '.$instanceType]);		
		//Find Relations
		$metaRelData = $this->queryResultToArr( $this->neolib-> get_metaRels($neocl, $domain, $instanceType ) );			
		// Find Lookup Attributes
		$metaLoAttrData = $this->queryResultToArr(  $this->neolib-> get_metaLookupAttrs($neocl, $domain, $instanceType) );		
		$instanceData = [];
		$returnValues = [];
		$tetc = [
			'text' => TextType::class,
			'textarea' => TextAreaType::class,
			'relation' => ChoiceType::class,
			'datetime-local' => DateTimeType::class,
			'date' => DateType::class,
			'bool' => ChoiceType::class,
			'number' => IntegerType::class,
			'lookupattr' => ChoiceType::class,
		];

		if ($instanceID != "") { // retrieve node information and add to params
		  $titprefix = 'Edit ';
		  $instanceData = $this->neolib->getInstanceEditData($neocl, $domain, $instanceType, $instanceID);
		}

		$nameData ='';
		$descData ='';
		if (array_key_exists('entity',$instanceData)) {
			$nameData = $instanceData['entity']['name'];
			if (array_key_exists('description',$instanceData['entity'])) {	$descData = $instanceData['entity']['description'];}
		}		
		
		//Create Form Fields	
        $form = $this->createFormBuilder($returnValues)
            ->add('save', SubmitType::class, ['label' => 'Save']);
	
		// Add Attributes to the form	
		foreach($metaAttrData as $item)
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
				} else{
					// this is where the item is actually added
					$form = $form->add($item['name'], $tetc[$item['type']], [ 'required' => false,'label_attr' => ['class' =>'input-group-addon']]);
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
		foreach($metaLoAttrData as $item)
		{
			// Get choices
			$arr = [];
			$arr = $this->retrieverelnameid($item['relid'], $item['domain'], $item['fromtype'], true);
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
		foreach($metaRelData as $item)
		{
			// Get choices
			$arr = [];
			$arr = $this->retrieverelnameid($item['relid'], $item['domain'], $item['totype'], false);
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
		
        $form=$form->getForm();
		
		$form->handleRequest($request);			
					
		if ($form->isSubmitted() && $form->isValid()) {
			// get Return values
			$returnValues = $form->getData();
			// Write to Database
			$this->neolib->CreateOrMergeInstance($neocl, $domain, $instanceType, $metaAttrData, $metaLoAttrData, $metaRelData, $returnValues, $instanceID);
			
			//return new Response('<html><body> asdf</body></html>' );
			return $this->redirect($this->redirecturl.$domain.'/'.$instanceType.'/');
		}

        return $this->render('data_editor/instance/form.html.twig', [
            'form' => $form->createView(),
			'title'=> $titprefix.$instanceType,
			'redir'=> $this->redirecturl.$domain.'/'.$instanceType.'/',
        ]);
    }
	
	private function retrieverelnameid($relid, $domain, $instanceType, $isLoAttr)
	{
		$neocl = $this->getNeo4jClient();		
		$tti2 = [];
		if ($instanceType != 'instanceType')
		{
			$attrArray = $this->neolib-> get_relattr($neocl,$relid);
			if (($instanceType=='MetaType') && ($domain =='FunctionalType'))
			{	$totypeinstances = $this->neolib-> get_allMetaTypes($neocl);	}
			else
			{	$totypeinstances = $this->neolib-> get_instancenames($neocl, $domain, $instanceType,0);	}
			if ($isLoAttr) { $tti2 = $this->queryResultToKeyKey($totypeinstances); }
				      else { $tti2 = $this->queryResultToKeyVal($totypeinstances); }
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
