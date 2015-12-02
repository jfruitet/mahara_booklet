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


// ------------------------------------------------------------------------------
function get_skilltodisplayform($domainsselected='', $skillsselected='', $thresholdactive=0){
	global $USER;
    $compositeform = array();
	$elementskills = array();
	$elements = array();
	$tab_skillsselected = array();
	//echo "<br />DEBUG : lib_skills.php :: 22 <br />\n";
	//echo "<br>DOMAINSSELECTED: $domainsselected, SKILLSSELECTED: $skillsselected TRESHOLDACTIVE: $thresholdactive\n";
	//exit;


	if (empty($skillsselected)){
		if (!empty($domainsselected)){
    		$goto = get_config('wwwroot') . '/artefact/booklet/displayframesskills.php?domainsselected='.$domainsselected;
		}
		else{
			$goto = get_config('wwwroot') . '/artefact/booklet/displayframesskills.php?domainsselected=';
		}
        redirect($goto);
	}

	// Domains
    $list_of_skills_selected = array();

    if (!empty($skillsselected)){
		$tab_skillsselected = explode('-', $skillsselected);
		foreach($tab_skillsselected as $index_skillselected){
			if (isset($index_skillselected)){
				$index_skillselected = trim($index_skillselected);
                if (is_numeric($index_skillselected)){
                	$list_of_skills_selected[] = $index_skillselected   ;
				}
			}
		}
	}

 	//	echo "<br />DEBUG : lib_skills.php :: 52 <br />\n";
	//	echo "<br>DOMAINSSELECTED: $domainsselected, SKILLSSELECTED: $skillsselected TRESHOLDACTIVE: $thresholdactive<br />LIST_OF_SKILLS_SELECTED<br />\n";
    //  print_object($list_of_skills_selected);
	//	exit;

	if (!empty($list_of_skills_selected)){
        //echo "<br />DEBUG : lib_skills.php :: 58 <br />\n";
		//echo "<br />LIST_OF_SKILLS_SELECTED<br />\n";
       	//print_object($list_of_skills_selected);
		//exit;

		// Skills
		$nbskills=0;
		foreach ($list_of_skills_selected as $idskill){
			if (!empty($idskill)){
            if ($askill = get_record('artefact_booklet_skill', 'id', $idskill)){
				//echo "<br />DEBUG :: lib_skills.php :: 67 <br />ASKILL<br />\n";
				//print_object($idskill);


                $elementskills['html_skill'.$nbskills] = array(
   	    		    'type' => 'html',
       	        	'title' => get_string('skillstitle','artefact.booklet'),
	    	        'value' => '<h3>'.$askill->domain.' :: '.$askill->code.'</h3>',
        		);

                $elementskills['skilldescription'.$nbskills] = array(
                      		'type' => 'html',
                	    	'title' => get_string('descriptionlist', 'artefact.booklet'),
	                    	'value' => $askill->description,
		    	);

                $elementskills['skillscale'.$nbskills] = array(
    		                'type' => 'html',
        		            'title' => get_string('scale', 'artefact.booklet'),
                		    'value' => get_skill_choice_display($askill, $askill->threshold),
     			);

                $elements['skillid'.$nbskills] = array(
    		                'type' => 'hidden',
                		    'value' => $askill->id,
     			);

 				$nbskills++;
			}
			}
		}

		if (!empty($elementskills)){
		    $elements['skills'] = array(
	        	    	'type' => 'fieldset',
	            		'legend' => get_string('skills', 'artefact.booklet'),
    		        	'elements' => $elementskills,
			);
		}


		// Frames
        $listrecords = null;
		foreach ($list_of_skills_selected as $idskill){
			// freeskills
			$sql = "SELECT DISTINCT ro.id as idobject, ro.* , re.*
				FROM {artefact_booklet_frskllresult} re
				JOIN {artefact_booklet_object} ro
	            	ON (re.idobject = ro.id)
	            WHERE re.idowner = ?
                	AND re.idskill = ?
	            ORDER BY ro.displayorder, re.idrecord  ";

			//echo "<br />DEBUG :: lib_skills.php :: 119 <br />SQL&gt; ".$sql."<br />\n";

			if ($recs = get_records_sql_array($sql, array($USER->get('id'), $idskill))){
				foreach ($recs as $rec){
  					//echo "<br />REC<br />\n";
    	   			//print_object($rec);
					if ($rec){
                        $listrecords[$rec->idobject] = $rec;
					}
				}
			}

			// listskills
			$sql = "SELECT DISTINCT ro.id as idobject, ro.* , re.*
				FROM {artefact_booklet_lskillsresult} re
				JOIN {artefact_booklet_object} ro
	            	ON (re.idobject = ro.id)
	            WHERE re.idowner = ?
                	AND re.idskill = ?
	            ORDER BY ro.displayorder, re.idrecord  ";

			//echo "<br />DEBUG :: lib_skills.php :: 144 <br />SQL&gt; ".$sql."<br />\n";
			//exit;
			if ($recs = get_records_sql_array($sql, array($USER->get('id'), $idskill))){
				foreach ($recs as $rec){
					if ($rec){
                        $listrecords[$rec->idobject] = $rec;
					}
				}
			}

		}
        //echo "<br />DEBUG :: lib_skills.php :: 152 <br />LISTRECORDS<br />\n";
		//print_object($listrecords);
		//exit;

        $nb=0;

        $listframes = array();

		if ($listrecords){
        	foreach ($listrecords as $rec){
                if ($aframe = get_record('artefact_booklet_frame', 'id', $rec->idframe)){
                	$listframes[$aframe->id] = $aframe;
				}
			}
		}
        //echo "<br />DEBUG :: lib_skills.php :: 167 <br />LISTFRAMES<br />\n";
    	//print_object($listframes);
		//exit;

		if ($listframes){
			foreach ($listframes as $aframe){
				if (!empty($aframe)){
                	//echo "<br />DEBUG :: lib_skills.php :: 173 <br />AFRAME<br />\n";
					//print_object($aframe);
					//$askill = get_record('artefact_booklet_skill', 'id', $rec->idskill);
        	        //$aframe = get_record('artefact_booklet_frame', 'id', $rec->idframe);
					if ($atab= get_record('artefact_booklet_tab', 'id', $aframe->idtab)){
                	    //echo "<br />DEBUG :: lib_skills.php :: 178 <br />ATAB<br />\n";
						//print_object($atab);
					}
					else{
        	            //echo "<br />DEBUG :: lib_skills.php :: 182 <br />ATAB ERROR<br />\n";
						//exit;
					}
	                if ($atome= get_record('artefact_booklet_tome', 'id', $atab->idtome)){
    	                //echo "<br />DEBUG :: lib_skills.php :: 186 <br />ATOME<br />\n";
        	        	//print_object($atome);
					}
					else{
                    	//echo "<br />DEBUG :: lib_skills.php :: 188 <br />ATOME ERROR<br />\n";
						//exit;
					}
					/*
            	    $elements['objecttitle'.$nb] = array(
    		                'type' => 'html',
                            'title' => get_string('title', 'artefact.booklet'),
                			'value' => $rec->title,
					);
					*/


					// J'ai des doutes sur cette valeur dont j'ignore pourquoi ell est utilis‚e car $rec 'es t pas positionn‚ dans cette boucle.
    	            $elements['idobject'.$nb] = array(
       	            		'type' => 'hidden',
           	        		'value' => $rec->idobject,
					);


                	$elements['idframe'.$nb] = array(
       	            		'type' => 'hidden',
           	        		'value' => $aframe->id,
					);
					/*
        	        $elements['objectname'.$nb] = array(
       	            		'type' => 'hidden',
           	        		'value' => $rec->name,
					);
					*/

					/**************/
					/*	Ce code provoque une erreur sous PostGres
									 TESTER ORIGINE DE CETTE ERREUR
					*/
					if ($adisplayform = display_a_frame($atome->id, $aframe->id)){
						$i=0;
						foreach ($adisplayform as $adform){
		    	            $elements['frame'.$nb.'_'.$aframe->id.'_'.$i] = $adform;
							$i++;
						}
					}
					/**/

    	            $elements['select'.$nb] = array(
       		        	'type' => 'checkbox',
            	        'title' => get_string('selectframe', 'artefact.booklet'),
                	    'defaultvalue' => $aframe->id,
					);

    	            $elements['htmlend'.$nb] = array(
   	    			    'type' => 'html',
       	    	    	'title' => '',
	    	    	    'value' => '<br /><hr /><br />',
	        		);

    	    		//echo "<br />DEBUG :: lib_skills.php :: 237 <br />ELEMENTS<br />\n";
					//print_object($elements);

					$nb++;
				}
			}
		}

        //echo "<br />DEBUG :: lib_skills.php :: 248 <br />ELEMENTS<br />\n";
		//print_object($elements);
		//exit;

		if (!empty($elements)){

           	$elements['domainsselected'] = array(
                    'type' => 'hidden',
    	            'value' => $domainsselected,
       		);

           	$elements['nbskills'] = array(
       	            'type' => 'hidden',
           	        'value' => $nbskills,
            );
/*
// Inutile car ces informations ne servent à rien et ne sont pas réellement disponibles de façon unique
           	$elements['idtome'] = array(
       	            'type' => 'hidden',
           	        'value' => $idtome,
            );

           	$elements['idtab'] = array(
       	            'type' => 'hidden',
           	        'value' => $idtab,
            );
*/

            $elements['iduser'] = array(
       	            'type' => 'hidden',
           	        'value' => $USER->get('id'),
            );

			$elements['nbframes'] = array(
            		'type' => 'hidden',
        	    	'value' => $nb,
	    	);

       	    $elements['submit'] = array(
            		'type' => 'submitcancel',
        	    	'value' => array(get_string('save','artefact.booklet'), get_string('cancel')),
    	         	'goto' => get_config('wwwroot') . '/artefact/booklet/index.php',
	    	);

			$aform = array(
				'name' => 'selectskillsfromframes',
	        	'plugintype' => 'artefact',
    	    	'pluginname' => 'booklet',
          		'renderer' => 'table', // 'online' fait planter
    		    'method' => 'post',
        		'successcallback' => 'multiselectframes_submit',
				'elements' => $elements,
    		);

			$compositeform['selectskillsfromframes'] = pieform($aform);

		}
	}
    //echo "<br />DEBUG :: lib_skills.php :: 283 <br />COMPOSITEFORM<br />\n";
	//print_object($compositeform);
	//exit;

    return $compositeform;
}




// ------------------------------------------------------------------------------
function get_skillsframesform($domainsselected='', $skillsselected='' ) {
	global $USER;
    global $THEME;
	// DEBUG
	//echo "<br /> DEBUG :: lib.php :: 2548 :: <br />\n";
	//print_object($askill);
	//exit;

	$compositeform = array();
	$elements = array();
	$tab_skills_selected = array();

    $imageframesskills = $THEME->get_url('images/btn_show.png', false, 'artefact/booklet');
    $framesskillsstr = get_string('registeredframes','artefact.booklet');

	$thelink = '<a href="'.get_config('wwwroot').'/artefact/booklet/framesskills.php" target="_blank"><img src="'.$imageframesskills.'" alt="'.$framesskillsstr.'" title="'.$framesskillsstr.'" /></a> ';

    $fform = array(
        	'name' => 'framesskills',
	        'plugintype' => 'artefact',
    	    'pluginname' => 'booklet',
      		'renderer' => 'table', // 'online' fait planter
        	'method'      => 'post',
        	'successcallback' => 'framesskills_submit',
		    'elements' => array(
    	    	'optionnal' => array(
	    	    	'type' => 'fieldset',
		    		'name' => 'inputform',
					'title' => get_string ('selectframesfromskills', 'artefact.booklet'),
	        		'collapsible' => true,
    	        	'collapsed' => true,
	    	        'legend' => get_string('selectframesfromskills', 'artefact.booklet'),
            	    'elements' => array(
	            		'frames' => array(
	    	            	'type' => 'html',
            		    	'value' => get_string('gotoframes', 'artefact.booklet').' : '.$thelink,
                    		'description' => get_string('registeredframes', 'artefact.booklet'),
							'help' => false,
       	    			),
					),
/*
					'domainsselected' => array(
                		'type' => 'hidden',
            			'value' => $domainsselected,
	        		),

   		        	'idtab' => array(
       					'type' => 'hidden',
           				'value' => $idtab,
	   	        	),
*/
				),
			),
		);

    	$compositeform['framesform'] = pieform($fform);


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
	$sql = "SELECT DISTINCT domain FROM {artefact_booklet_skill} WHERE (owner = ? OR owner = ?) ORDER BY domain ASC";
    $domains = get_records_sql_array($sql, array(0, $USER->get('id')));
    //print_object($domains);
	//exit;

	if (!empty($domains)){
       	$nbdomains = count($domains);
		if ($nbdomains>1){
	    	$domain_options = array();
			$domain_selected = array();
			$d=1;
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
    	         	'goto' => get_config('wwwroot') . '/artefact/booklet/index.php',
	    	);


   	    	$domainchoice = array(
            	'name' => 'domainchoice',
	        	'plugintype' => 'artefact',
    	    	'pluginname' => 'booklet',
   	    	    // 'validatecallback' => 'validate_selectlist',
       	    	'successcallback' => 'selectsomedomainsframes_submit',
                'renderer' => 'table', // 'online' fait planter
               	'elements' => $elementdomains,
            );
   	    	$compositeform['domainchoice'] = pieform($domainchoice);
		}
	}

	if (!empty($list_of_domains_selected)){
            $where='';
			$params = array(0, $USER->get('id'));
			foreach($list_of_domains_selected as $d){
				if (!empty($where)){
					$where.=' OR domain = ? ';
				}
				else{
                    $where.=' domain = ? ';
				}
				$params[]= $domain_options[$d];
			}
			$sql = "SELECT * FROM {artefact_booklet_skill} WHERE (owner=? OR owner=?) AND ".$where." ORDER BY code ASC";
			$skills = get_records_sql_array($sql, $params);
	}
	else{
			$sql = "SELECT * FROM {artefact_booklet_skill} WHERE (owner=? OR owner=?) ORDER BY domain ASC, code ASC";
		    $skills = get_records_sql_array($sql, array(0, $USER->get('id')));
	}
	/*
			echo $sql;
			print_object($params);

			print_object($skills);
			exit;
	*/

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

			$elementsskills['threshold'] = array(
                'type' => 'checkbox',
                'help' => false,
                'title' => get_string('usethreshold','artefact.booklet'),
                'defaultvalue' => 1,
                'description' => get_string('usethresholddesc','artefact.booklet'),
        	);

            $elementsskills['domainsselected'] = array(
                    'type' => 'hidden',
                    'value' => $domainsselected,
            );

    	    $choice = array(
                'name' => 'listchoice',
                'plugintype' => 'artefact',
                'pluginname' => 'booklet',
        	    // 'validatecallback' => 'validate_selectlist',
            	'successcallback' => 'selectskillsframes_submit',
                'renderer' => 'table', // 'online' fait planter,
                'elements' => $elementsskills,
            );
        	$compositeform['choice'] = pieform($choice);
	}



		//print_object($compositeform);
		//exit;
    	return $compositeform;
}


// ----------------------------------------------------------------------------
function get_skillsform($idtab, $domainsselected='', $skillsselected='' ) {
	global $USER;

	// DEBUG
	//echo "<br /> DEBUG :: lib_skills.php :: 530 :: DOMAINSELECTED : $domainsselected\n";
	//exit;
    $compositeform = array();
	$elements = array();
	$tab_skills_selected = array();
    $designer = get_record('artefact_booklet_designer', 'id', $USER->get('id'));

	if (empty($domainsselected)){
    	$domainsselected='any';
	}

	// Domains
    $list_of_domains_selected = array();

    if (!empty($domainsselected) && ($domainsselected!='any')){
		$tab_domainsselected = explode('-', $domainsselected);
        // DEBUG
		//echo "<br /> DEBUG :: libskills.php :: 548 :: <br />TAB DOMAINSELECTED\n";
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
    $params=array();
	if ($designer){
		$sql = "SELECT DISTINCT domain FROM {artefact_booklet_skill} ORDER BY domain ASC";

	}
	else{
        $sql = "SELECT DISTINCT domain FROM {artefact_booklet_skill} WHERE owner = ? ORDER BY domain ASC";
        $params=array($USER->id);
	}
    $domains = get_records_sql_array($sql, $params);
    //print_object($domains);
	//exit;

	if (!empty($domains)){
       	$nbdomains = count($domains);
		//echo "DEBUG <br />NBDOMINS : $nbdomains      \n";
		//exit;
		if ($nbdomains>1){
	    	$domain_options = array();
			$domain_selected = array();
			$d=1;
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
    	       	    'renderer' => 'table', // 'online' fait planter
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
				$params[] = $domain_options[$d];
			}

			if ($designer){
       			$sql = "SELECT * FROM {artefact_booklet_skill} WHERE ".$where." ORDER BY code ASC";
			}
			else{
    	    	$sql = $sql = "SELECT * FROM {artefact_booklet_skill}  WHERE (".$where.") AND (owner = ?) ORDER BY domain ASC, code ASC";
        		$params[] = $USER->id;
			}

		    $skills = get_records_sql_array($sql, $params);
	}
	else{
        $params=array();
		if ($designer){
   			$sql = "SELECT * FROM {artefact_booklet_skill} ORDER BY domain ASC, code ASC";
		}
		else{
    	    $sql = $sql = "SELECT * FROM {artefact_booklet_skill} WHERE owner = ? ORDER BY domain ASC, code ASC";
        	$params=array($USER->id);
		}

		$skills = get_records_sql_array($sql, $params);
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
   	           'type' => 'submitcancel',
               'value' => array(get_string('savechecklist','artefact.booklet'), get_string('cancel')),
               'goto' => get_config('wwwroot') . '/artefact/booklet/index.php?id='.$idtab,
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
                'renderer' => 'table', // 'online' fait planter
                'elements' => $elementsskills,
            );
        	$compositeform['choice'] = pieform($choice);
	}


	$aform = array(
			'name' => 'addskillform',
            'plugintype' => 'artefact',
    	    'pluginname' => 'booklet',
            'renderer' => 'table', // 'online' fait planter
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
                      		'type' => 'textarea',
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
      		'renderer' => 'table', // 'online' fait planter
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
function selectsomedomainsframes_submit(Pieform $form, $values) {
    global $SESSION;
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
            $goto = get_config('wwwroot') . '/artefact/booklet/selectframesskills.php?domainsselected='.$domainsselected;
		}
		else{
			$goto = get_config('wwwroot') . '/artefact/booklet/selectframesskills.php?domainsselected=';
		}
	}
	else{
        $goto = $_SERVER['HTTP_REFERER'];
	}
	redirect($goto);
}


// -------------------------------------
function selectsomedomains_submit(Pieform $form, $values) {
    global $SESSION;
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
			$goto = get_config('wwwroot') . '/artefact/booklet/manageskills.php?idtab='.$values['idtab'];
		}
	}
	else{
        $goto = $_SERVER['HTTP_REFERER'];
	}
	redirect($goto);
}


// -------------------------------------
function selectskillsframes_submit(Pieform $form, $values) {
    global $SESSION;
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
			$goto = get_config('wwwroot').'/artefact/booklet/displayframesskills.php?domainsselected='.$values['domainsselected'].'&skillsselected='.$skillsselected.'&thresholdactive='.$values['threshold'];
		}
	}
	redirect($goto);
}


// -------------------------------------
function multiselectframes_submit(Pieform $form, $values) {
    global $SESSION;
	global $USER;
    $skillslist='';
    $str='';
    $t_framesselected=array();
	// DEBUG
	//echo "<br />DEBUG :: lib_skills.php :: 1021 :: <br />VALUES : <br />\n";
	//print_object($values);
	//exit;

	if (!empty($values['nbskills'])){
 		for ($i=0; $i<$values['nbskills']; $i++){
			if (!empty($values['skillid'.$i])){
            	$skillslist.=$values['skillid'.$i].',';
			}
		}
        $skillslist=substr($skillslist,0,strlen($skillslist)-1);
	}

	if (!empty($values['nbframes'])){
 		for ($i=0; $i<$values['nbframes']; $i++){
			if (!empty($values['select'.$i])){
            	$t_framesselected[]=$values['idframe'.$i];
			}
		}

		// DEBUG
		//echo "<br />DEBUG :: lib_skills.php :: 1042 :: <br />T_FRAMESELECTED <br />\n";
		//print_object($t_framesselected);
		//exit;
		// Creer les associations
		if ($t_framesselected){
			foreach ($t_framesselected as $idframe){
            	try {
					// Creer l'association

					// scale verification
    	    		if (!empty($skillslist) && preg_match("/;/", $skillslist)){
						$skillslist = str_replace(';',',',$this->get('skillslist'));
					}
					/*
					if (!empty($skillslist)){
  						$t_skills=explode(",", $skillslist);
						if (!empty($t_skills)){
							foreach ($t_skills as $idskill){
								if ($skill = get_record('artefact_booklet_skill', 'id', $idskill)){
		            	            $str .= " ".$skill->id." :: ".$skill->domain." :: ".$skill->code.",";
									//$str .= "<br />$skill->description."\n";
								}
							}
						}
					}
					*/

        			$str_title = '';

	        		$idtab = 0;
    	            $idtome = 0;
					if (!empty($idframe)){
						if ($frame = get_record('artefact_booklet_frame', 'id',  $idframe)){
							// Recuperer Livret et Page
        		    	    if ($tab = get_record('artefact_booklet_tab', 'id',  $frame->idtab)){
                        	    $idtab = $tab->id;
                                $idtome = $tab->idtome;
                                if ($atome= get_record('artefact_booklet_tome', 'id', $tab->idtome)){
                                	$str_title .=  strip_tags($atome->title);
								}
                                if (!empty($str_title)){
									$str_title .=  ' :: '.strip_tags($tab->title);
								}
								else{
                                    $str_title .=  strip_tags($tab->title);
								}
							}

							if (!empty($str_title)){
								 $str_title .= ' :: '.strip_tags($frame->title);
							}
							else{
                                $str_title .= strip_tags($frame->title);
							}
							// DEBUG
							//echo "<br>$str_title\n";
							//exit;

			                $skilltoframe = new stdclass();
        			        $skilltoframe->description = $idframe;
            	    		$skilltoframe->note = $idtome;
		        	        $skilltoframe->owner = $USER->get('id');
        		    	    $skilltoframe->author = $USER->get('id');
                			$skilltoframe->title = $str_title. ' ['.$skillslist.']';

							if ($rec_skframe = get_record('artefact', 'artefacttype', 'skillframe', 'description', $idframe, 'owner', $USER->get('id'))){
    	    		            $id = $rec_skframe->id;
							}
							else{
								$id = 0;
							}
							// DEBUG
							//echo "<br />DEBUG :: lib_skills.php :: 1095:: <br />SKILLTOFRAME<br />\n";
							//print_object($skilltoframe);
							//exit;

        	    	    	if ($artefact = new ArtefactTypeSkillFrame($id, $skilltoframe)){
     	    	    		    $artefact->commit();
								// DEBUG
								//echo "<br />DEBUG :: lib_skills.php :: 966 :: <br />SKILLSLIST : $skillslist <br />\n";
								//print_object($artefact);
								//exit;

							}
						}
					}
		    	}
    			catch (Exception $e) {
        			$SESSION->add_error_msg(get_string('skillframesavefailed', 'artefact.booklet'));
    			}
			}
		}
	}
	else{
    	$SESSION->add_error_msg(get_string('skillframedoesnotexist', 'artefact.booklet'));
	}


    //$goto = get_config('wwwroot') . 'artefact/booklet/index.php?tab=' . $idtab . '&browse=1';
    $goto = get_config('wwwroot') . 'artefact/booklet/index.php';
	redirect($goto);
}

// -------------------------------------
function selectskills_submit(Pieform $form, $values) {
    global $SESSION;
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
	else{
		$goto = get_config('wwwroot').'/artefact/booklet/index.php?idtab='.$values['idtab'];
	}
	redirect($goto);
}


// ----------------------------------------------
function askill_submit(Pieform $form, $values){
    global $SESSION;
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
    global $SESSION;
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
    	    	            'type' => 'textarea',
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
          		'renderer' => 'table', // 'online' fait planter
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
    global $SESSION;
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




// -----------------------------
    function display_a_frame($idtome, $idframe) {
        // Affiche une frame en fonction du type de l'objet
        global $USER, $THEME;
        $elements = array();
        $components = array();
        $rslt = '';

		if ($idframe){
            // DEBUG
			//echo "<br />DEBUG :: lib_skills.php :: display_a_frame :: 1710<br />IDFRAME : ".$idframe."<br />FRAME : <br />\n";
			if ($frame = get_record('artefact_booklet_frame', 'id', $idframe)){
				//print_object($frame);
				if (!empty($idtome)){
					if ($tome = get_record('artefact_booklet_tome', 'id', $idtome)){
						$rslt .= "<p>".get_string('tometitle','artefact.booklet').' <b>'.strip_tags($tome->title)."</b></p>\n";
					}
				}
				if (!empty($frame->idtab)){
					if ($tab = get_record('artefact_booklet_tab', 'id', $frame->idtab)){
						$rslt .= "<p>".get_string('tabtitle','artefact.booklet').' <b>'.strip_tags($tab->title)."</b></p>\n";
					}
				}

				if (!empty($rslt)){
					$components['tome' . $frame->id] =  array(
                           	       	'type' => 'html',
                            	   	'title' => get_string('tometitle', 'artefact.booklet'),
    	                            'value' =>  $rslt,
   	    	        );
				}


       	        $pf = null;
                if (!$frame->list) { // ce n'est pas une liste
                    if ($objects = get_records_array('artefact_booklet_object', 'idframe', $frame->id, 'displayorder')){
		               	// liste des objets du frame ordonnes par displayorder

   	                	foreach ($objects as $object) {
       	                	$help = ($object->help != null);

    	       	            if ($object->type == 'longtext') {
               	            	$val = null;
                       	        // ce n'est pas une liste : rechercher le contenu du champ texte
                           	    $sql = "SELECT * FROM {artefact_booklet_resulttext} WHERE idobject = ? AND idowner = ?";
                               	if ($vals = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
									$val = $vals[0];
	                            }
							   	$components['lt' . $object->id] =  array(
                           	       	'type' => 'html',
                            	   	'title' => $object->title,
	                               	'help' => $help,
    	                            'value' => ((!empty($val)) ? $val->value : NULL),
   	    	                    );
				        	}
               	        	else if ($object->type == 'area') {
                   	        	$val = null;
                           	    $sql = "SELECT * FROM {artefact_booklet_resulttext} WHERE idobject = ? AND idowner = ?";
                               	if ($vals = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
                                	$val = $vals[0];
								}
                       	        $components['ta' . $object->id] =  array(
                           	        'type' => 'html',
                               	    'title' => $object->title,
                                   	'help' => $help,
                                    'value' => ((!empty($val)) ? $val->value : NULL),
   	                            );
       	                    }

	                	    else if ($object->type == 'htmltext') {
    	                	    $val = null;
                                $sql = "SELECT * FROM {artefact_booklet_resulttext} WHERE idobject = ? AND idowner = ?";
                               	if ($vals = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
	                               	$val = $vals[0];
    	                       	}
                                $components['ht' . $object->id] =  array(
                                    'type' => 'html',
                               	    'title' => $object->title,
                                   	'help' => $help,
	                                'value' => ((!empty($val)) ? $val->value : NULL),
    	                        );
        	                }
                            else if ($object->type == 'reference') {
	        	                    $val = null;
    	                            $str_reference = '';
									if ($reference = get_record('artefact_booklet_reference', 'idobject', $object->id)){
										if ($objectlinked = get_record('artefact_booklet_object', 'id', $reference->idobjectlinked)){
											if ($referenceframe = get_record('artefact_booklet_frame', 'id', $objectlinked->idframe)){
												if ($notframelist){
													$str_reference = display_object_linked($reference->idobjectlinked, $USER->get('id')).' <a href="'.get_config('wwwroot').'/artefact/booklet/index.php?idframe='.$referenceframe->id.'&tab='.$referenceframe->idtab.'&okdisplay=1"><img src="'.$imagelinked.'" alt="'.$objectlinkedstr.'" title="'.$objectlinkedstr.'" /></a>';
												}
            									else if ($objmodifinframe) {
													if ($record){
														$str_reference = display_object_linked($reference->idobjectlinked, $USER->get('id')).' <a href="'.get_config('wwwroot').'/artefact/booklet/index.php?idframe='.$referenceframe->id.'&tab='.$referenceframe->idtab.'&okdisplay=1"><img src="'.$imagelinked.'" alt="'.$objectlinkedstr.'" title="'.$objectlinkedstr.'" /></a>';
													}
												}
											}
											if (!$str_reference){
    	        			    	        	$str_reference = get_string('referencehasnovalue','artefact.booklet'). ' <b>'. $objectlinked->title .'</b> '.get_string('offrame','artefact.booklet'). ' <i>'.$referenceframe->title.'</i>';
											}
										}
          								if ($str_reference){
		                	                $components['ref' . $object->id] =  array(
   			                	                'type' => 'html',
           		    	        	            'title' => $object->title,
               		            		        'value' => $str_reference,
                   		            		);
										}
                    	    	    }
							}

                	    	else if ($object->type == 'synthesis') {
                   	        	$val = null;

                           	    $sql = "SELECT * FROM {artefact_booklet_resulttext} WHERE idobject = ? AND idowner = ?";
                               	if ($vals = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
                                	$val = $vals[0];
                           		}
								$rec = false;
       	                	    if (!is_null($record)) {
           	                	    $rec = true;
	           	                }
       	                        $components['ta' . $object->id] =  array(
           	                        'type' => 'html',
               	                    'title' => $object->title,
                   	                'help' => $help,
                       	            'value' => ((!empty($val)) ? $val->value : NULL),
                           	    );
    	                	}
        	            	else if ($object->type == 'shorttext') {
            	        		$val = null;

                    	    	$sql = "SELECT * FROM {artefact_booklet_resulttext} WHERE idobject = ? AND idowner = ?";
                        	    if ($vals = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
                            		$val = $vals[0];
	                            }

							    $components['st' . $object->id] =  array(
                       	            'type' => 'html',
                           	        'title' => $object->title,
                               	    'help' => $help,
                                   	'value' => ((!empty($val)) ? $val->value : NULL),
                                );
   	                        }

           	            	else if ($object->type == 'listskills') {
							// DEBUG
							//echo "<br />lib.php :: 2783<br />\n";
							//print_object($object);
							//exit;

								if ($list = get_record('artefact_booklet_list', 'idobject', $object->id)){
      	        	        		$components['ls' . $object->id] = array(
          	    	        	      	'type' => 'html',
	        	        	            'title' => $object->title,
   		        	        	        'value' => $list->description,
       	    	    	            );

            	                    if ($res_lofskills = get_records_array('artefact_booklet_listofskills', 'idlist', $list->id)){
										foreach ($res_lofskills as $res){
                                            $header = false;
											$hidden = false;
											if ($skill = get_record('artefact_booklet_skill', 'id', $res->idskill)){
												// donnees saisies
                                                $sdescription = strip_tags($skill->description);
												switch ($skill->type){
													case 0 : $header = true; break;
                                            		case 2 : $hidden = true; break;
													default : break;
												}
    	   										$index = 0;
    	                		                $rec=null;
            	           	                    $sql = "SELECT * FROM {artefact_booklet_lskillsresult} WHERE idobject = ? AND idowner = ? and idskill = ?";
	            	   		           	    	if ($recs = get_records_sql_array($sql,  array($object->id, $USER->get('id'), $skill->id))){
               	    	                   	    	$rec = $recs[0]; // le premier suffit car le cadre n'est pas une liste
												}
												if ($rec){
													$index = $rec->value - 1;
												}
												// la boîte de saisie
           	                               		if (!$header){
													if ($str_choice = get_skill_choice_display($skill, $index)){
		        										$components['rl' . $object->id.'_'.$list->id.'_'.$skill->id] = array(
			        	        	        	      		'type' => 'html',
				    		                	       		'title' => $skill->domain.' :: '.$skill->code,
    		    				                        	'value' => $sdescription."<br />".$str_choice.'<hr />',
															//'description' => $skill->description,
	    	        				               		);
													}
												}
												elseif (!$hidden){
                                                    $str_choice = '';
                                                    $sdescription = '<span class="blueback">'.$sdescription.'</span>';
											        $components['rl' . $object->id.'_'.$list->id.'_'.$skill->id] = array(
	        	        	        	     	 		'type' => 'html',
			    		                	       		'title' => $skill->domain.' :: '.$skill->code,
    			    		                        	'value' => $sdescription.'<hr />',
														//'description' => $skill->description,
	    	        			               		);

												}

											}
										}
									}
								}
               				}

            	        	else if ($object->type == 'radio') {
							// DEBUG
							//echo "<br />lib.php :: 1785<br />\n";
							//print_object($object);
							//exit;
   	                        	$val = null;
       	                    	if (count_records('artefact_booklet_radio', 'idobject', $object->id) != 0) {
           	                    	if ($res = get_records_array('artefact_booklet_radio', 'idobject', $object->id)){
                                                    	                	$sql = "SELECT * FROM {artefact_booklet_resultradio} WHERE idobject = ? AND idowner = ?";
                    	                if ($vals = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
   	                    	            	$val = $vals[0];
        	                    	    }
										if ($val){
	            	            	        $strradio = '';
   		            	            	    foreach ($res as $value) {
												if (!empty($value)){
													if (!empty($strradio)){
               		            	        	    	$strradio .= ' | ';
													}
   		            		            	        if ($value->id == $val->idchoice){
														$strradio .= '<b>'.$value->option. '</b> ';
													}
													else{
   	                		            		        $strradio .= $value->option.' ';
													}
	        	                    		    }
											}

          	        	                	$components['ra' . $object->id] = array(
               	        	               		'type' => 'html',
                	        	               	'help' => $help,
   	                	        	           	'title' => $object->title,
       	                	        	       	'value' => ((!empty($val)) ? $strradio : NULL),
            	                	       );

   	            	                	}
									}
								}
							}

                       		else if ($object->type == 'checkbox') {
                           		$val = null;

   	                            $sql = "SELECT * FROM {artefact_booklet_resultcheckbox} WHERE idobject = ? AND idowner = ?";
       	                        if ($vals = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
           	                    	$val = $vals[0];
								}
							    $components['cb' . $object->id] = array(
       	                            'type' => 'html',
           	                        'help' => $help,
               	                    'title' => $object->title,
                   	                'value' => ((!empty($val)) ? $val->value : NULL),
                       	        );
                           	}

   	                    	else if ($object->type == 'date') {
       	                    	$val = null;

               	                $sql = "SELECT * FROM {artefact_booklet_resultdate} WHERE idobject = ? AND idowner = ?";
                   	            if ($vals = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
                       	        	$val = $vals[0];
                           		}
								$components['da' . $object->id] = array(
                   	                'type' => 'html',
                       	            'value' => ((!empty($val)) ? date("m/d/Y",strtotime($val->value)) : date("m/d/Y",time())),
                           	        'title' => $object->title,
                               	    'description' => get_string('dateofbirthformatguide'),
                                );
   	                        }

							else if ($object->type == 'attachedfiles') {
                       	        if ($vals = get_column('artefact_booklet_resultattachedfiles', 'artefact',  'idobject', $object->id, 'idowner', $USER->get('id'))){
									$strfiles='';
									foreach ($vals as $val){
										if (!empty($val)){
											if ($artefactfile=get_record('artefact', 'id', $val)){
												$strfiles.= '<a target="_blank" href="'.get_config('wwwroot').'/artefact/file/download.php?file='.$val.'">'.$artefactfile->title.'</a> ';
											}
										}
									}

								    $components['af' . $object->id] =  array(
   	                	                'type' => 'html',
       	                	            'title' => $object->title,
           	                	        'help' => $help,
               	                	    'value' => $strfiles,
                	                );
								}
					        }

                        	else if ($object->type == 'freeskills') {
                	    		$vals = array();
                    	    	if (!$frame->list) {
                        	        $sql = "SELECT * FROM {artefact_booklet_frskllresult} WHERE idobject = ? AND idowner = ?";
                            	    $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
								}
       							else {
                        	        $sql = "SELECT * FROM {artefact_booklet_frskllresult} WHERE idrecord = ? AND idobject = ? AND idowner = ?";
                            	    $vals = get_records_sql_array($sql, array($record->idrecord, $object->id, $USER->get('id')));
								}
								//print_object($vals);
								//exit;
                            	//if ($notframelist || !$objmodifotherframe) {
/*
	                           		$alink = '<a href="'.get_config('wwwroot').'/artefact/booklet/freeskills.php?id='.$object->id.'&idrecord='.$idrecord.'&domainsselected=0"><img src="'.$imageaddfreeskills.'" alt="'.$addfreeskillsstr.'" title="'.$addfreeskillsstr.'" /></a> ';
				        	    	if ($notframelist || !$objmodifotherframe) {
        	                			$components['frsk' . $object->id] = array(
                            	        	'type' => 'html',
                	                    	'title' => $object->title,
               	                	    	'value' => $alink,
            	    	            	);
									}
*/

									if ($vals){
										//print_object($vals);
										//exit;
										foreach ($vals as $val){
	                                        $header = false;
											$hidden = false;
											if ($skill = get_record('artefact_booklet_skill', 'id', $val->idskill)){
												// donnees saisies
                	                            switch ($skill->type){
													case 0 : $header = true; break;
                        	                   		case 2 : $hidden = true; break;
													default : break;
												}

												$scale = $skill->scale;
												$domain = $skill->domain;
												$sdescription = strip_tags($skill->description);
    	                                        $code = $skill->code;
												$threshold = $skill->threshold;
            	                                $str_skill = "$domain::$code";

												$index = $val->value - 1;

												// la boîte de saisie
           	            	                   	if (!$header){
													if ($str_choice = get_skill_choice_display($skill, $index)){
	        	           								$components['frsk' . $object->id.'_'.$skill->id] = array(
		        		        	        	      		'type' => 'html',
			    			                	       		'title' => $skill->domain.' :: '.$skill->code,
    			    			                        	'value' => $sdescription."<br />".$str_choice.'<hr />',
															//'description' => $skill->description,
	    	        					               	);
													}
												}
												elseif (!$hidden){
                                            	    $str_choice = '';
                                                	$sdescription = '<span class="blueback">'.$sdescription.'</span>';
        	           								$components['frsk' . $object->id.'_'.$skill->id] = array(
	        		        	        	      		'type' => 'html',
		    			                	       		'title' => $skill->domain.' :: '.$skill->code,
    		    			                        	'value' => $sdescription.'<hr />',
														//'description' => $skill->description,
	    	        				               	);
												}
											}
										}
									}
								//}
							}
						} // fin de for each objects
					} // Fin de if objects
				}   // fin de la frame n'est pas une liste


				else {  // La frame est une liste
				// ********************************************************************************************************
				//                               LISTE
				// ********************************************************************************************************
                    //echo "<br />FRAME IS LIST <br />OBJECTS : \n";
 					$vertical=false;
					$separateur='';
					$intitules = array();
       				$nbrubriques=0;
					$lastposition = array();
                    $rslt='';
                    /*
					$edit_link=array();
					$edit_link1 = '<th><a href="'.get_config('wwwroot').'/artefact/booklet/index.php?idframe='.$frame->id.'&tab='.$idtab.'&idmodifliste=';
					$edit_link2 = '&okdisplay=0"><img src="'.$imageedit.'" alt="'.$editstr.'" title="'.$editstr.'" /></a></th>'."\n";
                    */
					$color=array();

           			if ($objectslist = get_records_array('artefact_booklet_object', 'idframe', $frame->id, 'displayorder')){
                    	//echo "<br />OBJECTSLIST :<br /> \n";
						//print_object($objectslist);
						//exit;


						// headers
   	    			    $pos=0;
						foreach ($objectslist as $object) {
           	    			$key=$object->id;
            		        $intitules[$key]= $object->title;
            		        $lastposition[$key]=false;
							// si une liste de competences est présente on n'affiche pas en tableau
							if (($object->type == 'listskills') || ($object->type == 'freeskills')){
                		    	$vertical = true;
							}
						}

               			$lastposition[$key]=true;
               			$nbrubriques=count($intitules);
				    	$vertical = ($nbrubriques>5) ? true : $vertical;
   	            		$separateur=($vertical)? '</tr><tr>' : '';
						$n=0;
						$rslt .= "\n<table class=\"tablerenderer3\">";
						if (!$vertical){
							$rslt .= "<thead>\n<tr>";
        	    	       	foreach ($objectslist as $object) {
				          		$rslt .= "<th>". $object->title . "</th>";
							}
                            $rslt .= "<th>&nbsp;</th>"; // derniere case
							$rslt .= "</tr></thead>";
						}


						// calcul du nombre d'elements de la liste
						switch ($objectslist[0]->type) {
					                case 'longtext':
					                case 'shorttext':
					                case 'area':
					                case 'htmltext':
	            		            	$n = count_records('artefact_booklet_resulttext', 'idobject', $objectslist[0]->id, 'idowner', $USER->get('id'));
/*
	            	    	        	$sql = "SELECT re.idrecord FROM {artefact_booklet_resulttext} re
	            	            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";
*/
	            	    	        	$sql = "SELECT re.idrecord FROM {artefact_booklet_resulttext} re, {artefact_booklet_resultdisplayorder} rd
	            	            	        WHERE (re.idrecord = rd.idrecord)
											AND (re.idowner = rd.idowner)
	            	            	        AND re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";
	            	        	    	$listidrecords = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id')));
	            	            		break;
					                case 'radio':
	            		            	$n = count_records('artefact_booklet_resultradio', 'idobject', $objectslist[0]->id, 'idowner', $USER->get('id'));
/*
	            	    	        	$sql = "SELECT re.idrecord FROM {artefact_booklet_resultradio} re
	            	            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";
*/
											$sql = "SELECT re.idrecord, rd.displayorder
											FROM {artefact_booklet_resultradio} re, {artefact_booklet_resultdisplayorder} rd
											FROM {artefact_booklet_resultcheckbox} re, {artefact_booklet_resultdisplayorder} rd
	            	            	        WHERE (re.idrecord = rd.idrecord)
											AND (re.idowner = rd.idowner)
	            	            	        AND re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";
	            	        	    	$listidrecords = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id')));
	            	            		break;
					                case 'checkbox':
	            		            	$n = count_records('artefact_booklet_resultcheckbox', 'idobject', $objectslist[0]->id, 'idowner', $USER->get('id'));
/*
	            	    	        	$sql = "SELECT re.idrecord FROM {artefact_booklet_resultcheckbox} re
	            	            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";
*/
	            	    	        	$sql = "SELECT re.idrecord, rd.displayorder
											FROM {artefact_booklet_resultcheckbox} re, {artefact_booklet_resultdisplayorder} rd
	            	            	        WHERE (re.idrecord = rd.idrecord)
											AND (re.idowner = rd.idowner)
	            	            	        AND re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";

	            	        	    	$listidrecords = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id')));
	            	            		break;
					                case 'date':
	            		            	$n = count_records('artefact_booklet_resultdate', 'idobject', $objectslist[0]->id, 'idowner', $USER->get('id'));
/*
	            	    	        	$sql = "SELECT re.idrecord FROM {artefact_booklet_resultdate} re
	            	            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";
*/	            	    	        	$sql = "SELECT re.idrecord, rd.displayorder
											FROM {artefact_booklet_resultdate} re, {artefact_booklet_resultdisplayorder} rd
	            	            	        WHERE (re.idrecord = rd.idrecord)
											AND (re.idowner = rd.idowner)
	            	            	        AND re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";

	            	        	    	$listidrecords = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id')));
	            	            		break;
					                case 'attachedfiles':
	            		            	$n = count_records('artefact_booklet_resultattachedfiles', 'idobject', $objectslist[0]->id, 'idowner', $USER->get('id'));
	            	    	        	// TO DO : ne compter que les records ayant un idrecord different
	            	        	    	break;
					                case 'listskills':
	            		            	$n = count_records('artefact_booklet_lskillsresult', 'idobject', $objectslist[0]->id, 'idowner', $USER->get('id'));
/*
	            	    	        	$sql = "SELECT re.idrecord FROM {'artefact_booklet_lskillsresult'} re
	            	            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";
*/
	            	    	        	$sql = "SELECT re.idrecord, rd.displayorder
											FROM {'artefact_booklet_lskillsresult'} re, {artefact_booklet_resultdisplayorder} rd
	            	            	        WHERE (re.idrecord = rd.idrecord)
											AND (re.idowner = rd.idowner)
	            	            	        AND re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";
	            	        	    	$listidrecords = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id')));
	            	            		break;
					                case 'reference':
	            		            	$n = count_records('artefact_booklet_refresult', 'idobject', $objectslist[0]->id, 'idowner', $USER->get('id'));
/*
	            	    	        	$sql = "SELECT re.idrecord FROM {'artefact_booklet_refresult'} re
	            	            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";
*/
	            	    	        	$sql = "SELECT re.idrecord, rd.displayorder
											FROM {'artefact_booklet_refresult'} re, {artefact_booklet_resultdisplayorder} rd
	            	            	        WHERE (re.idrecord = rd.idrecord)
											AND (re.idowner = rd.idowner)
	            	            	        AND re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";

	            	        	    	$listidrecords = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id')));
	            	            		break;
					                case 'freeskills':
	            		            	//$n = count_records('artefact_booklet_frskllresult', 'idobject', $objectslist[0]->id, 'idowner', $USER->get('id'));
	            	    	        	$sql = "SELECT DISTINCT idrecord FROM {artefact_booklet_frskllresult}
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?";
                                        $n = count($recs = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id'))));

/*
	            	    	        	$sql = "SELECT re.idrecord FROM {artefact_booklet_frskllresult} re
	            	            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";
*/
	            	    	        	$sql = "SELECT re.idrecord, rd.displayorder
											FROM {artefact_booklet_frskllresult} re , {artefact_booklet_resultdisplayorder} rd
	            	            	        WHERE (re.idrecord = rd.idrecord)
											AND (re.idowner = rd.idowner)
	            	            	        AND re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";

	            	        	    	$listidrecords = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id')));
	            	            		break;

            			} // Fin du switch

                        //echo "<br /> DEBUG :: 2283 :: LISTIDRECORDS\n";
						//print_object($listidrecords);
						//exit;

						// construction d'un tableau des lignes : une par element, chaque ligne contient les valeurs de tous les objets
					    $ligne = array();
						for ($i = 0; $i <= $n; $i++) {
            	   			$ligne[$i] = "";
                            $edit_link[] = 10000000000000000000;
                            $color[$i] = $i % 2;
					    }
                        //echo "<br /> DEBUG :: 2098 :: N : $n<br />\n";

						//print_object($color);
						//exit;

						// pour chaque objet, on complete toutes les lignes
				        foreach ($objectslist as $object) {


				    	    if ($object->type == 'longtext' || $object->type == 'shorttext' || $object->type == 'area'
												|| $object->type == 'htmltext' || $object->type == 'synthesis') {
/*
            		    	    $sql = "SELECT re.*, rd.displayorder FROM {artefact_booklet_resulttext} re
 JOIN {artefact_booklet_resultdisplayorder} rd
 ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
 WHERE re.idobject = ?
 AND re.idowner = ?
 ORDER BY rd.displayorder";
 */
            		    	    $sql = "SELECT re.*, rd.displayorder FROM {artefact_booklet_resulttext} re, {artefact_booklet_resultdisplayorder} rd
 WHERE (re.idrecord = rd.idrecord)
 AND (re.idowner = rd.idowner)
 AND re.idobject = ?
 AND re.idowner = ?
 ORDER BY rd.displayorder";

		                        if ($txts = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
									//echo "<br /> DEBUG :: 4479\n";
									//print_object($txts);
									//exit;

		                        	$i = 0;
					           	    foreach ($txts as $txt) {
										if (!empty($txt)){
	                                        if ($txt->id < $edit_link[$i]){
												$edit_link[$i]=$txt->id; // recuperer la valeur courante de $idmodifliste a savoir l'id de artefact_booklet_resulttext
											}

											if ($vertical){
						           	        	$ligne[$i].= "<th class=\"tablerenderer".$color[$i]."\">".$intitules[$object->id]. "</th>";
											}
											$ligne[$i].="<td class=\"tablerenderer3\">". $txt->value . "</td>";
											if ($vertical){
												if (!$lastposition[$object->id]){
													$ligne[$i].=$separateur;
												}
												else{
	                        		        		$ligne[$i].="</tr><tr><th  class=\"tablerenderer3\" colspan=\"2\"><hr /></th>";
												}
											}
											else if ($lastposition[$object->id]){
        		                                    //$ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
											}
                                            $i++ ;
										}
	    				    		}
                				}
							}
                            else if ($object->type == 'reference') {
								if (!empty($listidrecords)){
			                        $i = 0;
		    		                foreach ( $listidrecords as $a_record){
	    	                            $str_reference = '';
										if ($reference = get_record('artefact_booklet_reference', 'idobject', $object->id)){
											if ($objectlinked = get_record('artefact_booklet_object', 'id', $reference->idobjectlinked)){
												if ($referenceframe = get_record('artefact_booklet_frame', 'id', $objectlinked->idframe)){
													if ($notframelist){
														$str_reference = display_object_linked($reference->idobjectlinked, $USER->get('id')).' <a href="'.get_config('wwwroot').'/artefact/booklet/index.php?idframe='.$referenceframe->id.'&tab='.$referenceframe->idtab.'&okdisplay=1"><img src="'.$imagelinked.'" alt="'.$objectlinkedstr.'" title="'.$objectlinkedstr.'" /></a>';
													}
            										else if ($objmodifinframe) {
														if ($record){
															$str_reference = display_object_linked($reference->idobjectlinked, $USER->get('id')).' <a href="'.get_config('wwwroot').'/artefact/booklet/index.php?idframe='.$referenceframe->id.'&tab='.$referenceframe->idtab.'&okdisplay=1"><img src="'.$imagelinked.'" alt="'.$objectlinkedstr.'" title="'.$objectlinkedstr.'" /></a>';
														}
													}
												}
												if (!$str_reference){
    	        			    	        		$str_reference = get_string('referencehasnovalue','artefact.booklet'). ' <b>'. $objectlinked->title .'</b> '.get_string('offrame','artefact.booklet'). ' <i>'.$referenceframe->title.'</i>';
												}
											}
          									if ($str_reference){
        	                		            if ($vertical){
        			    	    	   		   	$ligne[$i].= "<th class=\"tablerenderer2\">".$intitules[$object->id]. "</th>";
												}
			        			   		    	$ligne[$i].= "<td class=\"tablerenderer2\">".$str_reference. "</td>";
												if ($vertical){
													if (!$lastposition[$object->id]){
														$ligne[$i].=$separateur;
													}
													else{
            		    		        				$ligne[$i].="</tr><tr><th colspan=\"2\"><hr /></th>";
													}
												}
												else if ($lastposition[$object->id]){
													//$ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
												}
											}
											$i++;
                	    	    	    }
									}
								}
							}

                        	else if ($object->type == 'freeskills') {
                                $vals = array();
                            	$i = 0;
								if ($vertical){
									$ligne[$i].= "<th class=\"tablerenderer3\">".$intitules[$object->id]. "</th>";
								}

                                //$ligne[$i].= "<td class=\"tablerenderer3\">\n<table class=\"tablerenderer3\">\n";
                                $ligne[$i].= "<td class=\"tablerenderer3\">\n<ul>\n";
								// ATTENTION : Il y a un regroupement par idrecord
								$str_skills='';
 /*
                           		$sql = "SELECT re.*, rd.displayorder FROM {artefact_booklet_frskllresult} re
 JOIN {artefact_booklet_resultdisplayorder} rd
 ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
 WHERE re.idobject = ?
 AND re.idowner = ?
 ORDER BY re.idrecord, rd.displayorder";
 */
                            		$sql = "SELECT re.*, rd.displayorder FROM {artefact_booklet_frskllresult} re, {artefact_booklet_resultdisplayorder} rd
 WHERE (re.idrecord = rd.idrecord) AND (re.idowner = rd.idowner)
 AND re.idobject = ?
 AND re.idowner = ?
 ORDER BY re.idrecord, rd.displayorder";

                           		$vals = get_records_sql_array($sql,  array($object->id, $USER->get('id')));
								if (!empty($vals)){
   									$seriecourante=$vals[0]->idrecord;
                                	foreach ($vals as $val){
	                                    if ($val){
											if ($val->idrecord != $seriecourante){    // ATTENTION : Il y a un regroupement par idrecord
            									$ligne[$i].=$str_skills."\n</table></td>\n";
                                                if ($vertical){
													if (!$lastposition[$object->id]){
														$ligne[$i].=$separateur;
													}
													else{
				            	    			   		$ligne[$i].="</tr><tr><th  class=\"tablerenderer3\" colspan=\"2\"><hr /></th>";
													}
												}
												else if ($lastposition[$object->id]){
                                                    //$ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
												}

												$i++;
												if ($vertical){
						           	        		$ligne[$i].= "<th class=\"tablerenderer3\">".$intitules[$object->id]. "</th>";
												}

                                				//$ligne[$i].= "<td class=\"tablerenderer3\">\n<table class=\"tablerenderer3\">\n";
                                                $ligne[$i].= "<td class=\"tablerenderer3\">\n<ul>\n";

												$str_skills='';
                                                $seriecourante=$val->idrecord;
											}

											$index = $val->value - 1;   // la valeur stockee n'est pas un indice mais une position

        	                                $header = false;
											$hidden = false;
											if ($skill = get_record('artefact_booklet_skill', 'id', $val->idskill)){
												// donnees saisies
                	    	                    switch ($skill->type){
													case 0 : $header = true; break;
                        	    	            	case 2 : $hidden = true; break;
													default : break;
												}

												$scale = $skill->scale;
												$domain = $skill->domain;
												$sdescription = strip_tags($skill->description);
    		                                    $code = $skill->code;
												$threshold = $skill->threshold;
            		                            $str_skill = "$domain::$code";

												$index = $val->value - 1;
                            	                if (!$header){
													if ($str_choice = get_skill_choice_display($skill, $index)){
                                                    	$str_skills .= '<li>&nbsp;'.$domain.' :: <i>'.$code.'</i>'.$sdescription.'<br />'.$str_choice.'</li>'."\n";
													}
												}
												elseif (!$hidden){
    	                                            $str_choice = '';
        	                                      	$sdescription = '<span class="blueback">'.$sdescription.'</span>';
           					           	        	$str_skills .= '<li>'.$domain.' :: <i>'.$skill->code.'</i>'.$sdescription.'&nbsp;</li>'."\n";
												}
											}
          								}
									}
								}
								$ligne[$i].=$str_skills."\n</ul></td>\n";

								if ($vertical){
									if (!$lastposition[$object->id]){
										$ligne[$i].=$separateur;
									}
									else{
            	    			   		//$ligne[$i].="</tr><tr><th  class=\"tablerenderer3\" colspan=\"2\"><hr /></th>";
                                        $ligne[$i].="</tr>\n";
									}
								}
								else if ($lastposition[$object->id]){
									//$ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
								}

            	    		    $i++;
							}

							//--------------------- LISTSKILLS -----------------------------------
           	    			else if ($object->type == 'listskills') {
								if ($list = get_record('artefact_booklet_list', 'idobject', $object->id))	{
                                    $sql = "SELECT * FROM {artefact_booklet_listofskills} ls
 WHERE ls.idlist = ?
 ORDER BY ls.displayorder";
	    	        	            if ($res_lofskills = get_records_sql_array($sql,array($list->id))){
										//print_object($res_lofskills);
										//exit;
                                        if (!empty($listidrecords)){
											$i = 0;
											foreach ( $listidrecords as $a_record){

												$sql = "SELECT re.*,  rd.displayorder FROM {artefact_booklet_lskillsresult} re, {artefact_booklet_resultdisplayorder} rd
 WHERE (re.idrecord = rd.idrecord)
 AND (re.idowner = rd.idowner)
 AND re.idobject = ?
 AND re.idowner = ?
 AND re.idrecord = ?
 ORDER BY rd.displayorder";

        	                   			    	//$vals = get_records_sql_array($sql,  array($object->id, $USER->get('id'), $res->idskill));
            	                                $vals = get_records_sql_array($sql,  array($object->id, $USER->get('id'), $a_record->idrecord));

												// DEBUG
												//echo "<br />lib_vizualisation.php :: 3442 :: VALS<br />\n";
												//print_object($vals);
												//exit;
                                	            if (!empty($vals)){
	                                                $str_skills='';
													if ($vertical){
    		    	           		    				$ligne[$i].= "<th class=\"tablerenderer2\">".$intitules[$object->id]. "</th>";
													}
            		    				            $ligne[$i].= "<td class=\"tablerenderer2\">\n<ul>\n";

                    	            				foreach ($vals as $val){
                        	                            $index = $val->value - 1;   // la valeur stockee n'est pas un indice mais une position
				        		                        $header = false;
														$hidden = false;
														if ($skill = get_record('artefact_booklet_skill', 'id', $val->idskill)){
																// donnees saisies
				                	    	                    switch ($skill->type){
																	case 0 : $header = true; break;
                        	    					            	case 2 : $hidden = true; break;
																	default : break;
																}

																$scale = $skill->scale;
																$domain = $skill->domain;
																$sdescription = $skill->description;
				    		                                    $code = $skill->code;
																$threshold = $skill->threshold;
            		            				                $str_skill = "$domain::$code";

                            	                if (!$header){
													if ($str_choice = get_skill_choice_display($skill, $index)){
                                                    	$str_skills .= '<li>'.$domain.' :: <i>'.$skill->code.'</i>&nbsp;'.$sdescription.'<br />'.$str_choice.'</li>'."\n";
													}
												}
												elseif (!$hidden){
    	                                            $str_choice = '';
        	                                      	$sdescription = '<span class="blueback">'.$sdescription.'</span>';
           					           	        	$str_skills .= '<li>'.$domain.' :: <i>'.$skill->code.'</i>&nbsp;'.$sdescription.'</li>'."\n";
												}														}
													}

													$ligne[$i].=$str_skills."\n</ul></td>\n";
													if ($vertical){
														if (!$lastposition[$object->id]){
															$ligne[$i].=$separateur;
														}
														else{
            	    								   		$ligne[$i].="</tr><tr><th colspan=\"2\"><hr /></th>";
														}
													}
													else if ($lastposition[$object->id]){
														//$ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
													}
												}

												$i++;
											}

										}
									}
								}
							}
							else if ($object->type == 'radio') {
	        	               	$sql = "SELECT re.*, rd.displayorder FROM {artefact_booklet_resultradio} re, {artefact_booklet_resultdisplayorder} rd,  {artefact_booklet_radio} ra
  WHERE (re.idrecord = rd.idrecord)
  AND (re.idowner = rd.idowner)
  AND (ra.id = re.idchoice)
  AND re.idobject = ?
  AND re.idowner = ?
 ORDER BY rd.displayorder";
		                       	if ($radios = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
				           	       	$i = 0;
									if (!empty($radios)){
			    	                   	foreach ($radios as $radio){
											if ($vertical){

						           	        	$ligne[$i].= "<th class=\"tablerenderer3\">".$intitules[$object->id]. "</th>";
											}

				        		            $ligne[$i].= "<td class=\"tablerenderer3\">".$radio->option . "</td>";
											if ($vertical){
												if (!$lastposition[$object->id]){
													$ligne[$i].=$separateur;
												}
												else{
            	    		       		        	$ligne[$i].="</tr><tr><th  class=\"tablerenderer3\" colspan=\"2\"><hr /></th>";
												}
											}
											else if ($lastposition[$object->id]){
                                             //$ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
											}

    			        	   		   	    $i++ ;
        			    	           	}
									}
								}
				   			}

							else if ($object->type == 'checkbox') {
            	    			$sql = "SELECT re.*, rd.displayorder FROM {artefact_booklet_resultcheckbox} re, {artefact_booklet_resultdisplayorder} rd
 WHERE (re.idrecord = rd.idrecord)
 AND (re.idowner = rd.idowner)
 AND re.idobject = ?
 AND re.idowner = ?
 ORDER BY rd.displayorder";
	            	            if ($checkboxes = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
		            	        	$i = 0;
	    		        	    	foreach ($checkboxes as $checkbox) {
											if ($vertical){

						           	        	$ligne[$i].= "<th class=\"tablerenderer3\">".$intitules[$object->id]. "</th>";
											}

	       	    	            	    $ligne[$i].= "<td class=\"tablerenderer3\">".($checkbox->value ? get_string('true', 'artefact.booklet')  : get_string('false', 'artefact.booklet') ) . "</td>";
										if ($vertical){
											if (!$lastposition[$object->id]){
												$ligne[$i].=$separateur;
											}
											else{
                		        		    	$ligne[$i].="</tr><tr><th class=\"tablerenderer3\" colspan=\"2\"><hr /></th>";
											}
										}
										else if ($lastposition[$object->id]){
											//$ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
										}

    	               	    			$i++ ;
	    	       	    			}
	    						}
							}
					        else if ($object->type == 'date') {
    	    	    		            	$sql = "SELECT re.*, rd.displayorder FROM {artefact_booklet_resultdate} re, {artefact_booklet_resultdisplayorder} rd
	            		            	        WHERE (re.idrecord = rd.idrecord)
												AND (re.idowner = rd.idowner)
	            	    	        	        AND re.idobject = ?
	            	        	    	        AND re.idowner = ?
	            	            		        ORDER BY rd.displayorder";
        	    	    	        		if ($dates = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
	            			            		$i = 0;
		            	    		        	foreach ($dates as $date) {
											if ($vertical){

						           	        	$ligne[$i].= "<th class=\"tablerenderer3\">".$intitules[$object->id]. "</th>";
											}

	            			            	    	$ligne[$i].= "<td class=\"tablerenderer3\">".format_date(strtotime($date->value), 'strftimedate') . "</td>";
													if ($vertical){
														if (!$lastposition[$object->id]){
															$ligne[$i].=$separateur;
														}
														else{
            			            			           	$ligne[$i].="</tr><tr><th class=\"tablerenderer3\" colspan=\"2\"><hr /></th>";
														}
													}
													else if ($lastposition[$object->id]){
														//$ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
													}
	   		        	            		    	$i++ ;
	    		        	            		}
   							        }
							}
				    	    else if ($object->type == 'attachedfiles') {
            		    	        $sql = "SELECT re.*, rd.displayorder FROM {artefact_booklet_resultattachedfiles} re, {artefact_booklet_resultdisplayorder} rd
	            	            		        WHERE (re.idrecord = rd.idrecord)
												AND (re.idowner = rd.idowner)
	            	            		        AND re.idobject = ?
	            	            	    	    AND re.idowner = ?
	            	            	        	ORDER BY rd.displayorder";
			            	        if ($attachedfiles = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
    			        	        	for ($i = 0; $i < $n; $i++) {
											if ($vertical){

						           	        	$ligne[$i].= "<th class=\"tablerenderer3\">".$intitules[$object->id]. "</th>";
											}

            			    		   	    $ligne[$i].= "<td class=\"tablerenderer3\"><table>";
            		    	        	}
		            			       	if (!empty($attachedfiles)){
    		        		    	   		foreach ($attachedfiles as $attachedfile) {
            			    	    	    	if ($f = artefact_instance_from_id($attachedfile->artefact)){
														// debugger_on();

														 // DEBUG
														 //echo "<br />lib_skills.php :: 2731 :: file  \n";
														 //print_object($f);
														 //exit;
														 if (isset($f->title)){
                                                            $ftitle= $f->title;
														 }
														 else{
                                                            $ftitle= 'File TITLE';
														 }
                                                          if (isset($f->description)){
                                                            $fdescription= $f->title;
														 }
														 else{
                                                            $fdescription= 'File DESCRIPTION';
														 }
   														// debugger_off();

            	    		    	    		    	$j = 0;
            	            					    	foreach ($listidrecords as $idrc) {
            	            	    				    	if ($attachedfile->idrecord == $idrc->idrecord) {
	            	            	           					$i = $j;
			            	            	        		}
	    			        	            	        	$j++;
    	        				            	    	}
														/*  ********** ERROR SUR MAHARA DEV
// [Fri Sep 18 11:44:59.659768 2015] [:error] [pid 11108] [client 82.231.144.201:52503] PHP Fatal error: Cannot access protected property ArtefactTypeImage::$title in /var/www/html/mahara/artefact/booklet/lib_skills.php on line 2739, referer: http://mahara-dev.univ-brest.fr/mahara//artefact/booklet/selectframesskills.php?domainsselected=2

        	    		    		        	    	$ligne[$i].= '<tr><td class="tablerenderer'.$color[$i].'"><img src="' .
            		    	        			    		$f->get_icon(array('id' => $attachedfile->artefact, 'viewid' => isset($options['viewid']) ? $options['viewid'] : 0)) .
 '" alt=""></td><td class="tablerenderer'.$color[$i].'"><a href="' .
 get_config('wwwroot') . "artefact/file/download.php?file=" . $attachedfile->artefact .
 '">' . $f->title . '</a> (' . $f->describe_size() . ')' . $f->description . '</td></tr>'."\n";
 ****************/

        	    		    		        	    	$ligne[$i].= '<tr><td class="tablerenderer'.$color[$i].'"><img src="' .
            		    	        			    		$f->get_icon(array('id' => $attachedfile->artefact, 'viewid' => isset($options['viewid']) ? $options['viewid'] : 0)) .
 '" alt=""></td><td class="tablerenderer'.$color[$i].'"><a href="' .
 get_config('wwwroot') . "artefact/file/download.php?file=" . $attachedfile->artefact .
 '">' . $ftitle . '</a> (' . $f->describe_size() . ') ' . $fdescription . '</td></tr>'."\n";
											}

			   		       	            }
									}
	        	    	   		    for ($i = 0; $i < $n; $i++) {
										$ligne[$i] .= "</table></td>";
										if ($vertical){
											if (!$lastposition[$object->id]){
												$ligne[$i].=$separateur;
											}
											else{
            	    	       		            $ligne[$i].="</tr><tr><th  class=\"tablerenderer3\" colspan=\"2\"><hr /></th>";
											}
										}
										else if ($lastposition[$object->id]){
		                                     //$ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
										}
			                        }
		       				    }
    		       			}
						}   // Fin de for each objectslist

				        for ($i = 0; $i < $n; $i++) {
       						$rslt .= "\n<tr>" . $ligne[$i] . "</tr>";
						}

						$rslt .= "\n</table>\n ";
        	       		$components['framelist' . $frame->id] = array(
					       	'type' => 'html',
							'help' => '',
					        'title' => '',
    	   	   	    		'value' => $rslt,
						);
					} // Fin de Objectlist
                } // Fin de if List

				if ((count($components) != 0) && !$frame->list) {
        	   		$elements['idtab'] = array(
	                	'type' => 'hidden',
    	           	    'value' => $frame->idtab,
	    	       	);
		    	    $elements['idtome'] = array(
    		    	   	'type' => 'hidden',
        	        	'value' => $idtome,
	                );
			        $elements['list'] = array(
    			      	'type' => 'hidden',
        		        'value' => false
                	);
	               	$elements[$frame->id] = array(
		           	    'type' => 'fieldset',
	    	       	    'legend' => $frame->title,
		    	        'help' => ($frame->help != null),
    		    	  	'elements' => $components
	        	    );
        		}

        		if ((count($components) != 0) && $frame->list) {
					$elements['idtab'] = array(
						'type' => 'hidden',
            	    	'value' => $frame->idtab,
					);

			        $elements['idtome'] = array(
    			    	'type' => 'hidden',
        		        'value' => $idtome,
            		);
		        	$elements['list'] = array(
    		    		'type' => 'hidden',
	        	        'value' => true
    	        	);
        	        $elements[$frame->id] = array(
	        	    	'type' => 'fieldset',
	            		'legend' => $frame->title,
		            	'help' => ($frame->help != null),
    		        	'elements' => $components,
					);
				}
				return ( $elements );

			} // Fin de if Frame
		}   // Fin de if frameid
		return '';
    }


