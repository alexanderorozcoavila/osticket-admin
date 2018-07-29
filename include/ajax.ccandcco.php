<?php
/*********************************************************************
    ajax.thread.php

    AJAX interface for thread

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2015 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

if(!defined('INCLUDE_DIR')) die('403');

include_once(INCLUDE_DIR.'class.ticket.php');
include_once(INCLUDE_DIR.'class.user.php');
include_once(INCLUDE_DIR.'class.thread.php');
include_once(INCLUDE_DIR.'class.collaborator.php');
require_once(INCLUDE_DIR.'class.ajax.php');
require_once(INCLUDE_DIR.'class.note.php');
include_once INCLUDE_DIR . 'class.thread_actions.php';

class CcAndCcoAjaxAPI extends AjaxController {

    function addCc($tid, $uid=0) {
        //print var_dump($_POST);
        $collab = Collaborator::create(array(
            'isactive' => '1',
            'thread_id' => $_POST['threadId'],
            'user_id' => $_POST['userId'],
            'role' => $_POST['role'],
        ));
        if ($collab->save(true))
            return $collab;
    }

    function addCco($tid, $uid=0) {
        $collab = Collaborator::create(array(
            'isactive' => '1',
            'thread_id' => $_POST['threadId'],
            'user_id' => $_POST['userId'],
            'role' => $_POST['role'],
        ));
        if ($collab->save(true))
            return $collab;
    }

    function addUser($tid, $uid=0) {
        $user = new User(array(
            'name' => Format::htmldecode(Format::sanitize($_POST['name'], false)),
            'created' => new SqlFunction('NOW'),
            'updated' => new SqlFunction('NOW'),
            //XXX: Do plain create once the cause
            // of the detached emails is fixed.
            'default_email' => UserEmail::ensure($_POST['email'])
        ));
        print var_dump($user);
    }

}