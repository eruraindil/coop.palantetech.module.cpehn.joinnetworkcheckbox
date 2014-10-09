<?php

require_once 'joinnetworkcheckbox.civix.php';

/**
 * Implementation of hook_civicrm_post
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_post
 */

//We check to see if "Join the Network" is checked.  If so, let's add them to the network group and send a welcome message (regardless of whether they're already a member or not).
function joinnetworkcheckbox_civicrm_post( $op, $objectName, $objectId, &$objectRef ) {
  if($op == 'create' && ($objectName == 'Contribution' || $objectName == 'Participant') ) {
    $cid = $objectRef->contact_id;

    //is the "Join the Network" custom box checked?
    $params = array(
      'version' => 3,
      'sequential' => 1,
      'entity_id' => $cid,
      'return.custom_46' => 1,
    );
    //Re-reading this code today, I realize this'd be better with a 'getvalue', not a 'getsingle', but oh well.
    $result = civicrm_api('CustomValue', 'getsingle', $params);

    //if so, then:
    if ($result[0][0] == 1) {
      //add them to the CPEHN Network group (group_id 5).
      $params = array(
        'version' => 3,
        'sequential' => 1,
        'contact_id' => $cid,
        'group_id' => 5,
      );
      $result = civicrm_api('GroupContact', 'create', $params);

      //then blank the "Join the Network" field
      $params = array(
        'version' => 3,   
        'sequential' => 1,
        'entity_id' => $cid,
        'custom_46' => 0,
      );
      $result = civicrm_api('CustomValue', 'create', $params);

      //and finally, send a welcome message   
      joinnetworkcheckbox_send_welcome_message($cid);
    }
  }
}

function joinnetworkcheckbox_send_welcome_message($contact_id) {
  $params = array(
    'version' => 3,
    'sequential' => 1,
    'contact_id' => $contact_id,
    'is_primary' => 1,
  );
  $result = civicrm_api('Email', 'getsingle', $params);  
  $email = $result['email'];

  $component = new CRM_Mailing_BAO_Component();
  $component->is_default = 1;
  $component->is_active = 1;
  $component->component_type = 'Welcome';

  $component->find(TRUE);

  $emailDomain = CRM_Core_BAO_MailSettings::defaultDomain();

  $html = $component->body_html;

  if ($component->body_text) {
    $text = $component->body_text;
  }
  else {
    $text = CRM_Utils_String::htmlToText($component->body_html);
  }

  $bao            = new CRM_Mailing_BAO_Mailing();
  $bao->body_text = $text;
  $bao->body_html = $html;
  $tokens         = $bao->getTokens();

  $html = CRM_Utils_Token::replaceDomainTokens($html, $domain, TRUE, $tokens['html']);

  $text = CRM_Utils_Token::replaceDomainTokens($text, $domain, FALSE, $tokens['text']);

  $mailParams = array(
    'groupName' => 'Mailing Event ' . $component->component_type, 'subject' => $component->subject,
    'from' => "\"California Pan-Ethnic Health Network\" <info@$emailDomain>",
    'toEmail' => $email,
    'replyTo' => "do-not-reply@$emailDomain",
    'returnPath' => "do-not-reply@$emailDomain",
    'html' => $html,
    'text' => $text,
  );
  // send - ignore errors because the desired status change has already been successful
  $unused_result = CRM_Utils_Mail::send($mailParams);
}

