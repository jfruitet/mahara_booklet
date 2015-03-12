<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-booklet
 * @author     Catalyst IT Ltd
 * @author     Christophe DECLERCQ - christophe.declercq@univ-nantes.fr
 * @author     Jean FRUITET - jean.fruitet@univ-nantes.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

function get_skillsform($idtab, $domainsselected='', $skillsselected='' ) {
	global $USER;

	// DEBUG
	//echo "<br /> DEBUG :: lib.php :: 2548 :: <br />\n";
	//print_object($askill);
	//exit;

	$elements = array();
	$tab_skills_selected = array();


	if (empty($domainsselected)){
    	$domainsselected='any';
	}

	// Domains
    $list_of_domains_selected = array();

    if (!empty($domainsselected) && ($domainsselected!='any')){
		$tab_domainsselected = explode('-', $domainsselected);
		//print_object($tab_domainsselected);
		//exit;
		foreach($tab_domainsselected as $index_domainselected){
			if (isset($index_domainselected)){
    			$list_of_domains_selected[] = trim($index_domainselected);
			}
		}
	}

	//print_object($list_of_domains_selected);
	//exit;

    // Skills
/*
	if (empty($skillsselected)){
    	$skillsselected='any';
	}

    $list_of_skills_selected = array();

    if (!empty($skillsselected) && ($skillsselected!='any')){
		$tab_skillsselected = explode('-', $skillsselected);
		//print_object($tab_skillsselected);
		//exit;
		foreach($tab_skillsselected as $index_skillselected){
			if (isset($index_skillselected)){
    			$list_of_skills_selected[] = trim($index_skillselected);
			}
		}
	}
*/

	$sql = "SELECT DISTINCT domain FROM {artefact_booklet_skill} ORDER BY domain ASC";
    $domains = get_records_sql_array($sql, array());
    //print_object($domains);
	//exit;

	if (!empty($domains)){
       	$nbdomains = count($domains);
		if ($nbdomains>1){
	    	$domain_options = array();
			$domain_selected = array();
			$d=0;
    		if ($domainsselected=='any'){
				foreach ($domains as $domain){
	               	$domain_options[$d]=$domain->domain;
   	                $domain_selected[] = $d;
					$d++;
				}
			}
			else{
				foreach ($domains as $domain){
	               	$domain_options[$d]=$domain->domain;
					if (array_search($d, $list_of_domains_selected)){
    	                $domain_selected[] = $d;
					}
					$d++;
				}
			}
		    //print_object($domain_options);
			//exit;
   			$elementdomains['domainselect'] = array(
	        		'type' => 'select',
	    	    	'title' => '', //get_string('selectdomains','artefact.booklet'),
		        	'multiple' => true,
    		    	'options' => $domain_options,
        			'defaultvalue' => $domain_selected,
        			//'size' => count($domains),
	                'size' => 3,
    	            'description' => get_string('multiselect', 'artefact.booklet'),
   			);

       	    $elementdomains['submit'] = array(
            		'type' => 'submitcancel',
        	    	'value' => array(get_string('savedomainchoice','artefact.booklet'), get_string('cancel')),
    	         	'goto' => get_config('wwwroot') . '/artefact/booklet/index.php?id='.$idtab,
	    	);

            $elementdomains['idtab'] = array(
        	            'type' => 'hidden',
            	        'value' => $idtab,
    	    );

   	    	$domainchoice = array(
            	'name' => 'domainchoice',
	        	'plugintype' => 'artefact',
    	    	'pluginname' => 'booklet',
   	    	    // 'validatecallback' => 'validate_selectlist',
       	    	'successcallback' => 'selectsomedomains_submit',
           	    'renderer' => 'table',
               	'elements' => $elementdomains,
            );
   	    	$compositeform['domainchoice'] = pieform($domainchoice);
		}
	}

	if (!empty($list_of_domains_selected)){
            $where='';
			$params = array();
			foreach($list_of_domains_selected as $d){
				if (!empty($where)){
					$where.=' OR domain = ? ';
				}
				else{
                    $where.=' domain = ? ';
				}
				$params[]= $domain_options[$d];
			}
			$sql = "SELECT * FROM {artefact_booklet_skill} WHERE ".$where." ORDER BY code ASC";
		    $skills = get_records_sql_array($sql, $params);
	}
	else{
			$sql = "SELECT * FROM {artefact_booklet_skill} ORDER BY domain ASC, code ASC";
		    $skills = get_records_sql_array($sql, array());
	}


	if (!empty($skills)){
			$i=0;
            $elementsskills = array();
        	foreach ($skills as $skill){
                $str_scale =  get_skill_choice_display($skill, $skill->threshold);
                $elementsskills['html'.$i] = array(
                			'type' => 'html',
                			'value' => $skill->description.' ['.$str_scale.']'."\n",
           		);

				if (!empty($tab_skills_selected[$skill->id])){
                    $elementsskills['select'.$i] = array(
        		        	'type' => 'checkbox',
                			'defaultvalue' => $tab_skills_selected[$skill->id],
		                	'title' =>  $skill->domain.' :: '.$skill->code,
        		        	//'description' => get_string('checked', 'artefact.booklet'),
           			);
				}
				else{
                    $elementsskills['select'.$i] = array(
        		        	'type' => 'checkbox',
                			'defaultvalue' => 0,
		                	'title' =>  $skill->domain.' :: '.$skill->code,
        		        	//'description' => '',
           			);
				}
                $elementsskills['id'.$i] = array(
		                	'type' => 'hidden',
        		        	'value' => $skill->id,
           		);
                $elementsskills['type'.$i] = array(
		                	'type' => 'hidden',
        		        	'value' => $skill->type,
           		);

                $i++;
			}

	        $elementsskills['nbitems'] = array(
                	'type' => 'hidden',
                	'value' => $i,
    	    );

       		$elementsskills['submit'] = array(
            	'type' => 'submit',
            	'value' => get_string('savechecklist','artefact.booklet'),
        	);

			$elementsskills['delete'] = array(
                'type' => 'checkbox',
                'help' => false,
                'title' => get_string('eraseskills','artefact.booklet'),
                'defaultvalue' => 0,
                'description' => get_string('eraseskillsdesc','artefact.booklet'),
        	);

            $elementsskills['domainsselected'] = array(
                    'type' => 'hidden',
                    'value' => $domainsselected,
            );

            $elementsskills['idtab'] = array(
        	            'type' => 'hidden',
            	        'value' => $idtab,
    	    );

    	    $choice = array(
                'name' => 'listchoice',
                'plugintype' => 'artefact',
                'pluginname' => 'booklet',
        	    // 'validatecallback' => 'validate_selectlist',
            	'successcallback' => 'selectskills_submit',
                'renderer' => 'table',
                'elements' => $elementsskills,
            );
        	$compositeform['choice'] = pieform($choice);
	}


	$aform = array(
			'name' => 'addskillform',
            'plugintype' => 'artefact',
    	    'pluginname' => 'booklet',
	        'renderer' => 'table',
    	    'method' => 'post',
        	'successcallback' => 'askill_submit',
			'elements' => array(

    	    	'optionnal' => array(
	    	    	'type' => 'fieldset',
		    		'name' => 'inputform',
					'title' => get_string ('addnewskill', 'artefact.booklet'),
	        		'collapsible' => true,
    	        	'collapsed' => true,
	    	        'legend' => get_string('addnewskill', 'artefact.booklet'),
            	    'elements' => array(

	                	'skilldomain' => array(
		                    'type' => 'text',
    		                'title' => get_string('domain', 'artefact.booklet'),
        		            'size' => 40,
            		        'defaultvalue' => NULL,
                		    'rules' => array(
                    	    'required' => true,
	                    	),
    	                	'help' => true
        	        	),
            	    	'skillcode' => array(
                	    	'type' => 'text',
	                	    'title' => get_string('code', 'artefact.booklet'),
    	                	'size' => 20,
	        	            'defaultvalue' => NULL,
    	        	        'rules' => array(
        	        	        'required' => true,
            	        	),
	            	    ),
    	            	'skilltype' => array(
	        	            'type' => 'text',
    	        	        'title' => get_string('sktype', 'artefact.booklet'),
        	        	    'size' => 20,
            	        	'defaultvalue' => NULL,
	            	        'rules' => array(
    	                	    'required' => true,
        	        	    ),
							'description' => get_string('skilltype', 'artefact.booklet'),
    	            	),

	    	            'skilldescription' => array(
    	    	            'type' => 'wysiwyg',
        	    	        'rows' => 5,
            	    	    'cols' => 60,
                	    	'title' => get_string('descriptionlist', 'artefact.booklet'),
	                    	'defaultvalue' => get_string('skilldescriptionmodel', 'artefact.booklet'),
							'description' => get_string('skilldescriptiondesc', 'artefact.booklet'),
			                'rules' => array(
    			            	'required' => true,
        			        ),
               			),

		                'skillscale' => array(
    		                'type' => 'text',
        		            'title' => get_string('generalscale', 'artefact.booklet'),
            		        'size' => 60,
                		    'defaultvalue' => get_string('generalscalemodel', 'artefact.booklet'),
                    		'description' => get_string('generalscaledesc', 'artefact.booklet'),
	                    	'rules' => array(
    	                    	'required' => true,
	        	            ),
    	        	    ),
	    	            'skillthreshold' => array(
    	    	            'type' => 'text',
        	    	        'title' => get_string('threshold', 'artefact.booklet'),
            	    	    'size' => 10,
                	    	'defaultvalue' => get_string('thresholdscalemodel', 'artefact.booklet'),
                    		'description' => get_string('thresholdscaledesc', 'artefact.booklet'),
		                    'rules' => array(
    		                    'required' => true,
        		            ),
            		    ),

	     	    	    'submit' => array(
	         	           'type' => 'submitcancel',
	                        'value' => array(get_string('saveskill', 'artefact.booklet'), get_string('cancel')),
    	                    'goto' => get_config('wwwroot') . '/artefact/booklet/index.php?id='.$idtab,
        	    	    ),
					),
				),

            	'domainsselected' => array(
                    'type' => 'hidden',
                   	'value' => $domainsselected,
        		),

	           	'idtab' => array(
        	            'type' => 'hidden',
            	        'value' => $idtab,
   		        ),

			),
    );

	$compositeform['addform'] = pieform($aform);

    $sform = array(
        	'name' => 'inputskills',
	        'plugintype' => 'artefact',
    	    'pluginname' => 'booklet',
    		'renderer' => 'table',
        	'method'      => 'post',
        	'successcallback' => 'skills_submit',
		    'elements' => array(
    	    	'optionnal' => array(
	    	    	'type' => 'fieldset',
		    		'name' => 'inputform',
					'title' => get_string ('inputnewskills', 'artefact.booklet'),
	        		'collapsible' => true,
    	        	'collapsed' => true,
	    	        'legend' => get_string('inputnewskills', 'artefact.booklet'),
            	    'elements' => array(
	            		'skills' => array(
	    	            'type' => 'textarea',
    	    	        'rows' => 10,
        	    	    'cols' => 100,
            	    	'title' => get_string('listofskills', 'artefact.booklet'),
            		    'defaultvalue' => '',
                    	'description' => get_string('inputlistofskillsmodel', 'artefact.booklet'),
						'help' => true
       	    		),
   		        	'submit' => array(
       	 	           'type' => 'submitcancel',
           	        		'value' => array(get_string('saveskills', 'artefact.booklet'), get_string('cancel')),
               	    	    'goto' => get_config('wwwroot') . '/artefact/booklet/index.php?id='.$idtab,
	               		),
					),
				),
           		'domainsselected' => array(
                	'type' => 'hidden',
            		'value' => $domainsselected,
	        	),

   		        'idtab' => array(
       				'type' => 'hidden',
           			'value' => $idtab,
	   	        ),
			),
    	);

    	$compositeform['skillsform'] = pieform($sform);

		//print_object($compositeform);
		//exit;
    	return $compositeform;
}


// -------------------------------------
function selectsomedomains_submit(Pieform $form, $values) {
    global $_SESSION;
	global $_SERVER;
	//print_object($values);
	//exit;
	// Domain selection
    $domainsselected='';

	if (isset($values['domainselect'])){
		foreach($values['domainselect'] as $a_domain){
	        $domainsselected.= $a_domain.'-';
		}
        $domainsselected=substr($domainsselected,0,strlen($domainsselected)-1);
		//echo "$domainsselected";
		//exit;
		if (!empty($domainsselected)){
            $goto = get_config('wwwroot') . '/artefact/booklet/manageskills.php?idtab='.$values['idtab'].'&domainsselected='.$domainsselected;
		}
		else{
			$goto = get_config('wwwroot') . '/artefact/booklet/manageskills.php?idtab='.$values['idtab'].'&domainsselected=';
		}
	}
	else{
        $goto = $_SERVER['HTTP_REFERER'];
	}
	redirect($goto);
}

// -------------------------------------
function selectskills_submit(Pieform $form, $values) {
    global $_SESSION;
	global $USER;
    $skillsselected='';
    $t_skillsselected=array();      // Liste des enregistrement selectionnes
	$where='';
    $select='';
	$params = array();

	if (!empty($values['nbitems'])){
 		for ($i=0; $i<$values['nbitems']; $i++){
			if (!empty($values['select'.$i])){
				// Creer l'association
				$a_skill = new stdclass();
        	   	$a_skill->idskill = $values['id'.$i];
            	//print_object($a_skill);

            	$t_skillsselected[]=$a_skill;
			}
		}
	}
    if (!empty($t_skillsselected)){
		//print_object($t_skillsselected);
		//exit;

		if ($values['delete']){	// SUPPRIMER
        	foreach($t_skillsselected as $a_skill){
				delete_records('artefact_booklet_frskllresult', 'idskill', $a_skill->idskill);
                delete_records('artefact_booklet_lskillsresult', 'idskill', $a_skill->idskill);
                delete_records('artefact_booklet_listofskills', 'idskill', $a_skill->idskill);
                delete_records('artefact_booklet_skill', 'id', $a_skill->idskill);
			}
			$goto = get_config('wwwroot').'/artefact/booklet/manageskills.php?idtab='.$values['idtab'].'&domainsselected='.$values['domainsselected'];
		}
		else{ // EDITER
			// Formater la liste des modifications
            foreach($t_skillsselected as $a_skill){
                //print_object($a_skill);
				//exit;
     			$skillsselected.=$a_skill->idskill.'-';
			}
            $skillsselected = substr($skillsselected, 0, strlen($skillsselected)-1);
			//echo "<br />$skillsselected\n";
			//exit;
			if (!empty($skillsselected)){
				$goto = get_config('wwwroot').'/artefact/booklet/editskills.php?idtab='.$values['idtab'].'&domainsselected='.$values['domainsselected'].'&skillsselected='.$skillsselected;
			}
		}
	}
	redirect($goto);
}


// ----------------------------------------------
function askill_submit(Pieform $form, $values){
    global $_SESSION;
	global $USER;
		if (!empty($values['domainsselected'])){
    		$goto = get_config('wwwroot') . '/artefact/booklet/manageskills.php?idtab='.$values['idtab'].'&domainsselected='.$values['domainsselected'];
		}
		else{
			$goto = get_config('wwwroot') . '/artefact/booklet/manageskills.php?idtab='.$values['idtab'].'&domainsselected=';
		}

		$skill = new stdclass();

		$ok=true;
		if ($ok && !empty($values['skilldomain'])){
            $skill->domain =  trim($values['skilldomain']);
  		}
		else{
            $ok=false;
		}
		if ($ok && isset($values['skillcode'])){
            $skill->code =  trim($values['skillcode']);
  		}
		else{
            $ok=false;
		}

		if ($ok && isset($values['skilltype'])){
            $skill->type =  trim($values['skilltype']);
  			if (($skill->type != 0) && ($skill->type != 1) && ($skill->type != 2)){
                $skill->type = '1';
			}
		}
		else{
            $ok=false;
		}

		if ($ok && !empty($values['skilldescription'])){
			$skill->description = $values['skilldescription'];
		}
		else{
            $ok=false;
		}

		if ($ok && !empty($values['skillscale'])){
			$scale= trim($values['skillscale']);

			$scale_str='';

			if ( preg_match("/\,/", $scale)){
            	$tscale=explode(",", $scale);
				foreach ($tscale as $avalue){
					if (isset($avalue)){
                        $avalue = trim($avalue);
						if (!empty($avalue)){
                    		$scale_str .= $avalue . ',';
						}
					}
				}
                $scale_str=substr($scale_str,0,strlen($scale_str)-1);
			}
			else{
                $scale_str=$scale;
			}

			if (!empty($scale_str)){
                $skill->scale = $scale_str;
			}
			else{
                $ok=false;
			}
		}
        else{
            $ok=false;
		}

        $threshold='';
		if ($ok && isset($values['skillthreshold'])){
		    $threshold = trim($values['skillthreshold']);
			if (! is_numeric($threshold)){
                $threshold = 0;
			}
			else{
                $tscale=explode(",", $skill->scale);
				if ($threshold > count($tscale)){
    	            $threshold = count($tscale);
				}
				elseif ($threshold<0){
                	$threshold = 0;
				}
			}
		    $skill->threshold = $threshold ;
		}
		else{
            $ok=false;
		}

			// DEBUG
			//echo "<br />DEBUG :: lib_skills.php :: 559 : SCALE INPUT : $scale ; SCALE OUTPUT : $scale_str<br />\n";
			//print_object($skill);
			//exit;

		// enregistrer le skill
		if ($ok){
            try {
					// Creer le skill
					if ($rec_skill = get_record('artefact_booklet_skill', 'domain', $skill->domain, 'code', $skill->code)){
						$idskill = $rec_skill->id;
	                	$skill->id = $rec_skill->id;
		            	update_record('artefact_booklet_skill', $skill);
					}
   					else{
                    	$skill->owner = $USER->get('id');
						$idskill = insert_record('artefact_booklet_skill', $skill, 'id', true);
					}
		    }
    		catch (Exception $e) {
        		$SESSION->add_error_msg(get_string('skillsavefailed', 'artefact.booklet'));
    		}
		}
		else{
           	$SESSION->add_error_msg(get_string('skillsavefailed', 'artefact.booklet'));
		}

	redirect($goto);
}


// ----------------------------------------------
function skills_submit(Pieform $form, $values){
    global $_SESSION;
	global $USER;
    srand();

	if (!empty($values['skills'])){
    	//
		if ($tlist=explode("\n", strip_tags($values['skills']))){
			foreach ( $tlist as $line){
				if (!empty($line)){
					$skill = new stdclass();

					// "domain1;code1;description1;[scale_value_value11,scale_value12,scale_value13,...,scale_value1N;threshold1]
					if ($fields = explode("[", $line)){
		                if (!empty($fields[0])){
    		                       	// "domain1;type1;code1;description1;
        		                    if ($domain_code_description = explode(";", $fields[0])){
                                        $nbfields=count($domain_code_description);
										if ( $nbfields<5){    // pas de type dans le donnees
											if (isset($domain_code_description[0])){
                		                		$skill->domain =  trim(str_replace("-","",$domain_code_description[0]));
												$current_domain = $skill->domain;
                    	    	       		}
											else{
                            	    	    	$skill->domain = $current_domain;
											}
			                                if (isset($domain_code_description[1])){
    			                                $skill->code = trim($domain_code_description[1]);
											}
											else{
                			                    $skill->code = strtoupper($current_domain).'_'.$object->id.'_'.rand();
											}
	                        		        if (isset($domain_code_description[2])){
    	                        		        $skill->description = trim($domain_code_description[2]);
											}
											else{
        	        	                    	$skill->description = get_string('unknown', 'artefact.booklet');
											}
               		                    	$skill->type = '1';
										}
										else{
											if (isset($domain_code_description[0])){
                		                		$skill->domain =  trim(str_replace("-","",$domain_code_description[0]));
												$current_domain = $skill->domain;
	                    	    	        }
											else{
        	                    	    	    $skill->domain = $current_domain;
											}
		        	                        if (isset($domain_code_description[1])){
    		        	                        $skill->type = trim($domain_code_description[1]);
											}
											else{
                		        	            $skill->type = '1';
											}
		                                	if (isset($domain_code_description[2])){
    		                                	$skill->code = trim($domain_code_description[2]);
											}
											else{
                		                    	$skill->code = strtoupper($current_domain).'_'.$object->id.'_'.rand();
											}
	                        	        	if (isset($domain_code_description[3])){
    	                        	        	$skill->description = trim($domain_code_description[3]);
											}
											else{
        		        	                    $skill->description = get_string('unknown', 'artefact.booklet');
											}
										}
									}
						}
    	            	if (!empty($fields[1])){
	        	                // scale_value_value11,scale_value12,scale_value13,...,scale_value1N|threshold1]
    	        	            if ($scale_threshold = explode("|", $fields[1])){
										if (!empty($scale_threshold[0])){
            	        	                // scale_value_value11,scale_value12,scale_value13,...,scale_value1N
                	        	        	$skill->scale = trim($scale_threshold[0]);
                    	        }
								else if (!empty($skill->type)){ // Type 1 necessite un bareme
	                        	            //$skill->scale = $values['scale'];
	                                        $skill->scale = get_string('generalscalemodel','artefact.booklet');
    	                                    $skill->threshold  = '3';
								}
        		                if (!empty($scale_threshold[1])){
            		                        // threshold1]
											// Chasser ']'
                    		                $skill->threshold = trim(substr($scale_threshold[1],0,-1));
								}
							}
						}
					}
					$t_skill[] = $skill;
				}
			}
		}
	}

	// enregistrer les skills
	if (!empty($t_skill)){
		foreach ($t_skill as $a_skill){
			// Creer le skill
			if ($rec_skill = get_record('artefact_booklet_skill', 'domain', $a_skill->domain, 'code', $a_skill->code)){
				$idskill = $rec_skill->id;
	            $a_skill->id = $rec_skill->id;
		        update_record('artefact_booklet_skill', $a_skill);
			}
   			else{
                $a_skill->owner = $USER->get('id');
				insert_record('artefact_booklet_skill', $a_skill);
			}
		}
	}
	if (!empty($values['domainsselected'])){
    	$goto = get_config('wwwroot') . '/artefact/booklet/manageskills.php?idtab='.$values['idtab'].'&domainsselected='.$values['domainsselected'];
	}
	else{
		$goto = get_config('wwwroot') . '/artefact/booklet/manageskills.php?idtab='.$values['idtab'].'&domainsselected=';
	}

	redirect($goto);
}

//----------------------------------------------------
function get_skillseditform($idtab, $domainsselected='', $skillsselected='' ) {
	global $USER;
    $compositeform = array();
	$elements = array();
	$tab_skillsselected = array();

	if (empty($skillsselected)){
		if (!empty($domainsselected)){
    		$goto = get_config('wwwroot') . '/artefact/booklet/manageskills.php?idtab='.$values['idtab'].'&domainsselected='.$domainsselected;
		}
		else{
			$goto = get_config('wwwroot') . '/artefact/booklet/manageskills.php?idtab='.$values['idtab'].'&domainsselected=';
		}
        redirect($goto);
	}

	// Domains
    $list_of_skills_selected = array();

    if (!empty($skillsselected)){
		$tab_skillsselected = explode('-', $skillsselected);
		//print_object($tab_skillsselected);
		//exit;
		foreach($tab_skillsselected as $index_skillselected){
			if (isset($index_skillselected)){
				$index_skillselected = trim($index_skillselected);
                if (is_numeric($index_skillselected)){
                	$list_of_skills_selected[] = $index_skillselected   ;
				}
			}
		}
	}


	if (!empty($list_of_skills_selected)){
       	//print_object($list_of_skills_selected);
		//exit;
		$nb=0;
		foreach ($list_of_skills_selected as $idskill){
            if ($skill = get_record('artefact_booklet_skill', 'id', $idskill)){
				//print_object($skill);
				//exit;

                $elements['html'.$nb] = array(
   	    		    'type' => 'html',
       	        	'title' => get_string('skillstitle','artefact.booklet'),
	    	        'value' => '<h3>'.$skill->domain.' :: '.$skill->code.'</h3>',
        		);

                $elements['skilldomain'.$nb] = array(
		                    'type' => 'text',
    		                'title' => get_string('domain', 'artefact.booklet'),
        		            'size' => 40,
            		        'defaultvalue' => $skill->domain,
                		    'rules' => array(
                    	    	'required' => true,
	                    	),
                      		'description' => get_string('domaindesc', 'artefact.booklet'),
        	    );


                $elements['skillcode'.$nb] = array(
                	    	'type' => 'text',
	                	    'title' => get_string('code', 'artefact.booklet'),
    	                	'size' => 20,
	        	            'defaultvalue' => $skill->code,
    	        	        'rules' => array(
        	        	        'required' => true,
            	        	),
                      		'description' => get_string('codedesc', 'artefact.booklet'),

    	            	);

                $elements['skilltype'.$nb] = array(
	        	            'type' => 'text',
    	        	        'title' => get_string('sktype', 'artefact.booklet'),
        	        	    'size' => 20,
            	        	'defaultvalue' => $skill->type,
	            	        'rules' => array(
    	                	    'required' => true,
        	        	    ),
							'description' => get_string('skilltype', 'artefact.booklet'),
    	);

                $elements['skilldescription'.$nb] = array(
    	    	            'type' => 'wysiwyg',
        	    	        'rows' => 5,
            	    	    'cols' => 60,
                	    	'title' => get_string('descriptionlist', 'artefact.booklet'),
	                    	'defaultvalue' => $skill->description,
							'description' => get_string('skilldescriptiondesc', 'artefact.booklet'),
			                'rules' => array(
    			            	'required' => true,
        			        ),
    	);

                $elements['skillscale'.$nb] = array(
    		                'type' => 'text',
        		            'title' => get_string('generalscale', 'artefact.booklet'),
            		        'size' => 60,
                		    'defaultvalue' => $skill->scale,
                    		'description' => get_string('generalscaledesc', 'artefact.booklet'),
	                    	'rules' => array(
    	                    	'required' => true,
	        	            ),
     	);

                $elements['skillthreshold'.$nb] = array(
    	    	            'type' => 'text',
        	    	        'title' => get_string('threshold', 'artefact.booklet'),
            	    	    'size' => 10,
                	    	'defaultvalue' => $skill->threshold,
                    		'description' => get_string('thresholdscaledesc', 'artefact.booklet'),
		                    'rules' => array(
    		                    'required' => true,
        		            ),
     	);

                $elements['skillowner'.$nb] = array(
       	            		'type' => 'hidden',
           	        		'value' => $skill->owner,
				);

                $elements['id'.$nb] = array(
       	            		'type' => 'hidden',
           	        		'value' => $skill->id,
				);

                $elements['htmlend'.$nb] = array(
   	    		    'type' => 'html',
       	        	'title' => '',
	    	        'value' => '<br /><hr /><br />',
        		);


				$nb++;

			}
		}

		if (!empty($elements)){

           	$elements['domainsselected'] = array(
                    'type' => 'hidden',
    	            'value' => $domainsselected,
       		);

           	$elements['nbskills'] = array(
       	            'type' => 'hidden',
           	        'value' => $nb,
            );

           	$elements['idtab'] = array(
       	            'type' => 'hidden',
           	        'value' => $idtab,
            );

			$elements['submit'] = array(
            		'type' => 'submitcancel',
        	    	'value' => array(get_string('save','artefact.booklet'), get_string('cancel')),
    	         	'goto' => get_config('wwwroot') . '/artefact/booklet/manageskills.php?idtab='.$idtab,
	    	);

			$aform = array(
				'name' => 'editskillsform',
	        	'plugintype' => 'artefact',
    	    	'pluginname' => 'booklet',
		        'renderer' => 'table',
    		    'method' => 'post',
        		'successcallback' => 'manyskillsedit_submit',
				'elements' => $elements,
    		);

			$compositeform['editskills'] = pieform($aform);

		}
	}
	//print_object($compositeform);
	//exit;

    return $compositeform;
}


// -------------------------------------
function manyskillsedit_submit(Pieform $form, $values) {
    global $_SESSION;
	global $USER;
    $skillsselected='';
    $t_skillsselected=array();      // Liste des enregistrement selectionnes
	$where='';
    $select='';
	$params = array();

	if (!empty($values['nbskills'])){
 		for ($i=0; $i<$values['nbskills']; $i++){
			if (!empty($values['id'.$i])){
				// Creer l'association
				$a_skill = new stdclass();
        	   	$a_skill->id = $values['id'.$i];
                $a_skill->domain = $values['skilldomain'.$i];
                $a_skill->code = $values['skillcode'.$i];
                $a_skill->description = $values['skilldescription'.$i];
                $a_skill->type = $values['skilltype'.$i];
                $a_skill->scale = $values['skillscale'.$i];
                $a_skill->threshold = $values['skillthreshold'.$i];
                $a_skill->owner = $values['skillowner'.$i];
            	// print_object($a_skill);
                try {
    		        update_record('artefact_booklet_skill', $a_skill);
			    }
    			catch (Exception $e) {
        			$SESSION->add_error_msg(get_string('skillsavefailed', 'artefact.booklet'));
	    		}
			}
		}
	}
	$goto = get_config('wwwroot').'/artefact/booklet/manageskills.php?idtab='.$values['idtab'].'&domainsselected='.$values['domainsselected'];
	redirect($goto);
}

