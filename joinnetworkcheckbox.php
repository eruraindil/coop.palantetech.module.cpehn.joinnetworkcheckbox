<?php

require_once 'joinnetworkcheckbox.civix.php';

/**
 * Implements hook_civicrm_post()
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_post
 */
function joinnetworkcheckbox_civicrm_post($op, $objectName, $objectId, &$objectRef) {
  // this will fire on any contact added to any group. Use $objectRef->group_id to narrow it down.
  if ($op == 'create' && $objectName == 'GroupContact') {
    $cid = $objectRef->contact_id;
    // send a welcome message
    joinnetworkcheckbox_send_welcome_message($cid);
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
    'groupName' => 'Mailing Event ' . $component->component_type,
    'subject' => $component->subject,
    // This should probably pull the default org name or something.
    'from' => "\"YOUR NAME HERE\" <info@$emailDomain>",
    'toEmail' => $email,
    'replyTo' => "do-not-reply@$emailDomain",
    'returnPath' => "do-not-reply@$emailDomain",
    'html' => $html,
    'text' => $text,
  );
  // send - ignore errors because the desired status change has already been successful
  $unused_result = CRM_Utils_Mail::send($mailParams);
}
