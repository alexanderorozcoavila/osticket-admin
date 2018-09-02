<?php
global $cfg;

if (!$info['title'])
    $info['title'] = "Reenviar Ticket ".$idTicketAssign;

?>
<h3 class="drag-handle"><?php echo $info['title']; ?></h3>
<b><a class="close" href="#"><i class="icon-remove-circle"></i></a></b>
<div class="clear"></div>
<hr/>
<div id="ticket-status" style="display:block; margin:5px;">
    <form method="post" name="status" id="status" action="<?php echo $action; ?>">
        
    </form>
</div>
<div class="clear"></div>
