<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     Catalyst IT Ltd
 * @author     Christophe DECLERCQ - christophe.declercq@univ-nantes.fr
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/* classe pour pieforms et fonctions JS pour la visualisation d'un booklet */
class ArtefactTypeVisualization extends ArtefactTypebooklet {

    public function commit() {
        // le commit insere un artefact pour un cadre et ajoute dans view artefact mention de ce cadre pour tous les blockinstances concernes
        global $USER;
        parent::commit();

        $selectedtome = get_record('artefact_booklet_selectedtome', 'iduser', $USER->get('id'));
		if (empty($selectedtome)){
			$a = new StdClass;
            $a->iduser=$USER->get('id');
            $rec=get_record_sql('SELECT MIN(id) as idtome FROM {artefact_booklet_tome}');
			if ($rec->idtome){
            	$a->idtome=$rec->idtome;
                // DEBUG
				//echo "<br />DEBUG :: lib.php ::1902<br />\n";
				//print_object($a);
				//exit;
            	insert_record('artefact_booklet_selectedtome', $a);
			}
		}
        if ($blockinstances = get_records_sql_array('
            SELECT id, "view", configdata
            FROM {block_instance}
            WHERE blocktype = \'entirebooklet\'
            AND "view" IN (
                SELECT id
                FROM {view}
                WHERE "owner" = ?)', array($this->owner))) {
            foreach ($blockinstances as $blockinstance) {
                $whereobject = (object)array(
                    'view' => $blockinstance->view,
                    'artefact' => $this->get('id'),
                    'block' => $blockinstance->id,);
                // pour savoir si le blockinstance concerne le meme livret, voir le champ note du 1er artefact de cette vue
                $arts = get_records_sql_array('
                    SELECT *
                    FROM {view_artefact} va
                    INNER JOIN {artefact} a
                    ON va.artefact = a.id
                    WHERE va.block = ? ', array($blockinstance->id));
                if (!empty($selectedtome->idtome) && $arts[0] && ($arts[0]->note == $selectedtome->idtome)) {
                    ensure_record_exists('view_artefact', $whereobject, $whereobject);
                }
            }
        }
    }
/**
 * Format liste ou format non liste
 *
 */

    public function render_self($options) {
        // Affichage d'un cadre : un champ du livret dans le blocktype, appele sur un artefact
        global $USER;
        require_once(get_config('docroot') . 'artefact/lib.php');

		// Modif JF
        $vertical=false;
        $separateur='';
        $intitules = array();
        $nbrubriques=0;
		$lastposition = array();

		$frame = get_record('artefact_booklet_frame', 'id', $this->description);
		if (!empty($frame)) {
        	$rslt = "\n<h3>".$frame->title."</h3>";

        	if ($frame-> list) { // le cadre est une liste
            	$objects = get_records_array('artefact_booklet_object', 'idframe', $frame->id, 'displayorder');

				// headers
                $pos=0;
				foreach ($objects as $object) {
                    $key=$object->id;
	            	$intitules[$key]= $object->title;
                    $lastposition[$key]=false;
            	}
                $lastposition[$key]=true;
                $nbrubriques=count($intitules);

    			$vertical = ($nbrubriques>5) ? true : false;
                $separateur=($vertical)? '</tr><tr>' : '';

            	$rslt .= "\n<fieldset>\n<table class=\"tablerenderer \">";
				if (!$vertical){
					$rslt .= "<thead>\n<tr>";
                    foreach ($objects as $object) {
                		$rslt .= "<th>". $object->title . "</th>";
					}
					$rslt .= "</tr></thead>";
				}
                $n=0;

				// calcul du nombre d'elements de la liste
				switch ($objects[0]->type) {
                case 'longtext':
                case 'shorttext':
                case 'area':
                case 'htmltext':
                    $n = count_records('artefact_booklet_resulttext', 'idobject', $objects[0]->id, 'idowner', $this -> author);
					// MODIF JF 2015/01/22
					// do est un mot reserve pour PostGres :  do -> rd
                    $sql = "SELECT re.idrecord FROM {artefact_booklet_resulttext} re
                            JOIN {artefact_booklet_resultdisplayorder} rd
                            ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
                            WHERE re.idobject = ?
                            AND re.idowner = ?
                            ORDER BY rd.displayorder";
                    $listidrecords = get_records_sql_array($sql, array($objects[0]->id, $this -> author));
                    break;
                case 'radio':
                    $n = count_records('artefact_booklet_resultradio', 'idobject', $objects[0]->id, 'idowner', $this -> author);
                    // MODIF JF 2015/01/22
					// do est un mot reserve pour PostGres :  do -> rd
                    $sql = "SELECT re.idrecord FROM {artefact_booklet_resultradio} re
                            JOIN {artefact_booklet_resultdisplayorder} rd
                            ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
                            WHERE re.idobject = ?
                            AND re.idowner = ?
                            ORDER BY rd.displayorder";
                    $listidrecords = get_records_sql_array($sql, array($objects[0]->id, $this -> author));
                    break;
                case 'checkbox':
                    $n = count_records('artefact_booklet_resultcheckbox', 'idobject', $objects[0]->id, 'idowner', $this -> author);
                    // MODIF JF 2015/01/22
					// do est un mot reserve pour PostGres :  do -> rd
                    $sql = "SELECT re.idrecord FROM {artefact_booklet_resultcheckbox} re
                            JOIN {artefact_booklet_resultdisplayorder} rd
                            ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
                            WHERE re.idobject = ?
                            AND re.idowner = ?
                            ORDER BY rd.displayorder";
                    $listidrecords = get_records_sql_array($sql, array($objects[0]->id, $this -> author));
                    break;
                case 'date':
                    $n = count_records('artefact_booklet_resultdate', 'idobject', $objects[0]->id, 'idowner', $this -> author);
                    // MODIF JF 2015/01/22
					// do est un mot reserve pour PostGres :  do -> rd
                    $sql = "SELECT re.idrecord FROM {artefact_booklet_resultdate} re
                            JOIN {artefact_booklet_resultdisplayorder} rd
                            ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
                            WHERE re.idobject = ?
                            AND re.idowner = ?
                            ORDER BY rd.displayorder";
                    $listidrecords = get_records_sql_array($sql, array($objects[0]->id, $this -> author));
                    break;
                case 'attachedfiles':
                    $n = count_records('artefact_booklet_resultattachedfiles', 'idobject', $objects[0]->id, 'idowner', $this -> author);
                    // TO DO : ne compter que les records ayant un idrecord different
                    break;
                case 'listskills':
                    $n = count_records('artefact_booklet_lskillsresult', 'idobject', $objects[0]->id, 'idowner', $this -> author);
                    $sql = "SELECT re.idrecord FROM {artefact_booklet_lskillsresult} re
                            JOIN {artefact_booklet_resultdisplayorder} rd
                            ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
                            WHERE re.idobject = ?
                            AND re.idowner = ?
                            ORDER BY rd.displayorder";
                    $listidrecords = get_records_sql_array($sql, array($objects[0]->id, $this -> author));

                    break;
                case 'reference':
                    $n = count_records('artefact_booklet_lskillsresult', 'idobject', $objects[0]->id, 'idowner', $this -> author);
                    $sql = "SELECT re.idrecord FROM {artefact_booklet_refresult} re
                            JOIN {artefact_booklet_resultdisplayorder} rd
                            ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
                            WHERE re.idobject = ?
                            AND re.idowner = ?
                            ORDER BY rd.displayorder";
                    $listidrecords = get_records_sql_array($sql, array($objects[0]->id, $this -> author));

                    break;
				case 'freeskills':
	            		$sql = "SELECT DISTINCT idrecord FROM {'artefact_booklet_frskllresult'}
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?";
                        $n = count($recs = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id'))));

	            	    $sql = "SELECT re.idrecord FROM {'artefact_booklet_frskllresult'} re
	            	            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";
	            	    $listidrecords = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id')));
	            	break;

            	}

				// construction d'un tableau des lignes : une par element, chaque ligne contient les valeurs de tous les objets
            	$ligne = array();
            	for ($i = 0; $i < $n; $i++) {
                	$ligne[$i] = "";
            	}
            	// pour chaque objet, on complete toutes les lignes
            	foreach ($objects as $object) {
					if ($object->type == 'longtext' || $object->type == 'shorttext' || $object->type == 'area' || $object->type == 'htmltext' || $object->type == 'synthesis') {
						// MODIF JF 2015/01/22
						// do est un mot reserve pour PostGres :  do -> rd
        	            $sql = "SELECT * FROM {artefact_booklet_resulttext} re
                            JOIN {artefact_booklet_resultdisplayorder} rd
                            ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
                            WHERE re.idobject = ?
                            AND re.idowner = ?
                            ORDER BY rd.displayorder";
            	        $txts = get_records_sql_array($sql, array($object->id, $this -> author));
						//print_object( $txts               );
						//exit;
	                    $i = 0;
    	                foreach ($txts as $txt) {

							if ($vertical){
            	                $ligne[$i].= "<th>".$intitules[$object->id]. "</th>";
							}
   							$ligne[$i].= "<td>". $txt->value . "</td>";
							if ($vertical){
								if (!$lastposition[$object->id]){
									$ligne[$i].=$separateur;
								}
								else{
        	                        $ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
								}
							}
                    	    $i++ ;
                    	}
                	}
            	    else if ($object->type == 'listskills') {
						if ($list = get_record('artefact_booklet_list', 'idobject', $object->id)){
                    		$i = 0;
							if ($vertical){
        	           			$ligne[$i].= "<th>".$intitules[$object->id]. "</th>";
							}
                   			$ligne[$i].= "<td class=\"tablerenderer2\"><ul>\n";
							$str_skills='';

                       		if ($res_lofskills = get_records_array('artefact_booklet_listofskills', 'idlist', $list->id)){
								foreach ($res_lofskills as $res){
                                    $header = false;
									$hidden = false;
									if ($skill = get_record('artefact_booklet_skill', 'id', $res->idskill)){

                                        switch ($skill->type){
											case 0 : $header = true; break;
                                            case 2 : $hidden = true; break;
											default : break;
										}


										$index = 0;
    									// donnees saisies
                                        $rec=null;
                              			$sql = "SELECT * FROM {artefact_booklet_lskillsresult} re
 JOIN {artefact_booklet_resultdisplayorder} rd
 ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
 JOIN {artefact_booklet_skill} rs
 ON (rs.id = re.idskill)
 WHERE re.idobject = ?
 AND re.idowner = ?
 AND re.idskill = ?
 ORDER BY re.idrecord, rd.displayorder";
               	                    	if ($recs = get_records_sql_array($sql,  array($object->id, $USER->get('id'), $skill->id))){
											foreach ($recs as $rec){
	                                            if ($rec){
													$index = $rec->value - 1;
													$nboptions=0;
            	                                    $str_choice = '';
													if (!$header){
														if ($tab_scale = explode(",", $skill->scale)){
															for ($j=0; $j<count($tab_scale); $j++){
                            	    		            		$a_scale_element = trim($tab_scale[$j]);
																if (!empty($a_scale_element)){
																	if ($index == $nboptions){
    	            													if ($str_choice){
																		$str_choice .= ' | <b>'.$a_scale_element.'</b>';
																		}
																		else{
        	        	                            	                	$str_choice .= '<b>'.$a_scale_element.'</b>';
																		}
																	}
																	else if ($skill->threshold == $nboptions){
	                													if ($str_choice){
																			$str_choice .= ' | <i>'.$a_scale_element.'</i>';
																		}
																		else{
        	    	                                    	            	$str_choice .= '<i>'.$a_scale_element.'</i>';
																		}
																	}
																	else{
				        	    	    								if ($str_choice){
																			$str_choice .= ' | '.$a_scale_element;
																		}
																		else{
                    	    	                    					$str_choice .= $a_scale_element;
																		}
																	}
            			        	                			    $nboptions++;
																}
															}
														}
													}
													if (!$hidden){
														$str_skills .= '<li>'.$skill->domain.' :: '.$skill->code.' :: '.$str_choice. '<br /><span="small">'.$skill->description.'</span></li>'."\n";
													}
													else{
														;
													}
												}
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
        		    	    		$ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
								}
							}
            				$i++ ;
						}
					}

                	else if ($object->type == 'freeskills') {
                    	$vals = array();
	                    $i = 0;
						if ($vertical){
        		        	$ligne[$i].= "<th>".$intitules[$object->id]. "</th>";
						}
                	   	$ligne[$i].= "<td class=\"tablerenderer2\"><ul>\n";
						$str_skills='';
						// ATTENTION : Il y a un regroupement par idrecord
                   		$sql = "SELECT * FROM {artefact_booklet_frskllresult} re
 JOIN {artefact_booklet_resultdisplayorder} rd
 ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
 WHERE re.idobject = ?
 AND re.idowner = ?
 ORDER BY re.idrecord, rd.displayorder";
						$vals = get_records_sql_array($sql,  array($object->id, $USER->get('id')));
						if (!empty($vals)){
   							$seriecourante=$vals[0]->idrecord;
                            foreach ($vals as $val){
	                        	if ($val){
									if ($val->idrecord != $seriecourante){    // ATTENTION : Il y a un regroupement par idrecord
                                        $ligne[$i].=$str_skills."\n</ul></td>\n";
										if ($vertical){
											if (!$lastposition[$object->id]){
												$ligne[$i].=$separateur;
											}
											else{
        	    								$ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
											}
										}
										$i++;
										if ($vertical){
    	    	        					$ligne[$i].= "<th>".$intitules[$object->id]. "</th>";
										}
            				       		$ligne[$i].= "<td class=\"tablerenderer2\"><ul>\n";
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
										$sdescription = $skill->description;
    		                            $code = $skill->code;
										$threshold = $skill->threshold;

                            	        if (!$header){
											$str_choice = get_skill_choice_display($skill, $index);
                                		    $sdescription = $skill->description;
										}
										else{
    	                                	$str_choice = '';
        	                                $sdescription = '<b>'.strip_tags($skill->description).'</b>';
										}

										if (!$hidden){
  							           		$str_skills .= '<li>'.$skill->domain.'<i>&nbsp;'.$skill->code.'</i>&nbsp;'.$str_choice.'<br />'.$sdescription.'</li>'."\n";
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
        	    				$ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
							}
						}
                        $i++;

					}

               		else if ($object->type == 'reference') {
                    	$sql = "SELECT * FROM {artefact_booklet_refresult} re
                            JOIN {artefact_booklet_resultdisplayorder} rd
                            ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
                            JOIN {artefact_booklet_reference} ra
                            ON (ra.id = re.idreference)
                            WHERE re.idobject = ?
                            AND re.idowner = ?
                            ORDER BY rd.displayorder";
                    	$recs_ref = get_records_sql_array($sql, array($object->id, $this -> author));
	                    $i = 0;
						if (!empty($recs_ref)){
        	            	foreach ($recs_ref as $reference){
								if ($vertical){
        	    	                $ligne[$i].= "<th>".$intitules[$object->id]. "</th>";
								}
                        		$ligne[$i].= "<td>".display_object_linked($reference->idobjectlinked, $this->author) . "</td>";
								if ($vertical){
									if (!$lastposition[$object->id]){
										$ligne[$i].=$separateur;
									}
									else{
        	                        	$ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
									}
								}
                    	    	$i++ ;
                    		}
						}
	                }

    	            else if ($object->type == 'radio') {
					// MODIF JF 2015/01/22
					// do est un mot reserve pour PostGres :  do -> rd
                    $sql = "SELECT * FROM {artefact_booklet_resultradio} re
                            JOIN {artefact_booklet_resultdisplayorder} rd
                            ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
                            JOIN {artefact_booklet_radio} ra
                            ON (ra.id = re.idchoice)
                            WHERE re.idobject = ?
                            AND re.idowner = ?
                            ORDER BY rd.displayorder";
                    $radios = get_records_sql_array($sql, array($object->id, $this -> author));
                    $i = 0;
					if (!empty($radios)){
                    	foreach ($radios as $radio){
							if ($vertical){
        	                    $ligne[$i].= "<th>".$intitules[$object->id]. "</th>";
							}
                        	$ligne[$i].= "<td>".$radio->option . "</td>";
							if ($vertical){
								if (!$lastposition[$object->id]){
									$ligne[$i].=$separateur;
								}
								else{
                                	$ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
								}
							}
                        	$i++ ;
                    	}
					}
                }
                else if ($object->type == 'checkbox') {
					// MODIF JF 2015/01/22
					// do est un mot reserve pour PostGres :  do -> rd
                    $sql = "SELECT * FROM {artefact_booklet_resultcheckbox} re
                            JOIN {artefact_booklet_resultdisplayorder} rd
                            ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
                            WHERE re.idobject = ?
                            AND re.idowner = ?
                            ORDER BY rd.displayorder";
                    $checkboxes = get_records_sql_array($sql, array($object->id, $this -> author));
                    $i = 0;
                    foreach ($checkboxes as $checkbox) {
						if ($vertical){
       	                    $ligne[$i].= "<th>".$intitules[$object->id]. "</th>";
						}
                        $ligne[$i].= "<td>".($checkbox->value ? get_string('true', 'artefact.booklet')  : get_string('false', 'artefact.booklet') ) . "</td>";
						if ($vertical){
							if (!$lastposition[$object->id]){
								$ligne[$i].=$separateur;
							}
							else{
                                $ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
							}
						}
                        $i++ ;
                    }
                }
                else if ($object->type == 'date') {
					// MODIF JF 2015/01/22
					// do est un mot reserve pour PostGres :  do -> rd
                    $sql = "SELECT * FROM {artefact_booklet_resultdate} re
                            JOIN {artefact_booklet_resultdisplayorder} rd
                            ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
                            WHERE re.idobject = ?
                            AND re.idowner = ?
                            ORDER BY rd.displayorder";
                    $dates = get_records_sql_array($sql, array($object->id, $this -> author));
                    $i = 0;
                    foreach ($dates as $date) {
						if ($vertical){
       	                    $ligne[$i].= "<th>".$intitules[$object->id]. "</th>";
						}
                        $ligne[$i].= "<td>".format_date(strtotime($date->value), 'strftimedate') . "</td>";
						if ($vertical){
							if (!$lastposition[$object->id]){
								$ligne[$i].=$separateur;
							}
							else{
                                $ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
							}
						}
                        $i++ ;
                    }
                }
                else if ($object->type == 'attachedfiles') {
					// MODIF JF 2015/01/22
					// do est un mot reserve pour PostGres :  do -> rd
                    $sql = "SELECT * FROM {artefact_booklet_resultattachedfiles} re
                            JOIN {artefact_booklet_resultdisplayorder} rd
                            ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
                            WHERE re.idobject = ?
                            AND re.idowner = ?
                            ORDER BY rd.displayorder";
                    $attachedfiles = get_records_sql_array($sql, array($object->id, $this -> author));
                    for ($i = 0; $i < $n; $i++) {
						if ($vertical){
                            $ligne[$i].= "<th>".$intitules[$object->id]. "</th>";
						}
                        $ligne[$i].= "<td><table>";
                    }
                    if (!empty($attachedfiles)){
                    	foreach ($attachedfiles as $attachedfile) {
                        	$f = artefact_instance_from_id($attachedfile->artefact);
                        	$j = 0;
                        	foreach ($listidrecords as $idrc) {
                            	if ($attachedfile->idrecord == $idrc->idrecord) {
                               		$i = $j;
                            	}
                            	$j++;
                        	}
                        	$ligne[$i].= "<tr><td><img src=" .
                        		$f->get_icon(array('id' => $attachedfile->artefact, 'viewid' => isset($options['viewid']) ? $options['viewid'] : 0)) .
                            	" alt=''></td><td><a href=" .
                            	get_config('wwwroot') . "artefact/file/download.php?file=" . $attachedfile->artefact .
                            	">" . $f->title . "</a> (" . $f->describe_size() . ")" . $f->description . "</td></tr>";
                    	}
					}
                    for ($i = 0; $i < $n; $i++) {
						$ligne[$i] .= "</table></td>";
						if ($vertical){
							if (!$lastposition[$object->id]){
								$ligne[$i].=$separateur;
							}
							else{
                                $ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
							}
						}
                    }
                	}
            	}
            	for ($i = 0; $i < $n; $i++) {
                	$rslt .= "\n<tr>" . $ligne[$i] . "</tr>";
            	}
				$rslt .= "\n</table>\n</fieldset> ";
        }
        // le cadre n'est pas une liste
        else {

			$objects = get_records_array('artefact_booklet_object', 'idframe', $frame->id, 'displayorder');
			if (!empty($objects)) {
            $rslt .= "\n<fieldset>\n<table class=\"tablerenderer \">";
            foreach ($objects as $object) {
                if ($object->type == 'longtext' || $object->type == 'shorttext' || $object->type == 'area' || $object->type == 'htmltext' || $object->type == 'synthesis') {
                    $val = get_record('artefact_booklet_resulttext', 'idowner', $this -> author, 'idobject', $object->id);
                    if ($val && $val -> value) {
                        $rslt .= "\n<tr><th>". $object -> title . "</th>";
                        $rslt .= "<td>";
                        $rslt .= $val -> value;
                    }
                }
                else if ($object->type == 'radio') {
                    $radio = get_record('artefact_booklet_resultradio', 'idowner', $this -> author, 'idobject', $object->id);
                    if ($radio && $radio->idchoice) {
                        $val = get_record('artefact_booklet_radio', 'id', $radio->idchoice);
                        $rslt .= "\n<tr><th>". $object -> title . "</th>";
                        $rslt .= "<td>";
                        $rslt .= $val -> option;
                    }
                }
                else if ($object->type == 'checkbox') {
                    $coche = get_record('artefact_booklet_resultcheckbox', 'idowner', $this -> author, 'idobject', $object->id);
                    if ($coche) {
                        $rslt .= "\n<tr><th>". $object -> title . "</th>";
                        $rslt .= "<td>";
                        $rslt .= ($coche->value ? get_string('true', 'artefact.booklet')  : get_string('false', 'artefact.booklet') );
                    }
                }
                else if ($object->type == 'date') {
                    $date = get_record('artefact_booklet_resultdate', 'idowner', $this -> author, 'idobject', $object->id);
                    if ($date) {
                        $rslt .= "\n<tr><th>". $object -> title . "</th>";
                        $rslt .= "<td>";
                        $rslt .= format_date(strtotime($date->value), 'strftimedate') ;
                    }
                }
                else if ($object->type == 'attachedfiles') {
                    $sql = "SELECT * FROM {artefact_booklet_resultattachedfiles}
                            WHERE idobject = ?
                            AND idowner = ?";
                    $attachedfiles = get_records_sql_array($sql, array($object->id, $this -> author));
                    $rslt .= "\n<tr><th>". $object -> title . "</th>";
                    $rslt .= "<td> <table>";
                    if ($attachedfiles) foreach ($attachedfiles as $attachedfile) {
                        $f = artefact_instance_from_id($attachedfile->artefact);
                        $rslt .= "<tr><td class='iconcell'><img src=" .
                        $f->get_icon(array('id' => $attachedfile->artefact, 'viewid' => isset($options['viewid']) ? $options['viewid'] : 0)) .
                        " alt=''></td> <td><a href=" .
                        get_config('wwwroot') . "artefact/file/download.php?file=" . $attachedfile->artefact .
                        ">" . $f->title . "</a> (" . $f->describe_size() . ")" . $f->description . "</td></tr>";
                    }
                    $rslt .= "</table>";

			    }
                else if ($object->type == 'listskills') {
                    $sql = "SELECT re.*, rs.*  FROM {artefact_booklet_lskillsresult} re
                            JOIN {artefact_booklet_skill} rs
                            ON (rs.id = re.idskill)
                            WHERE re.idobject = ?
                            AND re.idowner = ?";
                    $skills = get_records_sql_array($sql, array($object->id, $this->author));

					if (!empty($skills)){
                        $rslt .= "\n<tr><th>". $object -> title . "</th><td>\n<table>\n";
                     	foreach ($skills as $skill){
                            $value = $skill->value;
                            $str_evaluation = '';
							if (!empty($value)){
                                $value--;
                                if ($tab_scale=explode(",", $skill->scale)){
                                	$str_evaluation = $tab_scale[$value];
								}
							}
                        	$rslt .= '<tr><td>'.$skill->domain.'</td><td><b>'.$skill->code.'</b><td><i>'. $str_evaluation.'</i></td></tr><tr><td colspan="3">'. $skill->description.'</td></tr>'."\n";
                    	}
                        $rslt .= "</table>\n";
					}
				}

                else if ($object->type == 'freeskills') {
                    $sql = "SELECT re.*, rs.*  FROM {artefact_booklet_frskllresult} re
                            JOIN {artefact_booklet_skill} rs
                            ON (rs.id = re.idskill)
                            WHERE re.idobject = ?
                            AND re.idowner = ?";
                    $skills = get_records_sql_array($sql, array($object->id, $this->author));

					if (!empty($skills)){
                        $rslt .= "\n<tr><th>". $object -> title . "</th><td>\n<table>\n";
                     	foreach ($skills as $skill){
                            $value = $skill->value;
                            $str_evaluation = '';
							if (!empty($value)){
                                $value--;
                                if ($tab_scale=explode(",", $skill->scale)){
                                	$str_evaluation = $tab_scale[$value];
								}
							}
                        	$rslt .= '<tr><td>'.$skill->domain.'</td><td><b>'.$skill->code.'</b><td><i>'. $str_evaluation.'</i></td></tr><tr><td colspan="3">'. $skill->description.'</td></tr>'."\n";
                    	}
                        $rslt .= "</table>\n";
					}
				}

				else if ($object->type == 'reference') {
					$reference = get_record('artefact_booklet_refresult', 'idowner', $this -> author, 'idobject', $object->id);
                   	if ($reference && $reference->idreference) {
                        $val = get_record('artefact_booklet_reference', 'id', $reference->idreference);
                        $rslt .= "\n<tr><th>". $object -> title . "</th>";
                        $rslt .= "<td>";
                        $rslt .= display_object_linked($val->idobjectlinked, $this -> author);
                    }
                }


                $rslt .= "</td></tr>";
            }

			}
            $rslt .= "\n</table>";
            $rslt .= "\n</fieldset>\n";
        }
		//
		}
        // return array('html' => clean_html($rslt));
        if (!empty($rslt)){
			return array('html' => $rslt);
		}
    }

    public static function is_singular() {
        return true;
    }

    public static function submenu_items($idtome) {
        global $USER;
        $tabs = get_records_array('artefact_booklet_tab', 'idtome', $idtome, 'displayorder');
        // liste des tabs du tome tries par displayorder
        $selectedtome = get_record('artefact_booklet_selectedtome', 'iduser', $USER->get('id'));
		if (empty($selectedtome)){
			$a = new StdClass;
			$a->iduser=$USER->get('id');
            $rec=get_record_sql('SELECT MIN(id) as idtome FROM {artefact_booklet_tome}');
			if ($rec->idtome){
            	$a->idtome=$rec->idtome;
                // DEBUG
				//echo "<br />DEBUG :: lib.php ::1902<br />\n";
				//print_object($a);
				//exit;
            	insert_record('artefact_booklet_selectedtome', $a);
			}
		}

        $opt = null;
        if ($selectedtome && $idtome != $selectedtome->idtome) {
        // Cas ou un designer regarde un tome qui n'est pas celui qu'il a selectionne
            $opt = "&tome=" . $idtome;
        }
        $items = array();
        if ($tabs) {
            foreach ($tabs as $tab) {
                // cree un tableau : displayorder -> tableau (page url title)
                $items[$tab->displayorder] = array(
                    'page'    => $tab->displayorder,
                    'url'     => 'artefact/booklet/index.php?tab=' . $tab->displayorder . $opt,
                    'title'    => $tab->title,
                );
            }
        }
        if (defined('BOOKLET_SUBPAGE') && isset($items[BOOKLET_SUBPAGE])) {
        // pour differencier 1er et second appel depuis index.php
            $items[BOOKLET_SUBPAGE]['selected'] = true;
        }
        return $items;
        // renvoit le tableau des tabs avec au 2nd appel mention de celui qui est selectionne
    }

	/********************************************************************************
	 *
	 *
	 *     VISUALIZATION GET AFRAME FORM DISPLAY
	 *
	 *   ***************************************************************************/
    public static function get_aframeform($idtome, $idtab, $idframe, $idmodifliste = null, $browse) {
        // idmodifliste est l'index dans artefact_booklet_resulttext
        global $USER;
		// Modif JF
		global $THEME;
        $editstr = get_string('edit','artefact.booklet');
        $editallstr = get_string('editall','artefact.booklet');
        $imageedit = $THEME->get_url('images/btn_edit.png');
		$showstr = get_string('show','artefact.booklet');
		$showallstr = get_string('showall','artefact.booklet');
        $imageshow = $THEME->get_url('images/btn_info.png');
		$objectlinkedstr = get_string('objectlinked','artefact.booklet');
        $imagelinked = $THEME->get_url('images/btn_show.png', false, 'artefact/booklet');
        $imageaddfreeskills = $THEME->get_url('images/btn_check.png', false, 'artefact/booklet');
        $addfreeskillsstr = get_string('addfreeskills','artefact.booklet');

		$showlink  = array();
        $editlink  = array();

		// Astuce pour forcer l'affichage
		if ($idmodifliste==-1){
            $idmodifliste=null;
		}


        require_once(get_config('libroot') . 'pieforms/pieform.php');
        if (!is_null($idmodifliste)) {
            $record = get_record('artefact_booklet_resulttext', 'id', $idmodifliste);
			$idrecord = $record->idrecord;
            $objmodif = get_record('artefact_booklet_object', 'id', $record->idobject);
        }
        else {
            $record = null;
			$idrecord = 0;
            $objmodif = null;
        }
        if (!$tome = get_record('artefact_booklet_tome', 'id', $idtome)) {
            return null;
        }
        foreach (get_records_array('artefact_booklet_tab', 'idtome', $idtome, 'displayorder') as $item) {
            // liste des tabs du tome tries par displayorder
            if ($item->displayorder == $idtab) {
                // parcours pour trouver le tab dont le displayorder est idtab
                $tab = $item;
            }
        }
		// liste des cadres ordonnes en profondeur d'abord
        // $tabaff_codes = get_frames_codes_ordered($tab->id);
		//foreach ($tabaff_codes as $key => $val){
        //    echo "<br />DEBUG :: ".$key."=".$val."\n";
		//}
		//exit;
        $components = array();
        $elements = array();
        $bookletform = array();
        $bookletform["entete"] = $tab->help;
        // if ($tabaff_codes) {
            // foreach ($tabaff_codes as $key => $val) {
				if (!empty($idframe) && ($frame = get_record('artefact_booklet_frame', 'id', $idframe))){
                	$components = array();
                	$elements = null;
                	$components = null;
                	$pf = null;
                	// Quatre conditions exclusives
	                $notframelist = !$frame->list;
    	            $framelistnomodif = $frame->list && !$objmodif;
        	        $objmodifinframe = $objmodif && ($objmodif->idframe == $frame->id);
            	    $objmodifotherframe = $objmodif && ($objmodif->idframe != $frame->id);

				// afficher le bouton alternant affichage et edition

					if ( !$frame->list){
					   	$elements['showedit'] = array(
							'type' => 'html',
							'title' => '',
							'value' =>  '<div class="right">
<a href="'.get_config('wwwroot').'/artefact/booklet/index.php?tab='.$idtab.'&okdisplay=1&idframe='.$idframe.'"><img src="'.$imageshow.'" alt="'.$showstr.'" title="'.$showstr.'" /></a>
<a href="'.get_config('wwwroot').'/artefact/booklet/index.php?tab='.$idtab.'&okdisplay=0"><img src="'.$imageedit.'" alt="'.$editallstr.'" title="'.$editallstr.'" /></a>
</div>',
						);
					}

        	        $objects = get_records_array('artefact_booklet_object', 'idframe', $frame->id, 'displayorder');
            	    // liste des objets du frame ordonnes par displayorder
                	if ($objects) {
	            	    foreach ($objects as $object) {
	            	        $help = ($object->help != null);
	            	        if ($object->type == 'longtext') {
								$val = null;
	            	            if ($notframelist) {
	            	            	// ce n'est pas une liste : rechercher le contenu du champ texte
	            	            	$sql = "SELECT * FROM {artefact_booklet_resulttext} WHERE idobject = ? AND idowner = ?";
	            	            	$vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
	            	            	$val = $vals[0];
	            	            }
	            	            else if ($objmodifinframe) {
	            	            	// modification d'un element de liste
	            	            	$val = get_record('artefact_booklet_resulttext', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
	            	            }
	            	            if ($notframelist || !$objmodifotherframe) {
	            	            	$components['lt' . $object->id] =  array(
	            	            		'type' => 'text',
	            	            	    'title' => $object->title,
                                    	'size' => 50,
                                    	'help' => $help,
                                    	'defaultvalue' => ((!empty($val)) ? $val->value : NULL),
                                	);
                            	}
                    		}
                        	else if ($object->type == 'area') {
                            	$val = null;
	                            if ($notframelist) {
    	                            $sql = "SELECT * FROM {artefact_booklet_resulttext} WHERE idobject = ? AND idowner = ?";
        	                        $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
            	                    $val = $vals[0];
                	            }
                    	        else if ($objmodifinframe) {
                        	        $val = get_record('artefact_booklet_resulttext', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
                            	}
	                            if ($notframelist || !$objmodifotherframe) {
    	                            $components['ta' . $object->id] =  array(
        	                            'type' => 'textarea',
            	                        'rows' => 10,
                	                    'cols' => 50,
                    	                'title' => $object->title,
                        	            'help' => $help,
                            	        'resizable' => false,
                                	    'defaultvalue' => ((!empty($val)) ? $val->value : NULL),
	                                );
        	                    }
    	                    }
            	            else if ($object->type == 'htmltext') {
                	            $val = null;
                    	        if ($notframelist) {
                        	        $sql = "SELECT * FROM {artefact_booklet_resulttext} WHERE idobject = ? AND idowner = ?";
                            	    $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
	                                $val = $vals[0];
    	                        }
        	                    else if ($objmodifinframe) {
            	                    $val = get_record('artefact_booklet_resulttext', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
                	            }
                    	        if ($notframelist || !$objmodifotherframe) {
                        	        $components['ht' . $object->id] =  array(
                            	        'type' => 'wysiwyg',
                                	    'rows' => 20,
                                    	'cols' => 60,
	                                    'title' => $object->title,
    	                                'help' => $help,
        	                            'resizable' => false,
            	                        'defaultvalue' => ((!empty($val)) ? $val->value : NULL),
                	                    'rules' => array('maxlength' => 65536),
                    	           );
                        	    }
	                        }
                            else if ($object->type == 'reference') {
        	                    $val = null;
                                $str_reference = '';
								if ($reference = get_record('artefact_booklet_reference', 'idobject', $object->id)){
									if ($objectlinked = get_record('artefact_booklet_object', 'id', $reference->idobjectlinked)){
										if ($referenceframe = get_record('artefact_booklet_frame', 'id', $objectlinked->idframe)){
                                            $str_reference = display_object_linked($reference->idobjectlinked, $USER->get('id')).' <a href="'.get_config('wwwroot').'/artefact/booklet/index.php?idframe='.$referenceframe->id.'&tab='.$referenceframe->idtab.'&okdisplay=0"><img src="'.$imagelinked.'" alt="'.$objectlinkedstr.'" title="'.$objectlinkedstr.'" /></a>';
											if(!$str_reference){
   			                            		$str_reference = get_string('referencehasnovalue','artefact.booklet'). ' <b>'. $objectlinked->title .'</b> '.get_string('offrame','artefact.booklet'). ' <i>'.$referenceframe->title.'</i>';
											}
										}
									}
                                    if ($notframelist) {
                	                	$sql = "SELECT * FROM {artefact_booklet_refresult} WHERE idobject = ? AND idowner = ?";
	                    	            $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
    	                    	        $val = $vals[0];
        	                    	}
	        	                    else if ($objmodifinframe) {
    	        	                    $val = get_record('artefact_booklet_refresult', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
        	        	            }
                        	        if (empty($val)){
										// creer la valeur
										$rec_refresult = new stdclass();
                                        $rec_refresult->idobject = $object->id;
                                        $rec_refresult->idowner = $USER->get('id');
                                        $rec_refresult->idreference = $reference->id;
                                        if ($record) {
											$rec_refresult->idrecord = $record->id;
										}
										else{
                                            $rec_refresult->idrecord = null;
										}
										insert_record('artefact_booklet_refresult', $rec_refresult);
									}

	            	                $rec = false;
    	            	            if (!is_null($record)) {
        	            	            $rec = true;
            	            	    }
                	            	if ($notframelist || !$objmodifotherframe) {
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
            	                if ($notframelist) {
                	                $sql = "SELECT * FROM {artefact_booklet_resulttext} WHERE idobject = ? AND idowner = ?";
                    	            $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
                        	        $val = $vals[0];
                            	}
	                            else if ($objmodifinframe) {
    	                            $val = get_record('artefact_booklet_resulttext', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
        	                    }
            	                $rec = false;
                	            if (!is_null($record)) {
                    	            $rec = true;
                        	    }
                            	if ($notframelist || !$objmodifotherframe) {
	                                $components['ta' . $object->id] =  array(
    	                                'type' => 'wysiwyg',
        	                            'rows' => 20,
            	                        'cols' => 60,
                	                    'title' => $object->title,
                    	                'help' => $help,
                        	            'resizable' => false,
                            	        'defaultvalue' => ((!empty($val)) ? $val->value : NULL),
                                	);
	                                $components['btn' . $object->id] = array(
    	                                'type' => 'button',
        	                            'value' => get_string('generate', 'artefact.booklet'),
            	                        'onclick' => ($rec ? 'sendjsonrequest(\'compositegeneratesynthesis.php\',
                	                                 {\'idsynthesis\': ' . $object->id . ', \'idrecord\': ' . $record->idrecord . '},
                                                     \'GET\',
                                                     function(data) {
                                                         location.reload(true)
                                                     },
                                                     function() {
                                                         // @todo error
                                                     })'
                                                     : 'sendjsonrequest(\'compositegeneratesynthesis.php\',
                                                     {\'idsynthesis\': ' . $object->id . '},
                                                     \'GET\',
                                                     function(data) {
                                                         location.reload(true)
                                                     },
                                                     function() {
                                                        // @todo error
                                                     })')
                        	        );
                    	        }
	                        }
    	                    else if ($object->type == 'shorttext') {
        	                    $val = null;
            	                if ($notframelist) {
                	                $sql = "SELECT * FROM {artefact_booklet_resulttext} WHERE idobject = ? AND idowner = ?";
                    	            $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
                        	        $val = $vals[0];
                            	}
	                            else if ($objmodifinframe) {
    	                            $val = get_record('artefact_booklet_resulttext', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
        	                    }
            	                if ($notframelist || !$objmodifotherframe) {
                	                $components['st' . $object->id] =  array(
                    	                'type' => 'text',
                        	            'title' => $object->title,
                            	        'help' => $help,
                                	    'size' => 16,
	                                    'defaultvalue' => ((!empty($val)) ? $val->value : NULL),
    	                            );
        	                    }
            	            }
            	            else if ($object->type == 'listskills') {
								// DEBUG
								//echo "<br />lib.php :: 2783<br />\n";
								//print_object($object);
								//exit;
								// list
								if ($list = get_record('artefact_booklet_list', 'idobject', $object->id)){
          							if ($notframelist || !$objmodifotherframe) {
            	                    	$components['ls' . $object->id.'_'.$list->id] = array(
			                	                        'type' => 'html',
            			        	                    'title' => $object->title,
                                	        			'value' => $list->description,
                                    	);
				                    }

            	                    if ($res_lofskills = get_records_array('artefact_booklet_listofskills', 'idlist', $list->id)){
										foreach ($res_lofskills as $res){
                                            $header = false;
											$hidden = false;
											if ($skill = get_record('artefact_booklet_skill', 'id', $res->idskill)){
		                                      	switch ($skill->type){
												case 0 : $header = true; break;
            	                                case 2 : $hidden = true; break;
												default : break;
												}

                                                $options = array();
												$scale = $skill->scale;
												$domain = $skill->domain;
												$sdescription = $skill->description;
                                                $code = $skill->code;
												$threshold = $skill->threshold;
                                                $str_skill = "$domain $code";

												// donnees saisies
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
                                                $defaultvalue = 0;
												$nboptions=0;
												if ($tab_scale = explode(",", $scale)){
													for ($i=0; $i<count($tab_scale); $i++){
                                                    	$a_scale_element = trim($tab_scale[$i]);
														if (!empty($a_scale_element)){
															if ($index == $nboptions){
																//$defaultvalue = $tab_scale[$i];
                                                                $defaultvalue = $nboptions;
															}
															$options[$nboptions] = $a_scale_element;
                                                            $nboptions++;
														}
													}
												}


                                            	if ($notframelist || !$objmodifotherframe) {
         											if (!$header){
		    	                        			$components['rlc' . $object->id.'_'.$list->id.'_'.$skill->id] = array(
                                            			'type' => 'radio',
            			        	                    'options' => $options,
                        				                //'help' => $help,
                            	    			        'title' => $str_skill,
                                	        			'defaultvalue' => $defaultvalue,
                                                        'rowsize' => $nboptions,
                                                        'description' => $sdescription,
                                    				);
				    								}
													else if (!$hidden) {
		    	                        			$components['rlc' . $object->id.'_'.$list->id.'_'.$skill->id] = array(
                                            			'type' => 'html',
                               	    			        'title' => $str_skill,
                                	        			'value' => '<b>'.$sdescription.'</b>',
                                    				);

													}
												}

											}
										}
									}
								}
							}

                	        else if ($object->type == 'radio') {
                    	        $val = null;
                        	    if (count_records('artefact_booklet_radio', 'idobject', $object->id) != 0) {
                            	    $res = get_records_array('artefact_booklet_radio', 'idobject', $object->id);
                                	if ($notframelist) {
	                                    $sql = "SELECT * FROM {artefact_booklet_resultradio} WHERE idobject = ? AND idowner = ?";
    	                                $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
        	                            $val = $vals[0];
            	                    }
                	                else if ($objmodifinframe) {
                    	                $val = get_record('artefact_booklet_resultradio', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
                        	        }
                            	    $table = array();
                                	foreach ($res as $value) {
	                                    $table[$value->id] = $value->option;
    	                            }
        	                        if ($notframelist || !$objmodifotherframe) {
            	                        $components['ra' . $object->id] = array(
                	                        'type' => 'radio',
                    	                    'options' => $table,
                        	                'help' => $help,
                            	            'title' => $object->title,
                                	        'defaultvalue' => ((!empty($val)) ? $val->idchoice : NULL),
                                    	);
	                                }
    	                        }
        	                }
            	            else if ($object->type == 'checkbox') {
                	            $val = null;
                    	        if ($notframelist) {
                        	        $sql = "SELECT * FROM {artefact_booklet_resultcheckbox} WHERE idobject = ? AND idowner = ?";
                            	    $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
                                	$val = $vals[0];
	                            }
    	                        else if ($objmodifinframe) {
        	                        $val = get_record('artefact_booklet_resultcheckbox', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
            	                }
                	            if ($notframelist || !$objmodifotherframe) {
                    	            $components['cb' . $object->id] = array(
                        	            'type' => 'checkbox',
                            	        'help' => $help,
                                	    'title' => $object->title,
                                    	'defaultvalue' => ((!empty($val)) ? $val->value : NULL),
	                                );
    	                        }
        	                }
            	            else if ($object->type == 'date') {
                	            $val = null;
                    	        if ($notframelist) {
                        	        $sql = "SELECT * FROM {artefact_booklet_resultdate} WHERE idobject = ? AND idowner = ?";
                            	    $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
	                                $val = $vals[0];
    	                        }
        	                    else if ($objmodifinframe) {
            	                    $val = get_record('artefact_booklet_resultdate', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
                	            }
                    	        if ($notframelist || !$objmodifotherframe) {
                        	        $components['da' . $object->id] = array(
                            	        'type' => 'calendar',
                                	    'caloptions' => array(
	                                    	'showsTime' => false,
    	                                    'ifFormat' => get_string('strfdateofbirth', 'langconfig')
        	                            ),
            	                        'defaultvalue' => ((!empty($val)) ? strtotime($val->value) : time()),
                	                    'title' => $object->title,
                    	                'description' => get_string('dateofbirthformatguide'),
                        	        );
                            	}
	                        }
    	                    else if ($object->type == 'attachedfiles') {
        	                    $vals = array();
            	                if ($notframelist) {
                	                $vals = get_column('artefact_booklet_resultattachedfiles', 'artefact',  'idobject', $object->id, 'idowner', $USER->get('id'));
                    	        }
                        	    else if ($objmodifinframe) {
                            	    $vals = get_column('artefact_booklet_resultattachedfiles', 'artefact',  'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
	                            }
    	                        if ($notframelist || !$objmodifotherframe) {
        	                        $components['af' . $object->id] =  array(
            	                        'type' => 'filebrowser',
                	                    'title' => $object->title,
                    	                'help' => $help,
                        	            'folder' => 0,
                            	        'highlight' => null,
                                	    'browse' => $browse,
	                                    'page' => get_config('wwwroot') . 'artefact/booklet/index.php?' . 'tome=' . $tab->idtome . '&tab=' . $tab->displayorder . '&browse=1',
    	                                'browsehelp' => 'browsemyfiles',
        	                            'config' => array(
            	                            'upload' => true,
                	                        'uploadagreement' => get_config_plugin('artefact', 'file', 'uploadagreement'),
                    	                    'resizeonuploaduseroption' => get_config_plugin('artefact', 'file', 'resizeonuploaduseroption'),
                        	                'resizeonuploaduserdefault' => $USER->get_account_preference('resizeonuploaduserdefault'),
                            	            'createfolder' => false,
                                	        'edit' => false,
                                    	    'select' => true,
	                                    ),
    	                                'defaultvalue' => $vals,
        	                            'selectlistcallback' => 'artefact_get_records_by_id',
            	                        // 'selectcallback' => 'add_attachment',
                	                    // 'unselectcallback' => 'delete_attachment',
                    	            );
                        	    }
	                        }

                        	else if ($object->type == 'freeskills') {
                                $vals = array();
                    	    	if ($notframelist) {
                        	        $sql = "SELECT * FROM {artefact_booklet_frskllresult} WHERE idobject = ? AND idowner = ?";
                            	    $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
								}
       							else if ($objmodifinframe) {
                        	        $sql = "SELECT * FROM {artefact_booklet_frskllresult} WHERE idrecord = ? AND idobject = ? AND idowner = ?";
                            	    $vals = get_records_sql_array($sql, array($record->idrecord, $object->id, $USER->get('id')));
								}
								//print_object($vals);
								//exit;
		                        $alink = '<a href="'.get_config('wwwroot').'/artefact/booklet/freeskills.php?id='.$object->id.'&idrecord='.$idrecord.'&domainsselected=0"><img src="'.$imageaddfreeskills.'" alt="'.$addfreeskillsstr.'" title="'.$addfreeskillsstr.'" /></a> ';
        	                	$components['frsk' . $object->id] = array(
                            	        'type' => 'html',
                	                    'title' => $object->title,
               	                	    'value' =>$alink,
            	    	        );

                            	if ($notframelist || !$objmodifotherframe) {


									if ($vals){
										//print_object($vals);
										//exit;
										foreach ($vals as $val){
		        	                        $header = false;
											$hidden = false;
                        	                $tab_scale = array();
	                                  		//echo "<br />ISDSKILL : ".$val->idskill;
											//exit;
											$skill = get_record('artefact_booklet_skill', 'id', $val->idskill);
											//print_object($skill);

											if ($skill){
                                        		switch ($skill->type){
													case 0 : $header = true; break;
        		                                    case 2 : $hidden = true; break;
													default : break;
												}

    	                                        $options = array();
												$scale = $skill->scale;
												$domain = $skill->domain;
												$sdescription = $skill->description;
                    	                        $code = $skill->code;
												$threshold = $skill->threshold;
                            	                $str_skill = "$domain::$code";

												// donnees saisies
            									$index = $val->value - 1;


												// la boîte de saisie
                                            	$defaultvalue = 0;
												$nboptions=0;
												if ($tab_scale = explode(",", $scale)){
													for ($i=0; $i<count($tab_scale); $i++){
                                                    	$a_scale_element = trim($tab_scale[$i]);
														if (!empty($a_scale_element)){
															if ($index == $nboptions){
																//$defaultvalue = $tab_scale[$i];
                                                                $defaultvalue = $nboptions;
															}
															$options[$nboptions] = $a_scale_element;
                                                            $nboptions++;
														}
													}
												}

       											if (!$header){
		    	                        			$components['frsk' . $object->id.'_'.$skill->id] = array(
                                            			'type' => 'radio',
            			        	                    'options' => $options,
                        				                //'help' => $help,
                            	    			        'title' => $str_skill,
                                	        			'defaultvalue' => $defaultvalue,
                                                        'rowsize' => $nboptions,
                                                        'description' => $sdescription,
                                    				);
			    								}
												else if (!$hidden) {
		    	                        			$components['frsk' . $object->id.'_'.$skill->id] = array(
                                            			'type' => 'html',
                               	    			        'title' => $str_skill,
                                	        			'value' => '<b>'.$sdescription.'</b>',
                                    				);
												}
											}
										}
									}
								}
							}

    	                } // fin de foreach objects
        	        }


					if (count($components) != 0 && $notframelist) {
                	    $elements['idtab'] = array(
	                        'type' => 'hidden',
     	                   'value' => $idtab
        	            );
            	        $elements['idtome'] = array(
                	        'type' => 'hidden',
                    	    'value' => $idtome
	                    );
    	                $components['save' . $frame->id] = array(
        	                'type' => 'submit',
            	            'value' => get_string('valid', 'artefact.booklet'),
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

        	        if (count($components) != 0 && $frame->list) {
            	        if ($objmodif) {
                	         $components['idrecord'] = array(
                    	        'type' => 'hidden',
                        	    'value' => $record->idrecord
                         	);
	                    }
    	                $components['idtab'] = array(
        	                'type' => 'hidden',
            	            'value' => $idtab
                	    );
                    	$components['idtome'] = array(
                        	'type' => 'hidden',
	                        'value' => $idtome
    	                );
        	            $components['list'] = array(
            	            'type' => 'hidden',
                	        'value' => true
                    	);
	                    if ($objmodifinframe) {
    	                    $components['save' . $frame->id] = array(
        	                   'type' => 'submit',
            	               'value' => get_string('modify', 'artefact.booklet'),
                	        );
                    	}
                    	if ($framelistnomodif) {
                        	$components['save' . $frame->id] = array(
                            	'type' => 'submit',
	                            'value' => get_string('save', 'artefact.booklet'),
    	                    );
        	            }
            	        $elements = $components;
                	}

                	$pf = pieform(array(
	                    'name'        => 'pieform'.$frame->id,
    	                'plugintype'  => 'artefact',
        	            'pluginname'  => 'booklet',
            	        'configdirs'  => array(get_config('libroot') . 'form/', get_config('docroot') . 'artefact/file/form/'),
                	    'method'      => 'post',
                    	'renderer'    => 'table',
	                    'successcallback' => 'visualization_submit',
    	                'elements'    => $elements,
                    	'autofocus'   => false,
               	 	));


        	        if ($frame->list) {
            	        if ($framelistnomodif) {
                	        $pf = "<div id='pieform".$frame->id."form' class='hidden'>". $pf. "</div>" .
                              "<button id='addpieform".$frame->id."button' class='cancel' onclick='toggleCompositeForm(&quot;pieform".$frame->id."&quot;);'>".get_string('add','artefact.booklet')."</button>" ;
                    	}
                    	if ($objmodifinframe) {
                        	$pf = $pf . "<button id='addpieform".$frame->id."button' onclick='javascript:history.back()' class='cancel'>". get_string('cancel','artefact.booklet')."</button>";
	                    }

    	                $objects = get_records_array('artefact_booklet_object', 'idframe', $frame->id);

						$titlelink = array();
						$showlink  = array();
                        $editlink  = array();
        	            $item = null;
            	        if ($objects) {
                	        foreach ($objects as $object) {
                    	        if ((substr($object->name, 0, 5) == 'Title' || substr($object->name, 0, 5) == 'title') &&
                        	        ($object->type == 'area' || $object->type == 'shorttext' || $object->type == 'longtext'
                            	     || $object->type == 'synthesis' || $object->type == 'htmltext')) {
                                	$item = $object;
									break;
    	                        }
        	                }
            	        }
                	    if (is_null($item)) {
                    	    $sql = "SELECT * FROM {artefact_booklet_object}
                                WHERE idframe = ?
                                AND displayorder = (SELECT MIN(displayorder)
                                                    FROM {artefact_booklet_object}
                                                    WHERE idframe = ?
                                                    AND (type='area'
                                                         OR type='shorttext'
                                                         OR type='longtext'
                                                         OR type='htmltext'
                                                         OR type='synthesis')
                                                    )";
                        	$item = get_record_sql($sql, array($frame->id, $frame->id));
	                    }
						if ($item){
							//print_object($item);
							//exit;
							$sql = "SELECT re.*, rd.displayorder FROM {artefact_booklet_resulttext} re
 JOIN {artefact_booklet_resultdisplayorder} rd on re.idrecord = rd.idrecord
 WHERE re.idobject = ?
 AND re.idowner=? ORDER BY rd.displayorder ASC ";
 $recs = get_records_sql_array($sql, array($item->id, $USER->get('id')));
 //print_object($recs);
 //exit;
							if ($recs){
								foreach($recs as $rec){
                                            $titlelink[] = $rec->value;
											$showlink[] = '<a href="'.get_config('wwwroot').'/artefact/booklet/index.php?idframe='.$frame->id.'&tab='.$idtab.'&okdisplay=1&idmodifliste='.$rec->id.'"><img src="'.$imageshow.'" alt="'.$showstr.'" title="'.$showstr.'" /></a>';
                                            $editlink[] = '<a href="'.get_config('wwwroot').'/artefact/booklet/index.php?tab='.$idtab.'&idframe='.$frame->id.'&okdisplay=0&idmodifliste='.$rec->id.'"/><img src="'.$imageedit.'" alt="'.$editstr.'" title="'.$editstr.'" /></a>';
								}
							}
						}

                        					// afficher les boutons alternant affichage et edition
$alink1 = '<a href="'.get_config('wwwroot').'/artefact/booklet/index.php?idframe='.$idframe.'&tab='.$idtab.'&okdisplay=1"><img src="'.$imageshow.'" alt="'.$showstr.'" title="'.$showstr.'" /></a>';
$alink2 = '<a href="'.get_config('wwwroot').'/artefact/booklet/index.php?tab='.$idtab.'&okdisplay=0"><img src="'.$imageedit.'" alt="'.$editallstr.'" title="'.$editallstr.'" /></a>';



    	                if ($frame->help != null) {
        	                $aide = '<span class="help"><a href="" onclick="contextualHelp(&quot;pieform'.$frame->id.'&quot;,&quot;'.$frame->id.'&quot;,&quot;artefact&quot;,&quot;booklet&quot;,&quot;&quot;,&quot;&quot;,this); return false;"><img src="'.get_config('wwwroot').'/theme/raw/static/images/help.png" alt="Help" title="Help"></a></span>';
            	        }
                	    else {
                    	    $aide = null;
	                    }

                        $str_tab='';

						for($j=0; $j<count($showlink); $j++){
                        	$str_tab .= '<tr bgcolor="'.((($j % 2) == 0) ? '#ddeeff' : '#ccddff').'">
	<th width="100%">'.$titlelink[$j].'</td>
	<th align="right">'.$showlink[$j].' '.$editlink[$j].'</td>
</tr>';
						}

						 $pf = '<fieldset class="pieform-fieldset"><legend>' . $frame->title . ' ' . $aide . '</legend>
                           <table id="visualization'.$frame->id.'list" class="tablerenderer visualizationcomposite">
                               <thead>
                                   <tr>
                                       <th width="100%">' . (($item) ? $item->title : "") . '</th>
									   <th align="right">'.$alink1.' '.$alink2.'</th>
                                   </tr>
                               </thead>
                               <tbody>  '.$str_tab.'
                               </tbody>
                           </table>
                           ' . $pf . '
                           </fieldset>';
        	        }
            	    $bookletform[$frame->title] = $pf;
            	} // fin de frame
	        //} // fin de foreach frames
		//}
        return $bookletform;
    }
// fin de get_aframeform

//**************************************************************

    public static function get_aframeform_display($idtome, $idtab, $idframe, $idmodifliste = null, $browse) {
        // idmodifliste est l'index dans artefact_booklet_resulttext
        global $USER, $THEME;
        $editstr = get_string('edit','artefact.booklet');
        $imageedit = $THEME->get_url('images/btn_edit.png');
		$showallstr = get_string('showall','artefact.booklet');
        $imageshow = $THEME->get_url('images/btn_info.png');
		$objectlinkedstr = get_string('objectlinked','artefact.booklet');
        $imagelinked = $THEME->get_url('images/btn_show.png', false, 'artefact/booklet');
        $imageaddfreeskills = $THEME->get_url('images/btn_check.png', false, 'artefact/booklet');
        $addfreeskillsstr = get_string('addfreeskills','artefact.booklet');

        require_once(get_config('libroot') . 'pieforms/pieform.php');
        if (!is_null($idmodifliste)) {
            $record = get_record('artefact_booklet_resulttext', 'id', $idmodifliste);
            $objmodif = get_record('artefact_booklet_object', 'id', $record->idobject);
			$idrecord=$record->idrecord;
        }
        else {
            $record = null;
            $objmodif = null;
            $idrecord=0;;
        }
        if (!$tome = get_record('artefact_booklet_tome', 'id', $idtome)) {
            return null;
        }
        foreach (get_records_array('artefact_booklet_tab', 'idtome', $idtome, 'displayorder') as $item) {
            // liste des tabs du tome tries par displayorder
            if ($item->displayorder == $idtab) {
                // parcours pour trouver le tab dont le displayorder est idtab
                $tab = $item;
            }
        }

        $elements = array();
        $components = array();
        $bookletform = array();
        $bookletform["entete"] = $tab->help;

		if ($idframe){
			if ($frame = get_record('artefact_booklet_frame', 'id', $idframe)){
               	$components = array();
                $elements = null;
   	            $components = null;
       	        $pf = null;
           	    // Quatre conditions exclusives
               	$notframelist = !$frame->list;
                $framelistnomodif = $frame->list && !$objmodif;
   	            $objmodifinframe = $objmodif && ($objmodif->idframe == $frame->id);
       	        $objmodifotherframe = $objmodif && ($objmodif->idframe != $frame->id);
                $itementete = null; // titre entete de liste

				// afficher le bouton alternant affichage et edition
                   	$elements['showedit'] = array(
						'type' => 'html',
						'title' => '',
						'value' =>  '<div class="right">
<a href="'.get_config('wwwroot').'/artefact/booklet/index.php?tab='.$idtab.'&okdisplay=0&idframe='.$idframe.'"><img src="'.$imageedit.'" alt="'.$editstr.'" title="'.$editstr.'" /></a>
<a href="'.get_config('wwwroot').'/artefact/booklet/index.php?tab='.$idtab.'&okdisplay=1"><img src="'.$imageshow.'" alt="'.$showallstr.'" title="'.$showallstr.'" /></a>

</div>',
					);


                if ($notframelist) { // ce n'est pas une liste
                    if($objects = get_records_array('artefact_booklet_object', 'idframe', $frame->id, 'displayorder')){
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
                                            $str_reference = display_object_linked($reference->idobjectlinked, $USER->get('id')).' <a href="'.get_config('wwwroot').'/artefact/booklet/index.php?idframe='.$referenceframe->id.'&tab='.$referenceframe->idtab.'&okdisplay=1"><img src="'.$imagelinked.'" alt="'.$objectlinkedstr.'" title="'.$objectlinkedstr.'" /></a>';
											if(!$str_reference){
                                        		$str_reference = get_string('referencehasnovalue','artefact.booklet'). ' <b>'. $objectlinked->title .'</b> '.get_string('offrame','artefact.booklet'). ' <i>'.$referenceframe->title.'</i>';
											}
										}
									}
                                    if ($notframelist) {
                	                	$sql = "SELECT * FROM {artefact_booklet_refresult} WHERE idobject = ? AND idowner = ?";
	                    	            $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
    	                    	        $val = $vals[0];
        	                    	}
	        	                    else if ($objmodifinframe) {
    	        	                    $val = get_record('artefact_booklet_refresult', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
        	        	            }
                        	        if (empty($val)){
										// creer la valeur
										$rec_refresult = new stdclass();
                                        $rec_refresult->idobject = $object->id;
                                        $rec_refresult->idowner = $USER->get('id');
                                        $rec_refresult->idreference = $reference->id;
                                        if ($record) {
											$rec_refresult->idrecord = $record->id;
										}
										else{
                                            $rec_refresult->idrecord = null;
										}
										insert_record('artefact_booklet_refresult', $rec_refresult);
									}

	            	                $rec = false;
    	            	            if (!is_null($record)) {
        	            	            $rec = true;
            	            	    }
                	            	if ($notframelist || !$objmodifotherframe) {
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
								/*
    	                            $components['btn' . $object->id] = array(
        	                            'type' => 'button',
            	                        'value' => get_string('generate', 'artefact.booklet'),
                	                    'onclick' => ($rec ? 'sendjsonrequest(\'compositegeneratesynthesis.php\',
                    	                             {\'idsynthesis\': ' . $object->id . ', \'idrecord\': ' . $record->idrecord . '},
                        	                             \'GET\',
                            	                         function(data) {
                                	                         location.reload(true)
                                    	                 },
                                        	             function() {
                                            	             // @todo error
                                                	     })'
	                                                     : 'sendjsonrequest(\'compositegeneratesynthesis.php\',
    	                                                 {\'idsynthesis\': ' . $object->id . '},
        	                                             \'GET\',
            	                                         function(data) {
                	                                         location.reload(true)
                    	                                 },
                        	                             function() {
                            	                            // @todo error
                                	                     })')
                                	);
									*/

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
													$str_choice = get_skill_choice_display($skill, $index);
                                                    $sdescription = $skill->description;
												}
												else{
                                                    $str_choice = '';
                                                    $sdescription = '<span class="blueback">'.$skill->description.'</span>';
												}

												if (!$hidden){
											        $components['rl' . $object->id.'_'.$list->id.'_'.$skill->id] = array(
	        	        	        	      		'type' => 'html',
		    		                	       		'title' => $skill->domain.' :: '.$skill->code,
    		    		                        	'value' => $sdescription."<br />".$str_choice.'<hr />',
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
                    	    	if ($notframelist) {
                        	        $sql = "SELECT * FROM {artefact_booklet_frskllresult} WHERE idobject = ? AND idowner = ?";
                            	    $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
								}
       							else if ($objmodifinframe) {
                        	        $sql = "SELECT * FROM {artefact_booklet_frskllresult} WHERE idrecord = ? AND idobject = ? AND idowner = ?";
                            	    $vals = get_records_sql_array($sql, array($record->idrecord, $object->id, $USER->get('id')));
								}
								//print_object($vals);
								//exit;
                            	if ($notframelist || !$objmodifotherframe) {
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
												$sdescription = $skill->description;
    	                                        $code = $skill->code;
												$threshold = $skill->threshold;
            	                                $str_skill = "$domain::$code";

												$index = $val->value - 1;

												// la boîte de saisie
           	            	                   	if (!$header){
													$str_choice = get_skill_choice_display($skill, $index);
                                	                $sdescription = $skill->description;
												}
												else{
                                            	    $str_choice = '';
                                                	$sdescription = '<span class="blueback">'.$skill->description.'</span>';
												}

												if (!$hidden){
        	           								$components['frsk' . $object->id.'_'.$skill->id] = array(
	        		        	        	      		'type' => 'html',
		    			                	       		'title' => $skill->domain.' :: '.$skill->code,
    		    			                        	'value' => $sdescription."<br />".$str_choice.'<hr />',
														//'description' => $skill->description,
	    	        				               	);
												}
											}
										}
									}
								}
							}
						} // fin de for each objects
					} // Fin de if objects
				}   // fin de la frame n'est pas une liste


				else {  // La frame est une liste


					$vertical=false;
					$separateur='';
					$intitules = array();
       				$nbrubriques=0;
					$lastposition = array();
                    $rslt='';
                    $edit_link=array();
					$edit_link1 = '<th><a href="'.get_config('wwwroot').'/artefact/booklet/index.php?idframe='.$frame->id.'&tab='.$idtab.'&idmodifliste=';
					$edit_link2 = '&okdisplay=0"><img src="'.$imageedit.'" alt="'.$editstr.'" title="'.$editstr.'" /></a></th>'."\n";

           			if ($objectslist = get_records_array('artefact_booklet_object', 'idframe', $frame->id, 'displayorder')){
						// headers
   	    			    $pos=0;
						foreach ($objectslist as $object) {
           	    			$key=$object->id;
            		        $intitules[$key]= $object->title;
            		        $lastposition[$key]=false;
						}

               			$lastposition[$key]=true;
               			$nbrubriques=count($intitules);
				    	$vertical = ($nbrubriques>5) ? true : false;
   	            		$separateur=($vertical)? '</tr><tr>' : '';
						$n=0;
						$rslt .= "\n<table class=\"tablerenderer2\">";
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
	            	    	        	$sql = "SELECT re.idrecord FROM {artefact_booklet_resulttext} re
	            	            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";
	            	        	    	$listidrecords = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id')));
	            	            		break;
					                case 'radio':
	            		            	$n = count_records('artefact_booklet_resultradio', 'idobject', $objectslist[0]->id, 'idowner', $USER->get('id'));
	            	    	        	$sql = "SELECT re.idrecord FROM {artefact_booklet_resultradio} re
	            	            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";
	            	        	    	$listidrecords = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id')));
	            	            		break;
					                case 'checkbox':
	            		            	$n = count_records('artefact_booklet_resultcheckbox', 'idobject', $objectslist[0]->id, 'idowner', $USER->get('id'));
	            	    	        	$sql = "SELECT re.idrecord FROM {artefact_booklet_resultcheckbox} re
	            	            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";
	            	        	    	$listidrecords = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id')));
	            	            		break;
					                case 'date':
	            		            	$n = count_records('artefact_booklet_resultdate', 'idobject', $objectslist[0]->id, 'idowner', $USER->get('id'));
	            	    	        	$sql = "SELECT re.idrecord FROM {artefact_booklet_resultdate} re
	            	            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            	        WHERE re.idobject = ?
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
	            	    	        	$sql = "SELECT re.idrecord FROM {'artefact_booklet_lskillsresult'} re
	            	            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";
	            	        	    	$listidrecords = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id')));
	            	            		break;
					                case 'reference':
	            		            	$n = count_records('artefact_booklet_refresult', 'idobject', $objectslist[0]->id, 'idowner', $USER->get('id'));
	            	    	        	$sql = "SELECT re.idrecord FROM {'artefact_booklet_refresult'} re
	            	            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";
	            	        	    	$listidrecords = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id')));
	            	            		break;
					                case 'freeskills':
	            		            	//$n = count_records('artefact_booklet_frskllresult', 'idobject', $objectslist[0]->id, 'idowner', $USER->get('id'));
	            	    	        	$sql = "SELECT DISTINCT idrecord FROM {'artefact_booklet_frskllresult'}
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?";
                                        $n = count($recs = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id'))));

	            	    	        	$sql = "SELECT re.idrecord FROM {'artefact_booklet_frskllresult'} re
	            	            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";
	            	        	    	$listidrecords = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id')));
	            	            		break;

            			} // Fin du switch

                        //echo "<br /> DEBUG :: 4458\n";
						//print_object($listidrecords);

						// construction d'un tableau des lignes : une par element, chaque ligne contient les valeurs de tous les objets
					    $ligne = array();

						for ($i = 0; $i <= $n; $i++) {
            	   			$ligne[$i] = "";
                            $edit_link[] = 10000000000000000000;
					    }

						// pour chaque objet, on complete toutes les lignes
				        foreach ($objectslist as $object) {
				    	    if ($object->type == 'longtext' || $object->type == 'shorttext' || $object->type == 'area'
												|| $object->type == 'htmltext' || $object->type == 'synthesis') {
            		    	    $sql = "SELECT re.*, rd.displayorder FROM {artefact_booklet_resulttext} re
 JOIN {artefact_booklet_resultdisplayorder} rd
 ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
 WHERE re.idobject = ?
 AND re.idowner = ?
 ORDER BY rd.displayorder";
		                        if ($txts = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
									//echo "<br /> DEBUG :: 4479\n";
									//print_object($txts);
									//exit;

		                        	$i = 0;
					           	    foreach ($txts as $txt) {
										if (!empty($txt) && isset($txt->value) ){
	                                        if ($txt->id < $edit_link[$i]){
												$edit_link[$i]=$txt->id; // recuperer la valeur courante de $idmodifliste a savoir l'id de artefact_booklet_resulttext
											}

														if ($vertical){
						           	            	        $ligne[$i].= "<th>".$intitules[$object->id]. "</th>";
														}
														$ligne[$i].="<td class=\"tablerenderer2\">". $txt->value . "</td>";
														if ($vertical){
															if (!$lastposition[$object->id]){
																$ligne[$i].=$separateur;
															}
															else{
	                        		        	    			$ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
															}
														}
														else if ($lastposition[$object->id]){
        		                                            $ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
														}

					           	            	    	$i++ ;
										}
	    				    		}
                				}
							}
                            else if ($object->type == 'reference') {
	        	                $sql = "SELECT * FROM {artefact_booklet_refresult} re
 JOIN {artefact_booklet_resultdisplayorder} rd
  ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
 JOIN {artefact_booklet_reference} ra
  ON (ra.id = re.idreference)
 WHERE re.idobject = ?
  AND re.idowner = ?
 ORDER BY rd.displayorder";
		                        if ($vals = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
				           	        $i = 0;
									if (!empty($vals)){
		    	                    	foreach ($vals as $val){
			                                $str_reference = '';
											if ($reference = get_record('artefact_booklet_reference', 'id', $val->idreference)){
												if ($objectlinked = get_record('artefact_booklet_object', 'id', $reference->idobjectlinked)){
													if ($referenceframe = get_record('artefact_booklet_frame', 'id', $objectlinked->idframe)){
                                                        $str_reference = display_object_linked($reference->idobjectlinked, $USER->get('id')).' <a href="'.get_config('wwwroot').'/artefact/booklet/index.php?idframe='.$referenceframe->id.'&tab='.$referenceframe->idtab.'&okdisplay=1"><img src="'.$imagelinked.'" alt="'.$objectlinkedstr.'" title="'.$objectlinkedstr.'" /></a>';
														if(!$str_reference){
            			                            		$str_reference = get_string('referencehasnovalue','artefact.booklet'). ' <b>'. $objectlinked->title .'</b> '.get_string('offrame','artefact.booklet'). ' <i>'.$referenceframe->title.'</i>';
														}
													}
												}
                                            	if ($vertical){
        		    	       		        		$ligne[$i].= "<th class=\"tablerenderer2\">".$intitules[$object->id]. "</th>";
												}
			        	   		            	$ligne[$i].= "<td class=\"tablerenderer2\">".$str_reference. "</td>";
												if ($vertical){
													if (!$lastposition[$object->id]){
														$ligne[$i].=$separateur;
													}
													else{
            	    		        	    			$ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
													}
												}
												else if ($lastposition[$object->id]){
													$ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
												}
											}
				        	    		    $i++ ;
        		    	            	}
									}
								}
	                        }


                        	else if ($object->type == 'freeskills') {
                                $vals = array();
                            	$i = 0;
								if ($vertical){
        	           		       	$ligne[$i].= "<th class=\"tablerenderer2\">".$intitules[$object->id]. "</th>";
								}
                                $ligne[$i].= "<td class=\"tablerenderer2\">\n<table class=\"tablerenderer3\">\n";
								// ATTENTION : Il y a un regroupement par idrecord
								$str_skills='';
                           		$sql = "SELECT * FROM {artefact_booklet_frskllresult} re
 JOIN {artefact_booklet_resultdisplayorder} rd
 ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
 WHERE re.idobject = ?
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
				            	    			   		$ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
													}
												}
												else if ($lastposition[$object->id]){
                                                    $ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
												}

												$i++;
												if ($vertical){
        	           		       					$ligne[$i].= "<th class=\"tablerenderer2\">".$intitules[$object->id]. "</th>";
												}
                                				$ligne[$i].= "<td class=\"tablerenderer2\">\n<table class=\"tablerenderer3\">\n";

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
												$sdescription = $skill->description;
    		                                    $code = $skill->code;
												$threshold = $skill->threshold;
            		                            $str_skill = "$domain::$code";

												$index = $val->value - 1;
                            	                if (!$header){
													$str_choice = get_skill_choice_display($skill, $index);
                                		            $sdescription = $skill->description;
												}
												else{
    	                                            $str_choice = '';
        	                                      	$sdescription = '<span class="blueback">'.$skill->description.'</span>';
												}

												if (!$hidden){
  							           	        	$str_skills .= '<tr><td class="tablerenderer3">&nbsp;'.$skill->domain.'&nbsp;</td><td class="tablerenderer3">&nbsp;<i>'.$skill->code.'</i>&nbsp;</td></tr><tr><td colspan="2" class="tablerenderer3">&nbsp;'.strip_tags($sdescription).'&nbsp;</td></tr><tr><td colspan="2" class="tablerenderer3">&nbsp;'.$str_choice.'&nbsp;</td></tr>'."\n";
												}
											}
										}
									}
								}
								$ligne[$i].=$str_skills."\n</table></td>\n";

								if ($vertical){
									if (!$lastposition[$object->id]){
										$ligne[$i].=$separateur;
									}
									else{
            	    			   		$ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
									}
								}
								else if ($lastposition[$object->id]){
									$ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
								}

            	    		    $i++;
							}

							//--------------------- LISTSKILLS -----------------------------------
           	    			else if ($object->type == 'listskills') {
								if ($list = get_record('artefact_booklet_list', 'idobject', $object->id)){
                            		$i = 0;
									if ($vertical){
        	           		        	$ligne[$i].= "<th class=\"tablerenderer2\">".$intitules[$object->id]. "</th>";
									}
                                	$ligne[$i].= "<td class=\"tablerenderer2\">\n<table class=\"tablerenderer3\">\n";
									$str_skills='';

	    	        	            if ($res_lofskills = get_records_array('artefact_booklet_listofskills', 'idlist', $list->id)){
										foreach ($res_lofskills as $res){
                                        	$header = false;
											$hidden = false;
											if ($skill = get_record('artefact_booklet_skill', 'id', $res->idskill)){
                                            	switch ($skill->type){
													case 0 : $header = true; break;
		                                            case 2 : $hidden = true; break;
													default : break;
												}

												$index = 0;
												// donnees saisies
       		        		                    $rec=null;
                           		                $sql = "SELECT * FROM {artefact_booklet_lskillsresult} re
 JOIN {artefact_booklet_resultdisplayorder} rd
 ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
 JOIN {artefact_booklet_skill} rs
 ON (rs.id = re.idskill)
 WHERE re.idobject = ?
 AND re.idowner = ?
 AND re.idskill = ?
 ORDER BY re.idrecord, rd.displayorder";
                           			            if ($recs = get_records_sql_array($sql,  array($object->id, $USER->get('id'), $skill->id))){
                                   		        	foreach ($recs as $rec){
                                       		        	if ($rec){
															$index = $rec->value - 1;   // la valeur stockee n'est pas un indice mais une position
															// la boîte de saisie
   			       	                        	    		if (!$header){
																$str_choice = get_skill_choice_display($skill, $index);
           	        		                        	    	$sdescription = $skill->description;
															}
															else{
   	                    	                    				$str_choice = '';
           	                	                        		$sdescription = '<span class="blueback">'.$skill->description.'</span>';
															}
															if (!$hidden){
               						           	        		$str_skills .= '<tr><td class="tablerenderer3">&nbsp;'.$skill->domain.'&nbsp;</td><td class="tablerenderer3">&nbsp;<i>'.$skill->code.'</i>&nbsp;</td></tr><tr><td colspan="2" class="tablerenderer3">&nbsp;'.strip_tags($sdescription).'&nbsp;</td></tr><tr><td colspan="2" class="tablerenderer3">&nbsp;'.$str_choice.'&nbsp;</td></tr>'."\n";
                           	                            	}
														}
				           							}
												}
											}
										}
									}
									$ligne[$i].=$str_skills."\n</table></td>\n";

									if ($vertical){
										if (!$lastposition[$object->id]){
											$ligne[$i].=$separateur;
										}
										else{
            	    				   		$ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
										}
									}
									else if ($lastposition[$object->id]){
                                     	$ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
									}

            	    		    	$i++;
								}
							}
							else if ($object->type == 'radio') {
	        	               	$sql = "SELECT * FROM {artefact_booklet_resultradio} re
 JOIN {artefact_booklet_resultdisplayorder} rd
  ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
 JOIN {artefact_booklet_radio} ra
  ON (ra.id = re.idchoice)
 WHERE re.idobject = ?
  AND re.idowner = ?
 ORDER BY rd.displayorder";
		                       	if ($radios = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
				           	       	$i = 0;
									if (!empty($radios)){
			    	                   	foreach ($radios as $radio){
											if ($vertical){
    	    		    	   		       	    $ligne[$i].= "<th>".$intitules[$object->id]. "</th>";
											}
				        		            $ligne[$i].= "<td class=\"tablerenderer2\">".$radio->option . "</td>";
											if ($vertical){
												if (!$lastposition[$object->id]){
													$ligne[$i].=$separateur;
												}
												else{
            	    		       		        	$ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
												}
											}
											else if ($lastposition[$object->id]){
                                             $ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
											}

    			        	   		   	    $i++ ;
        			    	           	}
									}
								}
				   			}

							else if ($object->type == 'checkbox') {
            	    			$sql = "SELECT * FROM {artefact_booklet_resultcheckbox} re
 JOIN {artefact_booklet_resultdisplayorder} rd
  ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
 WHERE re.idobject = ?
  AND re.idowner = ?
 ORDER BY rd.displayorder";
	            	            if ($checkboxes = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
		            	        	$i = 0;
	    		        	    	foreach ($checkboxes as $checkbox) {
										if ($vertical){
   	       		            	    	   	$ligne[$i].= "<th>".$intitules[$object->id]. "</th>";
										}
	       	    	            	    $ligne[$i].= "<td class=\"tablerenderer2\">".($checkbox->value ? get_string('true', 'artefact.booklet')  : get_string('false', 'artefact.booklet') ) . "</td>";
										if ($vertical){
											if (!$lastposition[$object->id]){
												$ligne[$i].=$separateur;
											}
											else{
                		        		    	$ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
											}
										}
										else if ($lastposition[$object->id]){
											$ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
										}

    	               	    			$i++ ;
	    	       	    			}
	    						}
							}
					        else if ($object->type == 'date') {
    	    	    		            	$sql = "SELECT * FROM {artefact_booklet_resultdate} re
	            		            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            		            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	    	        	        WHERE re.idobject = ?
	            	        	    	        AND re.idowner = ?
	            	            		        ORDER BY rd.displayorder";
        	    	    	        		if ($dates = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
	            			            		$i = 0;
		            	    		        	foreach ($dates as $date) {
													if ($vertical){
  	    		                   	            		$ligne[$i].= "<th>".$intitules[$object->id]. "</th>";
													}
	            			            	    	$ligne[$i].= "<td class=\"tablerenderer2\">".format_date(strtotime($date->value), 'strftimedate') . "</td>";
													if ($vertical){
														if (!$lastposition[$object->id]){
															$ligne[$i].=$separateur;
														}
														else{
            			            			           	$ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
														}
													}
													else if ($lastposition[$object->id]){
														$ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
													}
	   		        	            		    	$i++ ;
	    		        	            		}
   							        }
							}
				    	    else if ($object->type == 'attachedfiles') {
            		    	        $sql = "SELECT * FROM {artefact_booklet_resultattachedfiles} re
	            	        	    	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            		        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            		        WHERE re.idobject = ?
	            	            	    	    AND re.idowner = ?
	            	            	        	ORDER BY rd.displayorder";
			            	        if ($attachedfiles = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
    			        	        	for ($i = 0; $i < $n; $i++) {
											if ($vertical){
    	    	    	               		    $ligne[$i].= "<th class=\"tablerenderer2\">".$intitules[$object->id]. "</th>";
											}
            			    		   	    $ligne[$i].= "<td class=\"tablerenderer2\"><table>";
            		    	        	}
		            			       	if (!empty($attachedfiles)){
    		        		    	   		foreach ($attachedfiles as $attachedfile) {
            			    	    	    	    	$f = artefact_instance_from_id($attachedfile->artefact);
            	    		    	    		    	$j = 0;
            	            					    	foreach ($listidrecords as $idrc) {
            	            	    				    	if ($attachedfile->idrecord == $idrc->idrecord) {
	            	            	           					$i = $j;
			            	            	        		}
	    			        	            	        	$j++;
    	        				            	    	}
        	    		    		        	    	$ligne[$i].= "<tr><td class=\"tablerenderer2\"><img src=" .
            		    	        			    		$f->get_icon(array('id' => $attachedfile->artefact, 'viewid' => isset($options['viewid']) ? $options['viewid'] : 0)) .
 " alt=''></td><td class=\"tablerenderer2\"><a href=" .
 get_config('wwwroot') . "artefact/file/download.php?file=" . $attachedfile->artefact .
 ">" . $f->title . "</a> (" . $f->describe_size() . ")" . $f->description . "</td></tr>";
			   		       	            }
									}
	        	    	   		    for ($i = 0; $i < $n; $i++) {
										$ligne[$i] .= "</table></td>";
										if ($vertical){
											if (!$lastposition[$object->id]){
												$ligne[$i].=$separateur;
											}
											else{
            	    	       		            $ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
											}
										}
										else if ($lastposition[$object->id]){
		                                     $ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
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
			} // Fin de if Frame
		}   // Fin de if frameid

		if (count($components) != 0 && $notframelist) {
        	   	$elements['idtab'] = array(
                	'type' => 'hidden',
               	    'value' => $idtab
	           	);
		        $elements['idtome'] = array(
    		       	'type' => 'hidden',
        	        'value' => $idtome
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

        if (count($components) != 0 && $frame->list) {
				$elements['idtab'] = array(
					'type' => 'hidden',
                	'value' => $idtab
				);

				$elements['idrecord'] = array(
					'type' => 'hidden',
                	'value' => $idrecord,
				);

		        $elements['idtome'] = array(
    		    	'type' => 'hidden',
        	        'value' => $idtome
            	);
		        $elements['list'] = array(
    		    	'type' => 'hidden',
        	        'value' => true
            	);
                $elements[$frame->id] = array(
	            	'type' => 'fieldset',
	            	'legend' => $frame->title,
		            'help' => ($frame->help != null),
    		        'elements' => $components

				);
		}


   	    $pf = pieform(array(
        	    'name'        => 'pieform'.$frame->id,
            	'plugintype'  => 'artefact',
	            'pluginname'  => 'booklet',
		        'configdirs'  => array(get_config('libroot') . 'form/', get_config('docroot') . 'artefact/file/form/'),
			    'method'      => 'post',
    	    	'renderer'    => 'table',
        	    'successcallback' => '',
            	'elements'    => $elements,
	            'autofocus'   => false,
		));
       	$bookletform[$frame->title] = $pf;

        return $bookletform;
    }
    // fin de get_aframeform_display



	/********************************************************************************
	 *
	 *
	 *     VISUALIZATION GET FOM DISPLAY
	 *
	 *
	 *   ***************************************************************************/

    public static function get_form_display($idtome, $idtab, $idmodifliste = null, $browse) {
        // idmodifliste est l'index dans artefact_booklet_resulttext
        global $USER, $THEME;
        $editstr = get_string('edit','artefact.booklet');
        $editallstr = get_string('editall','artefact.booklet');
        $imageedit = $THEME->get_url('images/btn_edit.png');
		$showallstr = get_string('showall','artefact.booklet');
        $imageshow = $THEME->get_url('images/btn_info.png');
		$objectlinkedstr = get_string('objectlinked','artefact.booklet');
        $imagelinked = $THEME->get_url('images/btn_show.png', false, 'artefact/booklet');
        $imageaddfreeskills = $THEME->get_url('images/btn_check.png', false, 'artefact/booklet');
        $addfreeskillsstr = get_string('addfreeskills','artefact.booklet');


        require_once(get_config('libroot') . 'pieforms/pieform.php');
        if (!is_null($idmodifliste)) {
            $record = get_record('artefact_booklet_resulttext', 'id', $idmodifliste);
            $idrecord = $record->idrecord;
            $objmodif = get_record('artefact_booklet_object', 'id', $record->idobject);
        }
        else {
            $record = null;
            $idrecord=0;
            $objmodif = null;
        }
        if (!$tome = get_record('artefact_booklet_tome', 'id', $idtome)) {
            return null;
        }
        foreach (get_records_array('artefact_booklet_tab', 'idtome', $idtome, 'displayorder') as $item) {
            // liste des tabs du tome tries par displayorder
            if ($item->displayorder == $idtab) {
                // parcours pour trouver le tab dont le displayorder est idtab
                $tab = $item;
            }
        }

		// Restitution des frames ordonnee en profondeur d'abord
		$tabaff_codes = get_frames_codes_ordered($tab->id);

		//echo "<br />DEBUG :: lib.php :: 2016 <br />\n";
		//foreach ($tabaff_codes as $key => $val){
        //    echo "<br />DEBUG :: ".$key."=".$val."\n";
		//}
		//exit;


        $elements = array();
        $components = array();
        $bookletform = array();
        $bookletform["entete"] = $tab->help;
        $drapeau=true; // on affiche le bouton prmettant d'editer la page
        if ($tabaff_codes) {
            foreach ($tabaff_codes as $key => $framecode) {
				if ($frame = get_record('artefact_booklet_frame', 'id', $key)){
					// ***************************************
	               	$components = array();
	                $elements = null;
   		            $components = null;
       		        $pf = null;
	           	    // Quatre conditions exclusives
    	           	$notframelist = !$frame->list;
        	        $framelistnomodif = $frame->list && !$objmodif;
   	        	    $objmodifinframe = $objmodif && ($objmodif->idframe == $frame->id);
       	        	$objmodifotherframe = $objmodif && ($objmodif->idframe != $frame->id);
	                $itementete = null; // titre entete de liste

				// afficher le bouton alternant affichage et edition
   				if ($drapeau){  // afficher le bouton
                   	$elements['showedit'] = array(
						'type' => 'html',
						'title' => '',
						'value' =>  '<div class="right">
<a href="'.get_config('wwwroot').'/artefact/booklet/index.php?tab='.$idtab.'&okdisplay=0"><img src="'.$imageedit.'" alt="'.$editallstr.'" title="'.$editallstr.'" /></a>
<a href="'.get_config('wwwroot').'/artefact/booklet/index.php?tab='.$idtab.'&okdisplay=1"><img src="'.$imageshow.'" alt="'.$showallstr.'" title="'.$showallstr.'" /></a>
</div>',
					);
					$drapeau=false;
				}

                	if ($notframelist) { // ce n'est pas une liste
                    	if($objects = get_records_array('artefact_booklet_object', 'idframe', $frame->id, 'displayorder')){
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
                                            $str_reference = display_object_linked($reference->idobjectlinked, $USER->get('id')).' <a href="'.get_config('wwwroot').'/artefact/booklet/index.php?idframe='.$referenceframe->id.'&tab='.$referenceframe->idtab.'&okdisplay=1"><img src="'.$imagelinked.'" alt="'.$objectlinkedstr.'" title="'.$objectlinkedstr.'" /></a>';
											if (!$str_reference){
            			                    	$str_reference = get_string('referencehasnovalue','artefact.booklet'). ' <b>'. $objectlinked->title .'</b> '.get_string('offrame','artefact.booklet'). ' <i>'.$referenceframe->title.'</i>';
											}
										}
									}
                                    if ($notframelist) {
                	                	$sql = "SELECT * FROM {artefact_booklet_refresult} WHERE idobject = ? AND idowner = ?";
	                    	            $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
    	                    	        $val = $vals[0];
        	                    	}
	        	                    else if ($objmodifinframe) {
    	        	                    $val = get_record('artefact_booklet_refresult', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
        	        	            }
                        	        if (empty($val)){
										// creer la valeur
										$rec_refresult = new stdclass();
                                        $rec_refresult->idobject = $object->id;
                                        $rec_refresult->idowner = $USER->get('id');
                                        $rec_refresult->idreference = $reference->id;
                                        if ($record) {
											$rec_refresult->idrecord = $record->id;
										}
										else{
                                            $rec_refresult->idrecord = null;
										}
										insert_record('artefact_booklet_refresult', $rec_refresult);
									}

	            	                $rec = false;
    	            	            if (!is_null($record)) {
        	            	            $rec = true;
            	            	    }
                	            	if ($notframelist || !$objmodifotherframe) {
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
									/*
    	                            $components['btn' . $object->id] = array(
        	                            'type' => 'button',
            	                        'value' => get_string('generate', 'artefact.booklet'),
                	                    'onclick' => ($rec ? 'sendjsonrequest(\'compositegeneratesynthesis.php\',
                    	                             {\'idsynthesis\': ' . $object->id . ', \'idrecord\': ' . $record->idrecord . '},
                        	                             \'GET\',
                            	                         function(data) {
                                	                         location.reload(true)
                                    	                 },
                                        	             function() {
                                            	             // @todo error
                                                	     })'
	                                                     : 'sendjsonrequest(\'compositegeneratesynthesis.php\',
    	                                                 {\'idsynthesis\': ' . $object->id . '},
        	                                             \'GET\',
            	                                         function(data) {
                	                                         location.reload(true)
                    	                                 },
                        	                             function() {
                            	                            // @todo error
                                	                     })')
                        	        	);
										*/

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
														$str_choice = get_skill_choice_display($skill, $index);
            	                                        $sdescription = $skill->description;
													}
													else{
                        	                            $str_choice = '';
                            	                        $sdescription = '<span class="blueback">'.$skill->description.'</span>';
													}
													if (!$hidden){
												        $components['rl' . $object->id.'_'.$list->id.'_'.$skill->id] = array(
	    		    	        	        	      		'type' => 'html',
		    				                	       		'title' => $skill->domain.' :: '.$skill->code,
    		    				                        	'value' => $sdescription."<br />".$str_choice.'<hr />',
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
    	                	    	if ($notframelist) {
        	                	        $sql = "SELECT * FROM {artefact_booklet_frskllresult} WHERE idobject = ? AND idowner = ?";
            	                	    $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
									}
       								else if ($objmodifinframe) {
                        		        $sql = "SELECT * FROM {artefact_booklet_frskllresult} WHERE idrecord = ? AND idobject = ? AND idowner = ?";
                            		    $vals = get_records_sql_array($sql, array($record->idrecord, $object->id, $USER->get('id')));
									}
									//print_object($vals);
									//exit;
/*

	        	                   	$alink = '<a href="'.get_config('wwwroot').'/artefact/booklet/freeskills.php?id='.$object->id.'&idrecord='.$idrecord.'&domainsselected=0"><img src="'.$imageaddfreeskills.'" alt="'.$addfreeskillsstr.'" title="'.$addfreeskillsstr.'" /></a> ';
       	        	        		$components['frsk' . $object->id] = array(
                       	    	        	'type' => 'html',
               	        	            	'title' => $object->title,
           	                		    	'value' =>$alink,
           	    	            	);
*/
        	                    	if ($notframelist || !$objmodifotherframe) {

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
													$sdescription = $skill->description;
    	        	                                $code = $skill->code;
													$threshold = $skill->threshold;
            	        	                        $str_skill = "$domain::$code";

													$index = $val->value - 1;

													// la boîte de saisie
           	            	        	           	if (!$header){
														$str_choice = get_skill_choice_display($skill, $index);
                                	        	        $sdescription = $skill->description;
													}
													else{
            	                                	    $str_choice = '';
    	                                            	$sdescription = '<span class="blueback">'.$skill->description.'</span>';
													}

													if (!$hidden){
        	        	   								$components['frsk' . $object->id.'_'.$skill->id] = array(
	        		    	    	        	      		'type' => 'html',
		    			    	            	       		'title' => $skill->domain.' :: '.$skill->code,
    		    			    	                    	'value' => $sdescription."<br />".$str_choice.'<hr />',
															//'description' => $skill->description,
	    	        				    	           	);
													}
												}
											}
										}
									}
								}
							} // fin de for each objects
						} // Fin de if objects
					}   // fin de la frame n'est pas une liste


				else {  // La frame est une liste


					$vertical=false;
					$separateur='';
					$intitules = array();
       				$nbrubriques=0;
					$lastposition = array();
                    $rslt='';
                    $edit_link=array();
					$edit_link1 = '<th><a href="'.get_config('wwwroot').'/artefact/booklet/index.php?idframe='.$frame->id.'&tab='.$idtab.'&idmodifliste=';
					$edit_link2 = '&okdisplay=0"><img src="'.$imageedit.'" alt="'.$editstr.'" title="'.$editstr.'" /></a></th>'."\n";

           			if ($objectslist = get_records_array('artefact_booklet_object', 'idframe', $frame->id, 'displayorder')){
						// headers
   	    			    $pos=0;
						foreach ($objectslist as $object) {
           	    			$key=$object->id;
            		        $intitules[$key]= $object->title;
            		        $lastposition[$key]=false;
						}

               			$lastposition[$key]=true;
               			$nbrubriques=count($intitules);
				    	$vertical = ($nbrubriques>5) ? true : false;
   	            		$separateur=($vertical)? '</tr><tr>' : '';
						$n=0;
						$rslt .= "\n<table class=\"tablerenderer2\">";
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
	            	    	        	$sql = "SELECT re.idrecord FROM {artefact_booklet_resulttext} re
	            	            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";
	            	        	    	$listidrecords = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id')));
	            	            		break;
					                case 'radio':
	            		            	$n = count_records('artefact_booklet_resultradio', 'idobject', $objectslist[0]->id, 'idowner', $USER->get('id'));
	            	    	        	$sql = "SELECT re.idrecord FROM {artefact_booklet_resultradio} re
	            	            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";
	            	        	    	$listidrecords = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id')));
	            	            		break;
					                case 'checkbox':
	            		            	$n = count_records('artefact_booklet_resultcheckbox', 'idobject', $objectslist[0]->id, 'idowner', $USER->get('id'));
	            	    	        	$sql = "SELECT re.idrecord FROM {artefact_booklet_resultcheckbox} re
	            	            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";
	            	        	    	$listidrecords = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id')));
	            	            		break;
					                case 'date':
	            		            	$n = count_records('artefact_booklet_resultdate', 'idobject', $objectslist[0]->id, 'idowner', $USER->get('id'));
	            	    	        	$sql = "SELECT re.idrecord FROM {artefact_booklet_resultdate} re
	            	            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            	        WHERE re.idobject = ?
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
	            	    	        	$sql = "SELECT re.idrecord FROM {'artefact_booklet_lskillsresult'} re
	            	            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";
	            	        	    	$listidrecords = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id')));
	            	            		break;
					                case 'reference':
	            		            	$n = count_records('artefact_booklet_refresult', 'idobject', $objectslist[0]->id, 'idowner', $USER->get('id'));
	            	    	        	$sql = "SELECT re.idrecord FROM {'artefact_booklet_refresult'} re
	            	            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";
	            	        	    	$listidrecords = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id')));
	            	            		break;
					                case 'freeskills':
	            		            	//$n = count_records('artefact_booklet_frskllresult', 'idobject', $objectslist[0]->id, 'idowner', $USER->get('id'));
	            	    	        	$sql = "SELECT DISTINCT idrecord FROM {'artefact_booklet_frskllresult'}
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?";
                                        $n = count($recs = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id'))));

	            	    	        	$sql = "SELECT re.idrecord FROM {'artefact_booklet_frskllresult'} re
	            	            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            	        WHERE re.idobject = ?
	            	            	        AND re.idowner = ?
	            	            	        ORDER BY rd.displayorder";
	            	        	    	$listidrecords = get_records_sql_array($sql, array($objectslist[0]->id, $USER->get('id')));
	            	            		break;

            			} // Fin du switch

                        //echo "<br /> DEBUG :: 4458\n";
						//print_object($listidrecords);

						// construction d'un tableau des lignes : une par element, chaque ligne contient les valeurs de tous les objets
					    $ligne = array();

						for ($i = 0; $i <= $n; $i++) {
            	   			$ligne[$i] = "";
                            $edit_link[] = 10000000000000000000;
					    }

						// pour chaque objet, on complete toutes les lignes
				        foreach ($objectslist as $object) {
				    	    if ($object->type == 'longtext' || $object->type == 'shorttext' || $object->type == 'area'
												|| $object->type == 'htmltext' || $object->type == 'synthesis') {
            		    	    $sql = "SELECT re.*, rd.displayorder FROM {artefact_booklet_resulttext} re
 JOIN {artefact_booklet_resultdisplayorder} rd
 ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
 WHERE re.idobject = ?
 AND re.idowner = ?
 ORDER BY rd.displayorder";
		                        if ($txts = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
									//echo "<br /> DEBUG :: 4479\n";
									//print_object($txts);
									//exit;

		                        	$i = 0;
					           	    foreach ($txts as $txt) {
										if (!empty($txt) && isset($txt->value) ){
	                                        if ($txt->id < $edit_link[$i]){
												$edit_link[$i]=$txt->id; // recuperer la valeur courante de $idmodifliste a savoir l'id de artefact_booklet_resulttext
											}

														if ($vertical){
						           	            	        $ligne[$i].= "<th>".$intitules[$object->id]. "</th>";
														}
														$ligne[$i].="<td class=\"tablerenderer2\">". $txt->value . "</td>";
														if ($vertical){
															if (!$lastposition[$object->id]){
																$ligne[$i].=$separateur;
															}
															else{
	                        		        	    			$ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
															}
														}
														else if ($lastposition[$object->id]){
        		                                            $ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
														}

					           	            	    	$i++ ;
										}
	    				    		}
                				}
							}
                            else if ($object->type == 'reference') {
	        	                $sql = "SELECT * FROM {artefact_booklet_refresult} re
 JOIN {artefact_booklet_resultdisplayorder} rd
  ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
 JOIN {artefact_booklet_reference} ra
  ON (ra.id = re.idreference)
 WHERE re.idobject = ?
  AND re.idowner = ?
 ORDER BY rd.displayorder";
		                        if ($vals = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
				           	        $i = 0;
									if (!empty($vals)){
		    	                    	foreach ($vals as $val){
			                                $str_reference = '';
											if ($reference = get_record('artefact_booklet_reference', 'id', $val->idreference)){
												if ($objectlinked = get_record('artefact_booklet_object', 'id', $reference->idobjectlinked)){
													if ($referenceframe = get_record('artefact_booklet_frame', 'id', $objectlinked->idframe)){
                                                        $str_reference = display_object_linked($reference->idobjectlinked, $USER->get('id')).' <a href="'.get_config('wwwroot').'/artefact/booklet/index.php?idframe='.$referenceframe->id.'&tab='.$referenceframe->idtab.'&okdisplay=1"><img src="'.$imagelinked.'" alt="'.$objectlinkedstr.'" title="'.$objectlinkedstr.'" /></a>';
														if(!$str_reference){
            			                            		$str_reference = get_string('referencehasnovalue','artefact.booklet'). ' <b>'. $objectlinked->title .'</b> '.get_string('offrame','artefact.booklet'). ' <i>'.$referenceframe->title.'</i>';
														}
													}
												}
                                            	if ($vertical){
        		    	       		        		$ligne[$i].= "<th class=\"tablerenderer2\">".$intitules[$object->id]. "</th>";
												}
			        	   		            	$ligne[$i].= "<td class=\"tablerenderer2\">".$str_reference. "</td>";
												if ($vertical){
													if (!$lastposition[$object->id]){
														$ligne[$i].=$separateur;
													}
													else{
            	    		        	    			$ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
													}
												}
												else if ($lastposition[$object->id]){
													$ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
												}
											}
				        	    		    $i++ ;
        		    	            	}
									}
								}
	                        }

                        	else if ($object->type == 'freeskills') {
                                $vals = array();
                            	$i = 0;
								if ($vertical){
        	           		       	$ligne[$i].= "<th class=\"tablerenderer2\">".$intitules[$object->id]. "</th>";
								}
                                $ligne[$i].= "<td class=\"tablerenderer2\">\n<table class=\"tablerenderer3\">\n";
								// ATTENTION : Il y a un regroupement par idrecord
								$str_skills='';
                           		$sql = "SELECT * FROM {artefact_booklet_frskllresult} re
 JOIN {artefact_booklet_resultdisplayorder} rd
 ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
 WHERE re.idobject = ?
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
				            	    			   		$ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
													}
												}
												else if ($lastposition[$object->id]){
                                                    $ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
												}

												$i++;
												if ($vertical){
        	           		       					$ligne[$i].= "<th class=\"tablerenderer2\">".$intitules[$object->id]. "</th>";
												}
                                				$ligne[$i].= "<td class=\"tablerenderer2\">\n<table class=\"tablerenderer3\">\n";

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
												$sdescription = $skill->description;
    		                                    $code = $skill->code;
												$threshold = $skill->threshold;
            		                            $str_skill = "$domain::$code";

												$index = $val->value - 1;
                            	                if (!$header){
													$str_choice = get_skill_choice_display($skill, $index);
                                		            $sdescription = $skill->description;
												}
												else{
    	                                            $str_choice = '';
        	                                      	$sdescription = '<span class="blueback">'.$skill->description.'</span>';
												}

												if (!$hidden){
  							           	        	$str_skills .= '<tr><td class="tablerenderer3">&nbsp;'.$skill->domain.'&nbsp;</td><td class="tablerenderer3">&nbsp;<i>'.$skill->code.'</i>&nbsp;</td></tr><tr><td colspan="2" class="tablerenderer3">&nbsp;'.strip_tags($sdescription).'&nbsp;</td></tr><tr><td colspan="2" class="tablerenderer3">&nbsp;'.$str_choice.'&nbsp;</td></tr>'."\n";
												}
											}
										}
									}
								}
								$ligne[$i].=$str_skills."\n</table></td>\n";

								if ($vertical){
									if (!$lastposition[$object->id]){
										$ligne[$i].=$separateur;
									}
									else{
            	    			   		$ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
									}
								}
								else if ($lastposition[$object->id]){
									$ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
								}

            	    		    $i++;
							}

							//--------------------- LISTSKILLS -----------------------------------
           	    			else if ($object->type == 'listskills') {
								if ($list = get_record('artefact_booklet_list', 'idobject', $object->id)){
                            		$i = 0;
									if ($vertical){
        	           		        	$ligne[$i].= "<th class=\"tablerenderer2\">".$intitules[$object->id]. "</th>";
									}
                                	$ligne[$i].= "<td class=\"tablerenderer2\">\n<table class=\"tablerenderer3\">\n";
									$str_skills='';

	    	        	            if ($res_lofskills = get_records_array('artefact_booklet_listofskills', 'idlist', $list->id)){
										foreach ($res_lofskills as $res){
                                        	$header = false;
											$hidden = false;
											if ($skill = get_record('artefact_booklet_skill', 'id', $res->idskill)){
                                            	switch ($skill->type){
													case 0 : $header = true; break;
		                                            case 2 : $hidden = true; break;
													default : break;
												}

												$index = 0;
												// donnees saisies
       		        		                    $rec=null;
                           		                $sql = "SELECT * FROM {artefact_booklet_lskillsresult} re
 JOIN {artefact_booklet_resultdisplayorder} rd
 ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
 JOIN {artefact_booklet_skill} rs
 ON (rs.id = re.idskill)
 WHERE re.idobject = ?
 AND re.idowner = ?
 AND re.idskill = ?
 ORDER BY re.idrecord, rd.displayorder";
                           			            if ($recs = get_records_sql_array($sql,  array($object->id, $USER->get('id'), $skill->id))){
                                   		        	foreach ($recs as $rec){
                                       		        	if ($rec){
															$index = $rec->value - 1;   // la valeur stockee n'est pas un indice mais une position
															// la boîte de saisie
   			       	                        	    		if (!$header){
																$str_choice = get_skill_choice_display($skill, $index);
           	        		                        	    	$sdescription = $skill->description;
															}
															else{
   	                    	                    				$str_choice = '';
           	                	                        		$sdescription = '<span class="blueback">'.$skill->description.'</span>';
															}
															if (!$hidden){
               						           	        		$str_skills .= '<tr><td class="tablerenderer3">&nbsp;'.$skill->domain.'&nbsp;</td><td class="tablerenderer3">&nbsp;<i>'.$skill->code.'</i>&nbsp;</td></tr><tr><td colspan="2" class="tablerenderer3">&nbsp;'.strip_tags($sdescription).'&nbsp;</td></tr><tr><td colspan="2" class="tablerenderer3">&nbsp;'.$str_choice.'&nbsp;</td></tr>'."\n";
                           	                            	}
														}
				           							}
												}
											}
										}
									}
									$ligne[$i].=$str_skills."\n</table></td>\n";

									if ($vertical){
										if (!$lastposition[$object->id]){
											$ligne[$i].=$separateur;
										}
										else{
            	    				   		$ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
										}
									}
									else if ($lastposition[$object->id]){
                                     	$ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
									}

            	    		    	$i++;
								}
							}
							else if ($object->type == 'radio') {
	        	               	$sql = "SELECT * FROM {artefact_booklet_resultradio} re
 JOIN {artefact_booklet_resultdisplayorder} rd
  ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
 JOIN {artefact_booklet_radio} ra
  ON (ra.id = re.idchoice)
 WHERE re.idobject = ?
  AND re.idowner = ?
 ORDER BY rd.displayorder";
		                       	if ($radios = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
				           	       	$i = 0;
									if (!empty($radios)){
			    	                   	foreach ($radios as $radio){
											if ($vertical){
    	    		    	   		       	    $ligne[$i].= "<th>".$intitules[$object->id]. "</th>";
											}
				        		            $ligne[$i].= "<td class=\"tablerenderer2\">".$radio->option . "</td>";
											if ($vertical){
												if (!$lastposition[$object->id]){
													$ligne[$i].=$separateur;
												}
												else{
            	    		       		        	$ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
												}
											}
											else if ($lastposition[$object->id]){
                                             $ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
											}

    			        	   		   	    $i++ ;
        			    	           	}
									}
								}
				   			}

							else if ($object->type == 'checkbox') {
            	    			$sql = "SELECT * FROM {artefact_booklet_resultcheckbox} re
 JOIN {artefact_booklet_resultdisplayorder} rd
  ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
 WHERE re.idobject = ?
  AND re.idowner = ?
 ORDER BY rd.displayorder";
	            	            if ($checkboxes = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
		            	        	$i = 0;
	    		        	    	foreach ($checkboxes as $checkbox) {
										if ($vertical){
   	       		            	    	   	$ligne[$i].= "<th>".$intitules[$object->id]. "</th>";
										}
	       	    	            	    $ligne[$i].= "<td class=\"tablerenderer2\">".($checkbox->value ? get_string('true', 'artefact.booklet')  : get_string('false', 'artefact.booklet') ) . "</td>";
										if ($vertical){
											if (!$lastposition[$object->id]){
												$ligne[$i].=$separateur;
											}
											else{
                		        		    	$ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
											}
										}
										else if ($lastposition[$object->id]){
											$ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
										}

    	               	    			$i++ ;
	    	       	    			}
	    						}
							}
					        else if ($object->type == 'date') {
    	    	    		            	$sql = "SELECT * FROM {artefact_booklet_resultdate} re
	            		            	        JOIN {artefact_booklet_resultdisplayorder} rd
	            		            	        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	    	        	        WHERE re.idobject = ?
	            	        	    	        AND re.idowner = ?
	            	            		        ORDER BY rd.displayorder";
        	    	    	        		if ($dates = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
	            			            		$i = 0;
		            	    		        	foreach ($dates as $date) {
													if ($vertical){
  	    		                   	            		$ligne[$i].= "<th>".$intitules[$object->id]. "</th>";
													}
	            			            	    	$ligne[$i].= "<td class=\"tablerenderer2\">".format_date(strtotime($date->value), 'strftimedate') . "</td>";
													if ($vertical){
														if (!$lastposition[$object->id]){
															$ligne[$i].=$separateur;
														}
														else{
            			            			           	$ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
														}
													}
													else if ($lastposition[$object->id]){
														$ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
													}
	   		        	            		    	$i++ ;
	    		        	            		}
   							        }
							}
				    	    else if ($object->type == 'attachedfiles') {
            		    	        $sql = "SELECT * FROM {artefact_booklet_resultattachedfiles} re
	            	        	    	        JOIN {artefact_booklet_resultdisplayorder} rd
	            	            		        ON (re.idrecord = rd.idrecord AND re.idowner = rd.idowner)
	            	            		        WHERE re.idobject = ?
	            	            	    	    AND re.idowner = ?
	            	            	        	ORDER BY rd.displayorder";
			            	        if ($attachedfiles = get_records_sql_array($sql, array($object->id, $USER->get('id')))){
    			        	        	for ($i = 0; $i < $n; $i++) {
											if ($vertical){
    	    	    	               		    $ligne[$i].= "<th class=\"tablerenderer2\">".$intitules[$object->id]. "</th>";
											}
            			    		   	    $ligne[$i].= "<td class=\"tablerenderer2\"><table>";
            		    	        	}
		            			       	if (!empty($attachedfiles)){
    		        		    	   		foreach ($attachedfiles as $attachedfile) {
            			    	    	    	    	$f = artefact_instance_from_id($attachedfile->artefact);
            	    		    	    		    	$j = 0;
            	            					    	foreach ($listidrecords as $idrc) {
            	            	    				    	if ($attachedfile->idrecord == $idrc->idrecord) {
	            	            	           					$i = $j;
			            	            	        		}
	    			        	            	        	$j++;
    	        				            	    	}
        	    		    		        	    	$ligne[$i].= "<tr><td class=\"tablerenderer2\"><img src=" .
            		    	        			    		$f->get_icon(array('id' => $attachedfile->artefact, 'viewid' => isset($options['viewid']) ? $options['viewid'] : 0)) .
 " alt=''></td><td class=\"tablerenderer2\"><a href=" .
 get_config('wwwroot') . "artefact/file/download.php?file=" . $attachedfile->artefact .
 ">" . $f->title . "</a> (" . $f->describe_size() . ")" . $f->description . "</td></tr>";
			   		       	            }
									}
	        	    	   		    for ($i = 0; $i < $n; $i++) {
										$ligne[$i] .= "</table></td>";
										if ($vertical){
											if (!$lastposition[$object->id]){
												$ligne[$i].=$separateur;
											}
											else{
            	    	       		            $ligne[$i].="</tr><tr><th colspan=\"2\"><hr></th>";
											}
										}
										else if ($lastposition[$object->id]){
		                                     $ligne[$i].=$edit_link1.$edit_link[$i].$edit_link2;
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


					if (count($components) != 0 && $notframelist) {
		        	   	$elements['idtab'] = array(
        	        	'type' => 'hidden',
            	   	    'value' => $idtab
		        	   	);
			        	$elements['idtome'] = array(
	    		       	'type' => 'hidden',
    	    	        'value' => $idtome
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

        			if (count($components) != 0 && $frame->list) {
						$elements['idtab'] = array(
						'type' => 'hidden',
    	            	'value' => $idtab
						);
				        $elements['idtome'] = array(
    		    		'type' => 'hidden',
        	        	'value' => $idtome
		            	);
				        $elements['list'] = array(
    			    	'type' => 'hidden',
        		        'value' => true
        	    	);

				    $elements[$frame->id] = array(
	            	'type' => 'fieldset',
	            	'legend' => $frame->title,
		            'help' => ($frame->help != null),
    		        'elements' => $components

					);
				}


   	   			$pf = pieform(array(
        	    'name'        => 'pieform'.$frame->id,
            	'plugintype'  => 'artefact',
	            'pluginname'  => 'booklet',
		        'configdirs'  => array(get_config('libroot') . 'form/', get_config('docroot') . 'artefact/file/form/'),
			    'method'      => 'post',
    	    	'renderer'    => 'table',
        	    'successcallback' => '',
            	'elements'    => $elements,
	            'autofocus'   => false,
				));
       			$bookletform[$frame->title] = $pf;
					// ***************************************
	            } // fin de foreach frames
			}
		}
        return $bookletform;
    }
    // fin de get_form_display


	/********************************************************************************
	 *
	 *
	 *     VISUALIZATION GET FOM
	 *
	 *
	 *   ***************************************************************************/
    public static function get_form($idtome, $idtab, $idmodifliste = null, $browse) {
        // idmodifliste est l'index dans artefact_booklet_resulttext
        global $USER;
		// Modif JF
		global $THEME;
        $editallstr = get_string('editall','artefact.booklet');
        $imageedit = $THEME->get_url('images/btn_edit.png');
		$showallstr = get_string('showall','artefact.booklet');
        $imageshow = $THEME->get_url('images/btn_info.png');
		$objectlinkedstr = get_string('objectlinked','artefact.booklet');
        $imagelinked = $THEME->get_url('images/btn_show.png', false, 'artefact/booklet');
        $imageaddfreeskills = $THEME->get_url('images/btn_check.png', false, 'artefact/booklet');
        $addfreeskillsstr = get_string('addfreeskills','artefact.booklet');

		// Astuce pour forcer l'affichage
		if ($idmodifliste==-1){
            $idmodifliste=null;
		}


        require_once(get_config('libroot') . 'pieforms/pieform.php');
        if (!is_null($idmodifliste)) {
            $record = get_record('artefact_booklet_resulttext', 'id', $idmodifliste);
			$idrecord = $record->idrecord;
            $objmodif = get_record('artefact_booklet_object', 'id', $record->idobject);
        }
        else {
            $record = null;
			$idrecord = 0;
            $objmodif = null;
        }
        if (!$tome = get_record('artefact_booklet_tome', 'id', $idtome)) {
            return null;
        }
        foreach (get_records_array('artefact_booklet_tab', 'idtome', $idtome, 'displayorder') as $item) {
            // liste des tabs du tome tries par displayorder
            if ($item->displayorder == $idtab) {
                // parcours pour trouver le tab dont le displayorder est idtab
                $tab = $item;
            }
        }
		// liste des cadres ordonnes en profondeur d'abord
        $tabaff_codes = get_frames_codes_ordered($tab->id);
		//foreach ($tabaff_codes as $key => $val){
        //    echo "<br />DEBUG :: ".$key."=".$val."\n";
		//}
		//exit;
        $components = array();
        $elements = array();
        $bookletform = array();
        $bookletform["entete"] = $tab->help;
		$drapeau=true;
        if ($tabaff_codes) {
            foreach ($tabaff_codes as $key => $val) {
				if ($frame = get_record('artefact_booklet_frame', 'id', $key)){
                	$components = array();
                	$elements = null;
                	$components = null;
                	$pf = null;
                	// Quatre conditions exclusives
	                $notframelist = !$frame->list;
    	            $framelistnomodif = $frame->list && !$objmodif;
        	        $objmodifinframe = $objmodif && ($objmodif->idframe == $frame->id);
            	    $objmodifotherframe = $objmodif && ($objmodif->idframe != $frame->id);

					// afficher les boutons alternant affichage et edition
   					if ($drapeau){  // afficher le bouton
                	   	$elements['showedit'] = array(
							'type' => 'html',
							'title' => '',
							'value' =>  '<div class="right">
<a href="'.get_config('wwwroot').'/artefact/booklet/index.php?tab='.$idtab.'&okdisplay=1"><img src="'.$imageshow.'" alt="'.$showallstr.'" title="'.$showallstr.'" /></a>
<a href="'.get_config('wwwroot').'/artefact/booklet/index.php?tab='.$idtab.'&okdisplay=0"><img src="'.$imageedit.'" alt="'.$editallstr.'" title="'.$editallstr.'" /></a>
</div>',
						);
						$drapeau=false;
					}



        	        $objects = get_records_array('artefact_booklet_object', 'idframe', $frame->id, 'displayorder');
            	    // liste des objets du frame ordonnes par displayorder
                	if ($objects) {
	            	    foreach ($objects as $object) {
	            	        $help = ($object->help != null);
	            	        if ($object->type == 'longtext') {
								$val = null;
	            	            if ($notframelist) {
	            	            	// ce n'est pas une liste : rechercher le contenu du champ texte
	            	            	$sql = "SELECT * FROM {artefact_booklet_resulttext} WHERE idobject = ? AND idowner = ?";
	            	            	$vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
	            	            	$val = $vals[0];
	            	            }
	            	            else if ($objmodifinframe) {
	            	            	// modification d'un element de liste
	            	            	$val = get_record('artefact_booklet_resulttext', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
	            	            }
	            	            if ($notframelist || !$objmodifotherframe) {
	            	            	$components['lt' . $object->id] =  array(
	            	            		'type' => 'text',
	            	            	    'title' => $object->title,
                                    	'size' => 50,
                                    	'help' => $help,
                                    	'defaultvalue' => ((!empty($val)) ? $val->value : NULL),
                                	);
                            	}
                    		}
                        	else if ($object->type == 'area') {
                            	$val = null;
	                            if ($notframelist) {
    	                            $sql = "SELECT * FROM {artefact_booklet_resulttext} WHERE idobject = ? AND idowner = ?";
        	                        $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
            	                    $val = $vals[0];
                	            }
                    	        else if ($objmodifinframe) {
                        	        $val = get_record('artefact_booklet_resulttext', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
                            	}
	                            if ($notframelist || !$objmodifotherframe) {
    	                            $components['ta' . $object->id] =  array(
        	                            'type' => 'textarea',
            	                        'rows' => 10,
                	                    'cols' => 50,
                    	                'title' => $object->title,
                        	            'help' => $help,
                            	        'resizable' => false,
                                	    'defaultvalue' => ((!empty($val)) ? $val->value : NULL),
	                                );
        	                    }
    	                    }
            	            else if ($object->type == 'htmltext') {
                	            $val = null;
                    	        if ($notframelist) {
                        	        $sql = "SELECT * FROM {artefact_booklet_resulttext} WHERE idobject = ? AND idowner = ?";
                            	    $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
	                                $val = $vals[0];
    	                        }
        	                    else if ($objmodifinframe) {
            	                    $val = get_record('artefact_booklet_resulttext', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
                	            }
                    	        if ($notframelist || !$objmodifotherframe) {
                        	        $components['ht' . $object->id] =  array(
                            	        'type' => 'wysiwyg',
                                	    'rows' => 20,
                                    	'cols' => 60,
	                                    'title' => $object->title,
    	                                'help' => $help,
        	                            'resizable' => false,
            	                        'defaultvalue' => ((!empty($val)) ? $val->value : NULL),
                	                    'rules' => array('maxlength' => 65536),
                    	           );
                        	    }
	                        }
                            else if ($object->type == 'reference') {
        	                    $val = null;
                                $str_reference = '';
								if ($reference = get_record('artefact_booklet_reference', 'idobject', $object->id)){
									if ($objectlinked = get_record('artefact_booklet_object', 'id', $reference->idobjectlinked)){
										if ($referenceframe = get_record('artefact_booklet_frame', 'id', $objectlinked->idframe)){
                                            $str_reference = display_object_linked($reference->idobjectlinked, $USER->get('id')).' <a href="'.get_config('wwwroot').'/artefact/booklet/index.php?idframe='.$referenceframe->id.'&tab='.$referenceframe->idtab.'&okdisplay=0"><img src="'.$imagelinked.'" alt="'.$objectlinkedstr.'" title="'.$objectlinkedstr.'" /></a>';
											if(!$str_reference){
   			                            		$str_reference = get_string('referencehasnovalue','artefact.booklet'). ' <b>'. $objectlinked->title .'</b> '.get_string('offrame','artefact.booklet'). ' <i>'.$referenceframe->title.'</i>';
											}
										}
									}
                                    if ($notframelist) {
                	                	$sql = "SELECT * FROM {artefact_booklet_refresult} WHERE idobject = ? AND idowner = ?";
	                    	            $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
    	                    	        $val = $vals[0];
        	                    	}
	        	                    else if ($objmodifinframe) {
    	        	                    $val = get_record('artefact_booklet_refresult', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
        	        	            }
                        	        if (empty($val)){
										// creer la valeur
										$rec_refresult = new stdclass();
                                        $rec_refresult->idobject = $object->id;
                                        $rec_refresult->idowner = $USER->get('id');
                                        $rec_refresult->idreference = $reference->id;
                                        if ($record) {
											$rec_refresult->idrecord = $record->id;
										}
										else{
                                            $rec_refresult->idrecord = null;
										}
										insert_record('artefact_booklet_refresult', $rec_refresult);
									}

	            	                $rec = false;
    	            	            if (!is_null($record)) {
        	            	            $rec = true;
            	            	    }
                	            	if ($notframelist || !$objmodifotherframe) {
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
            	                if ($notframelist) {
                	                $sql = "SELECT * FROM {artefact_booklet_resulttext} WHERE idobject = ? AND idowner = ?";
                    	            $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
                        	        $val = $vals[0];
                            	}
	                            else if ($objmodifinframe) {
    	                            $val = get_record('artefact_booklet_resulttext', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
        	                    }
            	                $rec = false;
                	            if (!is_null($record)) {
                    	            $rec = true;
                        	    }
                            	if ($notframelist || !$objmodifotherframe) {
	                                $components['ta' . $object->id] =  array(
    	                                'type' => 'wysiwyg',
        	                            'rows' => 20,
            	                        'cols' => 60,
                	                    'title' => $object->title,
                    	                'help' => $help,
                        	            'resizable' => false,
                            	        'defaultvalue' => ((!empty($val)) ? $val->value : NULL),
                                	);
	                                $components['btn' . $object->id] = array(
    	                                'type' => 'button',
        	                            'value' => get_string('generate', 'artefact.booklet'),
            	                        'onclick' => ($rec ? 'sendjsonrequest(\'compositegeneratesynthesis.php\',
                	                                 {\'idsynthesis\': ' . $object->id . ', \'idrecord\': ' . $record->idrecord . '},
                                                     \'GET\',
                                                     function(data) {
                                                         location.reload(true)
                                                     },
                                                     function() {
                                                         // @todo error
                                                     })'
                                                     : 'sendjsonrequest(\'compositegeneratesynthesis.php\',
                                                     {\'idsynthesis\': ' . $object->id . '},
                                                     \'GET\',
                                                     function(data) {
                                                         location.reload(true)
                                                     },
                                                     function() {
                                                        // @todo error
                                                     })')
                        	        );
                    	        }
	                        }
    	                    else if ($object->type == 'shorttext') {
        	                    $val = null;
            	                if ($notframelist) {
                	                $sql = "SELECT * FROM {artefact_booklet_resulttext} WHERE idobject = ? AND idowner = ?";
                    	            $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
                        	        $val = $vals[0];
                            	}
	                            else if ($objmodifinframe) {
    	                            $val = get_record('artefact_booklet_resulttext', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
        	                    }
            	                if ($notframelist || !$objmodifotherframe) {
                	                $components['st' . $object->id] =  array(
                    	                'type' => 'text',
                        	            'title' => $object->title,
                            	        'help' => $help,
                                	    'size' => 16,
	                                    'defaultvalue' => ((!empty($val)) ? $val->value : NULL),
    	                            );
        	                    }
            	            }
            	            else if ($object->type == 'listskills') {
								// DEBUG
								//echo "<br />lib.php :: 2783<br />\n";
								//print_object($object);
								//exit;
								// list
								if ($list = get_record('artefact_booklet_list', 'idobject', $object->id)){
          							if ($notframelist || !$objmodifotherframe) {
            	                    	$components['ls' . $object->id.'_'.$list->id] = array(
			                	                        'type' => 'html',
            			        	                    'title' => $object->title,
                                	        			'value' => $list->description,
                                    	);
				                    }

            	                    if ($res_lofskills = get_records_array('artefact_booklet_listofskills', 'idlist', $list->id)){
										foreach ($res_lofskills as $res){
                                            $header = false;
											$hidden = false;
											if ($skill = get_record('artefact_booklet_skill', 'id', $res->idskill)){
		                                      	switch ($skill->type){
												case 0 : $header = true; break;
            	                                case 2 : $hidden = true; break;
												default : break;
												}

                                                $options = array();
												$scale = $skill->scale;
												$domain = $skill->domain;
												$sdescription = $skill->description;
                                                $code = $skill->code;
												$threshold = $skill->threshold;
                                                $str_skill = "$domain $code";

												// donnees saisies
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
                                                $defaultvalue = 0;
												$nboptions=0;
												if ($tab_scale = explode(",", $scale)){
													for ($i=0; $i<count($tab_scale); $i++){
                                                    	$a_scale_element = trim($tab_scale[$i]);
														if (!empty($a_scale_element)){
															if ($index == $nboptions){
																//$defaultvalue = $tab_scale[$i];
                                                                $defaultvalue = $nboptions;
															}
															$options[$nboptions] = $a_scale_element;
                                                            $nboptions++;
														}
													}
												}


                                            	if ($notframelist || !$objmodifotherframe) {
         											if (!$header){
		    	                        			$components['rlc' . $object->id.'_'.$list->id.'_'.$skill->id] = array(
                                            			'type' => 'radio',
            			        	                    'options' => $options,
                        				                //'help' => $help,
                            	    			        'title' => $str_skill,
                                	        			'defaultvalue' => $defaultvalue,
                                                        'rowsize' => $nboptions,
                                                        'description' => $sdescription,
                                    				);
				    								}
													else if (!$hidden) {
		    	                        			$components['rlc' . $object->id.'_'.$list->id.'_'.$skill->id] = array(
                                            			'type' => 'html',
                               	    			        'title' => $str_skill,
                                	        			'value' => '<b>'.$sdescription.'</b>',
                                    				);

													}
												}

											}
										}
									}
								}
							}

                	        else if ($object->type == 'radio') {
                    	        $val = null;
                        	    if (count_records('artefact_booklet_radio', 'idobject', $object->id) != 0) {
                            	    $res = get_records_array('artefact_booklet_radio', 'idobject', $object->id);
                                	if ($notframelist) {
	                                    $sql = "SELECT * FROM {artefact_booklet_resultradio} WHERE idobject = ? AND idowner = ?";
    	                                $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
        	                            $val = $vals[0];
            	                    }
                	                else if ($objmodifinframe) {
                    	                $val = get_record('artefact_booklet_resultradio', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
                        	        }
                            	    $table = array();
                                	foreach ($res as $value) {
	                                    $table[$value->id] = $value->option;
    	                            }
        	                        if ($notframelist || !$objmodifotherframe) {
            	                        $components['ra' . $object->id] = array(
                	                        'type' => 'radio',
                    	                    'options' => $table,
                        	                'help' => $help,
                            	            'title' => $object->title,
                                	        'defaultvalue' => ((!empty($val)) ? $val->idchoice : NULL),
                                    	);
	                                }
    	                        }
        	                }
            	            else if ($object->type == 'checkbox') {
                	            $val = null;
                    	        if ($notframelist) {
                        	        $sql = "SELECT * FROM {artefact_booklet_resultcheckbox} WHERE idobject = ? AND idowner = ?";
                            	    $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
                                	$val = $vals[0];
	                            }
    	                        else if ($objmodifinframe) {
        	                        $val = get_record('artefact_booklet_resultcheckbox', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
            	                }
                	            if ($notframelist || !$objmodifotherframe) {
                    	            $components['cb' . $object->id] = array(
                        	            'type' => 'checkbox',
                            	        'help' => $help,
                                	    'title' => $object->title,
                                    	'defaultvalue' => ((!empty($val)) ? $val->value : NULL),
	                                );
    	                        }
        	                }
            	            else if ($object->type == 'date') {
                	            $val = null;
                    	        if ($notframelist) {
                        	        $sql = "SELECT * FROM {artefact_booklet_resultdate} WHERE idobject = ? AND idowner = ?";
                            	    $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
	                                $val = $vals[0];
    	                        }
        	                    else if ($objmodifinframe) {
            	                    $val = get_record('artefact_booklet_resultdate', 'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
                	            }
                    	        if ($notframelist || !$objmodifotherframe) {
                        	        $components['da' . $object->id] = array(
                            	        'type' => 'calendar',
                                	    'caloptions' => array(
	                                    	'showsTime' => false,
    	                                    'ifFormat' => get_string('strfdateofbirth', 'langconfig')
        	                            ),
            	                        'defaultvalue' => ((!empty($val)) ? strtotime($val->value) : time()),
                	                    'title' => $object->title,
                    	                'description' => get_string('dateofbirthformatguide'),
                        	        );
                            	}
	                        }
    	                    else if ($object->type == 'attachedfiles') {
        	                    $vals = array();
            	                if ($notframelist) {
                	                $vals = get_column('artefact_booklet_resultattachedfiles', 'artefact',  'idobject', $object->id, 'idowner', $USER->get('id'));
                    	        }
                        	    else if ($objmodifinframe) {
                            	    $vals = get_column('artefact_booklet_resultattachedfiles', 'artefact',  'idrecord', $record->idrecord, 'idobject', $object->id, 'idowner', $USER->get('id'));
	                            }
    	                        if ($notframelist || !$objmodifotherframe) {
        	                        $components['af' . $object->id] =  array(
            	                        'type' => 'filebrowser',
                	                    'title' => $object->title,
                    	                'help' => $help,
                        	            'folder' => 0,
                            	        'highlight' => null,
                                	    'browse' => $browse,
	                                    'page' => get_config('wwwroot') . 'artefact/booklet/index.php?' . 'tome=' . $tab->idtome . '&tab=' . $tab->displayorder . '&browse=1',
    	                                'browsehelp' => 'browsemyfiles',
        	                            'config' => array(
            	                            'upload' => true,
                	                        'uploadagreement' => get_config_plugin('artefact', 'file', 'uploadagreement'),
                    	                    'resizeonuploaduseroption' => get_config_plugin('artefact', 'file', 'resizeonuploaduseroption'),
                        	                'resizeonuploaduserdefault' => $USER->get_account_preference('resizeonuploaduserdefault'),
                            	            'createfolder' => false,
                                	        'edit' => false,
                                    	    'select' => true,
	                                    ),
    	                                'defaultvalue' => $vals,
        	                            'selectlistcallback' => 'artefact_get_records_by_id',
            	                        // 'selectcallback' => 'add_attachment',
                	                    // 'unselectcallback' => 'delete_attachment',
                    	            );
                        	    }
	                        }

                        	else if ($object->type == 'freeskills') {
                                $vals = array();
                    	    	if ($notframelist) {
                        	        $sql = "SELECT * FROM {artefact_booklet_frskllresult} WHERE idobject = ? AND idowner = ?";
                            	    $vals = get_records_sql_array($sql, array($object->id, $USER->get('id')));
								}
       							else if ($objmodifinframe) {
                        	        $sql = "SELECT * FROM {artefact_booklet_frskllresult} WHERE idrecord = ? AND idobject = ? AND idowner = ?";
                            	    $vals = get_records_sql_array($sql, array($record->idrecord, $object->id, $USER->get('id')));
								}
								//print_object($vals);
								//exit;
		                           	$alink = '<a href="'.get_config('wwwroot').'/artefact/booklet/freeskills.php?id='.$object->id.'&idrecord='.$idrecord.'&domainsselected=0"><img src="'.$imageaddfreeskills.'" alt="'.$addfreeskillsstr.'" title="'.$addfreeskillsstr.'" /></a> ';
        	                		$components['frsk' . $object->id] = array(
                            	        'type' => 'html',
                	                    'title' => $object->title,
               	                	    'value' =>$alink,
            	    	            );

                            	if ($notframelist || !$objmodifotherframe) {


									if ($vals){
										//print_object($vals);
										//exit;
										foreach ($vals as $val){
		        	                        $header = false;
											$hidden = false;
                        	                $tab_scale = array();
	                                  		//echo "<br />ISDSKILL : ".$val->idskill;
											//exit;
											$skill = get_record('artefact_booklet_skill', 'id', $val->idskill);
											//print_object($skill);

											if ($skill){
                                        		switch ($skill->type){
													case 0 : $header = true; break;
        		                                    case 2 : $hidden = true; break;
													default : break;
												}

    	                                        $options = array();
												$scale = $skill->scale;
												$domain = $skill->domain;
												$sdescription = $skill->description;
                    	                        $code = $skill->code;
												$threshold = $skill->threshold;
                            	                $str_skill = "$domain::$code";

												// donnees saisies
            									$index = $val->value - 1;


												// la boîte de saisie
                                            	$defaultvalue = 0;
												$nboptions=0;
												if ($tab_scale = explode(",", $scale)){
													for ($i=0; $i<count($tab_scale); $i++){
                                                    	$a_scale_element = trim($tab_scale[$i]);
														if (!empty($a_scale_element)){
															if ($index == $nboptions){
																//$defaultvalue = $tab_scale[$i];
                                                                $defaultvalue = $nboptions;
															}
															$options[$nboptions] = $a_scale_element;
                                                            $nboptions++;
														}
													}
												}

       											if (!$header){
		    	                        			$components['frsk' . $object->id.'_'.$skill->id] = array(
                                            			'type' => 'radio',
            			        	                    'options' => $options,
                        				                //'help' => $help,
                            	    			        'title' => $str_skill,
                                	        			'defaultvalue' => $defaultvalue,
                                                        'rowsize' => $nboptions,
                                                        'description' => $sdescription,
                                    				);
			    								}
												else if (!$hidden) {
		    	                        			$components['frsk' . $object->id.'_'.$skill->id] = array(
                                            			'type' => 'html',
                               	    			        'title' => $str_skill,
                                	        			'value' => '<b>'.$sdescription.'</b>',
                                    				);
												}
											}
										}
									}
								}
							}

    	                } // fin de foreach objects
        	        }


					if (count($components) != 0 && $notframelist) {
                	    $elements['idtab'] = array(
	                        'type' => 'hidden',
     	                   'value' => $idtab
        	            );
            	        $elements['idtome'] = array(
                	        'type' => 'hidden',
                    	    'value' => $idtome
	                    );
    	                $components['save' . $frame->id] = array(
        	                'type' => 'submit',
            	            'value' => get_string('valid', 'artefact.booklet'),
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

        	        if (count($components) != 0 && $frame->list) {
            	        if ($objmodif) {
                	         $components['idrecord'] = array(
                    	        'type' => 'hidden',
                        	    'value' => $record->idrecord
                         	);
	                    }
    	                $components['idtab'] = array(
        	                'type' => 'hidden',
            	            'value' => $idtab
                	    );
                    	$components['idtome'] = array(
                        	'type' => 'hidden',
	                        'value' => $idtome
    	                );
        	            $components['list'] = array(
            	            'type' => 'hidden',
                	        'value' => true
                    	);
	                    if ($objmodifinframe) {
    	                    $components['save' . $frame->id] = array(
        	                   'type' => 'submit',
            	               'value' => get_string('modify', 'artefact.booklet'),
                	        );
                    	}
                    	if ($framelistnomodif) {
                        	$components['save' . $frame->id] = array(
                            	'type' => 'submit',
	                            'value' => get_string('save', 'artefact.booklet'),
    	                    );
        	            }
            	        $elements = $components;
                	}

                	$pf = pieform(array(
	                    'name'        => 'pieform'.$frame->id,
    	                'plugintype'  => 'artefact',
        	            'pluginname'  => 'booklet',
            	        'configdirs'  => array(get_config('libroot') . 'form/', get_config('docroot') . 'artefact/file/form/'),
                	    'method'      => 'post',
                    	'renderer'    => 'table',
	                    'successcallback' => 'visualization_submit',
    	                'elements'    => $elements,
                    	'autofocus'   => false,
               	 	));
        	        if ($frame->list) {
            	        if ($framelistnomodif) {
                	        $pf = "<div id='pieform".$frame->id."form' class='hidden'>". $pf. "</div>" .
                              "<button id='addpieform".$frame->id."button' class='cancel' onclick='toggleCompositeForm(&quot;pieform".$frame->id."&quot;);'>".get_string('add','artefact.booklet')."</button>" ;
                    	}
                    	if ($objmodifinframe) {
                        	$pf = $pf . "<button id='addpieform".$frame->id."button' onclick='javascript:history.back()' class='cancel'>". get_string('cancel','artefact.booklet')."</button>";
	                    }

						//exit;
    	                $objects = get_records_array('artefact_booklet_object', 'idframe', $frame->id);
        	            $item = null;
            	        if ($objects) {
                	        foreach ($objects as $object) {
                    	        if ((substr($object->name, 0, 5) == 'Title' || substr($object->name, 0, 5) == 'title') &&
                        	        ($object->type == 'area' || $object->type == 'shorttext' || $object->type == 'longtext'
                            	     || $object->type == 'synthesis' || $object->type == 'htmltext')) {
                                	$item = $object;
	                                break;
    	                        }
        	                }
            	        }
                	    if (is_null($item)) {
                    	    $sql = "SELECT * FROM {artefact_booklet_object}
                                WHERE idframe = ?
                                AND displayorder = (SELECT MIN(displayorder)
                                                    FROM {artefact_booklet_object}
                                                    WHERE idframe = ?
                                                    AND (type='area'
                                                         OR type='shorttext'
                                                         OR type='longtext'
                                                         OR type='htmltext'
                                                         OR type='synthesis')
                                                    )";
                        	$item = get_record_sql($sql, array($frame->id, $frame->id));
	                    }
    	                if ($frame->help != null) {
        	                $aide = '<span class="help"><a href="" onclick="contextualHelp(&quot;pieform'.$frame->id.'&quot;,&quot;'.$frame->id.'&quot;,&quot;artefact&quot;,&quot;booklet&quot;,&quot;&quot;,&quot;&quot;,this); return false;"><img src="'.get_config('wwwroot').'/theme/raw/static/images/help.png" alt="Help" title="Help"></a></span>';
            	        }
                	    else {
                    	    $aide = null;
	                    }
    	                $pf = '<fieldset class="pieform-fieldset"><legend>' . $frame->title . ' ' . $aide . '</legend>
                           <table id="visualization'.$frame->id.'list" class="tablerenderer visualizationcomposite">
                               <thead>
                                   <tr>
                                       <th class="visualizationcontrols"></th>
                                       <th class="nom">' . (($item) ? $item->title : "") . '</th>
                                       <th class="visualizationcontrols"></th>
                                   </tr>
                               </thead>
                               <tbody>
                                   <tr>
                                       <td class="buttonscell"></td>
                                       <td class="toggle"></td>
                                       <td class="buttonscell"></td>
                                   </tr>
                               </tbody>
                           </table>
                           ' . $pf . '
                           </fieldset>';
        	        }
            	    $bookletform[$frame->title] = $pf;
            	} // fin de frame
	        } // fin de foreach frames
		}

        return $bookletform;
    }
    // fin de get_form

	/***************************************************/
    public static function get_js($compositetype, $ids, $tab) {
        // genere la liste des fonctions pour chaque frame
        $js = self::get_common_js();
        foreach ($ids as $id) {
            $js .= self::get_artefacttype_js($compositetype, $id, $tab);
        }
        return $js;
    }

    public static function get_artefacttype_js($compositetype, $id, $tab) {
        // genere les fonctions js pour une frame
        global $THEME;
        $imagemoveblockup = json_encode($THEME->get_url('images/btn_moveup.png'));
        $imagemoveblockdown = json_encode($THEME->get_url('images/btn_movedown.png'));
        $upstr = get_string('moveup','artefact.booklet');
        $downstr = get_string('movedown','artefact.booklet');
        $editstr = get_string('edit','artefact.booklet');
        $delstr = get_string('del','artefact.booklet');

        // Modif JF
        $showstr = get_string('show','artefact.booklet');
        $imageshow = json_encode($THEME->get_url('images/btn_info.png'));

        $js = <<<EOF
tableRenderers.{$compositetype}{$id} = new TableRenderer(
    '{$compositetype}{$id}list',
    'composite.json.php',
    [
EOF;
        $js .= <<<EOF
        function (r, d) {
            var buttons = [];
            if (r._rownumber > 1) {
                var up = A({'href': ''}, IMG({'src': {$imagemoveblockup}, 'alt':'{$upstr}'}));
                connect(up, 'onclick', function (e) {
                    e.stop();
                    return moveComposite(d.type, r.id, r.artefact, 'up', d.id);
                });
                buttons.push(up);
            }
            if (!r._last) {
                var down = A({'href': '', 'class':'movedown'}, IMG({'src': {$imagemoveblockdown}, 'alt':'{$downstr}'}));
                connect(down, 'onclick', function (e) {
                    e.stop();
                    return moveComposite(d.type, r.id, r.artefact, 'down', d.id);
                });
                buttons.push(' ');
                buttons.push(down);
            }
            return TD({'class':'movebuttons'}, buttons);
        },
EOF;
        $js .= self::get_tablerenderer_js();
        $js .= <<<EOF
          function (r, d) {
			// Modif JF
			// http://localhost/mahara101//artefact/booklet/index.php?tab=1&idframe=230
			var showlink = A({'href': 'index.php?tab={$tab}&idframe=' + d.id + '&okdisplay=1&idmodifliste=' + r.id, 'title': '{$showstr}'}, IMG({'src': {$imageshow}, 'alt':'{$showstr}'}));
            var editlink = A({'href': 'index.php?tab={$tab}&idframe=' + d.id + '&okdisplay=0&idmodifliste=' + r.id, 'title': '{$editstr}'}, IMG({'src': config.theme['images/btn_edit.png'], 'alt':'{$editstr}'}));
            var dellink = A({'href': '', 'title': '{$delstr}'}, IMG({'src': config.theme['images/btn_deleteremove.png'], 'alt': '[x]'}));
            connect(dellink, 'onclick', function (e) {
                e.stop();
                return deleteComposite(d.type, r.id, d.id);
            });
            // Modif JF
            return TD({'class':'right'}, null, showlink, ' ', editlink, ' ', dellink);
        }
    ]
);
EOF;
        $js .= <<<EOF
tableRenderers.{$compositetype}{$id}.type = '{$compositetype}';
tableRenderers.{$compositetype}{$id}.statevars.push('type');
tableRenderers.{$compositetype}{$id}.emptycontent = '';
tableRenderers.{$compositetype}{$id}.updateOnLoad();
tableRenderers.{$compositetype}{$id}.id = '{$id}';
tableRenderers.{$compositetype}{$id}.statevars.push('id');
EOF;
        return $js;
    }

    public static function get_common_js() {
        $cancelstr = get_string('cancel','artefact.booklet');
        $addstr = get_string('add','artefact.booklet');
        $confirmdelstr = get_string('compositedeleteconfirm','artefact.booklet');
        $js = <<<EOF
var tableRenderers = {};
function toggleCompositeForm(type) {
    var elemName = '';
    elemName = type + 'form';
	if (hasElementClass(elemName, 'hidden')) {
        removeElementClass(elemName, 'hidden');
        $('add' + type + 'button').innerHTML = '{$cancelstr}';
    }
    else {
        $('add' + type + 'button').innerHTML = '{$addstr}';
        addElementClass(elemName, 'hidden');
    }
}
function compositeSaveCallback(form, data) {
    key = form.id.substr(3);
    tableRenderers[key].doupdate();
    toggleCompositeForm(key);
    // Can't reset() the form here, because its values are what were just submitted,
    // thanks to pieforms
    forEach(form.elements, function(element) {
        if (hasElementClass(element, 'text') || hasElementClass(element, 'textarea')) {
            element.value = '';
        }
    });
}
function deleteComposite(type, id, idfr) {
    if (confirm('{$confirmdelstr}')) {
        sendjsonrequest('compositedelete.json.php',
            {'id': id, 'type': type},
            'GET',
            function(data) {
                tableRenderers[type+idfr].doupdate();
            },
            function() {
                // @todo error
            }
        );
    }
    return false;
}
function moveComposite(type, id, artefact, direction, idfr) {
    sendjsonrequest('compositemove.json.php',
        {'id': id, 'type': type, 'direction':direction},
        'GET',
        function(data) {
            tableRenderers[type+idfr].doupdate();
        },
        function() {
            // @todo error
        }
    );
    return false;
}
function contextualHelp(formName, helpName, pluginType, pluginName, page, section, ref) {
    var key;
    var target = $(formName + '_' + helpName + '_container');
    var url = config.wwwroot + 'artefact/booklet/help.php';
    var url_params = {
        'plugintype': pluginType,
        'pluginname': pluginName
    };
    var parentElement = 'messages';
    // deduce the key
    if (page) {
        key = pluginType + '/' + pluginName + '/' + page;
        url_params.page = page;
    }
    else if (section) {
        key = pluginType + '/' + pluginName + '/' + section;
        url_params.section = section;
    }
    else {
        key = pluginType + '/' + pluginName + '/' + formName + '/' + helpName;
        url_params.form = formName;
        url_params.element = helpName;
    }
    // close existing contextual help
    if (contextualHelpSelected) {
        removeElement(contextualHelpContainer);
        contextualHelpContainer = null;
        if (key == contextualHelpSelected) {
            // we're closing an already open one by clicking on the ? again
            contextualHelpSelected = null;
            contextualHelpOpened = false;
            return;
        }
        else {
            // we're closing a DIFFERENT one that's already open (we want to
            // continue and open the new one)
            contextualHelpSelected = null;
            contextualHelpOpened = false;
        }
    }
    // create and display the container
    contextualHelpContainer = DIV({
            'style': 'position: absolute;',
            'class': 'contextualHelp hidden'
        },
        IMG({'src': config.theme['images/loading.gif']})
    );
    appendChildNodes($(parentElement), contextualHelpContainer);
    var position = getElementPosition(ref);
    var dimensions = getElementDimensions(contextualHelpContainer);
    // Adjust the position. The element is moved towards the centre of the
    // screen, based on which quadrant of the screen the help icon is in
    screenDimensions = getViewportDimensions();
    if (position.x + dimensions.w < screenDimensions.w) {
        // Left of the screen - there's enough room for it
        position.x += 15;
    }
    else {
        position.x -= dimensions.w;
    }
    position.y -= 10;
    // Once it has been positioned, make it visible
    setElementPosition(contextualHelpContainer, position);
    removeElementClass(contextualHelpContainer, 'hidden');
    contextualHelpSelected = key;
    // load the content
    if (contextualHelpCache[key]) {
        buildContextualHelpBox(contextualHelpCache[key]);
        callLater(0, function() { contextualHelpOpened = true; });
        ensureHelpIsOnScreen(contextualHelpContainer, position);
    }
    else {
        if (contextualHelpDeferrable && contextualHelpDeferrable.cancel) {
            contextualHelpDeferrable.cancel();
        }
        badIE = true;
        sendjsonrequest(url, url_params, 'GET', function (data) {
            if (data.error) {
                contextualHelpCache[key] = data.message;
                replaceChildNodes(contextualHelpContainer, data.message);
            }
            else {
                contextualHelpCache[key] = data.content;
                buildContextualHelpBox(contextualHelpCache[key]);
            }
            contextualHelpOpened = true;
            ensureHelpIsOnScreen(contextualHelpContainer, position);
            processingStop();
        },
        function (error) {
            contextualHelpCache[key] = get_string('couldnotgethelp');
            contextualHelpContainer.innerHTML = contextualHelpCache[key];
            processingStop();
            contextualHelpOpened = true;
        },
        true, true);
    }
    contextualHelpContainer.focus();
}
EOF;
        $js .= self::get_showhide_composite_js();
        return $js;
    }


    static function get_showhide_composite_js() {
        return "
            function showhideComposite(r, content) {
                // get the reference for the title we just clicked on
                var titleTD = $('composite-' + r.artefact + '-' + r.id);
                var theRow = titleTD.parentNode;
                var bodyRow = $('composite-body-' + r.artefact +  '-' + r.id);
                if (bodyRow) {
                    if (hasElementClass(bodyRow, 'hidden')) {
                        removeElementClass(bodyRow, 'hidden');
                    }
                    else {
                        addElementClass(bodyRow, 'hidden');
                    }
                    return false;
                }
                // we have to actually create the dom node too
                var colspan = theRow.childNodes.length;
                var newRow = TR({'id': 'composite-body-' + r.artefact + '-' + r.id},
                    TD({'colspan': colspan}, content));
                insertSiblingNodesAfter(theRow, newRow);
            }
        ";
    }

    public static function get_tablerenderer_js() {
        return "
                'value',
                ";
    }

	// Modif JF
	/********************************************************************************
	 *
	 *
	 *     VISUALIZATION MENU
	 *     Display an horizontal tree of frames
	 *
	 *   ***************************************************************************/

    public static function get_menu_frames($idtome, $idtab, $idselectedframe = null, $idmodifliste = null, $browse = null, $okdisplay = null) {
        // idmodifliste est l'index dans artefact_booklet_resulttext
        global $USER, $THEME;
        $link_part1 = get_config('wwwroot').'/artefact/booklet/index.php?tab=';
        $link_part2 = '&amp;idframe=';
        $link_part3 ='';
		if (!empty($idmodifliste)){
			$link_part3.='&amp;idmodifliste='.$idmodifliste;
		}
		if (!empty($browse)){
			$link_part3.='&amp;browse='.$browse;
		}
        if (!empty($okdisplay)){
			$link_part3.='&amp;okdisplay='.$okdisplay;
		}

        $tome = null; // Tome a traiter
		$tab = null; // Tab a afficher
		$currentframe = null; // cadre courrant

        require_once(get_config('libroot') . 'pieforms/pieform.php');

		if (!is_null( $idselectedframe)) {
            $currentframe = get_record('artefact_booklet_frame', 'id', $idselectedframe);
        }

        if (!$tome = get_record('artefact_booklet_tome', 'id', $idtome)) {
            return null;
        }

        foreach (get_records_array('artefact_booklet_tab', 'idtome', $idtome, 'displayorder') as $item) {
            // liste des tabs du tome tries par displayorder
            if ($item->displayorder == $idtab) {
                // parcours pour trouver le tab dont le displayorder est idtab
                $tab = $item;
            }
        }



        // Liste des frames du tab
		// Ordonner les frames selon leur frame parent et leur ordre d'affichage
	    $recframes = get_records_sql_array('SELECT ar.id,  ar.title, ar.displayorder, ar.idparentframe FROM {artefact_booklet_frame} ar WHERE ar.idtab = ? ORDER BY ar.idparentframe ASC, ar.displayorder ASC', array($tab->id));
		// DEBUG
        //echo "<br />DEBUG :: lib.php :: 1976 <br />\n";
		//echo "<br />DEBUG :: FRAMES<br /> \n";
		//print_object($recframes);
        //echo "<br />\n";
		//exit;
		// 52 branches possibles a chaque noeud, Ã§a devrait suffire ...
		$tcodes = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');

		// REORDONNER sous forme d'arbre parcours en profondeur d'abord
        $tabaff_niveau = array();
		// Initialisation
    	foreach ($recframes as $recframe) {
        	if ($recframe){
            	$tabaff_niveau[$recframe->id] = 0;
			}
		}

        $tabaff_codes = array();  // liste des cadres dns l'ordre parcours transverse
		// Initialisation
		$n=0;
		foreach ($recframes as $recframe) {
        	if ($recframe){
            	$tabaff_codes[$recframe->id] =$tcodes[$n];
				$n++;
			}
		}

        $tabaff_codes_ordonnes = array();  // liste des cadres dans l'ordre parcours transverse
        $tabaff_codes_largeur = array();

        $niveau_courant = 0;
        $ordre_courant = 0;
        $parent_courant = 0;
        $tab_ordre_par_position = array();
        $tab_ordre_par_niveau = array();
        $tab_ordre_par_niveau[0]=-1;
		// Reordonner
        if ($recframes) {
			foreach ($recframes as $recframe) {
            if ($recframe){

				if ($recframe->idparentframe == 0){
                    $niveau_courant = 0;
                    $ordre_courant = $tab_ordre_par_niveau[$niveau_courant]+1;

				}
				else if ($recframe->idparentframe != $parent_courant){
					// changement de niveau
     				$niveau_courant = $tabaff_niveau[$recframe->idparentframe] + 1;
                    if (isset($tab_ordre_par_niveau[$niveau_courant])){
                    	$ordre_courant = $tab_ordre_par_niveau[$niveau_courant] + 1;
					}
					else{
                        $ordre_courant = 0;
						$tab_ordre_par_niveau[$niveau_courant] = 0;
					};
				}
                $tab_ordre_par_niveau[$niveau_courant] = $ordre_courant;

				$tabaff_niveau[$recframe->id] = $niveau_courant;
				$parent_courant = $recframe->idparentframe;

                $code='';
				if ($niveau_courant>0){
					$code =  $tabaff_codes[$recframe->idparentframe];
				}
                $code.=$tcodes[$ordre_courant];
                $tabaff_codes[$recframe->id] = $code;

                $position = $niveau_courant * 52 + $ordre_courant;
				$tab_ordre_par_position[$recframe->id] = $position;

				$a = new stdclass;
                $a->id = $recframe->id;
                $a->title = $recframe->title;
                $a->idparentframe = $recframe->idparentframe;
                $a->code = $code;
                $a->niveau = strlen($code);
                $a->rang = $ordre_courant;
                $a->position = $position;
				// A completer dans une seconde passe
                $a->nodelist = array();
                $a->nodenumber = 0;
                $a->nbfeuilles=0;
                // A completer dans une troisieme passe
                $a->colspan = 0;
                $a->col = 0;
                $tabaff_codes_ordonnes[$recframe->id] = $a;

                $ordre_courant++;
			}
			}
		}

        asort($tab_ordre_par_position);

		// Reorganisation
        foreach ($tab_ordre_par_position as $key => $val){
            $tabaff_codes_largeur[$key] = $tabaff_codes[$key];
		}

        $tabaff_codes_profondeur = $tabaff_codes;
        asort($tabaff_codes_profondeur);

        // CREATION du tableau a afficher
		// Pour chaque cadre dans le parcours en profondeur completer
		// liste des noeuds (nodelist) et nombre de noeuds (nodenumber)
		// Nombre_feuille(code) = longeur_code(fils le plus profond) - longeur_code(code)
		// fils_le_plus_profond(code) = liste_des_fils[DERNIER]
		//

		foreach ($tabaff_codes_profondeur as $key => $val){
			$object = $tabaff_codes_ordonnes[$key];
			$code_courant = $object->code;
			// rechercher les fils
            $i=0;
			$ok=true;
   			foreach ($tabaff_codes_profondeur as $idframe => $code){
    			$pos = strpos($code, $code_courant, 0);
				// echo "<br />ID FRAME : $idframe, CODE CHERCHE : $code_courant, CODE : $code,  POS : $pos\n";
				// exit;
				// Notez notre utilisation de !==.  != ne fonctionnerait pas comme attendu
				// car la position de 'a' est la 0-ieme (premier) caractere.
				if ($pos !== false) {
				    //La chaine '$code_courant' a ete trouvee dans la chaine '$code' et debute a la position $pos<br />\n";
					if ($pos===0){
						// C'est un fils
                        $object->nodelist[]=$idframe;
                        $object->nodenumber++;
					}
				}
			}
		}


        // CREATION du tableau a afficher (Suite)
		// Pour chaque cadre dans le parcours en profondeur completer
		// nombre de feuilles et nombre de cases (colspan)

		foreach ($tabaff_codes_profondeur as $key => $val){
			$object = $tabaff_codes_ordonnes[$key];
			$code_courant = $object->code;
			// rechercher les fils
            $i=0;
			$ok=true;
   			foreach ($object->nodelist as $idnode){
    			if ($tabaff_codes_ordonnes[$idnode]->nodenumber == 1){    // Les noeuds feuilles ont un nodelist reduite à eux-memes
                    $object->nbfeuilles++;
				}
			}
            $object->colspan = $object->nbfeuilles;
		}

        // CREATION du tableau a afficher (Fin)
		// Pour chaque cadre dans le parcours en LARGEUR completer col
		// Si changement de niveau col = col du pere
		// Sinon col = colspan occupees + cospan du precedant
		$niveau_courant=0;
        $col_precedant=0;
        $nb_col=0;
		$max_lig=0;
		// Parcours en largeur
		foreach ($tabaff_codes_largeur as $idframe => $code){
			$object = $tabaff_codes_ordonnes[$idframe];
			$niveau = $object->niveau;
            //echo "<br />CODE : ".$object->code.", NIVEAU : ".$object->niveau.", NIVEAU_COURANT : $niveau_courant \n";
            if ($niveau == 1){
                $nb_col+=$object->colspan;
			}
			if ($niveau != $niveau_courant){
				// Changement de niveau
                //echo "<br />CHANGEMENT NIVEAU \n";
                $max_lig++;
                if (!empty($object->idparentframe)){
                    if ($object_pere = $tabaff_codes_ordonnes[$object->idparentframe]){
                        $object->col = $object_pere->col;
					}
				}
				else{
                    $object->col = $col_precedant;
				}
                $niveau_courant = $niveau;
			}
			else{
                //echo "<br />MEME NIVEAU \n";
				$object->col = $object->colspan + $col_precedant;
			}
            $col_precedant = $object->col;
            //echo "<br />COLSPAN : ".$object->col ."\n";
		}

		// palette de couleurs
        $palette ='';
		/* ************* Supprimer le commentaire pour afficher la palette complete *****
        $palette = "\n".'<table>'."\n";
        //for ($i=0; $i<14; $i++){
        for ($i=0; $i<8; $i++){
            $palette.='<tr>';
			for ($j=0; $j<4; $j++){
		        $index_color = ($j  % 4) + 1;
            	$color=chr(65+$i) . "$index_color";
				//echo "<br />COLOR : niveau_$color\n";
                $palette.='<td class="niveau_'.$color.'" rowspan="'.$object->colspan.'">niveau_'.$color.' '.$i.' '.$j.'</td>';
			}
            $palette.='</tr>'."\n";
		}
        $palette .= "\n".'</table>'."\n";
		*********************************************************************************/

		// RECOPIER LES DONNEES
		// Table en profondeur d'abord

        $return_str='';
		$table_affichee = "\n".'<table>'."\n";
		//."\n".'<tbody><tr><th colspan="'.$nb_lig.'">'.$tab->title.' <i>'.get_string('selectframe','artefact.booklet').'</i></th></tr></tbody>'."\n";
        $nouvelleligne=true;
		$niveau_courant=0;
        $col_courante=0;
        $hierarchy_str='';

        foreach ($tabaff_codes_profondeur as $idframe => $code){
			$object = $tabaff_codes_ordonnes[$idframe];
			// print_object($object);

			$linkselect='<a class="select" href="'.$link_part1.$idtab.$link_part2.$object->id.$link_part3.'"><b>'.$object->title.'</b></a>';
			$linkunselect='<a class="menu1" href="'.$link_part1.$idtab.$link_part2.$object->id.$link_part3.'">'.$object->title.'</a>';

            $parentid = $object->idparentframe;
            $colposition = 0;
            $str=' &gt;'.$linkselect;
			if (!empty($parentid)){
     			while (!empty($parentid)){
        	    	$colposition += $tabaff_codes_ordonnes[$parentid]->colspan;
                    $str=' &gt;'.'<a class="menu" href="'.$link_part1.$idtab.$link_part2.$tabaff_codes_ordonnes[$parentid]->id.$link_part3.'">'.				           $tabaff_codes_ordonnes[$parentid]->title.'</a> '.$str;
            	    $parentid =  $tabaff_codes_ordonnes[$parentid]->idparentframe;
				}
			}
            $str='<a class="menu" href="'.$link_part1.$idtab.'">'.$tab->title.'</a>'.$str;
/*
            if (!empty($object->nodelist) && ($object->nodenumber>1)){
                $node=1;
                if (isset($object->nodelist[$node])){
					$filsid=$object->nodelist[$node];
					while (!empty($filsid)){
                    	$str.=' &gt;'.'<a class="menu" href="'.$link_part1.$tabaff_codes_ordonnes[$filsid]->id.$link_part2.'&amp;tab='.$idtab.'">'.$tabaff_codes_ordonnes[$filsid]->title.'</a> ';
						$node++;
						if (isset($object->nodelist[$node])){
                        	$filsid=$object->nodelist[$node];
						}
						else{
                            $filsid=0;
						}
					}
				}
			}
*/
			if ($nouvelleligne){
                //$table_affichee.='<tr class="niveau_'.$object->niveau.'">';
                $table_affichee.='<tr>';
                $nouvelleligne=false;
                //$col_courante = $colposition;
                $col_courante = 0;
			}

			if (!empty($colposition)){
                $colposition--;
			}

			//echo "<br />$object->code ::  COL_COURANTE : $col_courante -&gt; COLPOSITION : $colposition\n";

			if ($col_courante < $colposition){
                //echo "<br />AVANT : $object->code ALLER DE $col_courante A $colposition\n";
				for ($i=0; $i < $colposition; $i++){
            		// $table_affichee.='<td class="blank">&nbsp;</td>';
					$col_courante++;
				}
			}

            $cod = chr( (ord(strtoupper(substr($code,0,1))) - 64) % 8 + 64);
            $index_color = (($object->niveau - 1) % 4) + 1;
            $color="$cod$index_color";
			//echo "<br />CODE: $code / COLOR : niveau_$color\n";
			//exit;
            if ($object->id == $idselectedframe){
                $hierarchy_str=$str;
				if ($object->colspan>1){
					$table_affichee.='<td class="select" rowspan="'.$object->colspan.'">'.$linkselect.'</td>';
				}
				else{
					$table_affichee.='<td class="select">'.$linkselect.'</td>';
				}
			}
			else{
				if ($object->colspan>1){
					$table_affichee.='<td class="niveau_'.$color.'" rowspan="'.$object->colspan.'">'.$linkunselect.'</td>';
				}
				else{
					$table_affichee.='<td class="niveau_'.$color.'">'.$linkunselect.'</td>';
				}
			}

   // DEBUG
 /*
            if ($object->id == $idselectedframe){
				$table_affichee.='<td class="gold" rowspan="'.$object->colspan.'">'.
					'<b>'.$object->code.'</b></td>';
			}
			else{
				$table_affichee.='<td class="niveau_'.$color.'" rowspan="'.$object->colspan.'">'.
					$object->code.'</td>';
			}
*/
            $col_courante++;

			// Nouvelle ligne ?
			if ($object->nodenumber == 1){
	            //echo "<br /> APRES :  $object->code ALLER DE $col_courante A $max_lig\n";
				/*
				if ($col_courante<$max_lig){
					for ($i=$col_courante; $i<$max_lig; $i++){
       	    	       	$table_affichee.='<td class="blank">&nbsp;</td>';
					}
				}
				*/
                $table_affichee.='</tr>'."\n";
                $nouvelleligne=true;
			}
		}


        $table_affichee.='</table>'."\n";

        if (!empty($hierarchy_str)){
			$return_str='<div><span class="menu">'.$hierarchy_str.'</span></div>';
		}
        $return_str.=$palette.$table_affichee;

		//echo "<br />DEBUG :: lib.php :: 2269 :: TABLE AFFICHEE <br />$table_affichee\n";
		//exit;
        $menuform = array();
        $menuform["menu"] = $return_str;
        return $menuform;
	}


}

// fin de la classe : ArtefactTypeVisualization