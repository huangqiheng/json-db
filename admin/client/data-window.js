
/******************************
 	编辑数据窗口 
******************************/

function init_tab(fields, container_id)
{
	var tabul_id = container_id+'_tabul';
	$('#'+container_id).append('<ul style="margin-left: 20px;" id="'+tabul_id+'"></ul>');

	for (var tab_name in fields) {
		var ids = get_tabtable_id(container_id, tab_name);
		var tab_id=ids[0], table_id=ids[1];
		$('#'+tabul_id).append('<li>'+tab_name+'</li>');
		$('#'+container_id).append('<div id="'+tab_id+'"><p><table id="'+table_id+'" style="margin:auto; font-size:12px;"></table></div>');
	}

	$('#'+container_id).jqxTabs({ 
		position: 'top', 
		width: '570', 
		scrollable:false,
		selectedItem: 1 
	});
}

function edit_data_window(title, input, cb_done)
{
	var schema = input[0], old_data=input[1], options=input[2];
	var fields = schema.fields;
	var schema_options = schema.options;
	var initials = schema.initials;

	schema_options || (schema_options = []);
	initials || (initials = []);

	var seed = new Date().getTime();
	var window_id = 'win_'+seed.toString(); 
	var ok_id = window_id+'_ok';
	var no_id = window_id+'_no';
	var collapse_id = window_id+'_col';
	var notify_id = window_id+'_notify';
	var tabs_id = window_id+'_tab';
	var header_id = window_id+'_header';
	var btn_width = 50;
	var this_windows_class = 'edit_data_windows';

	$('body').append([
            '<div id="'+window_id+'" class="'+this_windows_class+'">',
                '<div id="'+header_id+'">',
		    '<table style="width:100% !important;"><tr><td>',
                    '<img width="20" height="20" src="images/new-table.png" alt="" style="float:left;" />',
		    '</td><td>',
		    	'<strong style="font-size:13px;line-height:23px;">'+title+'</strong>',
		    '</td><td align="right">',
			    '<div style="float:right;">',
				'<input type="button" id="'+ok_id+'" value="'+T('OK')+'" style="margin-right: 1px" />',
				'<input type="button" id="'+no_id+'" value="'+T('Cancel')+'" />',
				'<input type="button" id="'+collapse_id+'" value="'+T('Collapse')+'" />',
			    '</div>',
		    '</td></tr></table>',
		'</div>',
                '<div>',
                    '<div align="center"><table>',
			'<tr><td vlign="bottom">',
			    '<div id="'+notify_id+'" style="color:red; text-align:center;"></div>',
			'</td></tr>',
		        '<tr><td><div id="'+tabs_id+'" style="margin:auto;"></div></td></tr>',
                    '</table></div>',
                '</div>',
            '</div>'].join(''));

	var show_notify = function(msg){
		env.popup(T('ERROR'), T(msg));
	};

	var win_count = $('.'+this_windows_class).length;

	$('#'+window_id).jqxWindow({
			height: 555, width: 'auto',
			position: {x:win_count*30, y:12+win_count*41},
			minHeight: 450, minWidth: 600,
			maxHeight: 1000,
			resizable: true,  
			modalOpacity: 0.3,
			showCollapseButton: false,
			showCloseButton: false,
			cancelButton: $('#'+no_id),
			initContent: init_component
	});

	$('#'+window_id).on('close', function (event) {  
		if (event.target.id === window_id) {
			$('#'+window_id).jqxWindow('destroy'); 
		}
	}); 

	$('#'+header_id+' div:first').attr('style', 'width:100% !important');

	function init_component()
	{
		$('#'+ok_id).jqxButton({width: btn_width, height:23});
		$('#'+no_id).jqxButton({width: btn_width, height:23});
		$('#'+ok_id).on('click', function(e){
			grep_data(function(new_data){
				cb_done(new_data);
				$('#'+window_id).jqxWindow('close');
			});
		});

		$('#'+collapse_id).jqxButton({width: btn_width, height:23});
		$('#'+collapse_id).on('click', function(e){
			if ($('#'+window_id).jqxWindow('collapsed')) {
				$('#'+window_id).jqxWindow('expand');
				$('#'+collapse_id).jqxButton('val', T('Collapse'));
			} else {
				$('#'+window_id).jqxWindow('collapse');
				$('#'+collapse_id).jqxButton('val', T('Expand'));
			}
		});

		init_data_component();
	}

	function grep_data(fn_done)
	{
		var res_data = {};
		var name_fields = [];
		var tab_index = -1;
		for (var tab_name in fields) {
			tab_index++;
			res_data[tab_name] = {};
			var val_obj = fields[tab_name];
			var ids = get_tabtable_id(tabs_id, tab_name);
			var tab_id=ids[0], table_id=ids[1];
			for (var field_name in val_obj) {
				var field_value = val_obj[field_name];
				var field_id = get_tableitem_id(window_id, tab_name, field_name);
				var p = [tab_id, table_id, field_id, field_name];
				var res_val = '';

				do{
				if (field_value === 'jqxInput') 	   	   {res_val=getval_jqxInput(p);break;}
				if (field_value === 'jqxInput-id') 	   	   {res_val=getval_jqxInput_id(p);break;}
				if (field_value === 'jqxInput-time') 	   	   {res_val=getval_jqxInput_time(p);break;}
				if (field_value === 'jqxInput-text') 	   	   {res_val=getval_jqxInput_text(p);break;}
				if (field_value === 'jqxInput-text-json') 	   {res_val=getval_jqxInput_text_json(p);break;}
				if (field_value === 'jqxInput-name') 	   	   {res_val=getval_jqxInput_name(p);break;}
				if (field_value === 'jqxCheckBox')    	   	   {res_val=getval_jqxCheckBox(p);break;}
				if (field_value === 'jqxRadioButton')	   	   {res_val=getval_jqxRadioButton(p);break;}
				if (field_value === 'jqxListBox') 	   	   {res_val=getval_jqxListBox(p);break;}
				if (field_value === 'jqxListBox-name') 	   	   {res_val=getval_jqxListBox_name(p);break;}
				if (field_value === 'jqxListBox-onebox-url')  	   {res_val=getval_jqxListBox_onebox_url(p);break;}
				if (field_value === 'jqxListBox-onebox-id')   	   {res_val=getval_jqxListBox_onebox_id(p);break;}
				if (field_value === 'jqxListBox-onebox-id-same')   {res_val=getval_jqxListBox_onebox_id(p);break;}
				if (field_value === 'jqxListBox-images')   	   {res_val=getval_jqxListBox_images(p);break;}
				if (field_value === 'jqxComboBox') 	   	   {res_val=getval_jqxComboBox(p);break;}
				if (field_value === 'jqxNumberInput') 	   	   {res_val=getval_jqxNumberInput(p);break;}
				if (field_value === 'jqxNumberInput-size') 	   {res_val=getval_jqxNumberInput(p);break;}
				if (field_value === 'jqxNumberInput-price')	   {res_val=getval_jqxNumberInput(p);break;}
				if (field_value === 'jqxDateTimeInput')    	   {res_val=getval_jqxDateTimeInput(p);break;}
				}while(false);
				res_data[tab_name][field_name] = res_val;

				if (field_value === 'jqxInput-name') {
					name_fields.push([tab_index, field_id, res_val]);
				}
			}
		}

		if ((old_data===null) && (name_fields.length)) {
			var name_attr = name_fields[0];
			check_name_valid(name_attr[2], function(url){
				if (url === null) {
					fn_done(res_data);
				} else {
					$('#'+tabs_id).jqxTabs('select', name_attr[0]);
					$('#'+name_attr[1]).select();
				}
			});
		} else {
			fn_done(res_data);
		}
	}

	window.retrieve_p=function (src_p, tab_name, field_name, field_value) {
		var old_tab_id = src_p[0];
		var window_id = old_tab_id.match(/win_\d+/)[0];
		var tabs_id = old_tab_id.match(/win_\d+_tab/)[0];

		var ids = get_tabtable_id(tabs_id, tab_name);
		var tab_id=ids[0], table_id=ids[1];

		var field_id = get_tableitem_id(window_id, tab_name, field_name);
		var p = [tab_id, table_id, field_id, field_name, src_p[4]];
		return p;
	};

	function init_data_component()
	{
		init_tab(fields, tabs_id);
		var fill_data = merge_fields(old_data);

		for (var tab_name in fields) {
			var val_obj = fields[tab_name];
			var ids = get_tabtable_id(tabs_id, tab_name);
			var tab_id=ids[0], table_id=ids[1];
			for (var field_name in val_obj) {
				var field_value = val_obj[field_name];
				var field_id = get_tableitem_id(window_id, tab_name, field_name);
				var p = [tab_id, table_id, field_id, field_name, schema];

				//设置初始化值
				var init_value = null;
				if (fill_data.hasOwnProperty(field_name)) {
					init_value = fill_data[field_name];
				}

				var is_share = false;
				var is_readonly = false;
				var is_vital = false;
				var tips = null;
				var suffix_value = null;

				for(var i=0; i<initials.length; i++) {
					var item = initials[i];
					if (item.field === field_name) {
						init_value || (init_value = item.value);
						suffix_value = item.suffix;
						is_share = (item.share==='true');
						is_readonly = (item.readonly==='true');
						is_vital = (item.vital ==='true');
						tips = item.tips;
						break;
					}
				}
				init_val = [init_value,is_share,is_readonly,tips,is_vital,suffix_value];

				//设置选项值,先加入固定的选项
				var option_val = [];
				var is_fixed = false;
				var url_options = [];
				for(var i=0; i<schema_options.length; i++) {
					var item = schema_options[i];
					if (item.field === field_name) {
						for(var k=0; k<item.value.length; k++) {
							var item_val = item.value[k];
							if (item_val.match(/\/admin\/options\.php\?/g)) {
								url_options.push(item_val);
							} else {
								option_val.push(item_val);
							}
						}

						is_fixed = (item.fixed === 'true');
						break;
					}
				}

				//从其他表中，自动生成选项值
				if (url_options.length > 0) {
					if (field_value === 'jqxCheckBox') {addOptions_jqxCheckBox(p, init_val, url_options);}
					if (field_value === 'jqxRadioButton') {addOptions_jqxRadioButton(p, init_val, url_options);}
					if (field_value === 'jqxComboBox') {addOptions_jqxComboBox(p, init_val, url_options);}
				}

				//追加动态的选项值
				if ((options) && (!is_fixed) && (options.hasOwnProperty(field_name))) {
					option_val = option_val.concat(options[field_name]);
				}

				if (field_value === 'jqxInput') 	{addItem_jqxInput(p, init_val);continue;}
				if (field_value === 'jqxInput-id') 	{addItem_jqxInput_id(p, init_val);continue;}
				if (field_value === 'jqxInput-time') 	{addItem_jqxInput_time(p, init_val);continue;}
				if (field_value === 'jqxInput-text') 	{addItem_jqxInput_text(p, init_val);continue;}
				if (field_value === 'jqxInput-text-json') 	{addItem_jqxInput_text_json(p, init_val);continue;}
				if (field_value === 'jqxInput-name') 		{addItem_jqxInput_name(p, init_val);continue;}
				if (field_value === 'jqxCheckBox')      	{addItem_jqxCheckBox(p, init_val, option_val);continue;}
				if (field_value === 'jqxRadioButton')   	{addItem_jqxRadioButton(p, init_val, option_val);continue;}
				if (field_value === 'jqxListBox') 		{addItem_jqxListBox(p, init_val);continue;}
				if (field_value === 'jqxListBox-name') 		{addItem_jqxListBox_name(p, init_val);continue;}
				if (field_value === 'jqxListBox-onebox-url')	{addItem_jqxListBox_onebox_url(p, init_val);continue;}
				if (field_value === 'jqxListBox-onebox-id')	{addItem_jqxListBox_onebox_id(p, init_val);continue;}
				if (field_value === 'jqxListBox-onebox-id-same'){addItem_jqxListBox_onebox_id_same(p, init_val);continue;}
				if (field_value === 'jqxListBox-images')	{addItem_jqxListBox_images(p, init_val);continue;}
				if (field_value === 'jqxComboBox') 		{addItem_jqxComboBox(p, init_val, option_val);continue;}
				if (field_value === 'jqxNumberInput') 		{addItem_jqxNumberInput(p, init_val, 0);continue;}
				if (field_value === 'jqxNumberInput-size') 	{addItem_jqxNumberInput(p, init_val, 2,' MB');continue;}
				if (field_value === 'jqxNumberInput-price') 	{addItem_jqxNumberInput(p, init_val, 2);continue;}
				if (field_value === 'jqxDateTimeInput') 	{addItem_jqxDateTimeInput(p, init_val);continue;}
			}
		}
	}
}

function getval_jqxComboBox(p)
{
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3], schema=[4];
	return $('#'+field_id).jqxComboBox('val');
}

function combobox_readonly(field_id)
{
	$('#'+field_id+' input.jqx-widget-content').prop('disabled', true); 
	$('#'+field_id+' input.jqx-widget-content').css('background-color', 'inherit'); 
	$('#'+field_id+' input.jqx-widget-content').parent().css('background-color', 'rgb(250, 250, 250)'); 
}

function addItem_jqxComboBox(p, init_val, source, height, width)
{
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3], schema=[4];
	var init_value=init_val[0], is_share=init_val[1], is_readonly=init_val[2], tips=init_val[3], is_vital=init_val[4], suffix_value=init_val[5];
	width = width || 200; height = height || 25;
	source = source || []; 

	$('#'+table_id).append(['<tr><td align="right">'+caption+': </td>',
		'<td align="left"><div style="float:left;" id="'+field_id+'"></div>',
		(suffix_value)? '<div style="float:left;margin-top:6px;">'+suffix_value+'</div>' : '',
		'</td></tr>'].join(''));
	$('#'+field_id).jqxComboBox({
		autoDropDownHeight:true, 
		searchMode: 'equals',
		source: source, width: width, height: height});
	if (init_value) {
		$('#'+field_id).val(init_value);
	}
	if (is_readonly) {
		combobox_readonly(field_id);
	}
}

function getval_jqxNumberInput_size(p){return getval_jqxNumberInput(p);}
function getval_jqxNumberInput_price(p){return getval_jqxNumberInput(p);}
function getval_jqxNumberInput(p)
{
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3], schema=[4];
	return $('#'+field_id).jqxNumberInput('val');
}

function addItem_jqxNumberInput(p, init_val, digits, symbol, height, width)
{
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3], schema=[4];
	var init_value=init_val[0], is_share=init_val[1], is_readonly=init_val[2], tips=init_val[3], is_vital=init_val[4], suffix_value=init_val[5];
	width = width || 200; height = height || 25;
	symbol = symbol || '';
	$('#'+table_id).append(['<tr><td align="right">'+caption+': </td>',
		'<td align="left"><div id="'+field_id+'"></div></td></tr>'].join(''));
	$('#'+field_id).jqxNumberInput({symbolPosition:'right',symbol:symbol,min:0,decimalDigits:digits,width:width,height:height, inputMode:'simple',spinButtons:true});
	if (init_value) {
		$('#'+field_id).val(init_value);
	}
}

function getval_jqxDateTimeInput(p)
{
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3], schema=[4];
	var date_obj = $('#'+field_id).jqxDateTimeInput('getDate');
	return date_obj;
}

function addItem_jqxDateTimeInput(p, init_val, height, width)
{
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3], schema=[4];
	var init_value=init_val[0], is_share=init_val[1], is_readonly=init_val[2], tips=init_val[3], is_vital=init_val[4], suffix_value=init_val[5];
	width = width || 200; height = height || 25;
	$('#'+table_id).append(['<tr><td align="right">'+caption+': </td>',
		'<td align="left"><div id="'+field_id+'"></div></td></tr>'].join(''));

	$('#'+field_id).jqxDateTimeInput({culture:'zh-CN', formatString: 'D', width:width, height:height});
	if (init_value) {
		$('#'+field_id).jqxDateTimeInput('setDate', init_value);
	}
}

function addItem_jqxInput_id(p, id, width, height)
{
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3], schema=[4];
	width = width || 200;
	addItem_jqxInput(p, id, [], height, width);
	$('#'+field_id).jqxInput({disabled: true});
}

function addItem_jqxInput_name(p, init_val, source, height, width)
{
	width = width || 300;
	addItem_jqxInput(p, init_val, source, height, width);
}

function addItem_jqxInput_time(p, init_val, source, height, width)
{
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3], schema=[4];
	var init_value=init_val[0], is_share=init_val[1], is_readonly=init_val[2], tips=init_val[3], is_vital=init_val[4], suffix_value=init_val[5];
	if (caption === 'TIME') {
		init_value = new Date().toUTCString();
	}
	if (caption === 'CREATE') {
		if (!init_value) {
			init_value = new Date().toUTCString();
		}
	}
	init_val[0] = init_value;
	addItem_jqxInput(p, init_val, source, height, width);
	$('#'+field_id).jqxInput({disabled: true});
}


function addItem_jqxInput_text_json(p, init_val, source, height, width)
{
	var init_value=init_val[0], is_share=init_val[1], is_readonly=init_val[2], tips=init_val[3], is_vital=init_val[4], suffix_value=init_val[5];
	if (!init_value) {
		init_value = {};
	}
	init_val[0] = json_encode(init_value);
	return addItem_jqxInput_text(p, init_val, source, height, width);
}

function addItem_jqxInput_text(p, init_val, source, height, width)
{
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3], schema=[4];
	var init_value=init_val[0], is_share=init_val[1], is_readonly=init_val[2], tips=init_val[3], is_vital=init_val[4], suffix_value=init_val[5];
	width = width || 400; height = height || 50;
	source = source || []; 

	var html = '<tr><td align="right">'+caption+': </td>';
	html += '<td align="left"><textarea id="'+field_id+'"></textarea></td></tr>';
	$('#'+table_id).append(html);
	$('#'+field_id).jqxInput({width: width, height: height, source:source, placeHolder:tips});
	if (init_value) {
		$('#'+field_id).val(init_value);
	}
}

function addItem_jqxInput(p, init_val, source, height, width)
{
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3], schema=[4];
	var init_value=init_val[0], is_share=init_val[1], is_readonly=init_val[2], tips=init_val[3], is_vital=init_val[4], suffix_value=init_val[5];
	width = width || 300; height = height || 23;
	source = source || []; 

	$('#'+table_id).append(['<tr><td align="right">'+caption+': </td>',
		'<td align="left"><input style="float:left;" type="text" id="'+field_id+'"/>',
		(suffix_value)? '<div style="float:left;margin-top:5px;" >'+suffix_value+'</div>' : '',
		'</td></tr>'].join(''));
	$('#'+field_id).jqxInput({disabled:is_readonly, width: width, height: height, source:source, placeHolder:tips});
	if (init_value) {
		$('#'+field_id).val(init_value);
	}
}

function getval_jqxInput_text_json(p)
{
	var res = getval_jqxInput(p);
	if (res === '') {
		return {};
	}
	return json_decode(res);
}

function getval_jqxInput_id(p){return getval_jqxInput(p);}
function getval_jqxInput_time(p){return getval_jqxInput(p);}
function getval_jqxInput_name(p){return getval_jqxInput(p);}
function getval_jqxInput_text(p){return getval_jqxInput(p);}
function getval_jqxInput(p)
{
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3], schema=[4];
	return $('#'+field_id).jqxInput('val');
}

function render_null(data, finish)
{
	data.push(data[0]);
	data.push(data[0]);
	finish(data);
}

function render_onebox_url_html(url, image, title, desc)
{
	var content = desc.substring(0,82);
	var html = ['<table style="width:371px; height:80px; font-size:12"><tbody>',
	    '<tr>',
	    '<td rowspan="2"><a href="'+url+'" target="_blank"><img height="80" width="80" src="'+image+'"></a></td>',
	    '<td><strong>'+title+'</strong></td>',
	    '</tr><tr>',
	    '<td>'+content+'</td>',
	    '</tr></tbody></table>'].join('');
	return html;
}


function render_onebox_id(data, finish)
{
	jsonp(env.jsondb_root + '/service/onebox/name.php', {
		name:data[0], 
		db_name: get_db_name(),
		table_name: get_table_name(),
		type:'json'
	}, function(d){
		if (d.status === 'ok') {
			var html = render_onebox_url_html(d.ori_url, d.image, d.title, d.description);

			var value = {
				title: d.title,
				desc: d.description,
				image: d.image,
				url: d.ori_url,
				id: d.ID,
				time: d.update_time,
				ctime: d.create_time
			};

			data.push(html);
			data.push(value);
			finish(data);
		} else {
			env.popup(T('ERROR'), T('Maybe you input name is invalid.'));
		}
	});
}

function render_onebox_url(data, finish)
{
	jsonp(env.jsondb_root + '/service/onebox/url.php', 
		{url:data[0], type:'json'}, function(d){
		if (d.status === 'ok') {
			var html = render_onebox_url_html(d.ori_url, d.image, d.title, d.description);

			var value = {
				title: d.title,
				desc: d.description,
				image: d.image,
				url: d.ori_url,
				id: d.ID,
				time: d.update_time,
				ctime: d.create_time
			};

			data.push(html);
			data.push(value);
			finish(data);
		} else {
			env.popup(T('ERROR'), T('Maybe you input url is invalid.'));
		}
	});
}

function addItem_jqxListBox_images(p, init_val, height, width)
{
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3], schema=[4];
	var init_value=init_val[0], is_share=init_val[1], is_readonly=init_val[2], tips=init_val[3], is_vital=init_val[4], suffix_value=init_val[5];
	width = width || 400; height = height || 250;
	var img_width = 332;

	var url2img=function(url){
		return '<img height="50" width="50" src="'+url+'">';
	};

	var source = [];
	var enq_source = function(init_arr){
		for (var i in init_arr) {
			var item = init_arr[i];
			var html = url2img(item.trim());
			source.push({html:html, label:item, value:item});
		}
	};

	if (init_value) {
		if (init_value instanceof Array) {
			enq_source(init_value);
		} else {
			var init_values = init_value.split(',');
			enq_source(init_value.split(','));
		}
	}

	var render = function (data, finish)
	{
		var image_url = data[0];
		data.push(url2img(image_url));
		data.push(image_url);
		finish(data);
	};

	var image_id = field_id+'_image';
	var input_id = field_id+'_input';
	var addbtn_id = field_id+'_add';
	var delbtn_id = field_id+'_del';
	var upbtn_id = field_id+'_up';
	var downbtn_id = field_id+'_down';
	var btn_width = 22;
	var btn_height = 22;

	$('#'+table_id).append([
		'<tr><td align="right" style="vertical-align:top;">'+caption+': </td><td align="left" >',
		'<table border="0" style="border-spacing:0px;" ><tbody><tr><td style="padding:0px;">',
		'<div id="'+field_id+'" style="float:left;"></div>',
		'<div id="'+image_id+'" style="float:left; width:'+img_width+'px; height: '+height+'px; background-color:rgb(241, 241, 241);"></div>',
		'</td></tr><tr><td style="padding:0px;">',
		'<div><input type="text" id="'+input_id+'" style="float:left;" />',
		'<div id="'+addbtn_id+'" style="padding:0px;float:left;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/add.png"/></div>',
		'<div id="'+delbtn_id+'" style="padding:0px;float:left;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/delete.png"/></div>',
		'<div id="'+upbtn_id+'" style="padding:0px;float:left;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/up.png"/></div>',
		'<div id="'+downbtn_id+'" style="padding:0px;float:left;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/down.png"/></div>',
		'</td></tr></tbody></table>',
		'</td></tr>'
	].join(''));

	$('#'+field_id).jqxListBox({scrollBarSize:5, source: source, width: width-img_width, height: height});
	$('#'+input_id).jqxInput({disabled:is_readonly,width: width-(btn_width+2)*4, height: btn_height, placeHolder:tips});
	$('#'+addbtn_id).jqxButton({disabled:is_readonly,width: btn_width, height:btn_height});
	$('#'+delbtn_id).jqxButton({disabled:is_readonly,width: btn_width, height:btn_height});
	$('#'+upbtn_id).jqxButton({disabled:is_readonly,width: btn_width, height:btn_height});
	$('#'+downbtn_id).jqxButton({disabled:is_readonly,width: btn_width, height:btn_height});

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
	$('#'+field_id).jqxListBox('selectIndex', 0 ); 
}


function addItem_jqxListBox_onebox_id(p, init_val, render, height, width)
{
	render = render || render_onebox_id;
	height = height || 250;
	addItem_jqxListBox(p, init_val, render, height, width);
}

function addItem_jqxListBox_onebox_id_same(p, init_val, render, height, width)
{
	var schema = p[4];
	var initials = schema.initials || [];
	var fields = schema.fields || [];
	render = render || render_onebox_id;

	function fill_share(group, caption, type, value) {
		var new_p = retrieve_p(p, group, caption, value);
		if (type === 'jqxInput') {
			var field_id = new_p[2];
			$('#'+field_id).val(value);
		}
		if (type === 'jqxCheckBox') {
			for(var j=0; j<value.length; j++) {
				add_checkbox_item(new_p, value[j]);
			}
		}
		if (type === 'jqxRadioButton') {
			add_radiobtn_item(new_p, value);
		}
	}

	var render_hooker = function(data, finish) {
		render(data, function(data) {
			finish(data);
			var data_url = data[3]['url'];
			json(data_url, function(d){
				var field_types = merge_fields(fields);
				for(var i=0; i<initials.length; i++) {
					var item = initials[i];
					if (item.share !== 'true') {
						continue;
					}
					for(var group_name in d) {
						var items = d[group_name];
						for(var field_name in items) {
							if (field_name !== item.field) {
								continue;
							}
							var field_value = items[field_name];
							var field_type = field_types[field_name];
							fill_share(group_name, field_name, field_type, field_value);
						}
					}

				}
			});
		});
	};
	addItem_jqxListBox_onebox_id(p, init_val, render_hooker, height, width);
}

function addItem_jqxListBox_name(p, init_val, render, height, width)
{
	width = width || 300;
	p.push(check_name_valid);
	addItem_jqxListBox(p, init_val, render, height, width);
}

function addItem_jqxListBox_onebox_url(p, init_val, render, height, width)
{
	render = render || render_onebox_url;
	height = height || 250;
	addItem_jqxListBox(p, init_val, render, height, width);
}

function getval_jqxListBox_name(p){return getval_jqxListBox(p);}
function getval_jqxListBox_onebox_url(p){return getval_jqxListBox(p);}
function getval_jqxListBox_onebox_id(p){return getval_jqxListBox(p);}
function getval_jqxListBox_images(p){return getval_jqxListBox(p);}
function getval_jqxListBox(p)
{
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3], schema=[4];
	var res_objs = [];
	var items = $('#'+field_id).jqxListBox('getItems'); 
	for (var index in items) {
		var item = items[index];
		res_objs.push(item.value);
	}
	return res_objs;
}

function getval_jqxRadioButton(p, only_checked)
{
	if (only_checked===undefined) only_checked=true;
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3], schema=[4];
	var tr_class = field_id+'_trclass';
	var td_class = field_id+'_tdclass';
	var radiobtn_class = field_id+'_radiobtn_class';
	var radio_objs = $('table#'+table_id+' tr.'+tr_class+' td.'+td_class+' div.'+radiobtn_class);

	if (only_checked) {
		var res_val = '';
		for(var i=0; i<radio_objs.length; i++) {
			var td_item = radio_objs[i];
			var checked = $(td_item).jqxRadioButton('checked');
			if (checked) {
				var caption = $(td_item)[0].textContent;
				return caption;
			}
		}
		return res_val;
	} else {
		var res_arr = [];
		for(var i=0; i<radio_objs.length; i++) {
			var td_item = radio_objs[i];
			var caption = $(td_item)[0].textContent;
			res_arr.push(caption);
		}
		return res_arr;
	}
}

function getid_jqxRadioButton(p)
{
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3], schema=[4];

	var input_id = field_id+'_input';
	var sub_table_id = field_id+'_table';
	var tr_class = field_id+'_trclass';
	var td_class = field_id+'_tdclass';
	var group = field_id+'_group';
	var addbtn_id = field_id+'_add';
	var tips_id = field_id+'_tips';
	var radiobtn_class = field_id+'_radiobtn_class';
	return [input_id,sub_table_id,tr_class,td_class,group,addbtn_id,tips_id,radiobtn_class];
}

function addItem_jqxRadioButton(p, init_val, options, width)
{
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3], schema=[4];
	var init_value=init_val[0], is_share=init_val[1], is_readonly=init_val[2], tips=init_val[3], is_vital=init_val[4], suffix_value=init_val[5];
	var ids=getid_jqxRadioButton(p);
	var input_id=ids[0],sub_table_id=ids[1],tr_class=ids[2],td_class=ids[3],group=ids[4],addbtn_id=ids[5],tips_id=ids[6],radiobtn_class=ids[7];

	var btn_width = 22;
	var btn_height = 22;
	width = width || 200;
	width = width - btn_width - 6;

	$('#'+table_id).append([
		'<tr><td align="right" style="vertical-align:top;">'+caption+': </td><td align="left" >',
		'<div style="border: 1px solid rgb(224, 224, 224);">',
		    '<table width="100%"><tr><td>',
			'<table id="'+sub_table_id+'" border="0" style="width:100%; border-spacing:5px;" >',
			'</table>',
		    '</td><tr><td>',
			'<div id="'+addbtn_id+'" style="padding:0px;float:right;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/add.png"/></div>',
			'<input type="text" id="'+input_id+'" style="float:right;" />',
		        '<div id="'+tips_id+'" style="padding:3px;float:right;font-size:12px;color:gray;"></div>',
		    '</td></tr></table>',
		'</div>',
		'</td></tr>'
	].join(''));

	if ((typeof tips ==='string') && (tips.length > 1)) {
		$('#'+tips_id).text(tips);
	}
	$('#'+input_id).jqxInput({disabled:is_readonly, width: width-(btn_width+2)*4, height: btn_height, rtl:true});
	$('#'+addbtn_id).jqxButton({width: btn_width, height:btn_height});

	if (options instanceof Array) {
		for (var i in options) {
			var item = options[i];
			var checked = (init_value === item);
			add_radiobtn_item(p, item, group,checked);
		}
	}

	$('#'+addbtn_id).on('click', function(e){
		var input_caption = $('#'+input_id).val();
		if (input_caption != '') {
			add_radiobtn_item(p, input_caption, group);
			$('#'+input_id).val('');
		}
	});

}

function add_radiobtn_item(p, caption, group, checked)
{
	if (checked === undefined) {
		checked = true;
	}
	if (group === undefined) {
		group = '';
	}

	var ids=getid_jqxRadioButton(p);
	var input_id=ids[0],sub_table_id=ids[1],tr_class=ids[2],td_class=ids[3],group=ids[4],addbtn_id=ids[5],tips_id=ids[6],radiobtn_class=ids[7];

	//如果该值已经存在，则略过添加，只需勾选即可
	var now_has = getval_jqxRadioButton(p, false);
	for (var i in now_has) {
		if (now_has[i]=== caption) {
			var radio_objs = $('table#'+sub_table_id+' tr.'+tr_class+' td.'+td_class+' div.'+radiobtn_class);
			for(var k=0; k<radio_objs.length; k++) {
				var radio_elmt = radio_objs[k];
				if (radio_elmt) {
					var radio_text = $(radio_elmt).text();
					if (radio_text === caption) {
						$(radio_elmt).jqxRadioButton({groupName:group, checked: true});
						break;
					}
				}
			}

			$('#'+input_id).focus();
			return;
		}
	}

	var item_elmt = '<div class="'+radiobtn_class+'" style="float: left;">'+caption+'</div>';
	var td_elmt = '<td class="'+td_class+'" style="padding:0px;">'+item_elmt+'</td>';

	var tr_objs = $('table#'+sub_table_id+' tr.'+tr_class);
	var td_objs = $('table#'+sub_table_id+' tr.'+tr_class+' td.'+td_class);
	var mod_val = parseInt(td_objs.length % 3);

	if ((mod_val === 0) || isNaN(mod_val)) {
		$('table#'+sub_table_id).append('<tr class="'+tr_class+'"></tr>');
		tr_objs = $('table#'+sub_table_id+' tr.'+tr_class);
	}

	var tr_elmt = tr_objs[tr_objs.length-1];
	$(tr_elmt).append(td_elmt);
	var radio_objs = $('table#'+sub_table_id+' tr.'+tr_class+' td.'+td_class+' div.'+radiobtn_class);
	$(radio_objs[radio_objs.length-1]).jqxRadioButton({groupName:group, checked: checked});
}

function addOptions_jqxComboBox(p, init_val, url_options)
{

}

function addOptions_jqxRadioButton(p, init_val, url_options)
{
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3], schema=[4];
	var init_value=init_val[0], is_share=init_val[1], is_readonly=init_val[2], tips=init_val[3], is_vital=init_val[4], suffix_value=init_val[5];
	var ids=getid_jqxRadioButton(p);
	var input_id=ids[0],sub_table_id=ids[1],tr_class=ids[2],td_class=ids[3],group=ids[4],addbtn_id=ids[5],tips_id=ids[6],radiobtn_class=ids[7];

	for(var i in url_options) {
		var url = url_options[i];
		jsonp(url, {}, function(d){
			if (d.status !== 'ok') {return;}
			if (d.count == 0) {return;}

			for(var k in d.items){
				var item = d.items[k];

				var checked = false;
				if (init_value instanceof Array) {
					checked = (init_value.indexOf(item) !== -1);
				} else {
					checked = (item === init_value);
				}
				add_radiobtn_item(p, item, group,checked);
			}
		});
	}
}

function addOptions_jqxCheckBox(p, init_val, url_options)
{
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3], schema=[4];
	var init_value=init_val[0], is_share=init_val[1], is_readonly=init_val[2], tips=init_val[3], is_vital=init_val[4], suffix_value=init_val[5];
	var ids = getid_jqxCheckBox(p);
	var input_id=ids[0], sub_table_id=ids[1], tr_class=ids[2], td_class=ids[3], checkbox_class=ids[4];

	for(var i in url_options) {
		var url = url_options[i];
		jsonp(url, {}, function(d){
			if (d.status !== 'ok') {return;}
			if (d.count == 0) {return;}

			for(var k in d.items){
				var item = d.items[k];

				var checked = false;
				if (init_value instanceof Array) {
					checked = (init_value.indexOf(item) !== -1);
				} else {
					checked = (item === init_value);
				}
				add_checkbox_item(p, item, checked);
			}
		});
	}

}

function getid_jqxCheckBox(p)
{
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3], schema=[4];
	var input_id = field_id+'_input';
	var sub_table_id = field_id+'_table';
	var tr_class = field_id+'_trclass';
	var td_class = field_id+'_tdclass';
	var checkbox_class = field_id+'_checkbox_class';
	var tips_id = field_id+'_tips';
	var addbtn_id = field_id+'_add';
	return [input_id,sub_table_id,tr_class,td_class,checkbox_class,tips_id,addbtn_id];
}

function addItem_jqxCheckBox(p, init_val, options, width)
{
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3], schema=[4];
	var init_value=init_val[0], is_share=init_val[1], is_readonly=init_val[2], tips=init_val[3], is_vital=init_val[4], suffix_value=init_val[5];
	var ids = getid_jqxCheckBox(p);
	var input_id=ids[0], sub_table_id=ids[1], tr_class=ids[2], td_class=ids[3], checkbox_class=ids[4], tips_id=ids[5], addbtn_id=ids[6];
	var btn_width = 22;
	var btn_height = 22;
	width = width || 200;
	width = width - btn_width - 6;

	$('#'+table_id).append([
		'<tr><td align="right" style="vertical-align:top;">'+caption+': </td><td align="left" >',
		'<div style="border: 1px solid rgb(224, 224, 224);">',
		    '<table width="100%"><tr><td>',
			'<table id="'+sub_table_id+'" border="0" style="width:100%; border-spacing:5px;" >',
			'</table>',
		    '</td><tr><td>',
			'<div id="'+addbtn_id+'" style="padding:0px;float:right;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/add.png"/></div>',
			'<input type="text" id="'+input_id+'" style="float:right;" />',
		        '<div id="'+tips_id+'" style="padding:3px;float:right;font-size:12px;color:gray;"></div>',
		    '</td></tr></table>',
		'</div>',
		'</td></tr>'
	].join(''));

	if ((typeof tips ==='string') && (tips.length > 1)) {
		$('#'+tips_id).text(tips);
	}
	$('#'+input_id).jqxInput({disabled:is_readonly, width: width-(btn_width+2)*4, height: btn_height, rtl:true});
	$('#'+addbtn_id).jqxButton({disabled:is_readonly, width: btn_width, height:btn_height});

	if (options instanceof Array) {
		for (var i in options) {
			var item = options[i];
			var checked = false;
			if (init_value instanceof Array) {
				checked = (init_value.indexOf(item) !== -1);
			} else {
				checked = (item === init_value);
			}
			add_checkbox_item(p, item, checked);
		}
	}

	$('#'+addbtn_id).on('click', function(e){
		var input_caption = $('#'+input_id).val();
		if (input_caption != '') {
			add_checkbox_item(p, input_caption);
			$('#'+input_id).val('');
		}
	});

}

function add_checkbox_item(p, caption, checked)
{
	var ids = getid_jqxCheckBox(p);
	var input_id=ids[0], sub_table_id=ids[1], tr_class=ids[2], td_class=ids[3], checkbox_class=ids[4];

	if (checked === undefined) {
		checked = true;
	}

	//如果该值已经存在，则略过添加，只需勾选即可
	var now_has = getval_jqxCheckBox(p, false);
	for (var i in now_has) {
		if (now_has[i]=== caption) {
			var checkb_objs = $('table#'+sub_table_id+' tr.'+tr_class+' td.'+td_class+' div.'+checkbox_class);
			for(var k=0; k<checkb_objs.length; k++) {
				var checkb_elmt = checkb_objs[k];
				if (checkb_elmt) {
					var radio_text = $(checkb_elmt).text();
					if (radio_text === caption) {
						$(checkb_elmt).jqxCheckBox({checked: true});
						break;
					}
				}
			}

			$('#'+input_id).focus();
			return;
		}
	}

	var item_elmt = '<div class="'+checkbox_class+'" style="float: left;">'+caption+'</div>';
	var td_elmt = '<td class="'+td_class+'" style="padding:0px;">'+item_elmt+'</td>';

	var tr_objs = $('table#'+sub_table_id+' tr.'+tr_class);
	var td_objs = $('table#'+sub_table_id+' tr.'+tr_class+' td.'+td_class);
	var mod_val = parseInt(td_objs.length % 3);

	if ((mod_val === 0) || isNaN(mod_val)) {
		$('table#'+sub_table_id).append('<tr class="'+tr_class+'"></tr>');
		tr_objs = $('table#'+sub_table_id+' tr.'+tr_class);
	}

	var tr_elmt = tr_objs[tr_objs.length-1];
	$(tr_elmt).append(td_elmt);
	var checkb_objs = $('table#'+sub_table_id+' tr.'+tr_class+' td.'+td_class+' div.'+checkbox_class);
	$(checkb_objs[checkb_objs.length-1]).jqxCheckBox({checked: checked});
}

function getval_jqxCheckBox(p, only_checked)
{
	if (only_checked===undefined) only_checked=true;
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3], schema=[4];
	var tr_class = field_id+'_trclass';
	var td_class = field_id+'_tdclass';
	var checkbox_class = field_id+'_checkbox_class';
	var checkb_objs = $('table#'+table_id+' tr.'+tr_class+' td.'+td_class+' div.'+checkbox_class);
	var res_obj = [];
	for(var i=0; i<checkb_objs.length; i++) {
		var td_item = checkb_objs[i];
		var checked = $(td_item).jqxCheckBox('checked');
		if (checked || !only_checked) {
			var caption = $(td_item)[0].textContent;
			if (res_obj.indexOf(caption) === -1) {
				res_obj.push(caption);
			}
		}
	}
	return res_obj;
}

function addItem_jqxListBox(p, init_val, render, height, width)
{
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3], schema=p[4], check_valid=p[5];
	var init_value=init_val[0], is_share=init_val[1], is_readonly=init_val[2], tips=init_val[3], is_vital=init_val[4], suffix_value=init_val[5];
	width = width || 400; height = height || 90;
	render = render || render_null;

	var source = [];
	if (init_value instanceof Array) {
		for (var i in init_value) {
			var item = init_value[i];
			if (item.hasOwnProperty('title')) {
				var html = render_onebox_url_html(item.url, item.image, item.title, item.desc);
				source.push({html: html, label: item.title, value: item});
			} else {
				source.push({label: item, value: item});
			}
		}
	}

	var input_id = field_id+'_input';
	var addbtn_id = field_id+'_add';
	var delbtn_id = field_id+'_del';
	var upbtn_id = field_id+'_up';
	var downbtn_id = field_id+'_down';
	var btn_width = 22;
	var btn_height = 22;

	$('#'+table_id).append([
		'<tr><td align="right" style="vertical-align:top;">'+caption+': </td><td align="left">',
		'<div id="'+field_id+'"></div>',
		'<div><input type="text" id="'+input_id+'" style="float:left;" />',
		'<div id="'+addbtn_id+'" style="padding:0px;float:left;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/add.png"/></div>',
		'<div id="'+delbtn_id+'" style="padding:0px;float:left;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/delete.png"/></div>',
		'<div id="'+upbtn_id+'" style="padding:0px;float:left;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/up.png"/></div>',
		'<div id="'+downbtn_id+'" style="padding:0px;float:left;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/down.png"/></div>',
		'</div></td></tr>'
	].join(''));

	$('#'+field_id).jqxListBox({source: source, width: width, height: height});
	$('#'+input_id).jqxInput({width: width-(btn_width+2)*4, height: btn_height, placeHolder:tips});
	$('#'+addbtn_id).jqxButton({width: btn_width, height:btn_height});
	$('#'+delbtn_id).jqxButton({width: btn_width, height:btn_height});
	$('#'+upbtn_id).jqxButton({width: btn_width, height:btn_height});
	$('#'+downbtn_id).jqxButton({width: btn_width, height:btn_height});

	$('#'+addbtn_id).on('click',[input_id,field_id,render,check_valid], event_listbox_add);
	$('#'+delbtn_id).on('click', [field_id], event_listbox_delete);
	$('#'+upbtn_id).on('click', [field_id], event_listbox_up);
	$('#'+downbtn_id).on('click', [field_id], event_listbox_down);
}

