
$(document).on('click', 'div.question label', function(){
	var label = $(this);
	
	// New or old design? There's different HTML now
	
	if(label.parent('td').length)
	{
		// Old design
		var td = label.closest('td'),
			table = td.closest('table'),
			radio = label.find('input[type=radio]');
		
		if(!radio.is(':checked'))
		{
			// Remove all others
			table.find('td').removeClass('ticked');
	
			// Tick this one
			td.addClass('ticked');
		}
	}
	else
	{
		var answer = label.closest('.answer'),
			question = label.closest('.question'),
			radio = label.find('input[type=radio]'),
			tickSpan = label.find('.tick'),
			tickImg = label.find('img');
			
		if(!radio.is(':checked'))
		{
			// Remove all others
			question.find('.answer').removeClass('ticked');
	
			// Tick this one
			answer.addClass('ticked');
		}
	}
}).on('click', 'div.table-radio label', function(){

	var label = $(this),
		div = label.closest('div.table-radio'),
		td = label.closest('td'),
		tr = label.closest('tr'),
		radio = label.find('input[type=radio]');
	
	if(!radio.is(':checked'))
	{
		tr.find('div.table-radio').removeClass('ticked');
		
		div.addClass('ticked');
	}

}).on('click', 'div.buttons label', function(){
	var label = $(this),
		buttons = label.closest('div.buttons'),
		radio = label.find('input[type=radio]');
	
	if(!radio.is(':checked'))
	{
		buttons.find('label').removeClass('ticked');
		
		label.addClass('ticked');
	}
}).on('click', '#accession-form-1 .checkbox label', function(){

	var label = $(this),
		input = label.find('input'),
		img = label.find('img');
	

	
	if(input.is(':checked'))
	{
		img.show();
	}
	else
	{
		img.hide();
	}
});