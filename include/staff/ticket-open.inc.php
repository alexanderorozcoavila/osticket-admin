<?php
if (!defined('OSTSCPINC') || !$thisstaff
        || !$thisstaff->hasPerm(TicketModel::PERM_CREATE, false))
        die('Access Denied');

$info=array();
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);

if (!$info['topicId'])
    $info['topicId'] = $cfg->getDefaultTopicId();

$forms = array();
if ($info['topicId'] && ($topic=Topic::lookup($info['topicId']))) {
    foreach ($topic->getForms() as $F) {
        if (!$F->hasAnyVisibleFields())
            continue;
        if ($_POST) {
            $F = $F->instanciate();
            $F->isValidForClient();
        }
        $forms[] = $F;
    }
}

if ($_POST)
    $info['duedate'] = Format::date(strtotime($info['duedate']), false, false, 'UTC');
?>
<form action="tickets.php?a=open" method="post" class="save"  enctype="multipart/form-data">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="create">
 <input type="hidden" name="a" value="open">
<div style="margin-bottom:20px; padding-top:5px;">
    <div class="pull-left flush-left">
        <h2><?php echo __('Open a New Ticket');?></h2>
    </div>
</div>
 <table class="form_table fixed" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
    <!-- This looks empty - but beware, with fixed table layout, the user
         agent will usually only consult the cells in the first row to
         construct the column widths of the entire toable. Therefore, the
         first row needs to have two cells -->
        <tr><td style="padding:0;"></td><td style="padding:0;"></td></tr>
    </thead>
    <tbody>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('User Information'); ?></strong>: </em>
                <div class="error"><?php echo $errors['user']; ?></div>
            </th>
        </tr>
        <?php
        if ($user) { ?>
        <tr><td><?php echo __('User'); ?>:</td><td>
            <div id="user-info">
                <input type="hidden" name="uid" id="uid" value="<?php echo $user->getId(); ?>" />
            <a href="#" onclick="javascript:
                $.userLookup('ajax.php/users/<?php echo $user->getId(); ?>/edit',
                        function (user) {
                            $('#user-name').text(user.name);
                            $('#user-email').text(user.email);
                        });
                return false;
                "><i class="icon-user"></i>
                <span id="user-name"><?php echo Format::htmlchars($user->getName()); ?></span>
                &lt;<span id="user-email"><?php echo $user->getEmail(); ?></span>&gt;
                </a>
                <a class="inline button" style="overflow:inherit" href="#"
                    onclick="javascript:
                        $.userLookup('ajax.php/users/select/'+$('input#uid').val(),
                            function(user) {
                                $('input#uid').val(user.id);
                                $('#user-name').text(user.name);
                                $('#user-email').text('<'+user.email+'>');
                        });
                        return false;
                    "><i class="icon-retweet"></i> <?php echo __('Change'); ?></a>
            </div>
        </td></tr>
        <?php
        } else { //Fallback: Just ask for email and name
            ?>
        <tr>
            <td width="160" class="required"> <?php echo __('Email Address'); ?>: </td>
            <td>
                <div class="attached input">
                    <div style="width: 350px;border: 1px solid #aaa;">
                        <input type="text" size=45 name="email" id="user-email2" class="attached" style="width: 333px;border: 1px solid #aaa;"
                        autocomplete="off" autocorrect="off" value="<?php echo $info['email']; ?>" />
                    </div>
                        <script>
                    var REGEX_EMAIL = '([a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*@' +
                  '(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)';

                    $('#user-email2').selectize({
                        persist: true,
                        maxItems: 1,
                        plugins: ['remove_button'],
                        valueField: 'email',
                        labelField: 'email',
                        searchField: ['name', 'email','phone'],
                        options: [],
                        load: function(query, callback) {
                            if (!query.length) return callback();
                            $.ajax({
                                url: 'ajax.php/users/local?q=' + encodeURIComponent(query),
                                type: 'GET',
                                // async:false,
                                dataType: 'json',
                                error: function() {
                                    callback();
                                },
                                success: function(res) {
                                    console.log(res);
                                    callback(res);
                                }
                            });
                        },
                        render: {
                            item: function(item, escape) {
                                console.log(item.name + '1');
                                if(item.name == "undefined"){

                                }else{
                                    $('#user-name').val(item.name);
                                }
                                return '<div>' +
                                    (item.email ? '<span class="email">' + escape(item.email) + '</span>' : '') +
                                '</div>';

                            },
                            option_create: function(item, escape) {
                                return '<div class="create">Agregar <strong>' + escape(item.input) + '</strong>&hellip;</div>';
                            }
                        },
                        createFilter: function(input) {
                            var match, regex;

                            // email@address.com
                            regex = new RegExp('^' + REGEX_EMAIL + '$', 'i');
                            match = input.match(regex);
                            if (match) return !this.options.hasOwnProperty(match[0]);

                            // name <email@address.com>
                            regex = new RegExp('^([^<]*)\<' + REGEX_EMAIL + '\>$', 'i');
                            match = input.match(regex);
                            if (match) return !this.options.hasOwnProperty(match[2]);

                            return false;
                        },
                        create: function(input) {
                            
                            if ((new RegExp('^' + REGEX_EMAIL + '$', 'i')).test(input)) {
                                idusernew = 0;
                                $.ajax({
                                    url: 'ajax.php/ccandcco/1/adduser',
                                    type: 'POST',
                                    async:false,
                                    data: { name: input, email:input },
                                    // dataType: 'json',
                                    error: function() {
                                        console.log('error');
                                    },
                                    success: function(res) {
                                        idusernew = res;
                                        
                                    }
                                }); 
                                name = input.split('@');
                                $('#user-name').val(name[0]);
                                return {email: input, id:idusernew}; 
                                
                            }
                            var match = input.match(new RegExp('^([^<]*)\<' + REGEX_EMAIL + '\>$', 'i'));
                            if (match) {
                                return {
                                    email : match[2],
                                    name  : $.trim(match[1])
                                };
                            }
                            alert('Invalid email address.');
                            return false;
                        }
                    });
                    
                    $('selectize-input').css('border','1px solid #d0d0d0;');
                    </script>

                </div>
                <span class="error">*</span>
                <div class="error"><?php echo $errors['email']; ?></div>
            </td>
        </tr>
        <tr>
            <td width="160" class="required"> <?php echo __('Full Name'); ?>: </td>
            <td>
                <span style="display:inline-block;">
                    <input type="text" size=45 name="name" id="user-name" value="<?php echo $info['name']; ?>" /> </span>
                <span class="error">*</span>
                <div class="error"><?php echo $errors['name']; ?></div>
            </td>
        </tr>
        <?php
            $role = $thisstaff->getRole($thisstaff->dept);
            if ($role->hasPerm(Ticket::PERM_CCANDCCO)) {//Make CC optional feature? NO, for now.
                ?>
        <tr>
                <td width="120">
                    <label><strong><?php echo __('CC'); ?>:</strong></label>
                </td>
                <td>    
                    <div style="
                    float:left;
                    border: 1px solid #d0d0d0;
                    padding: 10px 10px;
                    width: 100%;
                    background: #fff;
                    -webkit-box-sizing: border-box;
                    -moz-box-sizing: border-box;
                    box-sizing: border-box;
                    -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.1);
                    box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.1);
                    -webkit-border-radius: 3px;
                    -moz-border-radius: 3px;
                    border-radius: 3px;">
                    <select id="cc-colaboradores" class="contacts" placeholder="Agregar"  multiple style="width: 90%;">
                        
                    </select>
                    <span style="
                    float: right;
                    top: 0px;
                    right: 0px;
                    margin-right: 10px;
                    margin-top: -27px;cursor:pointer;" id="span-cco">CCO</span>
                    </div>
                    <script>
                    var REGEX_EMAIL = '([a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*@' +
                  '(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)';

                    $('#cc-colaboradores').selectize({
                        persist: true,
                        maxItems: null,
                        plugins: ['remove_button'],
                        valueField: 'id',
                        labelField: 'email',
                        searchField: ['name', 'email','phone'],
                        options: [],
                        load: function(query, callback) {
                            if (!query.length) return callback();
                            $.ajax({
                                url: 'ajax.php/users/local?q=' + encodeURIComponent(query),
                                type: 'GET',
                                // async:false,
                                dataType: 'json',
                                error: function() {
                                    callback();
                                },
                                success: function(res) {
                                    console.log(res);
                                    callback(res);
                                }
                            });
                        },
                        render: {
                            item: function(item, escape) {
                                //console.log(item.name + '1');
                                return '<div>' +
                                    (item.email ? '<span class="email">' + escape(item.email) + '</span>' : '') +
                                '</div>';

                            },
                            option_create: function(item, escape) {
                                return '<div class="create">Agregar <strong>' + escape(item.input) + '</strong>&hellip;</div>';
                            }
                        },
                        createFilter: function(input) {
                            var match, regex;

                            // email@address.com
                            regex = new RegExp('^' + REGEX_EMAIL + '$', 'i');
                            match = input.match(regex);
                            if (match) return !this.options.hasOwnProperty(match[0]);

                            // name <email@address.com>
                            regex = new RegExp('^([^<]*)\<' + REGEX_EMAIL + '\>$', 'i');
                            match = input.match(regex);
                            if (match) return !this.options.hasOwnProperty(match[2]);

                            return false;
                        },
                        
                        create: function(input) {
                            
                            if ((new RegExp('^' + REGEX_EMAIL + '$', 'i')).test(input)) {
                                idusernew = 0;
                                $.ajax({
                                    url: 'ajax.php/ccandcco/1/adduser',
                                    type: 'POST',
                                    async:false,
                                    data: { name: input, email:input },
                                    // dataType: 'json',
                                    error: function() {
                                        console.log('error');
                                    },
                                    success: function(res) {
                                        idusernew = res;
                                        
                                    }
                                }); 
                                return {email: input, id:idusernew}; 
                                
                            }
                            var match = input.match(new RegExp('^([^<]*)\<' + REGEX_EMAIL + '\>$', 'i'));
                            if (match) {
                                return {
                                    email : match[2],
                                    name  : $.trim(match[1])
                                };
                            }
                            alert('Invalid email address.');
                            return false;
                        }
                    });
                    </script>
                </td>
             </tr>
                
             <tr id="tr-cco" style="display:none;">
                <td width="120">
                    <label><strong><?php echo __('CCO'); ?>:</strong></label>
                </td>
                <td>
                <div style="
                    float:left;
                    border: 1px solid #d0d0d0;
                    padding: 10px 10px;
                    width: 100%;
                    background: #fff;
                    -webkit-box-sizing: border-box;
                    -moz-box-sizing: border-box;
                    box-sizing: border-box;
                    -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.1);
                    box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.1);
                    -webkit-border-radius: 3px;
                    -moz-border-radius: 3px;
                    border-radius: 3px;">
                <select id="cco-colaboradores" class="contacts" placeholder="Agregar" multiple style="width: 90%;">
                
                </select>
                </div>
                    <script>
                    var REGEX_EMAIL = '([a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*@' +
                  '(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)';

                    $('#cco-colaboradores').selectize({
                        persist: true,
                        maxItems: null,
                        valueField: 'id',
                        labelField: 'email',
                        plugins: ['remove_button'],
                        searchField: ['name', 'email','phone'],
                        options: [],
                        load: function(query, callback) {
                            if (!query.length) return callback();
                            $.ajax({
                                url: 'ajax.php/users/local?q=' + encodeURIComponent(query),
                                type: 'GET',
                                // async:false,
                                dataType: 'json',
                                error: function() {
                                    callback();
                                },
                                success: function(res) {
                                    console.log(res);
                                    callback(res);
                                }
                            });
                        },
                        render: {
                            item: function(item, escape) {
                                //console.log(item.name + '1');
                                return '<div>' +
                                    (item.email ? '<span class="email">' + escape(item.email) + '</span>' : '') +
                                '</div>';
                            },
                            option_create: function(item, escape) {
                                return '<div class="create">Agregar <strong>' + escape(item.input) + '</strong>&hellip;</div>';
                            }
                        },
                        createFilter: function(input) {
                            var match, regex;

                            // email@address.com
                            regex = new RegExp('^' + REGEX_EMAIL + '$', 'i');
                            match = input.match(regex);
                            if (match) return !this.options.hasOwnProperty(match[0]);

                            // name <email@address.com>
                            regex = new RegExp('^([^<]*)\<' + REGEX_EMAIL + '\>$', 'i');
                            match = input.match(regex);
                            if (match) return !this.options.hasOwnProperty(match[2]);

                            return false;
                        },
                        create: function(input) {
                            
                            if ((new RegExp('^' + REGEX_EMAIL + '$', 'i')).test(input)) {
                                idusernew = 0;
                                $.ajax({
                                    url: 'ajax.php/ccandcco/1/adduser',
                                    type: 'POST',
                                    async:false,
                                    data: { name: input, email:input },
                                    // dataType: 'json',
                                    error: function() {
                                        console.log('error');
                                    },
                                    success: function(res) {
                                        idusernew = res;
                                        
                                    }
                                }); 
                                return {email: input, id:idusernew}; 
                                
                            }
                            var match = input.match(new RegExp('^([^<]*)\<' + REGEX_EMAIL + '\>$', 'i'));
                            if (match) {
                                return {
                                    email : match[2],
                                    name  : $.trim(match[1])
                                };
                            }
                            alert('Invalid email address.');
                            return false;
                        }
                    });
                    </script>
                </td>
             </tr>

        <?php
            }
        } ?>

        <?php
        if($cfg->notifyONNewStaffTicket()) {  ?>
        <tr>
            <td width="160"><?php echo __('Ticket Notice'); ?>:</td>
            <td>
            <input type="checkbox" name="alertuser" <?php echo (!$errors || $info['alertuser'])? 'checked="checked"': ''; ?>><?php
                echo __('Send alert to user.'); ?>
            </td>
        </tr>
        <?php
        } ?>
    </tbody>
    <tbody>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('Ticket Information and Options');?></strong>:</em>
            </th>
        </tr>
        <tr>
            <td width="160" class="required">
                <?php echo __('Ticket Source');?>:
            </td>
            <td>
                <select name="source">
                    <?php
                    $source = $info['source'] ?: 'Phone';
                    $sources = Ticket::getSources();
                    unset($sources['Web'], $sources['API']);
                    foreach ($sources as $k => $v)
                        echo sprintf('<option value="%s" %s>%s</option>',
                                $k,
                                ($source == $k ) ? 'selected="selected"' : '',
                                $v);
                    ?>
                </select>
                &nbsp;<font class="error"><b>*</b>&nbsp;<?php echo $errors['source']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="160">
                <?php echo __('Department'); ?>:
            </td>
            <td>
                <select name="deptId">
                    <option value="" selected >&mdash; <?php echo __('Select Department'); ?>&mdash;</option>
                    <?php
                    if($depts=Dept::getDepartments(array('dept_id' => $thisstaff->getDepts()))) {
                        foreach($depts as $id =>$name) {
                            if (!($role = $thisstaff->getRole($id))
                                || !$role->hasPerm(Ticket::PERM_CREATE)
                            ) {
                                // No access to create tickets in this dept
                                continue;
                            }
                            echo sprintf('<option value="%d" %s>%s</option>',
                                    $id, ($thisstaff->dept==$name)?'selected="selected"':'',$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<font class="error"><?php echo $errors['deptId']; ?></font>
            </td>
        </tr>
        <?php
        if($thisstaff->hasPerm(TicketModel::PERM_ASSIGN, false)) { ?>
        <tr>
            <td width="160"><?php echo __('Assign To');?>:</td>
            <td>
                <select id="assignId" name="assignId">
                    <option value="0" selected="selected">&mdash; <?php echo __('Select an Agent OR a Team');?> &mdash;</option>
                    <?php
                    if(($users=Staff::getAvailableStaffMembers())) {
                        echo '<OPTGROUP label="'.sprintf(__('Agents (%d)'), count($users)).'">';
                        foreach($users as $id => $name) {
                            $k="s$id";
                            echo sprintf('<option value="%s" %s>%s</option>',
                                        $k,(($info['assignId']==$k)?'selected="selected"':''),$name);
                        }
                        echo '</OPTGROUP>';
                    }

                    if(($teams=Team::getActiveTeams())) {
                        echo '<OPTGROUP label="'.sprintf(__('Teams (%d)'), count($teams)).'">';
                        foreach($teams as $id => $name) {
                            $k="t$id";
                            echo sprintf('<option value="%s" %s>%s</option>',
                                        $k,(($info['assignId']==$k)?'selected="selected"':''),$name);
                        }
                        echo '</OPTGROUP>';
                    }
                    ?>
                </select>&nbsp;<span class='error'>&nbsp;<?php echo $errors['assignId']; ?></span>
            </td>
        </tr>
        <?php } ?>
        </tbody>
        <tbody id="dynamic-form">
        <?php
            foreach ($forms as $form) {
                print $form->getForm()->getMedia();
                include(STAFFINC_DIR .  'templates/dynamic-form.tmpl.php');
            }
        ?>
        </tbody>
        <tbody>
        <?php
        //is the user allowed to post replies??
        if ($thisstaff->getRole()->hasPerm(TicketModel::PERM_REPLY)) { ?>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('Response');?></strong>: <?php echo __('Optional response to the above issue.');?></em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
            <?php
            if(($cannedResponses=Canned::getCannedResponses())) {
                ?>
                <div style="margin-top:0.3em;margin-bottom:0.5em">
                    <?php echo __('Canned Response');?>:&nbsp;
                    <select id="cannedResp" name="cannedResp">
                        <option value="0" selected="selected">&mdash; <?php echo __('Select a canned response');?> &mdash;</option>
                        <?php
                        foreach($cannedResponses as $id =>$title) {
                            echo sprintf('<option value="%d">%s</option>',$id,$title);
                        }
                        ?>
                    </select>
                    &nbsp;&nbsp;
                    <label class="checkbox inline"><input type='checkbox' value='1' name="append" id="append" checked="checked"><?php echo __('Append');?></label>
                </div>
            <?php
            }
                $signature = '';
                if ($thisstaff->getDefaultSignatureType() == 'mine')
                    $signature = $thisstaff->getSignature(); ?>
                <textarea
                    class="<?php if ($cfg->isRichTextEnabled()) echo 'richtext';
                        ?> draft draft-delete" data-signature="<?php
                        echo Format::htmlchars(Format::viewableImages($signature)); ?>"
                    data-signature-field="signature" data-dept-field="deptId"
                    placeholder="<?php echo __('Initial response for the ticket'); ?>"
                    name="response" id="response" cols="21" rows="8"
                    style="width:80%;" <?php
    list($draft, $attrs) = Draft::getDraftAndDataAttrs('ticket.staff.response', false, $info['response']);
    echo $attrs; ?>><?php echo $attrs;
                ?></textarea>
                    <div class="attachments">
<?php
print $response_form->getField('attachments')->render();
?>
                    </div>

                <table border="0" cellspacing="0" cellpadding="2" width="100%">
            <tr>
                <td width="100"><?php echo __('Ticket Status');?>:</td>
                <td>
                    <select name="statusId">
                    <?php
                    $statusId = $info['statusId'] ?: $cfg->getDefaultTicketStatusId();
                    $states = array('open');
                    if ($thisstaff->hasPerm(TicketModel::PERM_CLOSE, false))
                        $states = array_merge($states, array('closed'));
                    foreach (TicketStatusList::getStatuses(
                                array('states' => $states)) as $s) {
                        if (!$s->isEnabled()) continue;
                        $selected = ($statusId == $s->getId());
                        echo sprintf('<option value="%d" %s>%s</option>',
                                $s->getId(),
                                $selected
                                 ? 'selected="selected"' : '',
                                __($s->getName()));
                    }
                    ?>
                    </select>
                </td>
            </tr>
             <tr>
                <td width="100"><?php echo __('Signature');?>:</td>
                <td>
                    <?php
                    $info['signature']=$info['signature']?$info['signature']:$thisstaff->getDefaultSignatureType();
                    ?>
                    <label><input type="radio" name="signature" value="none" checked="checked"> <?php echo __('None');?></label>
                    <?php
                    if($thisstaff->getSignature()) { ?>
                        <label><input type="radio" name="signature" value="mine"
                            <?php echo ($info['signature']=='mine')?'checked="checked"':''; ?>> <?php echo __('My Signature');?></label>
                    <?php
                    } ?>
                    <label><input type="radio" name="signature" value="dept"
                        <?php echo ($info['signature']=='dept')?'checked="checked"':''; ?>> <?php echo sprintf(__('Department Signature (%s)'), __('if set')); ?></label>
                </td>
             </tr>
            </table>
            </td>
        </tr>
        <?php
        } //end canPostReply
        ?>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('Internal Note');?></strong>
                <font class="error">&nbsp;<?php echo $errors['note']; ?></font></em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <textarea
                    class="<?php if ($cfg->isRichTextEnabled()) echo 'richtext';
                        ?> draft draft-delete"
                    placeholder="<?php echo __('Optional internal note (recommended on assignment)'); ?>"
                    name="note" cols="21" rows="6" style="width:80%;" <?php
    list($draft, $attrs) = Draft::getDraftAndDataAttrs('ticket.staff.note', false, $info['note']);
    echo $attrs; ?>><?php echo $_POST ? $info['note'] : $draft;
                ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
<p style="text-align:center;">
    <input type="submit" name="submit" value="<?php echo _P('action-button', 'Open');?>">
    <input type="reset"  name="reset"  value="<?php echo __('Reset');?>">
    <input type="button" name="cancel" value="<?php echo __('Cancel');?>" onclick="javascript:
        $('.richtext').each(function() {
            var redactor = $(this).data('redactor');
            if (redactor && redactor.opts.draftDelete)
                redactor.deleteDraft();
        });
        window.location.href='tickets.php';
    ">
</p>
</form>
<script type="text/javascript">
$('#span-cco').click(function() {
            if ($("#tr-cco").css('display') == 'none') {
                $("#tr-cco").css('display','table-row');
            }else{
                $("#tr-cco").css('display','none');
            }
            console.log('open tr');
        });
$(function() {
    $('#new-ticket').click(function(){
        var opcion = confirm("¿Seguro que desea salir? \nCualquier cambio o información que hayas introducido \nserán descartados");
        return opcion;
    });
    $('input#user-email').typeahead({
        source: function (typeahead, query) {
            $.ajax({
                url: "ajax.php/users?q="+query,
                dataType: 'json',
                success: function (data) {
                    typeahead.process(data);
                }
            });
        },
        onselect: function (obj) {
            $('#uid').val(obj.id);
            $('#user-name').val(obj.name);
            $('#user-email').val(obj.email);
        },
        property: "/bin/true"
    });
});
</script>

