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

    private function addCcInternal($threadId,$userId) {
        //print var_dump($_POST);
        $collab = Collaborator::create(array(
            'isactive' => '1',
            'thread_id' => $threadId,
            'user_id' => $userId,
            'role' => 'M',
        ));
        if ($collab->save(true)){
            return true;
        }else{
            return true;
        }
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

    private function addCcoInternal($threadId,$userId) {
        $collab = Collaborator::create(array(
            'isactive' => '1',
            'thread_id' => $threadId,
            'user_id' => $userId,
            'role' => 'O',
        ));
        if ($collab->save(true)){
            return true;
        }else{
            return true;
        }
    }

    function ticketReenviar($tid) {
        $idTicketAssign = $tid;
        $ticketresult = Ticket::lookup($idTicketAssign);
        
        include(STAFFINC_DIR . 'templates/ticket-reenviar.tmpl.php');
    }

    function guardarReenviar($tid) {
        // exit;
        if(isset($_POST['threadId']) and !empty($_POST['threadId'])){
            $threadId = $_POST['threadId'];
        }
        if(isset($_POST['para']) and !empty($_POST['para'])){
            $para = $_POST['para'];
            $sql="UPDATE os_ticket SET user_id = '".$para."' WHERE ticket_id = ".$tid;
            if(true){
                foreach($_POST['cc'] as $cc){
                    print $cc."<br>";
                }
                foreach($_POST['cco'] as $cco){
                    print $cco."<br>";
                }
                if($r){
                    print "paso 1";
                    exit;
                }else{
                    print "paso 2";
                    exit;
                }
            }else{
                print "error paso 1";
                exit;
            }
        }else{
            print "error paso 1";
            exit;
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