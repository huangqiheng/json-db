function new_schema_window(title, cb_done, init_data)
{
	var time = new Date().getTime();
	var window_id = 'win_'+time.toString(); 
	var ok_id = window_id+'_ok';
	var no_id = window_id+'_no';
	var name_id = window_id+'_name';
	var title_id = window_id+'_title';
	var content_id = window_id+'_content';
	var image_id = window_id+'_image';
	var notify_id = window_id+'_notify';

	$('body').append([
            '<div id="'+window_id+'">',
                '<div>',
                    '<img width="20" height="20" src="images/new-table.png" alt="" style="float:left;" /><strong style="font-size:16px;">'+title+'</strong></div>',
                '<div>',
                    '<div align="center"><p><table>',
			'<tr><td align="right">'+T('name')+':</td><td><input type="text" id="'+name_id+'" /></td></tr>',
			'<tr><td align="right">'+T('title')+':</td><td><input type="text" id="'+title_id+'" /></td></tr>',
			'<tr><td align="right" style="vertical-align:top;">'+T('desc')+':</td><td><textarea id="'+content_id+'"></textarea></td></tr>',
			'<tr><td align="right">'+T('image')+':</td><td><input type="text" id="'+image_id+'" /></td></tr>',
                    '</table></div>',
                    '<div>',
		    '<div id="'+notify_id+'" style="color:red; text-align:center;"></div>',
                    '<div style="float:right; margin-top:15px; margin-right:40px;">',
                        '<input type="button" id="'+ok_id+'" value="'+T('OK')+'" style="margin-right: 10px" />',
                        '<input type="button" id="'+no_id+'" value="'+T('Cancel')+'" />',
                    '</div>',
                    '</div>',
                '</div>',
            '</div>'].join(''));

	$('#'+window_id).jqxWindow({height: 320, width: 450,
			resizable: false, isModal: true, modalOpacity: 0.3,
			okButton: $('#'+ok_id), cancelButton: $('#'+no_id),
			initContent: function () {
				$('#'+name_id).jqxInput({width:300, height: 25});
				$('#'+title_id).jqxInput({width:300, height: 25});
				$('#'+content_id).jqxInput({width:300, height: 50});
				$('#'+image_id).jqxInput({width:300, height: 25});

				if (init_data) {
					$('#'+name_id).val(init_data.name);
					$('#'+title_id).val(init_data.title);
					$('#'+content_id).val(init_data.content);
					$('#'+image_id).val(init_data.image);
					$('#'+notify_id).text(init_data.notify);
				}

				$('#'+ok_id).jqxButton({width: 65, height:35});
				$('#'+no_id).jqxButton({width: 65, height:35});
				focus_on_blank([name_id,title_id,content_id,image_id]);

				$('#'+ok_id).on('click', function(e){
					var d_name = $('#'+name_id).val();
					var d_title = $('#'+title_id).val();
					var d_content = $('#'+content_id).val();
					var d_image = $('#'+image_id).val();
					if ((d_title==='')||(d_content==='')||(d_image==='')) {
						var d_init = {
							'name': d_name,
							'title': d_title,
							'content': d_content,
							'image': d_image,
							'notify': T('Don\'t leave it blank please.')
						};
						new_schema_window(title, cb_done, d_init);
						return;
					}
					cb_done({
						'name': d_name,
						'title': d_title,
						'content': d_content,
						'image': d_image
					});
					$('#'+window_id).jqxWindow('destroy'); 
				});
				$('#'+no_id).on('click', function(e){
					$('#'+window_id).jqxWindow('destroy'); 
				});
			}
	});


	function focus_on_blank(id_arr) {
		for (var index in id_arr) {
			var id = id_arr[index];
			var value = $('#'+id).val();
			if (value === '') {
				$('#'+id).focus();
				return;
			}
		}
	}

}

function init_tab(vaild_fields, container_id, width)
{
	var tabul_id = container_id+'_tabul';
	$('#'+container_id).append('<ul style="margin-left: 30px;" id="'+tabul_id+'"></ul>');

	for (var tab_name in vaild_fields) {
		var tabitem_id = get_tabitem_id(container_id, tab_name);
		var tabtable_id = tabitem_id+'_table';
		$('#'+tabul_id).append('<li>'+tab_name+'</li>');
		$('#'+container_id).append('<div id="'+tabitem_id+'"><p><table id="'+tabtable_id+'" style="margin:auto;"></table></div>');
	}

	width = width || 640;
	$('#'+container_id).jqxTabs({ 
		position: 'top', 
		width: width, 
		scrollable:false,
	});
}

function get_tabitem_id(parent_id, input_str)
{
	return parent_id+'_'+md5(input_str);
}

function get_tableitem_id(tab_name, field_name)
{
	return 'field_'+md5(tab_name+field_name);
}

function addItem_jqxInput_id(p, id, width, height)
{
	tabitem_id=p[0]; table_id=p[1]; field_id=p[2]; caption=p[3];
	width = width || 200;
	addItem_jqxInput(p, id, [], height, width);
	$('#'+field_id).jqxInput({disabled: true});
}

function addItem_jqxInput_name(p, init_val, source, height, width)
{
	width = width || 300;
	addItem_jqxInput(p, init_val, source, height, width);
}

function addItem_jqxInput_text(p, init_val, source, height, width)
{
	tabitem_id=p[0]; table_id=p[1]; field_id=p[2]; caption=p[3];
	width = width || 400; height = height || 50;
	source = source || []; 

	var html = '<tr><td align="right">'+caption+'</td>';
	html += '<td align="left"><textarea id="'+field_id+'"></textarea></td></tr>';
	$('#'+table_id).append(html);
	$('#'+field_id).jqxInput({width: width, height: height, source:source});
	if (init_val) {
		$('#'+field_id).val(init_val);
	}
}

function addItem_jqxInput(p, init_val, source, height, width)
{
	tabitem_id=p[0]; table_id=p[1]; field_id=p[2]; caption=p[3];
	width = width || 400; height = height || 25;
	source = source || []; 

	var html = '<tr><td align="right">'+caption+'：</td>';
	html += '<td align="left"><input type="text" id="'+field_id+'" /></td></tr>';
	$('#'+table_id).append(html);
	$('#'+field_id).jqxInput({width: width, height: height, source:source});
	if (init_val) {
		$('#'+field_id).val(init_val);
	}
}

function render_null(data, finish)
{
	data.push(data[0]);
	data.push(data[0]);
	finish(data);
}

function jsonp(url, data, cb_done, cb_fail)
{
	$.ajax({
		type : "GET",
		dataType : "jsonp",
		data: data,
		url : url, 
	}).done(cb_done).fail(cb_fail);
}

function render_onebox(data, finish)
{
	jsonp('onebox/index.php', 
		{url:data[0], type:'json'}, function(d){
		if (d.status === 'ok') {
			var content = d.description.substring(0,82);
			var html = ['<table style="width:390px; height:80px; font-size:12"><tbody>',
			'<tr>',
			    '<td rowspan="2"><a href="'+d.ori_url+'" target="_blank"><img height="80" width="80" src="'+d.image+'"></a></td>',
			    '<td><strong>'+d.title+'</strong></td>',
			'</tr><tr>',
			    '<td>'+content+'</td>',
			'</tr></tbody></table>'].join('');

			var value = {
				title: d.title,
				desc: d.description,
				image: d.image,
				url: d.ori_url
			};

			data.push(html);
			data.push(value);
			finish(data);
		}
	});
}

function addItem_jqxListBox_images(p, init_val, height, width)
{
	tabitem_id=p[0]; table_id=p[1]; field_id=p[2]; caption=p[3];
	width = width || 400; height = height || 250;
	var img_width = 332;

	var source = [];
	if (init_val instanceof Array) {
		for (var i in init_val) {
			var item = init_val[i];
			source.push({label: item, value: item});
		}
	}

	var render = function (data, finish)
	{
		var image_url = data[0];
		data.push('<img height="50" width="50" src="'+image_url+'">');
		data.push(image_url);
		finish(data);
	};

	var image_id = field_id+'_image';
	var input_id = field_id+'_input';
	var addbtn_id = field_id+'_add';
	var delbtn_id = field_id+'_del';
	var upbtn_id = field_id+'_up';
	var downbtn_id = field_id+'_down';
	var btn_width = 25;
	var btn_height = 25;

	$('#'+table_id).append([
		'<tr><td align="right" style="vertical-align:top;">'+caption+'：</td><td align="left" >',
		'<table border="0" style="border-spacing:0px;" ><tbody><tr><td style="padding:0px;">',
		'<div id="'+field_id+'" style="float:left;"></div>',
		'<div id="'+image_id+'" style="float:left; width:'+img_width+'px; height: '+height+'px; background-color:rgb(241, 241, 241);"></div>',
		'</td></tr><tr><td style="padding:0px;">',
		'<input type="text" id="'+input_id+'" />',
		'<input type="button" id="'+addbtn_id+'" value="✚" />',
		'<input type="button" id="'+delbtn_id+'" value="✖︎" />',
		'<input type="button" id="'+upbtn_id+'" value="⬆︎" />',
		'<input type="button" id="'+downbtn_id+'" value="⬇︎" />',
		'</td></tr></tbody></table>',
		'</td></tr>'
	].join(''));

	$('#'+field_id).jqxListBox({scrollBarSize:5, source: source, width: width-img_width, height: height});
	$('#'+input_id).jqxInput({width: width-btn_width*4, height: btn_height});
	$('#'+addbtn_id).jqxButton({width: btn_width, height:btn_height});
	$('#'+delbtn_id).jqxButton({width: btn_width, height:btn_height});
	$('#'+upbtn_id).jqxButton({width: btn_width, height:btn_height});
	$('#'+downbtn_id).jqxButton({width: btn_width, height:btn_height});

	$('#'+addbtn_id).on('click',[input_id,field_id,render], event_listbox_add);
	$('#'+delbtn_id).on('click', [field_id], event_listbox_delete);
	$('#'+upbtn_id).on('click', [field_id], event_listbox_up);
	$('#'+downbtn_id).on('click', [field_id], event_listbox_down);

	$('#'+field_id).on('select', function (event) {
		var args = event.args;
		if (args) {
			var value = args.item.value;
			$('#'+image_id).html('<img height="'+height+'" width="'+img_width+'" src="'+value+'">');
		}
	});
}

function addItem_jqxListBox_name(p, init_val, render, height, width)
{
	width = width || 300;
	addItem_jqxListBox(p, init_val, render, height, width)
}

function addItem_jqxListBox(p, init_val, render, height, width)
{
	tabitem_id=p[0]; table_id=p[1]; field_id=p[2]; caption=p[3];
	width = width || 400; height = height || 90;
	render = render || render_null;

	var source = [];
	if (init_val instanceof Array) {
		for (var i in init_val) {
			var item = init_val[i];
			source.push({label: item, value: item});
		}
	}

	var input_id = field_id+'_input';
	var addbtn_id = field_id+'_add';
	var delbtn_id = field_id+'_del';
	var upbtn_id = field_id+'_up';
	var downbtn_id = field_id+'_down';
	var btn_width = 25;
	var btn_height = 25;

	$('#'+table_id).append([
		'<tr><td align="right" style="vertical-align:top;">'+caption+'：</td><td align="left">',
		'<div id="'+field_id+'"></div>',
		'<div><input type="text" id="'+input_id+'" />',
		'<input type="button" id="'+addbtn_id+'" value="✚" />',
		'<input type="button" id="'+delbtn_id+'" value="✖︎" />',
		'<input type="button" id="'+upbtn_id+'" value="⬆︎" />',
		'<input type="button" id="'+downbtn_id+'" value="⬇︎" />',
		'</div></td></tr>'
	].join(''));

	$('#'+field_id).jqxListBox({source: source, width: width, height: height});
	$('#'+input_id).jqxInput({width: width-btn_width*4, height: btn_height});
	$('#'+addbtn_id).jqxButton({width: btn_width, height:btn_height});
	$('#'+delbtn_id).jqxButton({width: btn_width, height:btn_height});
	$('#'+upbtn_id).jqxButton({width: btn_width, height:btn_height});
	$('#'+downbtn_id).jqxButton({width: btn_width, height:btn_height});

	$('#'+addbtn_id).on('click',[input_id,field_id,render], event_listbox_add);
	$('#'+delbtn_id).on('click', [field_id], event_listbox_delete);
	$('#'+upbtn_id).on('click', [field_id], event_listbox_up);
	$('#'+downbtn_id).on('click', [field_id], event_listbox_down);
}

function event_listbox_add(e) 
{
	var input_id = e.data[0];
	var listbox_id = e.data[1];
	var render = e.data[2];
	var input_str = $('#'+input_id).val();
	if (input_str.length > 0) {
		render([input_str, listbox_id], function (p) {
			var input_str=p[0], listbox_id=p[1], label=p[2], value=p[3];
			$('#'+listbox_id).jqxListBox('insertAt', {label: label, value: value}, 0); 
		});
		$('#'+input_id).val('');
	}
}

function event_listbox_delete(e) 
{
	var listbox_id = e.data[0];
	var index = $('#'+listbox_id).jqxListBox('getSelectedIndex'); 
	if (index >= 0) {
		$('#'+listbox_id).jqxListBox('removeAt',  index); 
	}
}

function event_listbox_up(e) 
{
	var listbox_id = e.data[0];
	var listbox = $('#'+listbox_id);
	var index = listbox.jqxListBox('getSelectedIndex'); 
	if (index > 0) {
		var item_up = cp(listbox.jqxListBox('getItem', index-1)); 
		var item_this = cp(listbox.jqxListBox('getItem', index)); 
		listbox.jqxListBox('updateAt', item_this, index-1);
		listbox.jqxListBox('updateAt', item_up, index);
		listbox.jqxListBox('selectIndex', index-1 ); 
	}
}

function event_listbox_down(e) 
{
	var listbox_id = e.data[0];
	var listbox = $('#'+listbox_id);
	var index = listbox.jqxListBox('getSelectedIndex'); 
	if (index !== -1) {
		var item_down = cp(listbox.jqxListBox('getItem', index+1)); 
		if (item_down.hasOwnProperty('label')) {
			var item_this = cp(listbox.jqxListBox('getItem', index)); 
			listbox.jqxListBox('updateAt', item_this, index+1);
			listbox.jqxListBox('updateAt', item_down, index);
			listbox.jqxListBox('selectIndex', index+1 ); 
		}
	}
}

function cp(old)
{
	return $.extend(true, {}, old);
}

function addItem_jqxComboBox(p, init_val, source, height, width)
{
	tabitem_id=p[0]; table_id=p[1]; field_id=p[2]; caption=p[3];
	width = width || 200; height = height || 25;
	source = source || []; 

	$('#'+table_id).append(['<tr><td align="right">'+caption+'：</td>',
		'<td align="left"><div id="'+field_id+'"></div></td></tr>'].join(''));
	$('#'+field_id).jqxComboBox({source: source, width: width, height: height});
	if (init_val) {
		$('#'+field_id).val(init_val);
	}
}

function addItem_jqxNumberInput(p, init_val, digits, symbol, height, width)
{
	tabitem_id=p[0]; table_id=p[1]; field_id=p[2]; caption=p[3];
	width = width || 200; height = height || 25;
	symbol = symbol || '';
	$('#'+table_id).append(['<tr><td align="right">'+caption+'：</td>',
		'<td align="left"><div id="'+field_id+'"></div></td></tr>'].join(''));
	$('#'+field_id).jqxNumberInput({symbolPosition:'right',symbol:symbol,min:0,decimalDigits:digits,width:width,height:height, inputMode:'simple',spinButtons:true});
	if (init_val) {
		$('#'+field_id).val(init_val);
	}
}

function addItem_jqxDateTimeInput(p, init_val, height, width)
{
	tabitem_id=p[0]; table_id=p[1]; field_id=p[2]; caption=p[3];
	width = width || 200; height = height || 25;
	$('#'+table_id).append(['<tr><td align="right">'+caption+'：</td>',
		'<td align="left"><div id="'+field_id+'"></div></td></tr>'].join(''));
	$('#'+field_id).jqxDateTimeInput({culture:'zh-CN', formatString: 'F', width:width, height:height});
}
