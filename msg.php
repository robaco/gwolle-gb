<?php
  /**
   * msg.php
   * Displays a message (error/updated) according on the $_REQUEST['msg'] variable.
   */
  $msg = (isset($_REQUEST['msg'])) ? $_REQUEST['msg'] : FALSE;
  
  if (isset($_REQUEST['updated'])) {
    $msg = ($_REQUEST['updated'] == 'true') ? 'changes-saved' : 'no-changes-made';
  }
  elseif (isset($_REQUEST['error'])) {
    $msg = 'error-saving';
  }
  
  
  if ($msg !== FALSE) {
    $error_messages = array(
      'check-akismet-configuration' =>  __('Please check your Akismet configuration.',$textdomain),
      'akismet-not-activated'       => str_replace('%1','admin.php?page=gwolle-gb/settings.php',__('Please <a href="%1">enable Akismet</a> to use the spam feature of Gwolle-GB.',$textdomain)),
      'no-massEditAction-selected'  => __('No mass edit action selected.',$textdomain),
      'error-deleting'              => __('An error occured while trying to delete the entry.',$textdomain),
      'no-changes-made'             => __('<strong>Notice:</strong> No changes were made.',$textdomain),
      'error-saving'                => __('An error occurred while saving your changes.',$textdomain)
    );
    $updated_messages = array(
      'successfully-uninstalled'    => __('<strong>Gwolle-GB has been successfully uninstalled.</strong> Thanks for using my plugin!',$textdomain),
      'deleted'                     => __('Entry successfully deleted.',$textdomain),
      'no-entries-edited'           => __('No entries were edited.',$textdomain),
      'changes-saved'               => __('Changes saved.',$textdomain),
      'successfully-unmarkedSpam'   => __('Entry successfully classified as not-spam.',$textdomain),
      'uninstall-not-confirmed'     => __('Uninstall process stopped.',$textdomain)
    );
    
    //  Messages with numbers
    $updated_messages['successfully-edited']  = (isset($_REQUEST['count']) && (int)$_REQUEST['count'] > 1) ? $_REQUEST['count'] . ' ' . __('entries',$textdomain) : __('One entry',$textdomain);
    $updated_messages['successfully-edited'] .= ' '.__('successfully edited.',$textdomain);
    
    //  Output
    if (isset($error_messages[$msg])) {
      echo '
      <div id="message" class="error fade">
        <p>'.$error_messages[$msg].'</p>
      </div>';
    }
    elseif (isset($updated_messages[$msg])) {
      echo '
      <div id="message" class="updated fade">
        <p>'.$updated_messages[$msg].'</p>
      </div>';
    }
    
    //  Additional messages
    if (isset($entry) && $entry['entry_isSpam'] === 1) {
      //	Notify that this entry is marked as spam.
			echo '
			<div id="message" class="error fade">
        <p>'.__('<strong>Attention:</strong> This entry is marked as spam!',$textdomain).'</p>
      </div>';
		}
  }
?>