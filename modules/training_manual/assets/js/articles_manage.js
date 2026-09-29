$(function(){
	"use strict";

	var viewLogs = [];

	var fnIsViewed = function(articleId){

	}

	$(document).ready(function(){
		var fnSearch = function(){
			var form = $('#form_search');

			var valueQuery = $('.input-search-list').val();
			var inputQuery = $('#form_search [name="filter_query"]');
			inputQuery.val(valueQuery);
			
			var valueBookId = $('#filter_book_id').val();
			if(valueBookId != undefined && valueBookId != null && valueBookId != ''){
				var inputBookId = $('#form_search [name="filter_book_id"]');
				if(inputBookId.length == 0){
					form.append('<input type="hidden" name="filter_book_id" value="'+valueBookId+'" />')
				}else{
					inputBookId.val(valueBookId);
				}
			}

			form.submit();
		}
		$(".btn-search-list").on('click',function(){
			fnSearch();
		});
		$('.input-search-list').keypress(function (e) {
			if (e.which == 13) {
				fnSearch();
				return false;
			}
		});
		$('#filter_book_id').on('change', function (e) {
			fnSearch();
		});
	});

	// bookmark
	$(document).ready(function(){
		var bookmarking = false;
		$('.training_manual-btn-bookmark').on('click', function(){
			var _this = $(this);
			var articleId = _this.data('id');
			if(articleId == undefined || articleId == null || articleId == ''){
				return;
			}
			var isOn = _this.hasClass('training_manual-bookmark-on');

			var reqData = {
				csrf_token: APP_CSRF_TOKEN,
				is_on: isOn ? 0 : 1,
				article_id: articleId,
			};

			function fnSwitch(newIsOn){
				if(newIsOn){
					_this.removeClass('training_manual-bookmark-off');
					_this.addClass('training_manual-bookmark-on');
				}else{
					_this.removeClass('training_manual-bookmark-on');
					_this.addClass('training_manual-bookmark-off');
				}
			}
			
			if(bookmarking){
				return;
			}
			bookmarking = true;
			$.ajax({
				url: bookmark_switch_url,
				type : 'POST',
				data: reqData,
				dataType: 'json',
				success: function(data){
					if(data.result != undefined && data.result == true){
						fnSwitch(!isOn);
					}
					bookmarking = false;
				},
				error: function(){
					bookmarking = false;
				},
			});

		});
	});

});

// Smart Choice Training Manual v1.1.5 controls
$(function(){
    $(document).on('click', '.training-manual-change-creator', function(){
        var articleId = parseInt($(this).attr('data-article-id'), 10) || 0;
        var creatorId = parseInt($(this).attr('data-current-creator-id'), 10) || 0;
        $('#trainingManualCreatorArticleId').val(articleId);
        $('#trainingManualCreatorModal input[name="staff_id"]').prop('checked', false);
        if (creatorId > 0) {
            $('#trainingManualCreatorStaff' + creatorId).prop('checked', true);
        }
        if (articleId < 1) { alert('The training article ID could not be identified. Refresh the page and try again.'); return; }
        $('#trainingManualCreatorModal').modal('show');
    });

    $(document).on('click', '.training-manual-mass-delete', function(){
        var checked = $('#trainingManualMassForm input[name="article_ids[]"]:checked').length;
        if (checked < 1) {
            alert('Please select at least one training article.');
            return false;
        }
        if (confirm('Delete selected training articles?')) {
            $('#trainingManualMassForm').submit();
        }
    });
});


// Smart Choice v1.2.8 creator validation
$(document).on('submit', '#trainingManualCreatorModal form', function(e){
    var articleId = parseInt($('#trainingManualCreatorArticleId').val(), 10) || 0;
    var staffId = parseInt($('#trainingManualCreatorModal input[name="staff_id"]:checked').val(), 10) || 0;
    if (articleId < 1 || staffId < 1) {
        e.preventDefault();
        alert('Select an employee before saving the creator.');
        return false;
    }
});
