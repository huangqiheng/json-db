/******************************
 	编辑数据窗口 
******************************/

function edit_data_window(title, input, cb_done)
{
	var fields=input[0], old_data=input[1], options=input[2];

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
		$('#'+notify_id).text(msg);
	};

	init_component();

	var win_count = $('.'+this_windows_class).length;

	$('#'+window_id).jqxWindow({
			height: 'auto', width: 'auto',
			position: {x:win_count*30, y:12+win_count*41},
			minHeight: 250, minWidth: 580,
			maxHeight: 1000,
			resizable: true,  
			modalOpacity: 0.3,
			showCollapseButton: false,
			showCloseButton: false,
			cancelButton: $('#'+no_id),
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
			cb_done(grep_data());
			$('#'+window_id).jqxWindow('close');
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

	function grep_data()
	{
		var res_data = {};
		for (var tab_name in fields) {
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
				if (field_value === 'jqxInput') 	   {res_val=getval_jqxInput(p);break;}
				if (field_value === 'jqxInput-id') 	   {res_val=getval_jqxInput_id(p);break;}
				if (field_value === 'jqxInput-time') 	   {res_val=getval_jqxInput_time(p);break;}
				if (field_value === 'jqxInput-text') 	   {res_val=getval_jqxInput_text(p);break;}
				if (field_value === 'jqxInput-name') 	   {res_val=getval_jqxInput_name(p);break;}
				if (field_value === 'jqxCheckBox')    	   {res_val=getval_jqxCheckBox(p);break;}
				if (field_value === 'jqxRadioButton')	   {res_val=getval_jqxRadioButton(p);break;}
				if (field_value === 'jqxListBox') 	   {res_val=getval_jqxListBox(p);break;}
				if (field_value === 'jqxListBox-name') 	   {res_val=getval_jqxListBox_name(p);break;}
				if (field_value === 'jqxListBox-onebox-url')  	   {res_val=getval_jqxListBox_onebox_url(p);break;}
				if (field_value === 'jqxListBox-onebox-id')   	   {res_val=getval_jqxListBox_onebox_id(p);break;}
				if (field_value === 'jqxListBox-onebox-id-same')   {res_val=getval_jqxListBox_onebox_id(p);break;}
				if (field_value === 'jqxListBox-images')   	{res_val=getval_jqxListBox_images(p);break;}
				if (field_value === 'jqxComboBox') 	   {res_val=getval_jqxComboBox(p);break;}
				if (field_value === 'jqxNumberInput-size') {res_val=getval_jqxNumberInput(p);break;}
				if (field_value === 'jqxNumberInput-price'){res_val=getval_jqxNumberInput(p);break;}
				if (field_value === 'jqxDateTimeInput')    {res_val=getval_jqxDateTimeInput(p);break;}

				if (field_value === 'jqxInput-share') 	   {res_val=getval_jqxInput(p);break;}
				if (field_value === 'jqxInput-text-share') {res_val=getval_jqxInput_text(p);break;}
				if (field_value === 'jqxCheckBox-share')   {res_val=getval_jqxCheckBox(p);break;}
				if (field_value === 'jqxRadioButton-share'){res_val=getval_jqxRadioButton(p);break;}
				if (field_value === 'jqxListBox-share')	   {res_val=getval_jqxListBox(p);break;}
				if (field_value === 'jqxListBox-onebox-url-share') {res_val=getval_jqxListBox_onebox_url(p);break;}
				if (field_value === 'jqxListBox-onebox-id-share')  {res_val=getval_jqxListBox_onebox_id(p);break;}
				if (field_value === 'jqxListBox-images-share')  {res_val=getval_jqxListBox_images(p);break;}
				if (field_value === 'jqxComboBox-share')   {res_val=getval_jqxComboBox(p);break;}
				}while(false);
				res_data[tab_name][field_name] = res_val;
			}
		}
		return res_data;
	}

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
				var p = [tab_id, table_id, field_id, field_name];

				var init_val = null;
				if (fill_data.hasOwnProperty(field_name)) {
					init_val = fill_data[field_name];
				}

				var option_val = [];
				if (options) {
					if (options.hasOwnProperty(field_name)) {
						option_val = options[field_name];
					}
				}

				if (field_value === 'jqxInput') 	{addItem_jqxInput(p, init_val);continue;}
				if (field_value === 'jqxInput-id') 	{addItem_jqxInput_id(p, init_val);continue;}
				if (field_value === 'jqxInput-time') 	{addItem_jqxInput_time(p, init_val);continue;}
				if (field_value === 'jqxInput-text') 		{addItem_jqxInput_text(p, init_val);continue;}
				if (field_value === 'jqxInput-name') 	{addItem_jqxInput_name(p, init_val);continue;}
				if (field_value === 'jqxCheckBox')      	{addItem_jqxCheckBox(p, init_val, option_val);continue;}
				if (field_value === 'jqxRadioButton')   	{addItem_jqxRadioButton(p, init_val, option_val);continue;}
				if (field_value === 'jqxListBox') 		{addItem_jqxListBox(p, init_val);continue;}
				if (field_value === 'jqxListBox-name') 	{addItem_jqxListBox_name(p, init_val);continue;}
				if (field_value === 'jqxListBox-onebox-url')	  	{addItem_jqxListBox_onebox_url(p, init_val);continue;}
				if (field_value === 'jqxListBox-onebox-id')	  {addItem_jqxListBox_onebox_id(p, init_val);continue;}
				if (field_value === 'jqxListBox-onebox-id-same')  {addItem_jqxListBox_onebox_id(p, init_val);continue;}
				if (field_value === 'jqxListBox-images')	{addItem_jqxListBox_images(p, init_val);continue;}
				if (field_value === 'jqxComboBox') 		{addItem_jqxComboBox(p, init_val, option_val);continue;}
				if (field_value === 'jqxNumberInput-size') 	{addItem_jqxNumberInput(p, init_val, 2,' MB');continue;}
				if (field_value === 'jqxNumberInput-price') 	{addItem_jqxNumberInput(p, init_val, 2);continue;}
				if (field_value === 'jqxDateTimeInput') {addItem_jqxDateTimeInput(p, init_val);continue;}

				if (field_value === 'jqxInput-share') 		{addItem_jqxInput(p, init_val);continue;}
				if (field_value === 'jqxInput-text-share')	{addItem_jqxInput_text(p, init_val);continue;}
				if (field_value === 'jqxCheckBox-share')	{addItem_jqxCheckBox(p, init_val, option_val);continue;}
				if (field_value === 'jqxRadioButton-share')   	{addItem_jqxRadioButton(p, init_val, option_val);continue;}
				if (field_value === 'jqxListBox-share') 	{addItem_jqxListBox(p, init_val);continue;}
				if (field_value === 'jqxListBox-onebox-url-share')	{addItem_jqxListBox_onebox_url(p, init_val);continue;}
				if (field_value === 'jqxListBox-onebox-id-share') {addItem_jqxListBox_onebox_id(p, init_val);continue;}
				if (field_value === 'jqxListBox-images-share')	{addItem_jqxListBox_images(p, init_val);continue;}
				if (field_value === 'jqxComboBox-share') 	{addItem_jqxComboBox(p, init_val, option_val);continue;}
			}
		}
	}
}

function merge_fields(data_obj)
{
	var output = {};
	for(var group in data_obj) {
		var items = data_obj[group];
		for(var field in items) {
			output[field] = items[field];
		}
	}
	return output;
}

function datatables_new(listview_id, aDataSet, aoColumns, event)
{
	datatables_css();
	var table_id = get_datatalbe_id(listview_id);

	var table_obj = $('#'+table_id);
	if (table_obj.length === 0) {
		$('#'+listview_id).html('<table cellpadding="0" cellspacing="0" border="0" class="display" id="'+table_id+'"></table>');
	}

	var oTable = $('#'+table_id).dataTable( {
		'bJQueryUI': true,
		'sPaginationType': 'full_numbers',
		'iDisplayLength': 25,
		"bLengthChange": true,
		"bPaginate": true,
		'aaData': aDataSet,
		'aaSorting': [[0,'desc']],
		'bStateSave': true,
		//"iCookieDuration": 3600*24*30, // 30day
		'aoColumns': aoColumns,
		"sDom": '<"H"Tfrl>t<"F"ip>',
		//"sDom": 'T<"clear">lfrtip',
		'oTableTools': {
			//'sRowSelect': 'multi',
			'sRowSelect': 'single',
			'aButtons': [
				{"sExtends": "select_all", "sButtonText": T('select all')},
				{"sExtends": "select_none", "sButtonText": T('select none')},
				{"sExtends": "text", "sButtonText": T('refresh'), "fnClick": 
					function (nButton, oConfig, oFlash) {event.on_refresh();}},
				{"sExtends": "text", "sButtonText": T('add'), "fnClick": 
					function (nButton, oConfig, oFlash) {event.on_add();}},
				{"sExtends": "text", "sButtonText": T('edit'), "fnClick": 
					function (nButton, oConfig, oFlash) {
						event.on_edit(datatables_selected(listview_id));
					}},
				{"sExtends": "text", "sButtonText": T('view'), "fnClick": 
					function (nButton, oConfig, oFlash) {
						event.on_view(datatables_selected(listview_id));
					}},
				{"sExtends": "text", "sButtonText": T('delete'),"fnClick": 
					function (nButton, oConfig, oFlash) {
						var del_objs = datatables_selected(listview_id);
						if (del_objs.length === 0){return;}
						confirm_dialog(T('Delete rows'), T('Are you sure???'), function(e){
							event.on_delete(del_objs);
						});
					}},
			],
		},
		"oLanguage": {
			"oPaginate": {
				'sFirst': '首页',
				'sLast': '尾页',
				'sNext': '下页',
				'sPrevious': '前页',
			},
			'sSearch': T('Search:'),
			'sEmptyTable': T('No data available in table'),
			'sInfoFiltered': T(' - filtering from _MAX_ records'),
			'sInfoEmpty': T('No entries to show'),
			'sInfo': T('Showing _START_ to _END_ of _TOTAL_ entries'),
			"sLengthMenu": T('Display')+'<select>'+
				'<option value="25">25</option>'+
				'<option value="50">50</option>'+
				'<option value="100">100</option>'+
				'<option value="200">200</option>'+
				'<option value="-1">'+T('All')+'</option>'+
				'</select> '+T('records')
		}

	});	

	$('#'+table_id).on('click','tr', function(event) {
		var oTT = TableTools.fnGetInstance(table_id);
		var obj = $(this);

		if (oTT.fnIsSelected(this)) {
			oTT.fnDeselect(obj);
		} else {
			oTT.fnSelect(obj);
		}
	});

}

function datatables_delete(listview_id, id_list, id_index)
{
	var table_id = get_datatalbe_id(listview_id);
	var oTable = $('#'+table_id).dataTable();
	var oSettings = oTable.fnSettings();
	var aoData = oSettings.aoData;

	var found_nTrs = [];
	for (var index in aoData){
		var aoData_item = aoData[index];
		var data = oTable.fnGetData(aoData_item.nTr);
		var id_cmp = data[id_index];
		if (id_list.indexOf(id_cmp) !== -1) {
			found_nTrs.push(aoData_item.nTr);
		}
	}

	if (found_nTrs.length === 0) {
		return false;
	}

	for (var index in found_nTrs){
		var nTr = found_nTrs[index];
		oTable.fnDeleteRow(nTr);
	}
	return true;
}

function datatables_update(listview_id, data_item, id_index)
{
	var table_id = get_datatalbe_id(listview_id);
	var oTable = $('#'+table_id).dataTable();
	var oSettings = oTable.fnSettings();
	var aoData = oSettings.aoData;

	var id_target = data_item[id_index];
	var found_nTr = null;
	for (var index in aoData){
		var aoData_item = aoData[index];
		var data = oTable.fnGetData(aoData_item.nTr);
		var id_cmp = data[id_index];
		if (id_target == id_cmp) {
			found_nTr = aoData_item.nTr;
			break;
		}
	}

	if (found_nTr === null) {
		return false;
	}

	oTable.fnUpdate(data_item, found_nTr); 
	return true;
}

function datatables_selected(listview_id)
{
	var table_id = get_datatalbe_id(listview_id);
	var oTT = TableTools.fnGetInstance(table_id);
	return oTT.fnGetSelectedData();
}

function datatables_add(listview_id, data)
{
	var table_id = get_datatalbe_id(listview_id);
	$('#'+table_id).dataTable().fnAddData(data);
}

function datatables_clear(listview_id)
{
	var table_id = get_datatalbe_id(listview_id);
	var is_datatables =  $.fn.DataTable.fnIsDataTable(document.getElementById(table_id)) ;
	if (is_datatables) {
		var oTable = $('#'+table_id).dataTable();
		oTable.fnDestroy();
		$('#'+listview_id).html('');
	}
}

function get_datatalbe_id(listview_id)
{
	return listview_id + '_table';
}


function datatables_css()
{
	if (window.datatables_css_init === undefined) {
		window.datatables_css_init = true;
	} else {
		return;
	}
	
	var html_str = hereDoc(function() {/*!
<style type='text/css'>
	div.DTTT_container.ui-buttonset {
		margin-bottom:0px !important;
	}
	a#ToolTables_listview_table_2 {
		margin-right: 3px !important;
	}
	div#listview_table_info {
		margin-top: 3px;
	}
	a.fg-button {
		height: 18px !important;
	}
	div.ui-toolbar {
		padding: 2px !important;
	}
	table.dataTable {
		font-size:12px !important;
		width:100% !important; 
	}
	div.dataTables_filter input {
		width: 260px !important;
		height: 23px !important;
	}
	div#listview_table_length {
		width: 180px !important;
	}
	div#listview_table_filter {
		width: 320px !important;
	}
</style>
	*/});
	$('body').append(html_str);
}
/**********************/


function hereDoc(f) 
{
	return f.toString().
		replace(/^[^\/]+\/\*!?/, '').
		replace(/\*\/[^\/]+$/, '');
};

function edit_fields_windows(title, input, cb_done)
{
	var listview = input[0];
	var fields = input[1];
	var onebox = input[2];
	var fields_types = input[3];

	if (onebox === undefined) {
		onebox = {};
		onebox.title = '';
		onebox.desc = '';
		onebox.image = '';
	}

	var time = new Date().getTime();
	var window_id = 'win_'+time.toString(); 
	var ok_id = window_id+'_ok';
	var no_id = window_id+'_no';
	var list_id = window_id+'_list';
	var name_id = window_id+'_name';
	var view_id = window_id+'_view';
	var category_id = window_id+'_cate';
	var type_id = window_id+'_type';
	var image_id = window_id+'_image';
	var add_id = window_id+'_add';
	var notify_id = window_id+'_notify';
	var ob_title_id = window_id+'_obtitle';
	var ob_desc_id = window_id+'_obdesc';
	var ob_image_id = window_id+'_obimage';

	var delbtn_id = window_id+'_del';
	var upbtn_id = window_id+'_up';
	var downbtn_id = window_id+'_down';
	var this_windows_class = 'edit_data_windows';

	$('body').append([
            '<div id="'+window_id+'" class="'+this_windows_class+'">',
                '<div>',
                    '<img width="20" height="20" src="images/new-table.png" alt="" style="float:left;" /><strong style="font-size:16px;">'+title+'</strong></div>',
                '<div>',
                    '<div align="center"><p><table>',
		        '<tr><td rowspan="2">',
			    '<div id="'+list_id+'"></div>',
			    '<div style="width:230px; height:30px;"><div style="float:right;">',
				'<input type="button" id="'+delbtn_id+'" value="✖︎" />',
				'<input type="button" id="'+upbtn_id+'" value="⬆︎" />',
				'<input type="button" id="'+downbtn_id+'" value="⬇︎" />',
			    '</div></div>',
			    '<div style="width:230px; border: 1px solid rgb(224, 224, 224);">',
			    	'<table style="font-size:13; width:100%;">',
				    '<tr><td colspan="2" align="center">'+T('onebox')+'</td></tr>',
				    '<tr><td align="right">'+T('title')+':</td><td><div id="'+ob_title_id+'"></div></td></tr>',
				    '<tr><td align="right">'+T('desc')+':</td><td><div id="'+ob_desc_id+'"></div></td></tr>',
				    '<tr><td align="right">'+T('thumbnail')+':</td><td><div id="'+ob_image_id+'"></div></td></tr>',
				'</table>',
			    '</div>',
			    '</td>',
			'<td><table style="font-size:13;">',
			    '<tr><td ><input type="button" id="'+add_id+'" value="'+T('Add/Modify')+'" /></td><td>',
				    '<div style="float:right;">',
					'<input type="button" id="'+ok_id+'" value="'+T('OK')+'" style="margin-right: 10px" />',
					'<input type="button" id="'+no_id+'" value="'+T('Cancel')+'" />',
				    '</div>',
			    	'</td></tr>',
			    '<tr><td align="right">'+T('category')+':</td><td><input type="text" id="'+category_id+'" /></td></tr>',
			    '<tr><td align="right">'+T('name')+':</td><td><input type="text" id="'+name_id+'" /></td></tr>',
			    '<tr><td align="right">'+T('view')+':</td><td><div id="'+view_id+'"><img height="15" width="30" src="images/eye.png"/></div></td></tr>',
			    '<tr><td align="right">'+T('type')+':</td><td><div id="'+type_id+'"></div></td></tr>',
			    '<tr><td></td><td><div style="width:300px; height:300px; border: 1px solid rgb(224, 224, 224);" id="'+image_id+'" /></td></tr>',
			'</table></td><tr><td vlign="bottom">',
			    '<div id="'+notify_id+'" style="color:red; text-align:center;"></div>',
			'</td></tr>',
                    '</table></div>',
                '</div>',
            '</div>'].join(''));

	var show_notify = function(msg){
		$('#'+notify_id).text(msg);
	};

	var win_count = $('.'+this_windows_class).length;

	$('#'+window_id).jqxWindow({height: 540, width: 660,
			position: {x:win_count*30, y:12+win_count*41},
			resizable: false, isModal: true, modalOpacity: 0.3,
			cancelButton: $('#'+no_id),
			initContent: tree_render
	});

	function tree_render() {
		$('#'+delbtn_id).jqxButton({width:25, height:25});
		$('#'+upbtn_id).jqxButton({width:25, height:25});
		$('#'+downbtn_id).jqxButton({width:25, height:25});
		$('#'+delbtn_id).on('click', event_tree_delete);
		$('#'+upbtn_id).on('click', event_tree_up);
		$('#'+downbtn_id).on('click', event_tree_down);


		$('#'+category_id).jqxInput({width:300, height: 25});
		$('#'+name_id).jqxInput({width:300, height: 25});
		$('#'+view_id).jqxCheckBox({width: 300, height: 25});
		$('#'+type_id).jqxDropDownList({source:fields_types, autoDropDownHeight:true,  
					placeHolder: T('Please choose field type'),
					width:300, height: 25});
		$('#'+type_id).on('select', function (event){
			var args = event.args;
			if (args) {
				var index = args.index;
				if (index !== -1) {
					var item = args.item;
					var value = item.value;
					var image = '<img height="300" width="300" src="images/'+value+'.png"/>';
					$('#'+image_id).html(image);
				}
			}                        
		});


		$('#'+ok_id).jqxButton({width: 65, height:35});
		$('#'+no_id).jqxButton({width: 65, height:35});
		$('#'+ok_id).on('click', function(e){
			var items = $('#'+list_id).jqxTree('getItems');
			var listview = [];
			var fields = {};
			for(var index in items) {
				var item = items[index];
				if (item.parentElement === null){//这是标签

				} else {//这是字段
					var p_item = $('#'+list_id).jqxTree('getItem', item.parentElement);
					var group_items = fields[p_item.label];
					if (group_items === undefined) {
						fields[p_item.label] = {};
						group_items = fields[p_item.label];
					}
					group_items[item.label] = item.value;

					if (item.checked) {
						listview.push(item.label);
					}
				}
			}

			var res_onebox = {};
			res_onebox.title = $('#'+ob_title_id).jqxDropDownList('val');
			res_onebox.desc = $('#'+ob_desc_id).jqxDropDownList('val');
			res_onebox.image = $('#'+ob_image_id).jqxDropDownList('val');

			cb_done([listview, fields, res_onebox]);
			$('#'+window_id).jqxWindow('close');
		});


		$('#'+window_id).on('close', function (event) {  
			if (event.target.id === window_id) {
				$('#'+window_id).jqxWindow('destroy'); 
			}
		}); 


		var source_data = [];
		var id_iter = 1;
		for (var group in fields) {
			var group_obj = {};
			id_iter++;
			group_obj.id = id_iter.toString();
			group_obj.parentid = '0';
			group_obj.label = group;
			source_data.push(group_obj);

			var items = fields[group];
			for (var key in items) {
				var value = items[key];
				var item_obj = {};
				id_iter++;
				item_obj.id = id_iter.toString();
				item_obj.parentid = group_obj.id;
				item_obj.label = key;
				item_obj.value = value;
				item_obj.checked = (listview.indexOf(key) !== -1);
				source_data.push(item_obj);
			}
		}

		var dataAdapter = new $.jqx.dataAdapter({
			datatype: "json",
			datafields: [
				{name: 'id'},
				{name: 'parentid'},
				{name: 'label'},
				{name: 'value'},
				{name: 'checked'}
			],
			id: 'id',
			localdata: source_data
		});
		dataAdapter.dataBind();
		var records = dataAdapter.getRecordsHierarchy('id', 'parentid', 'items');
		$('#'+list_id).jqxTree({source: records, width:'230px', height:'320px', 
					hasThreeStates:true, checkboxes:true,
					allowDrag:false
					});
		$('#'+list_id).jqxTree('expandAll');

		$('#'+list_id).on('checkChange', function (event) {
			var args = event.args;
			var element = args.element;
			var item = $('#'+list_id).jqxTree('getItem', element);
			if ((item.label === 'ID') && (!args.checked)) {
				setTimeout(function(){
					$('#'+list_id).jqxTree('checkItem', element, true);
				},1);
			}
		});

		$('#'+list_id).on('select', function (event) {
			$('#'+name_id).jqxInput({disabled:false});
			$('#'+view_id).jqxCheckBox({disabled:false});
			$('#'+type_id).jqxDropDownList({disabled:false});
			var args = event.args;
			if (args) {
				$('#'+category_id).val('');
				$('#'+name_id).val('');
				$('#'+view_id).jqxCheckBox('val', false);
				$('#'+type_id).jqxDropDownList('selectIndex', -1); 
				$('#'+image_id).html('');

				var item = $('#'+list_id).jqxTree('getItem', args.element);
				if (item.parentElement === null) {
					$('#'+category_id).val(item.label);
					return;
				}
				var item_root = $('#'+list_id).jqxTree('getItem', item.parentElement);

				$('#'+category_id).val(item_root.label);
				$('#'+name_id).val(item.label);
				$('#'+type_id).val(item.value);
				$('#'+view_id).jqxCheckBox('val', item.checked);

				if (item.label==='ID') {
					$('#'+name_id).jqxInput({disabled:true});
					$('#'+view_id).jqxCheckBox({disabled:true});
					$('#'+type_id).jqxDropDownList({disabled:true});
				}
				if (item.label==='TIME') {
					$('#'+name_id).jqxInput({disabled:true});
					$('#'+type_id).jqxDropDownList({disabled:true});
				}
			}
		});

		var get_list_items=function(){
			var items = $('#'+list_id).jqxTree('getItems');
			var field_names = [];
			for(var index in items) {
				var item = items[index];
				if (item.parentElement === null){//这是标签
					continue;
				} 
				field_names.push(item.label);
			}
			return field_names;
		};
		var onebox_source = get_list_items();

		$('#'+ob_title_id).jqxDropDownList({source:onebox_source, autoDropDownHeight:true,  placeHolder: T('Please choose field name'), width:170, height: 25});
		$('#'+ob_desc_id).jqxDropDownList({source:onebox_source, autoDropDownHeight:true,  placeHolder: T('Please choose field name'), width:170, height: 25});
		$('#'+ob_image_id).jqxDropDownList({source:onebox_source, autoDropDownHeight:true,  placeHolder: T('Please choose field name'), width:170, height: 25});
		if (onebox.title !== '') {
			$('#'+ob_title_id).jqxDropDownList('val', onebox.title);
		}
		if (onebox.desc !== '') {
			$('#'+ob_desc_id).jqxDropDownList('val', onebox.desc);
		}
		if (onebox.image !== '') {
			$('#'+ob_image_id).jqxDropDownList('val', onebox.image);
		}

		$('#'+ob_title_id).on('open', function (event) {
			var item_list = get_list_items();
			$('#'+ob_title_id).jqxDropDownList({source: item_list});
		}); 
		$('#'+ob_desc_id).on('open', function (event) {
			var item_list = get_list_items();
			$('#'+ob_desc_id).jqxDropDownList({source: item_list});
		}); 
		$('#'+ob_image_id).on('open', function (event) {
			var item_list = get_list_items();
			$('#'+ob_image_id).jqxDropDownList({source: item_list});
		}); 



		$('#'+add_id).jqxButton({width: 65, height:35});
		$('#'+add_id).on('click', function(e){
			var same_name = function(name){
				var treeItems = $('#'+list_id).jqxTree('getItems');
				for (var index in treeItems) {
					var item = treeItems[index];
					if (item.parentElement === null) {continue;}
					if (item.label === name) {
						return item;
					}
				}
				return null;
			};

			var d_name = $('#'+name_id).val();
			var d_group = $('#'+category_id).val();
			var d_type_i = $('#'+type_id).jqxDropDownList('getSelectedIndex'); 
			var d_view = $('#'+view_id).jqxCheckBox('val');

			if (d_group == ''){
				$('#'+category_id).jqxInput('focus'); 
				show_notify(T('please fill in "tab name"'));
				return;
			}

			if (d_name == ''){
				var selected = $('#'+list_id).jqxTree('getSelectedItem');
				if (selected.parentElement === null) {
					$('#'+list_id).jqxTree('updateItem', selected.element, {label:d_group});
					return;
				} else {
					show_notify(T('please fill in "field name"'));
					$('#'+name_id).jqxInput('focus'); 
					return;
				}
			}

                        if (d_type_i===-1){
				show_notify(T('please select field type'));
				return;
			} else {
				var item = $('#'+type_id).jqxDropDownList('getSelectedItem');
				var d_type = item.value;
			}

			var new_item = {label:d_name, value:d_type, checked:d_view};
			var update_item = same_name(d_name);
			if (update_item){
				$('#'+list_id).jqxTree('updateItem', update_item, new_item);
				$('#'+list_id).jqxTree('checkItem', update_item, new_item.checked);
				show_notify('');
				return;
			}

			var get_group = function(group){
				var treeItems = $('#'+list_id).jqxTree('getItems');
				var res_item = null;
				for (var index in treeItems) {
					var item = treeItems[index];
					if (item.parentElement === null) {
						if (group === undefined) {
							res_item = item;
						} else {
							if (item.label === group) {
								return item;
							}
						}
					}
				}
				return res_item;
			};

			var group = get_group(d_group);
			if (group === null) {
				$('#'+list_id).jqxTree('addTo', {label:d_group}, null, false);
				var group = get_group(d_group);
			}

			$('#'+list_id).jqxTree('addTo', new_item, group.element, false);
			$('#'+list_id).jqxTree('render');
			show_notify('');
			return;
		});

		function event_tree_delete(){
			var item = $('#'+list_id).jqxTree('getSelectedItem');
			if (item === null){return;}
			if (item.label === 'ID'){return;}
			if (item.label === 'TIME'){return;}
			$('#'+list_id).jqxTree('removeItem', item.element);
		}

		function replace_item(first, second)
		{
			var first_data = {};
			first_data.label = first.label;
			first_data.checked = first.checked;
			first_data.value = first.value;

			var second_data = {};
			second_data.label = second.label;
			second_data.checked = second.checked;
			second_data.value = second.value;

			$('#'+list_id).jqxTree('updateItem', first, second_data);
			$('#'+list_id).jqxTree('updateItem', second, first_data);
			$('#'+list_id).jqxTree('selectItem', second);
			$('#'+list_id).jqxTree('render');
		}

		function replace_group(item, updown)
		{
			if (item.isExpanded === false) {
				$('#'+list_id).jqxTree('expandItem', item.element);
				show_notify(T('expands the item, please click again.'));
				return;
			}
			show_notify('');

			var me_group = {
				label: item.label,
				value: item.value
			};

			var me_data = [];
			var iter_item = item;
			do {
				iter_item = $('#'+list_id).jqxTree('getNextItem', iter_item.element);
				if (iter_item===null){break;}
				if (iter_item.parentElement === null){break;}
				me_data.push({label:iter_item.label, value:iter_item.value, checked: iter_item.checked});
			} while(true);

			var next_group = function(input){
				var iter_item = input;
				do {
					if (updown === 'up') {
						iter_item = $('#'+list_id).jqxTree('getPrevItem', iter_item.element);
					} else {
						iter_item = $('#'+list_id).jqxTree('getNextItem', iter_item.element);
					}
					if (iter_item===null){break;}
				} while(iter_item.parentElement !== null);
				return iter_item;
			}

			var target_item = next_group(item);
			if (target_item === null) {return;}

			if (updown === 'up') {
				$('#'+list_id).jqxTree('addBefore', me_group, target_item);
				var new_group = $('#'+list_id).jqxTree('getPrevItem', target_item.element);
			} else {
				var target_item = next_group(target_item);
				if (target_item){
					$('#'+list_id).jqxTree('addBefore', me_group, target_item);
					var new_group = $('#'+list_id).jqxTree('getPrevItem', target_item.element);
				} else {
					$('#'+list_id).jqxTree('addTo', me_group, null);
					var items = $('#'+list_id).jqxTree('getItems'); 
					var new_group = items[items.length-1];
				}
			}

			var data = me_data.shift();
			do {
				$('#'+list_id).jqxTree('addTo', data, new_group.element);
			} while(data = me_data.shift());

			$('#'+list_id).jqxTree('removeItem', item.element);
			$('#'+list_id).jqxTree('expandAll');
			$('#'+list_id).jqxTree('selectItem', new_group);
		}

		function event_tree_up(){
			var item = $('#'+list_id).jqxTree('selectedItem');
			var prevItem = $('#'+list_id).jqxTree('getPrevItem', item.element);

			if (item.parentElement === null) {
				replace_group(item, 'up');
				return;
			}

			if (prevItem === null) {return;}
			if (prevItem.parentElement === null) {return;}
			replace_item(item, prevItem);
		}

		function event_tree_down(){
			var item = $('#'+list_id).jqxTree('selectedItem');
			var nextItem = $('#'+list_id).jqxTree('getNextItem', item.element);

			$('#'+list_id).jqxTree('expandAll');

			if (item.parentElement === null) {
				replace_group(item, 'down');
				return;
			}

			if (nextItem === null) {return;}
			if (nextItem.parentElement === null) {return;}
			replace_item(item, nextItem);
		}
	}
}

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
			cancelButton: $('#'+no_id),
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
					var d_name = $('#'+name_id).jqxInput('val');
					var d_title = $('#'+title_id).jqxInput('val');
					var d_content = $('#'+content_id).jqxInput('val');
					var d_image = $('#'+image_id).jqxInput('val');

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

					$('#'+window_id).jqxWindow('close');
				});

				$('#'+window_id).on('close', function (event) {  
					if (event.target.id === window_id) {
						$('#'+window_id).jqxWindow('destroy'); 
					}
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
		width: 'auto', 
		scrollable:false,
	});
}

function get_tabtable_id(parent_id, input_str)
{
	var tab_item_id = parent_id+'_'+md5(input_str);
	var table_id = tab_item_id+'_table';
	return [tab_item_id, table_id];
}

function get_tableitem_id(window_id, tab_name, field_name)
{
	return 'field_'+md5(window_id+tab_name+field_name);
}


function addItem_jqxInput_id(p, id, width, height)
{
	tab_id=p[0]; table_id=p[1]; field_id=p[2]; caption=p[3];
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
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3];
	if (caption === 'TIME') {
		init_val = new Date().toUTCString();
	}
	addItem_jqxInput(p, init_val, source, height, width);
	$('#'+field_id).jqxInput({disabled: true});
}

function addItem_jqxInput_text(p, init_val, source, height, width)
{
	tab_id=p[0]; table_id=p[1]; field_id=p[2]; caption=p[3];
	width = width || 400; height = height || 50;
	source = source || []; 

	var html = '<tr><td align="right">'+caption+': </td>';
	html += '<td align="left"><textarea id="'+field_id+'"></textarea></td></tr>';
	$('#'+table_id).append(html);
	$('#'+field_id).jqxInput({width: width, height: height, source:source});
	if (init_val) {
		$('#'+field_id).val(init_val);
	}
}

function addItem_jqxInput(p, init_val, source, height, width)
{
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3];
	width = width || 300; height = height || 23;
	source = source || []; 

	var html = '<tr><td align="right">'+caption+': </td>';
	html += '<td align="left"><input type="text" id="'+field_id+'" /></td></tr>';
	$('#'+table_id).append(html);
	$('#'+field_id).jqxInput({width: width, height: height, source:source});
	if (init_val) {
		$('#'+field_id).val(init_val);
	}
}

function getval_jqxInput_id(p){return getval_jqxInput(p);}
function getval_jqxInput_time(p){return getval_jqxInput(p);}
function getval_jqxInput_name(p){return getval_jqxInput(p);}
function getval_jqxInput_text(p){return getval_jqxInput(p);}
function getval_jqxInput(p)
{
	var tab_id=p[0], table_id=p[1], field_id=p[2], caption=p[3];
	return $('#'+field_id).jqxInput('val');
}

function render_null(data, finish)
{
	data.push(data[0]);
	data.push(data[0]);
	finish(data);
}

function json(url, cb_done, cb_fail)
{
	$.getJSON(url).done(cb_done).fail(cb_fail);
}

function post(url, data, cb_done, cb_fail)
{
	$.ajax({
		type : 'POST',
		dataType : "jsonp",
		data: data,
		url : url, 
	}).done(cb_done).fail(cb_fail);
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
	jsonp('/service/onebox/name.php', {
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
				time: d.update_time
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
	jsonp('/service/onebox/url.php', 
		{url:data[0], type:'json'}, function(d){
		if (d.status === 'ok') {
			var html = render_onebox_url_html(d.ori_url, d.image, d.title, d.description);

			var value = {
				title: d.title,
				desc: d.description,
				image: d.image,
				url: d.ori_url,
				id: d.ID,
				time: d.update_time
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
	tab_id=p[0]; table_id=p[1]; field_id=p[2]; caption=p[3];
	width = width || 400; height = height || 250;
	var img_width = 332;

	var url2img=function(url){
		return '<img height="50" width="50" src="'+url+'">';
	}

	var source = [];
	if (init_val instanceof Array) {
		for (var i in init_val) {
			var item = init_val[i];
			var html = url2img(item);
			source.push({html:html, label:item, value:item});
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
	$('#'+input_id).jqxInput({width: width-(btn_width+2)*4, height: btn_height});
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
	$('#'+field_id).jqxListBox('selectIndex', 0 ); 
}

function addItem_jqxListBox_onebox_id(p, init_val, render, height, width)
{
	render = render || render_onebox_id;
	height = height || 250;
	addItem_jqxListBox(p, init_val, render, height, width);
}

function addItem_jqxListBox_onebox_url(p, init_val, render, height, width)
{
	render = render || render_onebox_url;
	height = height || 250;
	addItem_jqxListBox(p, init_val, render, height, width);
}

function addItem_jqxListBox_name(p, init_val, render, height, width)
{
	width = width || 300;
	addItem_jqxListBox(p, init_val, render, height, width);
}
function getval_jqxListBox_name(p){return getval_jqxListBox(p);}
function getval_jqxListBox_onebox_url(p){return getval_jqxListBox(p);}
function getval_jqxListBox_onebox_id(p){return getval_jqxListBox(p);}
function getval_jqxListBox_images(p){return getval_jqxListBox(p);}
function getval_jqxListBox(p)
{
	tab_id=p[0]; table_id=p[1]; field_id=p[2]; caption=p[3];
	var res_objs = [];
	var items = $('#'+field_id).jqxListBox('getItems'); 
	for (var index in items) {
		var item = items[index];
		res_objs.push(item.value);
	}
	return res_objs;
}

function getval_jqxRadioButton(p)
{
	tab_id=p[0]; table_id=p[1]; field_id=p[2]; caption=p[3];
	var tr_class = field_id+'_trclass';
	var td_class = field_id+'_tdclass';
	var radiobtn_class = field_id+'_radiobtn_class';
	var radio_objs = $('table#'+table_id+' tr.'+tr_class+' td.'+td_class+' div.'+radiobtn_class);
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
}

function addItem_jqxRadioButton(p, init_val, options, width)
{
	tab_id=p[0]; table_id=p[1]; field_id=p[2]; caption=p[3];

	var input_id = field_id+'_input';
	var sub_table_id = field_id+'_table';
	var tr_class = field_id+'_trclass';
	var td_class = field_id+'_tdclass';
	var group = field_id+'_group';
	var addbtn_id = field_id+'_add';
	var radiobtn_class = field_id+'_radiobtn_class';
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
		    '</td></tr></table>',
		'</div>',
		'</td></tr>'
	].join(''));

	$('#'+input_id).jqxInput({width: width-(btn_width+2)*4, height: btn_height, rtl:true});
	$('#'+addbtn_id).jqxButton({width: btn_width, height:btn_height});

	if (options instanceof Array) {
		for (var i in options) {
			var item = options[i];
			var checked = (init_val === item);
			add_radiobtn_item(sub_table_id, tr_class, td_class, radiobtn_class, item, group,checked);
		}
	}

	$('#'+addbtn_id).on('click', function(e){
		var input_caption = $('#'+input_id).val();
		if (input_caption != '') {
			add_radiobtn_item(sub_table_id, tr_class, td_class, radiobtn_class, input_caption, group);
			$('#'+input_id).val('');
		}
	});

}

function add_radiobtn_item(table_id, tr_class, td_class, radiobtn_class, caption, group, checked)
{
	if (checked === undefined) {
		checked = true;
	}
	if (group === undefined) {
		group = '';
	}
	var item_elmt = '<div class="'+radiobtn_class+'" style="float: left;">'+caption+'</div>';
	var td_elmt = '<td class="'+td_class+'" style="padding:0px;">'+item_elmt+'</td>';

	var tr_objs = $('table#'+table_id+' tr.'+tr_class);
	var td_objs = $('table#'+table_id+' tr.'+tr_class+' td.'+td_class);
	var mod_val = parseInt(td_objs.length % 3);

	if ((mod_val === 0) || isNaN(mod_val)) {
		$('table#'+table_id).append('<tr class="'+tr_class+'"></tr>');
		tr_objs = $('table#'+table_id+' tr.'+tr_class);
	}

	var tr_elmt = tr_objs[tr_objs.length-1];
	$(tr_elmt).append(td_elmt);
	var radio_objs = $('table#'+table_id+' tr.'+tr_class+' td.'+td_class+' div.'+radiobtn_class);
	$(radio_objs[radio_objs.length-1]).jqxRadioButton({groupName:group, checked: checked});
}

function addItem_jqxCheckBox(p, init_val, options, width)
{
	tab_id=p[0]; table_id=p[1]; field_id=p[2]; caption=p[3];

	var input_id = field_id+'_input';
	var sub_table_id = field_id+'_table';
	var tr_class = field_id+'_trclass';
	var td_class = field_id+'_tdclass';
	var addbtn_id = field_id+'_add';
	var checkbox_class = field_id+'_checkbox_class';
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
		    '</td></tr></table>',
		'</div>',
		'</td></tr>'
	].join(''));

	$('#'+input_id).jqxInput({width: width-(btn_width+2)*4, height: btn_height, rtl:true});
	$('#'+addbtn_id).jqxButton({width: btn_width, height:btn_height});

	if (options instanceof Array) {
		for (var i in options) {
			var item = options[i];
			var checked = false;
			if (init_val instanceof Array) {
				checked = (init_val.indexOf(item) !== -1);
			} else {
				checked = (item === init_val);
			}
			add_checkbox_item(sub_table_id, tr_class, td_class, checkbox_class, item, checked);
		}
	}

	$('#'+addbtn_id).on('click', function(e){
		var input_caption = $('#'+input_id).val();
		if (input_caption != '') {
			add_checkbox_item(sub_table_id, tr_class, td_class, checkbox_class, input_caption);
			$('#'+input_id).val('');
		}
	});

}

function add_checkbox_item(table_id, tr_class, td_class, checkbox_class, caption, checked)
{
	if (checked === undefined) {
		checked = true;
	}
	var item_elmt = '<div class="'+checkbox_class+'" style="float: left;">'+caption+'</div>';
	var td_elmt = '<td class="'+td_class+'" style="padding:0px;">'+item_elmt+'</td>';

	var tr_objs = $('table#'+table_id+' tr.'+tr_class);
	var td_objs = $('table#'+table_id+' tr.'+tr_class+' td.'+td_class);
	var mod_val = parseInt(td_objs.length % 3);

	if ((mod_val === 0) || isNaN(mod_val)) {
		$('table#'+table_id).append('<tr class="'+tr_class+'"></tr>');
		tr_objs = $('table#'+table_id+' tr.'+tr_class);
	}

	var tr_elmt = tr_objs[tr_objs.length-1];
	$(tr_elmt).append(td_elmt);
	var checkb_objs = $('table#'+table_id+' tr.'+tr_class+' td.'+td_class+' div.'+checkbox_class);
	$(checkb_objs[checkb_objs.length-1]).jqxCheckBox({checked: checked});
}

function getval_jqxCheckBox(p)
{
	tab_id=p[0]; table_id=p[1]; field_id=p[2]; caption=p[3];
	var tr_class = field_id+'_trclass';
	var td_class = field_id+'_tdclass';
	var checkbox_class = field_id+'_checkbox_class';
	var checkb_objs = $('table#'+table_id+' tr.'+tr_class+' td.'+td_class+' div.'+checkbox_class);
	var res_obj = [];
	for(var i=0; i<checkb_objs.length; i++) {
		var td_item = checkb_objs[i];
		var checked = $(td_item).jqxCheckBox('checked');
		if (checked) {
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
	tab_id=p[0]; table_id=p[1]; field_id=p[2]; caption=p[3];
	width = width || 400; height = height || 90;
	render = render || render_null;

	var source = [];
	if (init_val instanceof Array) {
		for (var i in init_val) {
			var item = init_val[i];
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
	$('#'+input_id).jqxInput({width: width-(btn_width+2)*4, height: btn_height});
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

function getval_jqxComboBox(p)
{
	tab_id=p[0]; table_id=p[1]; field_id=p[2]; caption=p[3];
	return $('#'+field_id).jqxComboBox('val');
}

function addItem_jqxComboBox(p, init_val, source, height, width)
{
	tab_id=p[0]; table_id=p[1]; field_id=p[2]; caption=p[3];
	width = width || 200; height = height || 25;
	source = source || []; 

	$('#'+table_id).append(['<tr><td align="right">'+caption+': </td>',
		'<td align="left"><div id="'+field_id+'"></div></td></tr>'].join(''));
	$('#'+field_id).jqxComboBox({
		autoDropDownHeight:true, 
		searchMode: 'equals',
		source: source, width: width, height: height});
	if (init_val) {
		$('#'+field_id).val(init_val);
	}
}

function getval_jqxNumberInput_size(p){return getval_jqxNumberInput(p);}
function getval_jqxNumberInput_price(p){return getval_jqxNumberInput(p);}
function getval_jqxNumberInput(p)
{
	tab_id=p[0]; table_id=p[1]; field_id=p[2]; caption=p[3];
	return $('#'+field_id).jqxNumberInput('val');
}

function addItem_jqxNumberInput(p, init_val, digits, symbol, height, width)
{
	tab_id=p[0]; table_id=p[1]; field_id=p[2]; caption=p[3];
	width = width || 200; height = height || 25;
	symbol = symbol || '';
	$('#'+table_id).append(['<tr><td align="right">'+caption+': </td>',
		'<td align="left"><div id="'+field_id+'"></div></td></tr>'].join(''));
	$('#'+field_id).jqxNumberInput({symbolPosition:'right',symbol:symbol,min:0,decimalDigits:digits,width:width,height:height, inputMode:'simple',spinButtons:true});
	if (init_val) {
		$('#'+field_id).val(init_val);
	}
}

function getval_jqxDateTimeInput(p)
{
	tab_id=p[0]; table_id=p[1]; field_id=p[2]; caption=p[3];
	var date_obj = $('#'+field_id).jqxDateTimeInput('getDate');
	return date_obj;
}

function addItem_jqxDateTimeInput(p, init_val, height, width)
{
	tab_id=p[0]; table_id=p[1]; field_id=p[2]; caption=p[3];
	width = width || 200; height = height || 25;
	$('#'+table_id).append(['<tr><td align="right">'+caption+': </td>',
		'<td align="left"><div id="'+field_id+'"></div></td></tr>'].join(''));
	$('#'+field_id).jqxDateTimeInput({culture:'zh-CN', formatString: 'D', width:width, height:height});
	if (init_val) {
		$('#'+field_id).jqxDateTimeInput('setDate', init_val);
	}
}

function confirm_dialog(title, content, cb_ok)
{
	var seed = new Date().getTime();
	var window_id = 'win_'+seed.toString(); 
	var ok_id = window_id+'_ok';
	var no_id = window_id+'_no';
	var btn_width = 70;

	$('body').append([
            '<div id="'+window_id+'">',
                '<div>',
		    '<table><tr><td>',
			    '<img width="20" height="20" src="images/new-table.png" alt="" style="float:left;" />',
		    '</td><td>',
			    '<strong>'+title+'</strong>',
		    '</td></tr></table>',
		'</div>',
                '<div>',
                    '<div align="center"><table style="height:100%;">',
		        '<tr><td><div style="margin:auto;">'+content+'</div></td></tr>',
			'<tr><td>',
			    '<div style="float:right;">',
				'<input type="button" id="'+ok_id+'" value="'+T('OK')+'" style="margin-right: 1px" />',
				'<input type="button" id="'+no_id+'" value="'+T('Cancel')+'" />',
			    '</div>',
			'</td></tr>',
                    '</table></div>',
                '</div>',
            '</div>'].join(''));

	$('#'+window_id).jqxWindow({
			height: 'auto', width: 'auto',
			minHeight: 200, minWidth: 280,
			resizable: true,  
			isModal: true,
			modalOpacity: 0.3,
			okButton: $('#'+ok_id),
			cancelButton: $('#'+no_id),
	});

	$('#'+ok_id).jqxButton({width: btn_width, height:35});
	$('#'+no_id).jqxButton({width: btn_width, height:35});
	$('#'+ok_id).on('click', cb_ok);
	$('#'+window_id).on('close', function (event) {  
		if (event.target.id === window_id) {
			$('#'+window_id).jqxWindow('destroy'); 
		}
	}); 
}

function set_cookie(cname,cvalue,exdays)
{
	var d = new Date();
	d.setTime(d.getTime()+(exdays*24*60*60*1000));
	var expires = "expires="+d.toGMTString();
	document.cookie = cname + "=" + cvalue + "; " + expires;
}

function get_cookie(cname)
{
	var name = cname + "=";
	var ca = document.cookie.split(';');
	for(var i=0; i<ca.length; i++) 
	{
		var c = ca[i].trim();
		if (c.indexOf(name)==0) return c.substring(name.length,c.length);
	}
	return "";
}

function check_cookie()
{
	var user=get_cookie("username");
	if (user!="")
	{
		alert("Welcome again " + user);
	}
	else 
	{
		user = prompt("Please enter your name:","");
		if (user!="" && user!=null)
		{
			set_cookie("username",user,365);
		}
	}
}


