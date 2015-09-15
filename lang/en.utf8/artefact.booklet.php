<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

// debug
$string['incorrectbooklettab'] = "This booklet is bugged. Have you thought to save it ?";

// Included frame
$string['included'] = "Included frame";
$string['node'] = "Father frame";
$string['addsuccessorframe'] = "Add a new included frame";
$string['successorframe'] = "Included in frame %s";
$string['root'] = "At root level";
$string['tomove'] = "Frame to move";
$string['selectwheremove'] = "Select the frame destination of '%s'";
// Author & copyright
$string['author'] = "Author";
$string['authorform'] = "Save author";
$string['authorerror'] = "Error : Author's data not saved...";
$string['mail'] = "Mail";
$string['url'] = "Url";
$string['urldesc'] = "Institution's web site";
$string['copyrightdesc'] = "Input here the copyright licence for this Booklet";
$string['version'] = "Version";
$string['information'] = "About";
$string['passdesc'] = "Mandatory to edit this form...";
$string['newpassword'] = "New password ?";
$string['newpassdesc'] = "Let it blank or type in a new password if needed...";
$string['passerror'] = "Error : not matching password.";
$string['show'] = "Show";
$string['showall'] = "Show all";
$string['hide'] = "Hide";
$string['status'] = "Status";
$string['statusmodif'] = "Modification status";
$string['selectstatus'] = "Booklet modification";
$string['selectstatusdesc'] = "If status is 'forbidden' only the author may edit this booklet... and you have to choose <a target=\"_blank\" href=\"http://creativecommons.org/licenses/by-nd/4.0/\">CC BY-ND</a>";
$string['forbidden'] = "Forbidden";
$string['allowed'] = "Allowed";
$string['editforbidden'] = "Copyright";
$string['copyright'] = "Copyright";
$string['copyright_cc'] = "<a target=\"_blank\" href=\"http://creativecommons.org/licenses/by/4.0/\">CC BY</a>";
$string['copyright_ccnd'] = "<a target=\"_blank\" href=\"http://creativecommons.org/licenses/by-nd/4.0/\">CC BY-ND</a>";

//Titles
$string['pluginname'] = 'Booklet';
$string['booklet'] = 'Booklet';

//index.php
$string['modif'] = "Modify booklets";
$string['modifbooklet'] = "Modifier l'architecture des livrets";
$string['modifbookletdesc'] = "Be carrefull when editing booklet's architecture. It may impact a lot of user data.";
$string['tomechoice'] = "Choose the booklet to use : ";
$string['bookletsaved'] = "Booklet saved";

//table
$string['moveup'] = "Move up";
$string['movedown'] = "Move down";
$string['moveright'] = "Move right";
$string['moveleft'] = "Move left";
$string['edit'] = "Edit";
$string['editall'] = "Edit all";
$string['editforbidden'] = "Copyright";
$string['del'] = "Delete";
$string['cancel'] = "Cancel";
$string['add'] = "Add";
$string['compositedeleteconfirm'] = "Are you sure you want to delete this item ?";
$string['compositedeleted'] = "item has been deleted.";

//tomes.php
$string['tomes'] = "booklets";
$string['tomesname'] = "Titles of booklets";
$string['addtome'] = "Add a new booklet";
$string['tomesavefailed'] = "Fail";
$string['visualizetome'] = "Visualize the booklet";
$string['exporttome'] = "Export a booklet";
$string['importtome'] = "Import a booklet";
$string['export'] = "Export";
$string['filename'] = "File name";
$string['loadxmlfailed'] = "Error : opening xml file";
$string['noforwardref'] = "Error : forward reference ignored";

//tabs.php
$string['tomename'] = "Title of the booklet";
$string['helptome'] = "Booklet help";
$string['savetome'] = "Save the booklet";
$string['tabsname'] = "Titles of tabs";
$string['tabs'] = "Tabs";
$string['addttab'] = "Add a new tab";
$string['canceltab'] = "Return";
$string['public'] = "Public";
$string['visualizetab'] = "Visualize the tab";
$string['tabsavefailed'] = "Fail: frame creation";

//frames.php
$string['frame'] = "Frame";
$string['frames'] = "Frames";
$string['tabname'] = "Title of tab";
$string['helptab'] = "Tab help";
$string['savetab'] = "Save the tab";
$string['cancelframe'] = "Return";
$string['framesname'] = "Titles of frames";
$string['addframe'] = "Add a new frame";
$string['islist'] = "List";
$string['yes'] = "Yes";
$string['no'] = "No";
$string['framesavefailed'] = "Fail: included frame creation";
$string['selectframe'] = "Select a frame";

//objects.php
$string['nomobjects'] = "Titles of objects";
$string['typeobjects'] = "Types of objects";
$string['nameobjects'] = "Names of objects";
$string['objects'] = "Objects";
$string['addobject'] = "Add a new object";
$string['framename'] = "Title of frame";
$string['saveframe'] = "Save the frame";
$string['cancelobject'] = "Return";
$string['helpframe'] = "Frame help";
$string['objectsavefailed'] = "Fail to save the object. The name already exist!";
$string['radio'] = "Button radio";
$string['area'] = "Text area";
$string['htmltext'] = "Text editor";
$string['longtext'] = "Long line text";
$string['shorttext'] = "Short line text";
$string['checkbox'] = "Checkbox";
$string['synthesis'] = "Synthesis text";
$string['attachedfiles'] = "Attached files";
$string['typefield'] = "Field type : ";
$string['namefield'] = "Field name : ";
$string['helpobject'] = "Object help";

//options.php
$string['nameobject'] = "Name";
$string['titleobject'] = "Title";
$string['saveobject'] = "Save";
$string['options'] = "Options";
$string['choice'] = "Choice : ";
$string['addchoice'] = "Add the choice";
$string['canceloption'] = "Return";
$string['optionsname'] = "Options title";
$string['fieldlinkedname'] = "Field linked title";
$string['addfield'] = "Add a new field to link";
$string['addfieldsavefailed'] = "This field is already linked to this object!";


// list of skills
$string['addlist'] = "New list of skills";
$string['assessment'] = "Check assessment";
$string['code'] = "Code";
$string['codedesc'] = "Code has to be a key for a peculiar domain.";
$string['checked'] = "Checked";

$string['deleteskills'] = "Delete ?";
$string['deleteskillsdesc'] = "If checked all selected skills are deleted from the list";
$string['descriptionlist'] = "Description";
$string['descriptionlistdesc'] = "Describe the context where such skills, outcomes, competencies may be gained.";
$string['descriptionlistmodel'] = "Check skills and assessments";
$string['description'] = "Description";
$string['domain'] = "Domain";
$string['domaindesc'] = "A collection of skills related to the same theme or booklet.";

$string['generalscale'] = "Skill Assessment Scale";
$string['generalscalemodel'] = "Not relevant, Needs more work, Meets the mark, Going beyond, Super!";
$string['generalscaledesc'] = "A list of values used for the skill evaluation. '<i>Not relevant</i>' is mandatory as the first value of the list.";
$string['inputnewskills'] = "Add some new skills";
$string['listofskills'] = "Input list of skills";
$string['inputlistofskillsmodel'] = "domain;type;code;description;[scale_value_value1,scale_value2,scale_value3,...,scale_valueN|threshold]";
$string['listskills'] = "List of skills";
$string['multiselect'] = "Multi selection allowed";
$string['notanyskillselected'] = "Not any skill selected";
$string['savechecklist'] = "Save selected skills";
$string['savedomainchoice'] = "Save selected domains";
$string['scale'] = "Scale";

$string['selectdomain'] = "Select a domain";
$string['selectdomains'] = "Select some domains";
$string['selectskills'] = "Select skills";
$string['skill'] = "Skill";
$string['skills'] = "Skills";
$string['sktype'] = "Display type";
$string['skilltype'] = "Type (0: header, 1: item, 2: hidden)";
$string['skilldescriptionmodel'] = "Use action verbs to describe this skill";
$string['skilldescriptiondesc'] = "Display the know-hows and the expertises linked to this skill... ";
$string['skillsavefailed'] = "Fail to save the Skill. Some data missing...";

$string['threshold'] = "Threshold";
$string['thresholdscale'] = "Threshold";
$string['thresholdscalemodel'] = "3";
$string['thresholdscaledesc'] = "If the list of scale values is ''<i>Not relevant, Needs more work,<b>Meets the mark</b>,Going beyond,Super!</i>''  then the threshold is 3";

$string['unknown'] = "Unknown";
$string['unknowncode'] = "CODE";
$string['unknowndomain'] = "DOMAIN";

//visua
$string['save'] = "Save";
$string['valid'] = "Valid";
$string['cancel'] = "Cancel";
$string['datasaved'] = "Data saved";
$string['datasavefailed'] = "Data not saved";
$string['generate'] = "Synthesis";
$string['true'] = "True";
$string['false'] = "False";
$string['date'] = "Date";
$string['modify'] = "Modify";
$string['adminform'] = "Designers choice";
$string['adminfield'] = "Choose designers";
$string['adddesigner'] = "Enter designer name to add : ";
$string['errusername'] = "User not found !";
$string['useralreadyadd'] = "User already added !";
$string['usersaved'] = "User saved!";
$string['deletedesigner'] = "Remove a designer: ";
$string['delete'] = "Remove";
$string['userdeleted'] = "Designer removed";

// Reference
$string['selectafield'] = "Select a field";
$string['reference'] = "Field reference";
$string['idobject'] = "Object Id: %s";
$string['idframe'] = "Frame ID: %s";
$string['objectlinked'] = "Object linked: ";
$string['objecttitle'] = "Object title: %s";
$string['objecttype'] = "Type: %s";
$string['referencehasnovalue'] = "Reference has not any value. Please type in a new value for field ";
$string['offrame'] = "of frame ";


// Free skill
$string['addfreeskills'] = "Add user's skills";
$string['freeskills'] = "User's skills";
$string['saveskill'] = "Save this new skill";
$string['saveskills'] = "Save these new skills";
$string['addnewskill'] = "Type in an user's skill";
$string['manageskills'] = "Manage skills";
$string['skillsmanagement'] = "Skills management";
$string['manageskillsdesc'] = "Edit and delete skills may impact user data";
$string['eraseskills'] = "Erase this skills ?";
$string['eraseskillsdesc'] = "BE CARREFULL: If checked this skills will be erased from server and data user modified...";
$string['editskills'] = "Edit some skills";
$string['skillstitle'] = "Domain :: Code";
$string['selecteditskills'] = "Select skills to edit or erase";
$string['non_pertinent'] = "Not relevant";
$string['mandatoryscale'] = "Recommanded Scale";
$string['mandatoryvalue'] = "Mandatory Scale";
$string['mandatorythreshold'] = "Mandatory Threshold";



// Frames from skills
$string['selectskillsfromframes'] = "Select skills matching frames";
$string['selectskillsfromframesdesc'] = "Display the frames matching some skills";
$string['usethreshold'] = "Use skill's threshold to select frames";
$string['usethresholddesc'] = "Only frames matching skill's threshold are displayed";
$string['frametitle'] = "Title of frame";
$string['title'] = "Title of object";
$string['selectframe'] = "Select this frame";
$string['skillframe'] = "Skill/Frame association.";
$string['skillframesavefailed'] = "Fail to save the Skill/Frame association. Some data missing...";
$string['skillframedoesnotexist'] = "This association Skill/Frame does't exists";
$string['skills_research'] = "Researched Skills";
$string['skills_frame'] = "Selected Frame (generated the %s)";
$string['freeskilldesc'] = "BE CARREFULL: You have to <i>save first this frame</i> before select any skills !";
$string['registeredframes'] = "Frames that have yet been registered";
$string['gotoframes'] = "Follow this link to display yet registered Skills / Frames associations...";
$string['checkedfordelete'] ="Check for deletetion";
$string['checkedfordeletedesc'] ="Only the selection will be deleted, not the frame itself... But this can impact registered portfolios.";
$string['deleteframe'] ="Delete selected records";
$string['framesfromskills'] = "Skills linked to Frames";
$string['tometitle'] = "Booklet: ";
$string['tabtitle'] = "Page: ";
