$(document).ready(function() {		
    const modalChanges = function() {
        $("#aclModal .acl-list-item").removeClass(['abook-self', 'grouphide']).css('border', '0');
        $("#aclModal .acl-button-hide").css('display', 'none');
        $("#aclModal .acl-button-show").html('Add / Remove');
        $("#aclModal .acl-button-show").on('click', function(){
            let currentRecipientName = $(this).parent().find(".contactname").text();
            let currentRecipient = ($(this).hasClass("btn-outline-success")) ? currentRecipientName : null;
            if (currentRecipient != null) {
                $("#aclModal .acl-button-show").each(function(index, elem){
                    let possibleRecipient = $(this).parent().find(".contactname").text();
                    if (($(this).hasClass("btn-success") || currentRecipient == possibleRecipient) && ($("#jot-recipients").val()).indexOf(possibleRecipient) == -1) {
                        $("#jot-recipients").val($("#jot-recipients").val() + possibleRecipient + '; ');
                    }
                });
            }
            else {
                $("#jot-recipients").val(($("#jot-recipients").val()).replace(currentRecipientName + '; ', ''));
            }
        });
        if ($("#jot-recipients").val() != '') {
            $("#aclModal .acl-button-show").each(function(index, elem){
                let possibleRecipient = $(this).parent().find(".contactname").text();
                if (($("#jot-recipients").val()).indexOf(possibleRecipient) != -1) {
                    $(this).trigger('click');
                }					
            });
        }
    };

    $("#profile-jot-wrapper").prepend('<div id="jot-recipients-wrap" class="jothidden"><input class="w-100 border-0 rounded-top" name="recipients" id="jot-recipients" type="text" placeholder="Recipient(s)" tabindex="1" value=""></div>');
    $('#aclModal .modal-title').html('<i id="dialog-perms-icon" class="fas fa-users"></i>&nbsp; Add / Remove Recipients');
    $('#dbtn-acl, #aclModal #acl-dialog-description, #aclModal #acl-select, #aclModal label[for="acl-select"], #aclModal small.text-muted').css('display', 'none');	
    $('#jot-recipients-wrap').css('border-bottom', '1px solid var(--bs-border-color)');
    $('#jot-recipients-wrap input').css({
        'padding': '0.5rem',
        'outline': 'none'			
    });   
    
    $("#jot-recipients").on('focus', function(){
        $('#aclModal').modal('show');	
    });

    $('#aclModal').on('shown.bs.modal', function(e) {
        acl.on_custom(e);
        //$("#aclModal #acl-search").trigger('focus').trigger('blur');
        $("#aclModal .modal-header .btn-close").trigger('focus').trigger('blur');
        $("#aclModal #acl-search").css('display', 'none');
        $("#aclModal #acl-list-content").css('padding-top', '7px');
        modalChanges();
        const observer = new MutationObserver(modalChanges);
        observer.observe($("#aclModal #acl-list-content")[0], {
            characterData: true, 
            childList: true
        });
    });
    
    $('#aclModal').on('hidden.bs.modal', function() {
        //$("#aclModal #acl-search").val('');
        //acl.search();
    });

    $('#aclModal').parent().on('submit', function(e){
        if (e.keyCode == 13)
        {
            //e.preventDefault();
        }        
    });
});