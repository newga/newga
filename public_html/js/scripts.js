$(document).ready(function(){


	

	//toggle organisation on and off
	function userForm(form) {

		if (form.find('#User_role').val() == 50) {
			form.find('.organisation').show();			
		}

		form.find('#User_role').on('change',function(){
			if (form.find('#User_role').val() == 50) {
				form.find('.organisation').slideDown(400);			
			}
			else
			{
				form.find('.organisation').slideUp(400);	
			}
		});
		
	}

	//run user form if on correct page
	if ($('.user-form').length) { 
		userForm($(this));
	}

	//if query builder,set the and ors c
	if ($('.query_builder').length) {
		if ($('.results_area ul li').length > 0)
		{
			$('.build_area .first .and_choice').show();
			$('.build_area .first .contact').hide();
			toggleUpAndDown(true);
		}
		
	}
	

	//on query question choice
	$(document).on('change', '.query_choice', function() {
		
		var select = $(this),
			row = $(this).closest('.row'),
			rowNumber = row.data('row');
		


		row.find('.query_options').hide();
		row.find('.query_submit').hide();
		//first of all, change the true/false language
		var lang_type = $(this).find(':selected').data('lang');

		var positive = 'is';
		var negative = 'is not';

		//change language 
		switch(lang_type) {
			case 1:
				   positive = 'is';
				   negative = 'is not';
				   break;
			case 2:
				   positive = 'has';
				   negative = 'does not have';
				   break;
			case 3:
				   positive = 'did answer';
				   negative = 'did not answer';
				   break;
			default:
				   positive = 'is';
				   negative = 'is not';
		}

		//change value of Query_bool
		row.find('.bool_choice .positive').html(positive);
		row.find('.bool_choice .negative').html(negative);

		//if there are further options, then display
		var has_value = $(this).find(':selected').data('has-value');
		var id = $(this).find(':selected').data('id');
		
		var csrfToken = $('input[name="YII_CSRF_TOKEN"]').val();
		
		if (has_value) {
			row.find('.query_submit').hide();
			
			//make an ajax request to render the options relevant to that id
			$.ajax({
				type: 'POST',
				url: '/queries/ajax',
				data: {
					'id' : id,
					'render' : 1,
					'rowNumber' : rowNumber,
					'YII_CSRF_TOKEN':csrfToken
				},
				success: function(msg){
					row.find('.spacer').hide();
					row.find('.query_options').html(msg).fadeIn(100);
				}}
			);

		}

		//otherwise show the add button
		else {

			if (select.val()) {
				row.find('.query_submit').fadeIn(100);
			}
			row.find('.spacer').show();
		}
		

	});
	
	// ! Listeners for changes in current values
	$(document).on('change', '#invite-limit, .results_area .bool_choice, .results_area .and_choice, .results_area .query_num_input, .results_area .query_option', function(){
		getResults();
	});
	
	// ! GET RESULTS COUNT
	function getResults()
	{
		var form = $('#query-form');
		
		var formData = form.serializeArray();
		
		formData.push({name: 'results', value: true});
		
		$('.query-loader').fadeIn(100);
		
		$.ajax({
			type: 'POST',
			url: '/queries/ajax',
			//dataType:'JSON',
			data: formData,
			success: function(json){
			
				$('#counter').text(json.results);
			},
			error: function(response)
			{
				alert(response.responseText);
			},
			complete: function(){
				$('.query-loader').fadeOut(250);
			}
		});
	}
	
	$(document).on('change', '.and_choice', function(){
		var select = $(this);
	
	});
	
	$(document).on('change', '.query_option', function() {
		var select = $(this);
		var group = $(this).parent().parent().parent();
		if (select.val()) {
			group.find('.query_submit').fadeIn(100);
		}
		else {
			group.find('.query_submit').hide();
		}

	});

	// ! Validate numeric format
	$(document).on('keyup', '.query_num_input', function() {
		var select = $(this);
		var row = $(this).closest('.row');
		if (select.val()) {
			
			if (!isNaN(select.val())) {
				row.find('.query_submit').fadeIn(100);
				select.parent().find('.error').hide();
				
			}
			else
			{
				select.parent().find('.error').fadeIn(100);
				row.find('.query_submit').hide();
			}
		}
		else {
			row.find('.query_submit').hide();
		}
	});

	// ! Add a new rule
	$(document).on('click','.query_submit_button',function() {

		//hide any previous alerts
		$('.alerts').hide();

		var form = $(this).closest('form');

		var formData = form.serializeArray();
	
		
		// Add a field to tell the PHP that it's a new row
		formData.push({name: 'new-row', value: true});
		
		$.ajax({
				type: 'POST',
				url: '/queries/ajax',
				dataType:'JSON',
				data: formData,
				success: function(json){
				
					$('#counter').text(json.results);
					$('.results_area ul').append(json.html).fadeIn(100);

					//change the p contact to an and or button
					if ($('.results_area ul li').length > 0)
					{
						$('.build_area .first .and_choice').show();
						$('.build_area .first .contact').hide();

					}
					toggleUpAndDown();
					getResults();
				}}			
			);

		return false;

	});
	
	$('#sendInvites').click(function(){
		
		var input = $(this)
			count = input.data('count');
		
		if (!confirm("Are you sure you want to send invitation emails to " + count + " contacts?"))
		{
			return false;
		}
	});
	
	// ! Delete a row
	$(document).on('click','.query_row_delete',function(){
		var row = $(this).closest('.row');
		 if (confirm("Are you sure you'd like to remove this rule?\n\nThe query rules will not be saved until you click Update.")) {
			row.fadeOut(200, function(){
				row.remove();
				
				//if there are no more rows in the rules area, then turn and or button back
				if ($('.results_area ul li').length == 0)
				{
					//hide any previous alerts
					$('.alerts').hide();
	
					//this needs to be changed to be done by displaying or hiding classes
					$('.build_area .first .and_choice').hide();
					$('.build_area .first .contact').show();
					
				}
	
				toggleUpAndDown();
				getResults();
			});
		}
		
		return false;
	});

	//function to disable up and down buttons in the query builder rows
	function toggleUpAndDown(onload) {

		//hide any previous alerts
		if(!onload){
			$('.alerts').hide();
		}

		$('.results_area ul li .query_row_up').removeClass('disabled');
		$('.results_area ul li .query_row_down').removeClass('disabled');
		
		//disable
		$('.results_area ul li:first .query_row_up').addClass('disabled');
		$('.results_area ul li:last .query_row_down').addClass('disabled');


		//first and/or button
		$('.results_area ul li .and_choice').show();
		$('.results_area ul li .contact').hide();

		$('.results_area ul li:first .and_choice').hide();
		$('.results_area ul li:first .contact').show();
	}


	$(document).on('click','.query_row_up',function(){ 

		//hide any previous alerts
		$('.alerts').hide();

		var row = $(this).parent().parent().parent().parent();

		//get position
		var position = ($('.results_area ul li').index(row));
		var above = position - 1;
		//so now we want to copy the item above into temporary variable, 
		//remove it and then put it below current

		//clone
		var toMove = $('.results_area ul li:eq('+above+')').clone();

		//delete
		$('.results_area ul li:eq('+above+')').remove();

		//put back below
		row.after(toMove);

		toggleUpAndDown();
		return false;
	});


	$(document).on('click','.query_row_down',function(){ 

		//hide any previous alerts
		$('.alerts').hide();

		var row = $(this).parent().parent().parent().parent();

		//get position
		var position = ($('.results_area ul li').index(row));
		var above = position + 1;
		//so now we want to copy the item above into temporary variable, 
		//remove it and then put it below current

		//clone
		var toMove = $('.results_area ul li:eq('+above+')').clone();

		//delete
		$('.results_area ul li:eq('+above+')').remove();

		//put back below
		row.before(toMove);

		toggleUpAndDown();
		return false;
	});

	// ! SAVE
	$(document).on('submit','#query-form',function() {
		return false;
	});



	
	$(document).on('click','#save-query',function() {
		//save all the query data
		
		var input = $(this);
		var id = $(this).data('id');
		var invite = $(this).data('invite');
		
		
		input.prop('disabled', true).val('Saving...');

		var form = $('#query-form');
		
		var formData = form.serializeArray();
		
		formData.push({name: 'save', value: true});
		
		//make an ajax request to render the options relevant to that id
		$.ajax({
			type: 'POST',
			url: '/queries/ajax',
			dataType:"json",
			data: formData,
			success: function(response){
				
				input.prop('disabled', false).val('Save');
				
				if (response.errors) {
					var errorString = '';
					$.each(response.errors, function(k,v){
						
						errorString+=v+'</br>';

					});

					$('.alerts').html("<div class='alert alert-danger'>"+errorString+"</div>").hide().fadeIn(500);
				}

				if (response.success) {

					if(!response.id){
						// new. redirect. Flash set in controller.
						window.location.href = response.redirect;
					}
					else {
						$('.alerts').html("<div class='alert alert-success'>Query has been saved.</div>").hide().fadeIn(500);
						$('.total-results').text(response.resultsTotal);
					}
				
				}

			}}			
		);
	
		return false;
	});

	//confirm delete
		
	$('#delete-form').submit(function() {
		return confirm("Are you sure?");
	});
	

	$('input.delete-confirm, a.delete-confirm').click(function(){
		return confirm('Are you sure you want to delete this?');
	});


	$(document).on('click','#query_detail',function(){ 
		$('#loading_modal').modal();
	});


	$(document).on('click','#run-campaign',function(){ 
		$('#loading_modal').modal();
	});


//
// campaigns
//

}).on('click', '.campaign-group-management .edit-all, .campaign-group-outcomes-management .edit-all', function(e){

	// show manage group options or save them
	var $editSaveButton = $(this),
		table = $editSaveButton.closest('p').prev(),
		isGroupManager = table.closest('.campaign-group-management').length;

	if(!$editSaveButton.hasClass('btn-primary')){

		// now we prevent default.
		e.preventDefault();

		table.closest('form').addClass('in-edit');

		$editSaveButton.html('Save').toggleClass('btn-primary btn-default').siblings('.cancel').removeClass('hide');

		table.find('td.name span, td.fraction span');

		if(!isGroupManager || table.find('tr').length < 4){
			$editSaveButton.parent().siblings('.add-new').removeClass('hide');

			if(
				(isGroupManager && table.find('tr').length < 3)
				||
				(!isGroupManager && table.find('tr').length < 2)
			){
				table.find('tr').find('.remove').css('visibility', 'hidden');
			}
		}
	}


}).on('click', '#form-campaign-groups-createupdate .remove', function(e){

	// remove a group

	e.preventDefault();

	var $e = $(this).closest('a'),
		$form = $e.closest('form'),
		tr = $e.closest('tr');

	if(!$e.hasClass('clicked')){

		$e.addClass('clicked label label-danger').children().toggleClass('hide');
		$e.closest('td').mouseleave(function(){
			$(this).find('.remove').removeClass('clicked label label-danger').children().toggleClass('hide');
			$e.closest('td').unbind('mouseleave');
		})

	}
	else
	{
		tr.fadeTo(null, 0, function(){

			var siblings = tr.siblings();
			tr.remove();

			// save groups to this campaign
			$.ajax({
				type: 'POST',
				url: '',
				dataType:"json",
				data: $form.serialize(),

				success: function(data, textStatus){

					// update all the data.
					$.each(data.groups, function(i,e){
						var tr = $('#campaign-group-' + e.id);
						tr.find('.name span.hide a').text(e.name);
						tr.find('.fraction span.hide').text(e.fraction);
					});

					$form.find('a.add-new').removeClass('hide');

					if(siblings.length < 3){
						siblings.find('.remove').css('visibility', 'hidden');
					}

				},
				error: function(){
					alert('There was a problem saving those choices. Please refresh the page and try again.');
				}
			});
		 });
	}



}).on('click', '#form-outcomes-createupdate .remove', function(e){

	// remove an outcome

	e.preventDefault();

	var $e = $(this).closest('a'),
		$form = $e.closest('form'),
		tr = $e.closest('tr');

	if(!$e.hasClass('clicked')){

		$e.addClass('clicked label label-danger').children().toggleClass('hide');
		$e.closest('td').mouseleave(function(){
			$(this).find('.remove').removeClass('clicked label label-danger').children().toggleClass('hide');
			$e.closest('td').unbind('mouseleave');
		})

	}
	else
	{
		tr.fadeTo(null, 0, function(){

			var siblings = tr.siblings();
			tr.remove();

			// save groups to this campaign
			$.ajax({
				type: 'POST',
				url: '',
				dataType:"json",
				data: $form.serialize(),

				success: function(data, textStatus){

					// update all the data.
					$.each(data.outcomes, function(i,e){
						var tr = $('#campaign-outcome-' + e.id);
						tr.find('.name span.hide a').text(e.name);
						tr.find('.fraction span.hide').text(e.fraction);
					});

					$form.find('a.add-new').removeClass('hide');

					if(siblings.length < 2){
						siblings.find('.remove').css('visibility', 'hidden');
					}
				},
				error: function(){
					alert('There was a problem saving those choices. Please refresh the page and try again.');
				}
			});
		 });
	}





}).on('click', '#form-campaign-groups-createupdate .add-new', function(e){

	// remove a group

	e.preventDefault();

	var $this = $(this).closest('form');

	// save groups to this campaign
	$.ajax({
		type: 'POST',
		url: '',
		dataType:"json",
		data: $this.serialize() + '&addGroup=1',

		success: function(data, textStatus){

			// update all the data.
			$.each(data.groups, function(i,e){

				var tr = $('#campaign-group-' + e.id);
				if(tr.length){
					tr.find('.name span').find('a').text(e.name).end().find(':text').val(e.name);
					tr.find('.fraction span').eq(0).text(e.fraction).end().find(':text').val(e.fraction);
				}
				else
				{
					// add new one
					var tr = $this.find('tr:first'),
						newGroup = tr.clone();

					newGroup.appendTo(tr.closest('table'));

					newGroup.attr('id', tr.attr('id').replace(/[1-9][0-9]*/, e.id));

					var nameTD = newGroup.find('.name'),
						input = nameTD.find(':text');
					input.attr('name', input.attr('name').replace(/\[[1-9][0-9]*\]/, '['+e.id+']')).val(e.name);
					nameTD.find('a:first').text(e.name);

					input.select();

					var fractionTD = newGroup.find('.fraction'),
						input = fractionTD.find(':text');
					input.attr('name', input.attr('name').replace(/\[[1-9][0-9]*\]/, '['+e.id+']')).val(e.fraction);
					fractionTD.find('span:first').text(e.fraction);

					newGroup.find('a.goto').each(function(i2,e2){
						var $e2 = $(e2);
						$e2.attr('href', $e2.attr('href').replace(/[1-9][0-9]*\/update$/, e.id + '/update'));
					});

				}

				if(data.totalGroups > 3){
					$this.find('a.add-new').addClass('hide');
				}

			});

		},
		error: function(){
			alert('There was a problem saving those choices. Please refresh the page and try again.');
		}
	});



}).on('click', '#form-outcomes-createupdate .add-new', function(e){

	// remove a group

	e.preventDefault();

	var $form = $(this).closest('form');

	// save groups to this campaign
	$.ajax({
		type: 'POST',
		url: '',
		dataType:"json",
		data: $form.serialize() + '&add=1',

		success: function(data, textStatus){

			// update all the data.
			$.each(data.outcomes, function(i,e){


				var tr = $('#campaign-outcome-' + e.id);
				if(tr.length){
					tr.find('.name span').eq(0).text(e.name).end().next().find(':text').val(e.name);
				}
				else
				{

					// add new one
					var tr = $('table.group-outcomes tbody tr:first'),
						newOutcome = tr.clone();

					newOutcome.appendTo($form.find('table.group-outcomes'));

					newOutcome.attr('id', tr.attr('id').replace(/[0-9]+/, e.id));

					var nameTD = newOutcome.find('.name'),
						input = nameTD.find(':text');
					input.attr('name', input.attr('name').replace(/\[[0-9]+\]/, '['+e.id+']')).val(e.name);
					nameTD.find('a:first').text(e.name);

					input.select();
				}

			});

		},
		error: function(){
			alert('There was a problem saving those choices. Please refresh the page and try again.');
		}
	});



}).on('submit', '#form-campaign-groups-createupdate', function(e){

	// check they're all whole numbers and they total 100%
	e.preventDefault();

	var total = 0,
		$this = $(this),
		table = $this.find('table.campaign-groups');

	table.find('.fraction :text').each(function(i,e){
		var val = $(e).val();

		if(!val.match(/^[1-9][0-9]*$/)){
			alert('Fractions must be whole numbers.');
			return;
		}

		total += parseInt(val);
	});

	if(total !== 100){
		alert('Fractions must total 100%. The current total is ' + total + '%.');
		return;
	}

	table.find('.name :text').each(function(i,e){
		if(!$(e).val().length){
			$(e).select();
			alert('All groups require a name');
			return;
		}
	});


	// save groups to this campaign
	$.ajax({
		type: 'POST',
		url: '',
		dataType:"json",
		data: $this.serialize(),

		success: function(data, textStatus){

			// update all the data.
			$.each(data.groups, function(i,e){
				var tr = $('#campaign-group-' + e.id);
				tr.find('.name span.hide a').text(e.name);
				tr.find('.fraction span.hide').text(e.fraction);
			});

			// fake click the cancel button.
			$this.find('a.cancel').click();

		},
		error: function(){
			alert('There was a problem saving those choices. Please refresh the page and try again.');
		}
	});




}).on('submit', '#form-outcomes-createupdate', function(e){

	// check they're all whole numbers and they total 100%
	e.preventDefault();

	var $form = $(this),
		table = $form.find('table.group-outcomes');

	table.find('.name :text').each(function(i,e){
		var $e = $(e);
		if(!$e.val().length){
			$e.select();
			alert('All outcomes require a name');
			return;
		}
	});


	// save outcomes to this group
	$.ajax({
		type: 'POST',
		url: '',
		dataType:"json",
		data: $form.serialize(),

		success: function(data, textStatus){

			// update all the data.
			$.each(data.outcomes, function(i,e){
				var tr = $('#campaign-outcome-' + e.id);
				tr.find('.name span.static').text(e.name);
			});

			$form.find('.form-controls .edit-all').html('Edit outcomes').toggleClass('btn-primary btn-default');
			$('#form-outcomes-createupdate').removeClass('in-edit');

		},
		error: function(){
			alert('There was a problem saving those choices. Please refresh the page and try again.');
		}
	});



}).on('click', '.campaign-group-management .cancel', function(e){

	// cancel edit gruops
	e.preventDefault();

	var $this = $(this).addClass('hide'),
		list = $this.closest('p').prev();

	list.find('td.name span, td.fraction span').toggleClass('hide');
	list.find('td.options a').css('visibility', 'hidden');
	list.find('td.options, td.url span, td.jumpto').toggleClass('hide');

	$this.siblings('.edit-all').html('Edit ' + $this.data('type')).toggleClass('btn-primary btn-default');
	$this.parent().siblings('.add-new').addClass('hide');



}).on('click', '.is-url', function(e){

	$(this).next().toggleClass('invisible');
	$(this).parent().prev().find('.glyphicon').toggleClass('glyphicon-ok glyphicon-remove');

});

//create modal and
//reload page after modal closed
//copies variables to page is reopened in correct place
$('[data-toggle="ajaxModal"]').on('click',
	function(e) {
		e.preventDefault();
		
		var iframe = $('<iframe width="100%" height="100%" frameborder="0"></iframe>'),
		$this = $(this); 


		$('#iframe-holder').html(iframe);
		iframe.attr('src',$this.attr('href'));
		//set modal height on load
		iframe.on('load',function() {

			iframe.height(iframe.contents().find("html").height());
		
		})
		//copy campaign and group id data over to the
		$('#form-modal').data('groupid',$(this).data('groupid'));
		$('#form-modal').data('campaignid',$(this).data('campaignid'));
		$modal = $('#form-modal');
		$modal.modal();

	}
);

//close modal listener
$('#form-modal').on('hidden.bs.modal', function () {
	//reload the page
	//location.reload();
	window.location.href = '/campaigns/' + $('#form-modal').data('campaignid') + '/3/#group' + $('#form-modal').data('groupid');
})








