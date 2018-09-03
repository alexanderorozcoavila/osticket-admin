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
            return true;
    }

    function addCco($tid, $uid=0) {
        $collab = Collaborator::create(array(
            'isactive' => '1',
            'thread_id' => $_POST['threadId'],
            'user_id' => $_POST['userId'],
            'role' => $_POST['role'],
        ));
        if ($collab->save(true))
            return true;
    }

    function ticketReenviar($tid) {
        $idTicketAssign = $tid;
        $ticketresult = Ticket::lookup($idTicketAssign);
        
        include(STAFFINC_DIR . 'templates/ticket-reenviar.tmpl.php');
    }

    function guardarReenviar($tid) {
        if(isset($_POST['threadId']) and !empty($_POST['threadId'])){
            print $_POST['threadId']."<br>";
        }
        if(isset($_POST['role']) and !empty($_POST['role'])){
            print $_POST['role']."<br>";
        }
        if(isset($_POST['para']) and !empty($_POST['para'])){
            print $_POST['para']."<br>";
        }
        if(isset($_POST['cc']) and !empty($_POST['cc'])){
            foreach($_POST['cc'] as $cc){
                print "*cc: ".$cc."<br>";
            }
        }
        if(isset($_POST['cco']) and !empty($_POST['cco'])){
            foreach($_POST['cco'] as $cco){
                print "*cco: ".$cco."<br>";
            }
        }
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

        list($mailbox, $domain) = explode('@', $vars['email'], 2);
        try {
            $user->save(true);
            $user->emails->add($user->default_email);
            // Attach initial custom fields
        }
        catch (OrmException $e) {
            return null;
        }
        return $user->getId();
    }

    function delete(){
        $collab = Collaborator::objects()
            ->filter(array('thread_id'=>$_POST['threadId'],'user_id'=>$_POST['userId']));
        $collab->delete();
        
        print var_dump($collab);
    }



}