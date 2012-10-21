var lists;
var titles;
var positions;
var committees;

var send_message_timer;

function load()
{
	// not a solid solution, but fun
	// should just put in a generic select for each case that can be cloned later
	// difficult to do, however, this will have to work for now
	
	/*new Ajax.Request('index.php?noun=lists&verb=read&style=view&format=json', {
	  method: 'get',
	  onSuccess: function(transport) {
		response = transport.responseText.evalJSON();
		titles = response['titles'];
		positions = response['positions'];
		committees = response['committees'];
	  }
	});*/
	
	// no ajax... :-(
	
	titles = lists['titles'];
	positions = lists['positions'];
	committees = lists['committees'];
	
	if(location.search.indexOf('noun=messagesender') != -1)
		send_next_message();
}

function remove_row(type, nod)
{
	nod1 = nod.parentNode;
	par = document.getElementById(type+'s');
	par.removeChild(nod1);
}

function remove_erow(type, nod)
{
	$(nod).parents().eq(1).remove();
	
	if($('#'+type+'s tr').size() == 2)
		$('#no_'+type).show();
}

function add_row(type)
{
	i = parseInt(document.getElementById('max_'+type).getAttribute('value')) + 1;	
		
	par = document.getElementById(type+'s');
	main_node = document.createElement('div');
	main_node.setAttribute('id', type+'_' + i);
	if(type == 'email')
	{
		radio_node = document.createElement('input');
		//$(radio_node).attr('id', type+'_'+i+'_standard').attr('type', 'radio').attr('name', 'standard_email').attr('value', i);
		radio_node.setAttribute('id', type+'_' + i + '_standard');
		radio_node.setAttribute('type', 'radio');
		radio_node.setAttribute('name', 'standard_email');
		radio_node.setAttribute('value', i);
		main_node.appendChild(radio_node);
	}
	else if(type == 'award' || type == 'nomination' || 'membership')
	{
		objects = {'award': 'title', 'nomination': 'position', 'membership': 'committee'};
		select_node = document.createElement('select');
		select_node.setAttribute('name', type+'_' + i + '_'+objects[type]);
		if(type == 'award')
			options = titles;
		else if(type == 'nomination')
			options = positions;
		else if(type == 'membership')
			options = committees;
		for(id in options)
		{
			option_node = document.createElement('option');
			option_node.setAttribute('value', id);
			option_node.appendChild(document.createTextNode(options[id]));
			select_node.appendChild(option_node);
		}
		main_node.appendChild(select_node);
	}
	text_name = {'email': 'text', 'award': 'year', 'nomination': 'year', 'membership': 'year'};
	main_node.appendChild(document.createTextNode('\n'));
	text_node = document.createElement('input');
	text_node.setAttribute('id', type+'_' + i + '_' + text_name[type]);
	text_node.setAttribute('type', 'text');
	text_node.setAttribute('name', type+'_' + i + '_' + text_name[type]);
	main_node.appendChild(text_node);
	main_node.appendChild(document.createTextNode('\n'));
	button_node = document.createElement('input');
	button_node.setAttribute('id', type+'_' + i + '_remove');
	button_node.setAttribute('type', 'button');
	button_node.setAttribute('value', 'Ta bort');
	button_node.setAttribute('onclick', 'remove_row(\'' + type + '\',this)');
	main_node.appendChild(button_node);
	par.appendChild(main_node);
	
	document.getElementById('max_'+type).setAttribute('value', i);
}

function add_erow(type)
{
	i = parseInt($('#max_'+type).val())+1;
	
	main = $("<tr/>").attr('id', type+'_'+i);
	
		if(type == 'email')
	{
		radio = $("<input type='radio'/>").attr('id', type+'_'+i+'_standard').attr('name', 'standard_email').val(i);
		main.append($("<td/>").addClass('standard_email').append(radio));
	}
	else if(type == 'award' || type == 'nomination' || 'membership')
	{
		objects = {'award': 'title', 'nomination': 'position', 'membership': 'committee'};
		
		if(type == 'award')
			options = titles;
		else if(type == 'nomination')
			options = positions;
		else if(type == 'membership')
			options = committees;
		
		select = $("<select/>").attr('name', type+'_'+i+'_'+objects[type]);
		for(id in options)
			$("<option/>").val(id).text(options[id]).appendTo(select);
		main.append($("<td/>").addClass('field_option').addClass(type+'_'+objects[type]).append(select));
	}
	
	text_name = {'email': 'text', 'award': 'year', 'nomination': 'year', 'membership': 'year'};
	text = $("<input type='text'/>").attr('id', type+'_'+i+'_'+text_name[type]).attr('name', type+'_'+i+'_'+text_name[type]);
	button = $("<input type='button'/>").attr('id', type+'_'+i+'_remove').val('Ta bort').click(function(){remove_erow('type',this);});
	
	main.append($("<td/>").addClass('field_text').addClass(type+'_'+text_name[type]).append(text).append(button));
	
	main.append($("<td/>").addClass('field_error'));
	
	$('#'+type+'s > tbody').append(main);
	
	$('#no_'+type).hide();
	
	$('#max_'+type).val(i);
}

/*
function remove_award(i)
{
	nod1 = document.getElementById('award_' + i);
	par = document.getElementById('awards');
	par.removeChild(nod1);
}

function add_award()
{
	i = parseInt(document.getElementById('max_award').getAttribute('value')) + 1;
	
	par = document.getElementById('awards');
	main_node = document.createElement('div');
	main_node.setAttribute('id', 'award_' + i);
	select_node = document.createElement('select');
	select_node.setAttribute('name', 'award_' + i + '_title');
	for(title_id in titles)
	{
		option_node = document.createElement('option');
		option_node.setAttribute('value', title_id);
		option_node.appendChild(document.createTextNode(titles[title_id]));
		select_node.appendChild(option_node);
	}
	text_node = document.createElement('input');
	text_node.setAttribute('type', 'text');
	text_node.setAttribute('name', 'award_' + i + '_year');
	button_node = document.createElement('input');
	button_node.setAttribute('type', 'button');
	button_node.setAttribute('value', 'Remove');
	button_node.setAttribute('onclick', 'remove_award(' + i + ')');
	main_node.appendChild(select_node);
	main_node.appendChild(document.createTextNode('\n'));
	main_node.appendChild(text_node);
	main_node.appendChild(document.createTextNode('\n'));
	main_node.appendChild(button_node);
	par.appendChild(main_node);
	
	document.getElementById('max_award').setAttribute('value', i);
}

function remove_nomination(i)
{
	nod1 = document.getElementById('nomination_' + i);
	par = document.getElementById('nominations');
	par.removeChild(nod1);
}

function add_nomination()
{
	i = parseInt(document.getElementById('max_nomination').getAttribute('value')) + 1;
	
	par = document.getElementById('nominations');
	main_node = document.createElement('div');
	main_node.setAttribute('id', 'nomination_' + i);
	select_node = document.createElement('select');
	select_node.setAttribute('name', 'nomination_' + i + '_position');
	for(position_id in positions)
	{
		option_node = document.createElement('option');
		option_node.setAttribute('value', position_id);
		option_node.appendChild(document.createTextNode(positions[position_id]));
		select_node.appendChild(option_node);
	}
	text_node = document.createElement('input');
	text_node.setAttribute('type', 'text');
	text_node.setAttribute('name', 'nomination_' + i + '_year');
	button_node = document.createElement('input');
	button_node.setAttribute('type', 'button');
	button_node.setAttribute('value', 'Remove');
	button_node.setAttribute('onclick', 'remove_nomination(' + i + ')');
	main_node.appendChild(select_node);
	main_node.appendChild(document.createTextNode('\n'));
	main_node.appendChild(text_node);
	main_node.appendChild(document.createTextNode('\n'));
	main_node.appendChild(button_node);
	par.appendChild(main_node);
	
	document.getElementById('max_nomination').setAttribute('value', i);
}

function remove_membership(i)
{
	nod1 = document.getElementById('membership_' + i);
	par = document.getElementById('memberships');
	par.removeChild(nod1);
}

function add_membership()
{
	i = parseInt(document.getElementById('max_membership').getAttribute('value')) + 1;
	
	par = document.getElementById('memberships');
	main_node = document.createElement('div');
	main_node.setAttribute('id', 'membership_' + i);
	select_node = document.createElement('select');
	select_node.setAttribute('name', 'membership_' + i + '_committee');
	for(committee_id in committees)
	{
		option_node = document.createElement('option');
		option_node.setAttribute('value', committee_id);
		option_node.appendChild(document.createTextNode(committees[committee_id]));
		select_node.appendChild(option_node);
	}
	text_node = document.createElement('input');
	text_node.setAttribute('type', 'text');
	text_node.setAttribute('name', 'membership_' + i + '_year');
	button_node = document.createElement('input');
	button_node.setAttribute('type', 'button');
	button_node.setAttribute('value', 'Remove');
	button_node.setAttribute('onclick', 'remove_membership(' + i + ')');
	main_node.appendChild(select_node);
	main_node.appendChild(document.createTextNode('\n'));
	main_node.appendChild(text_node);
	main_node.appendChild(document.createTextNode('\n'));
	main_node.appendChild(button_node);
	par.appendChild(main_node);
	
	document.getElementById('max_membership').setAttribute('value', i);
}
*/

function show_search(node)
{
	$('#search_box').show();
	$('#search_link').hide();
	
	return true;
}

function do_search(node)
{
	/* note: in order for this to work, the user logged in must have listing rights (aka VIEW_CORE_PRIV) */
	
	$.getJSON("index.php?verb=read&noun=list/search&style=view&format=json&query="+encodeURI($("#search_query").val()),
		function(data){
			$('#search_results').empty();
			$.each(data, function(i,student){
				$("<a/>").attr("href", '#').click(function() { finish_search(this,i,student[0]+' '+student[1]); }).text(student[0]+' '+student[1]).appendTo("#search_results").after($("<br/>"));
			});
			$("<a/>").attr("href", '#').click(function() { finish_search(this,-1,"Ingen"); }).text("Ingen").appendTo("#search_results");
		});
	/* apparently, attr('onclick', ...) doesn't work in safari, why? */
	
	return true;
}

function finish_search(node, id, name)
{
	$('#owns_student_id');
	$('#search_link a').text(name);
	
	$('#search_box').hide()
	$('#search_link').show();
	
	$('#owns_student_id').val(id);
	
	return true;
}

function get_email_list(node)
{
	search = location.search.replace(/format=([a-zA-Z%0-9]*)/, 'format=json').replace(/#.*$/, '');
	if(search.search('format=json') == -1)
		search += '&format=json';
		
	$.getJSON('index.php'+search,
		function(data)
		{
			var emails = new Array(), missing = 0;
			$('#email_link').hide();
			$.each(data, function(i,student)
				{
					if(student[3] != null)
						emails.push('"'+student[0]+' '+student[1]+'" <'+student[3]+'>');
					else
						missing+=1;
				});
			$("#email_list_text").text(emails.join(', '));
			if(missing > 0)
			{
				$('#email_list_error').text('Varning: '+missing+' saknar e-postadresser.');
			}
			$('#email_list').show();
		});
	
	return true;
}

function dialog_answer(node, value)
{
	$('#dialog_result').val(value);
	$('#dialog_form').submit();
}

function update_checkbox(id)
{	
	if($('#'+id).attr('checked'))
		$('#'+id+'_neg').removeAttr('disabled');
	else
		$('#'+id+'_neg').attr('disabled', 'disabled');
}

function send_next_message()
{
	clearInterval(send_message_timer);
	$.getJSON('index.php'+'?verb=write&noun=messagesender&format=json',
		function(data)
		{
			$("#messages_remaining").text(data);
			if(data > 0)
				send_message_timer = setInterval(send_next_message, 13);
		});
}

function remove_message(message_id)
{
	location.href = 'index.php'+'?verb=write&noun=messagequeue&style=remove&format=xhtml&message_id='+message_id;
}

function send_message(message_id)
{
	location.href = 'index.php'+'?verb=write&noun=messagequeue&style=send&format=xhtml&message_id='+message_id;
}