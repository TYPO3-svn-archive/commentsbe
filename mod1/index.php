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
 
$LANG->includeLLFile('EXT:commentsbe/mod1/locallang.xml');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);	// This checks permissions and exits if the users has no permission for entry.
	// DEFAULT initialization of a module [END]
 
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
					
					$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['commentsbe']);
          $max_records = $this->extConf['max_records'];

					// Access check!
					// The page will show only if there is a valid page and if this page may be viewed by the user
					$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
					$access = is_array($this->pageinfo) ? 1 : 0;
				
					if (($this->id && $access) || ($BE_USER->user['admin'] && !$this->id))	{

						// Draw the header.
						$this->doc = t3lib_div::makeInstance('bigDoc');
						$this->doc->styleSheetFile2=$GLOBALS["temp_modPath"].'../typo3conf/ext/commentsbe/res/css/bemodule.css';
						$this->doc->backPath = $BACK_PATH;
						$this->doc->form='<form action="" name="myform3" method="post" enctype="multipart/form-data">';

						// JavaScript
						$this->doc->JScode = '
						<script src="../typo3conf/ext/commentsbe/res/js/jquery.js" type="text/javascript"></script>
						<script type="text/javascript" src="../typo3conf/ext/commentsbe/res/js/jquery.tablesorter.js"></script> 
						<script type="text/javascript" src="../typo3conf/ext/commentsbe/res/js/jquery.tablesorter.pager.js"></script> 
							<script language="javascript" type="text/javascript">
								script_ended = 0;
								function jumpToUrl(URL)	{
									document.location = URL;
								}
								
								$(document).ready(function(){ 
                  $("#tablesorter-demo")
                  .tablesorter({
                    sortList:[[0,0],[1,0],[3,0],[5,0],[6,0]],
                    widgets: [\'zebra\'],
                    headers: {
                      2: {
                        sorter: false
                      },
                      4: {
                        sorter: false
                      },
                      7: {
                        sorter: false
                      }, 
                      8: {
                        sorter: false
                      },
                      9: {
                        sorter: false
                      }
                    }
                  })
                  .tablesorterPager({
                    container: $("#pager"),
                    size: '.$max_records.',
                    positionFixed: false
                  });
                }); 
								
							</script>
              <script type="text/javascript">
                $(function () { // this line makes sure this code runs on page load
                	$(\'.checkall\').click(function () {
                		$(this).parents(\'fieldset:eq(0)\').find(\':checkbox\').attr(\'checked\', this.checked);
                	});
                })
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
            
            // Bulk actions
            if($_POST['actmul']) {            
              $fields = $_POST['fields'];
              if($fields != '') {
                
                $fields_new = '';
                foreach($fields as $field)$fields_new .= ','.intval($field);
                $fields_new = substr($fields_new,1);
                
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
              $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_comments_comments','deleted=0','','');
            }
            else {  
              $page_array = array();
              $page_array[] = $pid; // Insert active pid in array              
              // Show comments in subpages, if activated in ext manager
              if($this->extConf['show_sub'] == 1) {   
                $page_res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','pages','deleted=0 AND pid='.$pid);           
                while($row_page=mysql_fetch_assoc($page_res)) {
                  $page_array[] = $row_page['uid'];
                }
              } 
              $pages = implode(",",$page_array);                                                                                                        
              $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_comments_comments','deleted=0 AND pid IN ('.$pages.')','','');
            }
            
            // Get all records
            if($pid == '0') {
              $res2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_comments_comments','deleted=0');
            }
            else {
              $page_array2 = array();
              $page_array2[] = $pid; // Insert active pid in array              
              // Show comments in subpages, if activated in ext manager
              if($this->extConf['show_sub'] == 1) {   
                $page_res2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','pages','deleted=0 AND pid='.$pid);           
                while($row_page2=mysql_fetch_assoc($page_res2)) {
                  $page_array2[] = $row_page2['uid'];
                } 
              } 
              $pages2 = implode(",",$page_array2);                 
              $res2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_comments_comments','deleted=0 AND pid IN ('.$pages.')');
            }
            
            $num_rows = mysql_num_rows($res);    

            // No Comment
            if ($num_rows == '') {
              $content .= ''.$LANG->getLL('nocomment').'<br /><br />';
            }
            
            // Root Page and 1 Comment            
            else if ($num_rows == '1' && $pid == '0') {
              $content .= ''.$LANG->getLL('commentglobal_one').'<b> '.$num_rows.'</b> '.$LANG->getLL('commentglobal_two').'<br /><br />';
            }
            
            // Root Page and more than 1 Comment
            else if ($num_rows > '1' && $pid == '0') {
              $content .= ''.$LANG->getLL('commentglobalmore_one').'<b> '.$num_rows.'</b> '.$LANG->getLL('commentglobalmore_two').'<br /><br />';
            }
            
            // 1 Comment
            else if ($num_rows == '1') {
              $content .= ''.$LANG->getLL('onecomment_one').'<b> '.$num_rows.'</b> '.$LANG->getLL('onecomment_two').'<br /><br />';  
            }

            // More Comments
            else { 
              $content .= ''.$LANG->getLL('morecomments_one').' <b>'.$num_rows.'</b> '.$LANG->getLL('morecomments_two').'<br /><br />';             
            }
          
          // Show Table Head only if at least 1 Comment exists.
          if($num_rows >= '1') {  
          $content .= '
            <fieldset>
              <table id="tablesorter-demo" class="tablesorter">
              <thead>
                <tr>
                  <th class="id">'.$LANG->getLL('id').'</th>
                  <th>'.$LANG->getLL('pid').'</th>
                  <th>'.$LANG->getLL('date').'</th>
                  <th>'.$LANG->getLL('name').'</th>
                  <th>'.$LANG->getLL('comment').'</th>
                  <th>'.$LANG->getLL('approveboth').'</th>
                  <th>'.$LANG->getLL('hideshow').'</th>
                  <th>'.$LANG->getLL('edittwo').'</th>
                  <th>'.$LANG->getLL('deletetwo').'</th>
                  <th><input type="checkbox" class="checkall" title="'.$LANG->getLL('check_all').'"></th>
                </tr>
                </thead>';
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
                  <td class="img">'.$editUid.'</td>
                  <td>'.$pid_record.'</td>
                  <td class="date">'.$time.'</td>
                  <td class="name">'.$name.'</td>
                  <td>'.$comment_txt_crop.'</td>
                  
                  ';
                
                  if ($row[$approvedField])	{  
                    $content .='
                      <td class="img"><a href="'.$this->doc->issueCommand($params6).'">
                      <img src="'.$GLOBALS['BACK_PATH'].'../typo3conf/ext/comments/icon_comments.gif" border="0" title="'.$LANG->getLL('disapprove').'" align="top" alt=""
                      /></a></td>
                    ';
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
                      /></a></td>
                    ';
                  }

                  else {
                    $content .= '
                      <td class="img"><a href="'.$this->doc->issueCommand($params5).'">
                      <img src="'.$GLOBALS['BACK_PATH'].'sysext/t3skin/icons/gfx/button_hide.gif" border="0" title="'.$LANG->getLL('hide').'" align="top" alt="" /></a></td>';
                  }

                  $content .= '                                     
                    <td class="img"><a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params2,$GLOBALS['BACK_PATH'])).'">
                      <img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/edit2.gif','width="11" height="12"').' title="'.$LANG->getLL('edit').'" border="0" alt="" /></a>
                    </td>                                                      
                    <td class="img"><a href="'.$this->doc->issueCommand($params3).'"
                      onclick="return confirm(unescape(\''.rawurlencode(''.$LANG->getLL('delete_txt').'').'\'));">
                      <img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],'gfx/garbage.gif','width="11" height="12"').' title="'.$LANG->getLL('delete').'" alt="" /></a>
                    </td>                    
                    <td>
                      <input type="checkbox" name="fields[]" value="'.$editUid.'" />
                    </td>';                        
                  
                  $content .= '
                    </tr>
                  ';
                }      

                $content .= '
                  </table>   
                  </fieldset> 
                  <hr style="margin-top: 5px; margin-bottom: 5px;"/>
                ';

                if($num_rows != 0) {
                  $content .= '
                  <div class="pagenav">
                    <div id="pager" class="pager">  
                      <form>    
                      	<img src="../typo3conf/ext/commentsbe/res/img/pager/first.png" class="first" />  
                      	<img src="../typo3conf/ext/commentsbe/res/img/pager/prev.png" class="prev" />  
                      	<input type="text" class="pagedisplay"/>   
                      	<img src="../typo3conf/ext/commentsbe/res/img/pager/next.png" class="next" />
                      	<img src="../typo3conf/ext/commentsbe/res/img/pager/last.png" class="last" />
                        <span class="show_comments">'.$LANG->getLL('show_comments').'</span>   
                        <select class="pagesize">    
                    ';
                    
                    $select_val = trim($this->extConf['select_val']);
                    $select_val_arr = explode(",",$select_val);
                    $select_val_arr[] = $max_records; // Add starting value defined in ext manager
                    sort($select_val_arr); // Sort array 
                    $select_val_arr_unique = array_unique($select_val_arr);  
                    
                    // Build selectbox
                    foreach($select_val_arr_unique as $o) {
                      // Highlight starting value
                      if($o == $max_records) {
                        $content .= '   	   
                          		<option value="'.$o.'" selected="selected">'.$o.'</option>
                        ';    
                      }
                      else if($o == '') {
                        // Do nothing if array value is empty
                      }  
                      else {
                        $content .= '   	   
                          		<option value="'.$o.'">'.$o.'</option>
                        ';
                      }
                    }
                    
                    $content .= '      
                      	</select>   
                      </form> 
                    </div>   
                  </div>
                  ';
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
                <div class="clearit">&nbsp;</div>
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