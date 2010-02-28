<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Raphael Zschorsch <rafu1987@gmail.com>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 * Hint: use extdeveval to insert/update function index above.
 */


$LANG->includeLLFile('EXT:commentsbe/mod1/locallang.xml');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]

/**
 * Module 'Comments' for the 'commentsbe' extension.
 *
 * @author	Raphael Zschorsch <rafu1987@gmail.com>
 * @package	TYPO3
 * @subpackage	tx_commentsbe
 */
 
class  tx_commentsbe_module1 extends t3lib_SCbase {
				var $pageinfo;
				function init()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

					parent::init();

				}

				function menuConfig()	{
					global $LANG;
					$this->MOD_MENU = Array (
						'function' => Array (
							'1' => $LANG->getLL('function1'),
						)
					);
					parent::menuConfig();
				}

				function main()	{
					global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

					// Access check!
					// The page will show only if there is a valid page and if this page may be viewed by the user
					$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
					$access = is_array($this->pageinfo) ? 1 : 0;
				
					if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

						// Draw the header.
						$this->doc = t3lib_div::makeInstance('bigDoc');
						$this->doc->styleSheetFile2=$GLOBALS["temp_modPath"].'../typo3conf/ext/commentsbe/res/bemodule.css';
						$this->doc->backPath = $BACK_PATH;
						$this->doc->form='<form action="" method="post" enctype="multipart/form-data">';

						// JavaScript
						$this->doc->JScode = '
							<script language="javascript" type="text/javascript">
								script_ended = 0;
								function jumpToUrl(URL)	{
									document.location = URL;
								}
							</script>
						';
						$this->doc->postCode='
							<script language="javascript" type="text/javascript">
								script_ended = 1;
								if (top.fsMod) top.fsMod.recentIds["web"] = 0;
							</script>
						';

						$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
						$this->content.=$this->doc->spacer(5);
						//$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
						$this->content.=$this->doc->divider(5);

						// Render content:
						$this->moduleContent();

						// ShortCut
						if ($BE_USER->mayMakeShortcut())	{
							$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
						}

						$this->content.=$this->doc->spacer(10);
					} else {
							// If no access or if ID == zero

						$this->doc = t3lib_div::makeInstance('bigDoc');
						$this->doc->backPath = $BACK_PATH;

						$this->content.=$this->doc->startPage($LANG->getLL('title'));
						$this->content.=$this->doc->header($LANG->getLL('title'));
						$this->content.=$this->doc->spacer(5);
						$this->content.=$this->doc->spacer(10);
					}
				
				}

				function printContent()	{
        
					$this->content.=$this->doc->endPage();
					echo $this->content;
				}

				function moduleContent()	{
				  global $LANG;
					switch((string)$this->MOD_SETTINGS['function'])	{
						case 1:
						
						$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['commentsbe']);
            $max_records = $this->extConf['max_records'];
            $add_records = $this->extConf['add_records'];
            $text_crop = $this->extConf['text_crop'];
						
						// Get current Page ID
						$pid = $this->id;					
						$editTable = 'tx_comments_comments';						
                                    
            // "Create New" Button
            // params = Create New  
            $params = '&edit['.$editTable.']['.$pid.']=new&defVals['.$editTable.']';    
            $content .= '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$GLOBALS['BACK_PATH'])).'">
            <img src="sysext/t3skin/icons/gfx/new_el.gif" title="'.$LANG->getLL('newcomment').'" border="0" alt="" />
            </a><br /><br />';
            
            //check if the starting row variable was passed in the URL or not
            if (!isset($_GET['startrow']) or !is_numeric($_GET['startrow'])) {
              //we give the value of the starting row to 0 because nothing was found in URL
              $startrow = $max_records;
            //otherwise we take the value from the URL
            } else {
              $startrow = (int)$_GET['startrow'];
            } 
                                        
            $content .= '
            '.$LANG->getLL('orderby').' 
            <select name="orderby" size="1">
              ';
              if($_POST['orderby'] == 'uid') {
                $content .= '<option value="uid" selected="selected">'.$LANG->getLL('orderid').'</option>';
              }
              else {
                $content .= '<option value="uid">'.$LANG->getLL('orderid').'</option>';
              }
              
              if($_POST['orderby'] == 'crdate') {
                $content .= '<option value="crdate" selected="selected">'.$LANG->getLL('orderdate').'</option>';
              }
              else {
                $content .= '<option value="crdate">'.$LANG->getLL('orderdate').'</option>';
              }
              
              if($_POST['orderby'] == 'approved') {
                $content .= '<option value="approved" selected="selected">'.$LANG->getLL('orderapproved').'</option>';
              }
              else {
                $content .= '<option value="approved">'.$LANG->getLL('orderapproved').'</option>';
              }
              
              $content .= '
            </select>
            
            <select name="ascdesc" size="1">';
            
            if($_POST['ascdesc'] == 'asc') {
              $content .= '<option value="asc" selected="selected">'.$LANG->getLL('asc').'</option>';  
            }
            else {
              $content .= '<option value="asc">'.$LANG->getLL('asc').'</option>';
            }
            if($_POST['ascdesc'] == 'desc') {
              $content .= '<option value="desc" selected="selected">'.$LANG->getLL('desc').'</option>';
            }
            else {
              $content .= '<option value="desc">'.$LANG->getLL('desc').'</option>';
            }
            
            $content .= '
            </select>
            
            <input type="submit" value="'.$LANG->getLL('go').'" />
            <br /><br />            
            ';
            
            $orderby = ''.$_POST['orderby'].''.$_POST['ascdesc'];
            
            if($orderby == '') {
              $orderby = 'uid ASC';
            }
            else {
              $orderby = ''.$_POST['orderby'].' '.$_POST['ascdesc'];
            }
            
            // Bulk actions
            if($_POST['actmul']) {            
              $fields = $_POST['fields'];
              if($fields != '') {
                $fields_new = implode(",",$fields);
                // Approve
                if($_POST['bulkact'] == '1') {                              
                  $upd = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, 'UPDATE tx_comments_comments SET approved="1" WHERE uid IN ('.$fields_new.')');
                }
                // Disapprove
                else if($_POST['bulkact'] == '2') {                              
                  $upd = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, 'UPDATE tx_comments_comments SET approved="0" WHERE uid IN ('.$fields_new.')');
                }
                // Hide
                else if($_POST['bulkact'] == '3') {                              
                  $upd = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, 'UPDATE tx_comments_comments SET hidden="1" WHERE uid IN ('.$fields_new.')');
                }  
                // Show
                else if($_POST['bulkact'] == '4') {                              
                  $upd = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, 'UPDATE tx_comments_comments SET hidden="0" WHERE uid IN ('.$fields_new.')');
                }   
                // Delete
                else if($_POST['bulkact'] == '5') {                              
                  $upd = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, 'UPDATE tx_comments_comments SET deleted="1" WHERE uid IN ('.$fields_new.')');
                }                                                
              }
            }
					  
						// Show all comments
            if($pid == '0') {
              $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_comments_comments','deleted=0','',$orderby,$startrow);
            }
            else {
              $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_comments_comments','deleted=0 AND pid='.$pid.'','',$orderby,$startrow);
            }
            
            // Get all records
            if($pid == '0') {
              $res2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_comments_comments','deleted=0','',$orderby);
            }
            else {
              $res2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_comments_comments','deleted=0 AND pid='.$pid.'','',$orderby);
            }
            
            $num_rows = mysql_num_rows($res); 
            $num_rows2 = mysql_num_rows($res2); 

            // No Comment
            if ($num_rows2 == '') {
              $content .= ''.$LANG->getLL('nocomment').'<br /><br />';
            }
            
            // Root Page and 1 Comment            
            else if ($num_rows2 == '1' && $pid == '0') {
              $content .= ''.$LANG->getLL('commentglobal_one').'<b> '.$num_rows2.'</b> '.$LANG->getLL('commentglobal_two').'<br /><br />';
            }
            
            // Root Page and more than 1 Comment
            else if ($num_rows2 > '1' && $pid == '0') {
              $content .= ''.$LANG->getLL('commentglobalmore_one').'<b> '.$num_rows2.'</b> '.$LANG->getLL('commentglobalmore_two').'<br /><br />';
            }
            
            // 1 Comment
            else if ($num_rows2 == '1') {
              $content .= ''.$LANG->getLL('onecomment_one').'<b> '.$num_rows2.'</b> '.$LANG->getLL('onecomment_two').'<br /><br />';  
            }

            // More Comments
            else { 
              $content .= ''.$LANG->getLL('morecomments_one').' <b>'.$num_rows2.'</b> '.$LANG->getLL('morecomments_two').'<br /><br />';             
            }
          
          // Show Table Head only if at least 1 Comment exists.
          if($num_rows2 >= '1') {  
          $content .= '
            <table class="commentsbe">
              <tr>
                <th class="id">'.$LANG->getLL('id').'</th>';
                if($pid == '0') {
                  $content .= '<th>'.$LANG->getLL('pid').'</th>';
                }                   
          $content .= '
                <th>'.$LANG->getLL('date').'</th>
                <th>'.$LANG->getLL('name').'</th>
                <th>'.$LANG->getLL('comment').'</th>
                <th>'.$LANG->getLL('approveboth').'</th>
                <th>'.$LANG->getLL('hideshow').'</th>
                <th>'.$LANG->getLL('edittwo').'</th>
                <th>'.$LANG->getLL('deletetwo').'</th>
                <th>&nbsp;</th>
              </tr>';
              }
                        
              while($row=mysql_fetch_assoc($res)) {
                // Get the fields
                $editTable = 'tx_comments_comments';
                $editUid = $row['uid'];
                $pid_record = $row['pid'];
                $hiddenField = 'hidden';
                $approvedField = 'approved';
                $name = ''.$row['firstname'].' '.$row['lastname'].'';
                $comment_txt = $row['content'];
                $comment_txt_crop = ''.htmlspecialchars(substr($comment_txt, 0, $text_crop)).' ...';
                
                $tstamp = $row['crdate'];
                $time = ''.date("d.m.Y",$tstamp).' - '.date("H:i",$tstamp).'';
                                                
                /*                
                params2 = Edit
                params3 = Delete
                params4 = Hide
                params5 = Show
                params6 = Disapprove
                params7 = Approve                
                */                 
                
                // Get the params
                $params2 = '&edit['.$editTable.']['.$editUid.']=edit'; 
                $params3 ='&cmd['.$editTable.']['.$editUid.'][delete]=1';
                $params4 ='&data['.$editTable.']['.$editUid.']['.$hiddenField.']=0';
                $params5 ='&data['.$editTable.']['.$editUid.']['.$hiddenField.']=1';
                $params6 ='&data['.$editTable.']['.$editUid.']['.$approvedField.']=0';
                $params7 ='&data['.$editTable.']['.$editUid.']['.$approvedField.']=1';

                $this->currentTable = 'tx_comments_comments';
                
              $content .= '
                                          
              <tr>
                <td class="img">'.$editUid.'</td>';
                
                if($pid == '0') {
                  $content .= '<td>'.$pid_record.'</td>';
                }  
                
                $content .= '
                <td class="date">'.$time.'</td>
                <td class="name">'.$name.'</td>
                <td>'.$comment_txt_crop.'</td>
                
                ';
                
                if ($row[$approvedField])	{

$content .='
<td class="img"><a href="'.$this->doc->issueCommand($params6).'">
<img src="'.$GLOBALS['BACK_PATH'].'../typo3conf/ext/comments/icon_comments.gif" border="0" title="'.$LANG->getLL('disapprove').'" align="top" alt=""
/></a></td>';
}

else {
  $content .= '
  <td class="img"><a href="'.$this->doc->issueCommand($params7).'">
  <img src="'.$GLOBALS['BACK_PATH'].'../typo3conf/ext/comments/icon_comments_not_approved.gif" border="0" title="'.$LANG->getLL('approve').'" align="top" alt="" /></a></td>';
}                            

if ($row[$hiddenField])	{

$content .='
<td class="img"><a href="'.$this->doc->issueCommand($params4).'">
<img src="'.$GLOBALS['BACK_PATH'].'sysext/t3skin/icons/gfx/button_unhide.gif" border="0" title="'.$LANG->getLL('show').'" align="top" alt=""
/></a></td>';
}

else {
  $content .= '
  <td class="img"><a href="'.$this->doc->issueCommand($params5).'">
  <img src="'.$GLOBALS['BACK_PATH'].'sysext/t3skin/icons/gfx/button_hide.gif" border="0" title="'.$LANG->getLL('hide').'" align="top" alt="" /></a></td>';
}

$content .= '
                
<td class="img"><a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params2,$GLOBALS['BACK_PATH'])).'">
<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/edit2.gif','width="11" height="12"').' title="'.$LANG->getLL('edit').'" border="0" alt="" /></a></td>
              

<td class="img"><a href="'.$this->doc->issueCommand($params3).'"
onclick="return confirm(unescape(\''.rawurlencode(''.$LANG->getLL('delete_txt').'').'\'));">
<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/garbage.gif','width="11" height="12"').' title="'.$LANG->getLL('delete').'" alt="" /></a></td>

<td>
<input type="checkbox" name="fields[]" value="'.$editUid.'" />
</td>'; 
 
$content .= '
</tr>';
             }     

$content .= '</table>

<hr style="margin-top: 5px; margin-bottom: 5px;"/>';

if($num_rows2 != 0 && $num_rows2 != $num_rows) {
  $content .= '<a href="mod.php?M=web_txcommentsbeM1&id='.$pid.'&startrow='.($startrow+$add_records).'">'.$LANG->getLL('more_records').'</a>';
}

$content .= '
<div class="div-float">
  '.$LANG->getLL('bulkact').'
  <select name="bulkact" size="1">
    <option value="1">'.$LANG->getLL('bulkact_one').'</option> 
    <option value="2">'.$LANG->getLL('bulkact_two').'</option> 
    <option value="3">'.$LANG->getLL('bulkact_three').'</option>  
    <option value="4">'.$LANG->getLL('bulkact_four').'</option>  
    <option value="5">'.$LANG->getLL('bulkact_five').'</option>  
  </select>
  <input type="submit" name="actmul" value="'.$LANG->getLL('go').'" onclick="return confirm(unescape(\''.rawurlencode(''.$LANG->getLL('mul_txt').'').'\'));" />
</div>
';

$this->content.=$this->doc->section('',$content,0,1);
						
            break;
					}
				}
		}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/commentsbe/mod1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/commentsbe/mod1/index.php']);
}

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_commentsbe_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>