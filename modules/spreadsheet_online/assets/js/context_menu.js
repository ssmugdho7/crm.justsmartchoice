(function(){
	"use strict";
	initMenuFunction();
})(jQuery);

var staff_share_value = '';
var client_share_value = '';

function clickInsideElement( e, className ) {
	"use strict";
	var el = e.srcElement || e.target;
	if ( el.classList.contains(className) ) {
		return el;
	} else {
		while ( el = el.parentNode ) {
			if ( el.classList && el.classList.contains(className) ) {
				return el;
			}
		}
	}
	return false;
}

function getPosition(e) {
	"use strict";
	var posx = 0, posy = 0;
	if (!e) var e = window.event;
	if (e.pageX || e.pageY) {
		posx = e.pageX;
		posy = e.pageY;
	} else if (e.clientX || e.clientY) {
		posx = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
		posy = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
	}
	return {
		x: posx,
		y: posy
	}
}

      // Your Menu Class Name
      var taskItemClassName = "right-menu-position";
      var contextMenuClassName = "context-menu",
      contextMenuItemClassName = "context-menu__item",
      contextMenuLinkClassName = "context-menu__link", 
      contextMenuActive = "context-menu--active";
      var taskItemInContext, 
      clickCoords, 
      clickCoordsX, 
      clickCoordsY, 
      menu = document.querySelector("#context-menu"), 
      menuItems2 = menu.querySelectorAll(".context-menu__item");
      var menuState = 0, 
      menuWidth, 
      menuHeight, 
      menuPosition, 
      menuPositionX, 
      menuPositionY, 
      windowWidth, 
      k = 0, 
      windowHeight;

      function initMenuFunction() {
      	"use strict";
      	contextListener();
      	clickListener();
      	keyupListener();
      	resizeListener();
      }

      /**
       * Listens for contextmenu events.
       */
       function contextListener() {
       	"use strict";
       	document.addEventListener( "contextmenu", function(e) {
          open_context_menu(e);
        });

       }



       function open_context_menu(e){
        taskItemInContext = clickInsideElement( e, taskItemClassName );
        if ( taskItemInContext ) {
          e.preventDefault();
          toggleMenuOff();
          toggleMenuOn();
          positionMenu(e);
        } else {
          taskItemInContext = null;
          toggleMenuOff();
        }
      }
      /**
       * Listens for click events.
       */
       function clickListener() {
       	"use strict";
       	document.addEventListener( "click", function(e) {
       		var clickeElIsLink = clickInsideElement( e, contextMenuLinkClassName );
          if ( clickeElIsLink ) {
            e.preventDefault();
            menuItemListener( clickeElIsLink );
          } else {
            var button = e.which || e.button;
            if ( button === 1 ) {
             toggleMenuOff();
           }
         }
       });
       }

      /**
       * Listens for keyup events.
       */
       function keyupListener() {
       	"use strict";
       	window.onkeyup = function(e) {
       		if ( e.keyCode === 27 ) {
       			toggleMenuOff();
       		}
       	}
       }

      /**
       * Window resize event listener
       */
       function resizeListener() {
       	"use strict";
       	window.onresize = function(e) {
       		toggleMenuOff();
       	};
       }

      /**
       * Turns the custom context menu on.
       */
       function toggleMenuOn() {
       	"use strict";
       	if ( menuState !== 1 ) {
       		menuState = 1;
       		var parent_id = taskItemInContext.getAttribute('data-tt-parent-id');
       		parent_id = parent_id == undefined ? 0 : parent_id;
       		var id_set = taskItemInContext.getAttribute('data-tt-id');
       		var type = taskItemInContext.getAttribute('data-tt-type');
       		var name = taskItemInContext.getAttribute('data-tt-name');
       		var share = $('.context-menu').data('share');
       		var role = taskItemInContext.getAttribute('data-tt-role');

          $('#RelatedModal input[name="id"]').val(id_set);

       		if(parent_id){
       			$('.context-menu').attr('data-parent', parent_id);
       			$('.context-menu').attr('data-id', id_set);
       		}else{
       			$('.context-menu').attr('data-id', id_set);
       			$('.context-menu').attr('data-parent', 0);
       		}

       		if(type == "file"){
            $('.context-menu__link[data-action="create_file"]').parent().remove();
            $('.context-menu__link[data-action="create_folder"]').parent().remove();
            $('.context-menu__link[data-action="d_zip"]').parent().remove();

            if(share == true){
              if(role == 1){
                $('.context-menu__link[data-action="edit"]').parent().remove();
              }else{

                var length_edit = $('.context-menu__link[data-action="edit"]').length;
                length_edit == 0 
                ? 
                (

                  $('.context-menu__items').append('<li class="context-menu__item"><a href="#" class="context-menu__link" data-action="edit"><i class="fa fa-cog"></i> '+edit+' </a></li>'),
                  $('.context-menu__items').append('<li class="context-menu__item"><a href="#" class="context-menu__link" data-action="d_file"><i class="fa fa-download"></i> '+download_file+'</a></li>')
                  )

                : (
                  $('.context-menu__link[data-action="edit"]').parent().remove(), 
                  $('.context-menu__link[data-action="d_file"]').parent().remove(), 
                  $('.context-menu__items').append('<li class="context-menu__item"><a href="#" class="context-menu__link" data-action="edit"><i class="fa fa-cog"></i> '+edit+' </a></li>'),
                  $('.context-menu__items').append('<li class="context-menu__item"><a href="#" class="context-menu__link" data-action="d_file"><i class="fa fa-download"></i> '+download_file+'</a></li>')
                  
                  )


              }
            }
          }else{
            var length = $('.context-menu__link[data-action="create_file"]').length; 
            var length_download = $('.context-menu__link[data-action="d_file"]').length; 
            length_download == 0 ? "" : $('.context-menu__link[data-action="d_file"]').parent().remove();

            if(share == false){
              length == 0 ?
              ($('.context-menu__items').append('<li class="context-menu__item"><a href="#" class="context-menu__link" data-action="create_file"><i class="fa fa-plus"></i> '+create_file+'</a>'),
                $('.context-menu__items').append('<li class="context-menu__item"><a href="#" class="context-menu__link" data-action="create_folder"><i class="fa fa-plus"></i> '+create_folder+'</a>'))
              : 
              ($('.context-menu__link[data-action="create_file"]').remove(),
                $('.context-menu__link[data-action="create_folder"]').remove(),
                $('.context-menu__link[data-action="d_file"]').remove(),
                $('.context-menu__link[data-action="d_zip"]').remove(),
                $('.context-menu__items').append('<li class="context-menu__item"><a href="#" class="context-menu__link" data-action="create_file"><i class="fa fa-plus"></i> '+create_file+'</a>'),
                $('.context-menu__items').append('<li class="context-menu__item"><a href="#" class="context-menu__link" data-action="create_folder"><i class="fa fa-plus"></i> '+create_folder+'</a>'))
            }else{
              if(role == 1){
                $('.context-menu__link[data-action="edit"]').parent().remove();
              }else{
                var length_edit = $('.context-menu__link[data-action="edit"]').length;
                length_edit == 0 
                ? 
                (
                  $('.context-menu__items').append('<li class="context-menu__item"><a href="#" class="context-menu__link" data-action="edit"><i class="fa fa-cog"></i> '+edit+' </a></li>')
                  )
                : (
                  $('.context-menu__link[data-action="edit"]').parent().remove(), 
                  $('.context-menu__items').append('<li class="context-menu__item"><a href="#" class="context-menu__link" data-action="edit"><i class="fa fa-cog"></i> '+edit+' </a></li>'))
              }
            }
          }
          menu.classList.add( contextMenuActive );
        }
      }

      /**
       * Turns the custom context menu off.
       */
       function toggleMenuOff() {
       	"use strict";
       	if ( menuState !== 0 ) {
       		menuState = 0;
       		menu.classList.remove( contextMenuActive );
       	}
       }

       function positionMenu(e) {
       	"use strict";
       	clickCoords = getPosition(e);
       	clickCoordsX = clickCoords.x;
       	clickCoordsY = clickCoords.y;
       	menuWidth = menu.offsetWidth + 4;
       	menuHeight = menu.offsetHeight + 4;

       	windowWidth = window.innerWidth;
       	windowHeight = window.innerHeight;
       	if ( (windowWidth - clickCoordsX) < menuWidth ) {
       		menu.style.left = (windowWidth - menuWidth)-0 + "px";
       	} else {
       		menu.style.left = clickCoordsX-238 + "px";
       	}

       	if ( Math.abs(windowHeight - clickCoordsY) < menuHeight ) {
       		menu.style.top = (windowHeight - menuHeight)-0 + "px";
       	} else {
       		menu.style.top = clickCoordsY-165 + "px";
       	}
       }
        var rel_id_select = [];

       function menuItemListener( link ) {
       	"use strict";

        var parent_id = taskItemInContext.getAttribute('data-tt-parent-id');
        parent_id = parent_id == undefined ? 0 : parent_id;
        var id_set = taskItemInContext.getAttribute('data-tt-id');
        var type = taskItemInContext.getAttribute('data-tt-type');
        var name = taskItemInContext.getAttribute('data-tt-name');
        var share = $('.button-group__mono-colors').data('share');
        var role = taskItemInContext.getAttribute('data-tt-role');

        var moveToAlbumSelectedId = link.getAttribute("data-action");

        //start
        switch(moveToAlbumSelectedId) {
        	case 'edit':

        	if(type == 'file'){
        		$('#AddFolderModal').modal('hide');

        		if(share == false){
             requestGet(admin_url + 'spreadsheet_online/check_file_exits/'+id_set).done(function(response) {
              response = JSON.parse(response);
              if(response.success){
                window.location.replace(admin_url + 'spreadsheet_online/new_file_view/'+parent_id+'/'+id_set);
              }else{
                alert_float('warning', response.message);
              }

            })
           }else{

             requestGet(admin_url + 'spreadsheet_online/check_file_exits/'+id_set).done(function(response) {
              response = JSON.parse(response);
              if(response.success){
                // requestGet(admin_url + 'spreadsheet_online/get_hash_staff/' + id_set).done(function(response1) {
                //   response1 = JSON.parse(response1);
                  window.location.replace(admin_url + 'spreadsheet_online/file_view_share/'+parent_id+'/'+id_set);
                // })
              }else{
                alert_float('warning', response.message);
              }
            })
             
           }
         }else{
          $('#AddFolderModal .add-new').addClass('hide');
          $('#AddFolderModal .update-new').removeClass('hide');
          $('#AddFolderModal input[name="name"]').val(name);
          $('#AddFolderModal input[name="id"]').val(id_set);
          if(share == true){
           var length_submot_share = $('#AddFolderModal .modal-footer [type="submit"]').length;
           length_submot_share == 0 ? $('#AddFolderModal .modal-footer').append(`<button type="submit" class="btn btn-info">Submit</button>`)
           : ($('#AddFolderModal .modal-footer [type="submit"]').remove(), $('#AddFolderModal .modal-footer').append(`<button type="submit" class="btn btn-info">Submit</button>`));
         }
         $('#AddFolderModal').modal('show');
       }
       break;
       case 'delete':
       $.post(admin_url+'spreadsheet_online/delete_folder_file/'+id_set).done(function(response){
         response = JSON.parse(response);
         if(response.success == true) {
          alert_float('success', response.message);
          window.location.replace(admin_url + 'spreadsheet_online/manage');
        }
        else{
          alert_float('warning', response.message);
          window.location.replace(admin_url + 'spreadsheet_online/manage');
        }
      });
       break;
       case 'share':
       $('.remove_box_information_review').click();
       $('.remove_box_information_review_client').click();

       $('#ShareModal input[name="id"]').val(id_set);

       if(id_set != ''){
         
}else{
	$('.remove_box_information_review').click();
	$('.remove_box_information_review_client').click();
}

init_sharing_table();
$('#sharing-list-modal').modal('show');

// $('#ShareModal').modal('show');
break;  
case "related":
$('#RelatedModal input[name="id"]').val(id_set);
var val = $('#RelatedModal [name="id"]').val();

if(val != ''){
  requestGet(admin_url + 'spreadsheet_online/get_related_id/'+ val).done(function(response_s) {
    response_s = JSON.parse(response_s);

    $.each(response_s.type, function(index, value){
      requestGet(admin_url + 'spreadsheet_online/get_related/' + value + '/' + response_s.id[index]).done(function(response) {
        response = JSON.parse(response);
        rel_id_select[index] = response_s.id[index];
        if(index > 0){
          $('.new_box_information_review_related').click();
          $('select[name="rel_type['+index+']"]').val(value).selectpicker('refresh');
          $('select[name="rel_id['+index+']"').html(response);

        }else{
          $('select[name="rel_type['+index+']"]').val(value).selectpicker('refresh');
          $('select[name="rel_id['+index+']"').html(response);
        }
        $('select[name="rel_id['+index+']"').selectpicker('refresh');
      })
    })

    
  })
}

$('#RelatedModal').modal('show');
break;
case "d_zip":
const nameCleaned = name.replace(/\s/g, '_');
requestGet(admin_url + 'spreadsheet_online/get_folder_zip/'+ id_set + '/' + nameCleaned).done(function(response) {
	response = JSON.parse(response);
})
break;
case "d_file":
requestGet(admin_url + 'spreadsheet_online/check_file_exits/'+id_set).done(function(response) {
  response = JSON.parse(response);
  if(response.success){
    requestGet(admin_url + 'spreadsheet_online/get_file_sheet/'+ id_set).done(function(response1) {
      response1 = JSON.parse(response1);
      exportExcel(JSON.parse(response1), name);
    })
  }else{
    alert_float('warning', response.message);
  }
})


break;
case "create_file":
$("input[name='parent_id']").val(id_set);
$('.add_file_button').click();
break;
case "create_folder":
$("input[name='parent_id']").val(id_set);
$('.add_folder_button').click();
break;
default:
if(type == 'file'){
	if(share == false){
   requestGet(admin_url + 'spreadsheet_online/check_file_exits/'+id_set).done(function(response) {
    response = JSON.parse(response);
    if(response.success){
      window.location.replace(admin_url + 'spreadsheet_online/new_file_view/'+parent_id+'/'+id_set);
    }else{
      alert_float('warning', response.message);
    }
  })
 }else{
  requestGet(admin_url + 'spreadsheet_online/check_file_exits/'+id_set).done(function(response) {
    response = JSON.parse(response);
    if(response.success){
      // requestGet(admin_url + 'spreadsheet_online/get_hash_staff/' + id_set).done(function(response1) {
      //   response1 = JSON.parse(response1);
        window.location.replace(admin_url + 'spreadsheet_online/file_view_share/'+parent_id+'/'+id_set);
      // })
    }else{
      alert_float('warning', response.message);
    }
  })
}
}else{
	$('#AddFolderModal .add-new').addClass('hide');
	$('#AddFolderModal .update-new').removeClass('hide');
	$('#AddFolderModal input[name="name"]').val(name);
	$('#AddFolderModal input[name="id"]').val(id_set);
	$('#AddFolderModal .modal-footer [type="submit"]').remove();
	$('#AddFolderModal').modal('show');
}
break;
}
toggleMenuOff();


var _rel_id = $('select[name="rel_id[0]'),
_rel_type = $('select[name="rel_type[0]'),
_rel_id_wrapper = $('#rel_id_wrapper');

$('select[name="rel_type[0]"]').on('change', function() {
  var name = $(this).val();
  requestGet(admin_url + 'spreadsheet_online/get_related/' + _rel_type.val()).done(function(response) {
    response = JSON.parse(response);
    $('[for="rel_id[0]"]').html(name);
    if(response == ''){
      $('select[name="rel_id[0]"]').append('<option value="" selected></option>');
      $('select[name="rel_id[0]"]').selectpicker('refresh');
    }else{
      $('select[name="rel_id[0]"]').html(response);
      $('select[name="rel_id[0]"]').selectpicker('refresh');
    }
    var obj = $('select[name*="rel_id"]');
    for(let i = 0; i < rel_id_select.length; i++){
      obj.eq(i).val(rel_id_select[i]).change();  
    }
  });

})
}