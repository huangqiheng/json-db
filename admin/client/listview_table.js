
function refresh_listview(db_name, table_name)
{
	var table_desc = get_table_desc(db_name, table_name);
	var listview_id = 'listview';
	datatables_clear(listview_id);

	var data_url = get_url(db_name, table_name, 'listview.json');
	var schema_url = get_url(db_name, table_name, 'schema.json');

	var get_data = function(item_id, cb_done){
		var item_url = get_url(db_name, table_name, item_id.toString()+'.json');
		json(item_url, cb_done, function(e) {
			env.popup(T('ERROR'), T('no data is found, item id error.'));
		});
	};

	json(schema_url, schema_done, function(e) {
		env.popup(T('ERROR'), T('no data field is set, please add new.'));
	});

	var regist_buttons = function(id_index, buttons) {
		$('#custom_btns').empty();
		if (!buttons) {return;}
		var id_prefix = 'custom_button_';
		for(var i in buttons) {
			var button = buttons[i];
			var button_id = id_prefix + i;

			$('#custom_btns').append('<div id="'+button_id+'" style="float:left; padding:0px"><img height="25" width="25" src="'+button.image+'"/></div>');
			$('#'+button_id).jqxButton({width: nav_btn_width, height: nav_btn_height});
			$('#'+button_id).jqxTooltip(tip_data(button.title, button.tips));
			$('#'+button_id).on('click', button, function(e){
				var button = e.data;
				var selected = datatables_selected(listview_id);
				var urls = selected.map(function(item){return item[id_index];});
				var id = selected[0];
				post(button.url, {
					ids:urls,
					db_name: db_name,
					table_name: table_name
				}, function(d){
					if (d.status === 'ok') {
						if ((d.listview) && (d.listview.length)) {
							for(var i in d.listview) {
								var item = d.listview[i];
								datatables_update(listview_id, item, id_index);
							}
						} else {
							console.log(d);
						}
						env.popup(T('SUCCEED'), button.title + ' ' + T('execute succefully'));
					} else {
						env.popup(T('ERROR'), button.title + ' ' + T('execute error'));
					}
				}, function(e){
					env.popup(T('ERROR'), button.title + ' ' + T('execute failure'));
				});
			});
		}
	};

	function schema_done(schema_data) {
		var listview = schema_data.listview;

		var aoColumns = [];
		var id_index = -1;
		for (var index in listview) {
			var field_name = listview[index];
			if (field_name === 'ID') {
				id_index = index;
			}
			aoColumns.push({'sTitle': field_name});
		}

		regist_buttons(id_index, schema_data.buttons);

		var event = {};
		event.on_refresh = function(){
			$('#refresh_btn').trigger('click');
		};
		event.on_delete = function(selected_datas){
			if (selected_datas.length === 0) {
				return;
			}

			var id_list = [];
			for (var index in selected_datas) {
				var item = selected_datas[index];
				var item_id = item[id_index];
				id_list.push(item_id);
			}

			var req_data = {};
			req_data['cmd'] = 'delete';
			req_data['list'] = id_list;
			req_data['db_name'] = db_name;
			req_data['table_name'] = table_name;
			submit_data(req_data, function(d){
				datatables_delete(listview_id, d.id_list, id_index);
			});

		};
		event.on_add = function(){
			var options_ready=function(options) {
				var input = [schema_data, null, options];
				var title = T('Create new data')+'('+table_desc+')';
				edit_data_window(title, input, function(new_data){
					var req_data = {};
					req_data['cmd'] = 'create';
					req_data['data'] = new_data;
					req_data['db_name'] = db_name;
					req_data['table_name'] = table_name;
					submit_data(req_data, function(d){
						datatables_add(listview_id, d.listview);
						if (d.hasOwnProperty('reload')) {
							if (d.reload === true) {
								$('#refresh_btn').trigger('click');
							}
						}
					});
				});
			};

			var opt_url = get_url(db_name, table_name, 'options.json');
			json(opt_url, options_ready, function(d){
				options_ready({});
			});
		};
		event.on_view = function(selected_datas){
			for (var index in selected_datas) {
				var item = selected_datas[index];
				var item_id = item[id_index];
				var item_url = get_url(db_name, table_name, item_id.toString()+'.json');
				window.open(item_url, '_blank');
			}
		};
		event.on_edit = function(selected_datas){
			var item_ready = function(old_data, options){
				var input = [schema_data, old_data ,options];
				var title = T('Edit data')+'('+table_desc+')';
				edit_data_window(title, input, function(new_data){
					var req_data = {};
					req_data['cmd'] = 'update';
					req_data['data'] = new_data;
					req_data['db_name'] = db_name;
					req_data['table_name'] = table_name;
					req_data['force_empty'] = true;
					submit_data(req_data, function(d){
						datatables_update(listview_id, d.listview, id_index);
						if (d.hasOwnProperty('reload')) {
							if (d.reload === true) {
								$('#refresh_btn').trigger('click');
							}
						}
					});
				});
			};

			var get_items_data=function(options){ 
				for (var index in selected_datas) {
					var item = selected_datas[index];
					var item_id = item[id_index];
					get_data(item_id, function(old_data){
						item_ready(old_data, options);
					});
				}
			};

			var opt_url = get_url(db_name, table_name, 'options.json');
			json(opt_url, get_items_data, function(d){
				get_items_data({});
			});

		};

		var set_records_counter=function(data) {
			var counter_str = '('+data.length+T('records')+')';
			$('#counter_table_datas').html('<div id="counter_datasize" style="text-align:right;">'+counter_str+'</div>');
			var out_width = $('#counter_datasize').outerWidth();
			$('#counter_datasize').css('width', out_width+10);
		};

		json(data_url, function(data){
			datatables_new(listview_id, data, aoColumns, event);
			set_records_counter(data);
		}, function() {
			var data = [];
			datatables_new(listview_id, data, aoColumns, event);
			set_records_counter(data);
		});

		set_cookie('init_db', db_name, 30);
		set_cookie('init_table', table_name, 30);
	};
}

function datatables_new(listview_id, aDataSet, aoColumns, event)
{

	if (aDataSet.length > 50000) {
		aDataSet.length = 50000;
		env.popup(T('ERROR'), T('Too much datas, faint!'));
	}


	datatables_css();
	var table_id = get_datatalbe_id(listview_id);

	var table_obj = $('#'+table_id);
	if (table_obj.length === 0) {
		$('#'+listview_id).html('<table cellpadding="0" cellspacing="0" border="0" class="display" id="'+table_id+'"></table>');
	}

	var fn_init = function(image, tip_title, tip_content) {
		return function(node){
			$(node).jqxTooltip(tip_data(tip_title, tip_content));
			$(node).empty();
			$(node).css('padding', '0px');
			$(node).css('margin', '0px');
			$(node).html('<img height="21" width="23" src="'+image+'">');
		};
	};

	var oTable = $('#'+table_id).dataTable( {
		'bJQueryUI': true,
		'sPaginationType': 'input',
		'iDisplayLength': 25,
		'bLengthChange': true,
		'bPaginate': true,
		'responsive': true,
		'aaData': aDataSet,
		'aaSorting': [[0,'desc']],
		'bStateSave': true,
		//"iCookieDuration": 3600*24*30, // 30day
		'aoColumns': aoColumns,
		"sDom": '<"H"Tfrl>t<"F"ip>',
		"fnInitComplete": function(oSettings, json) {
			$('div#listview_table_filter').jqxTooltip(tip_data(T('TIP_FILTER'), T('TIP_FILTER_DESC'), 'bottom'));
			$('div#listview_table_length').jqxTooltip(tip_data(T('TIP_LENGTH'), T('TIP_LENGTH_DESC'), 'mouse'));
			$('table.display td').css('max-width', '150px');
			$('table.display td').css('word-wrap', 'break-word');
		},
		"fnDrawCallback": function( oSettings ) {
			//console.log('fnDrawCallback');
		},
		"fnPreDrawCallback": function( oSettings ) {
			//console.log('fnPreDrawCallback');
		},
		'tableTools': {
			'sRowSelect': 'os',
			'aButtons': [
				{"sExtends": "text", 'fnInit': fn_init('images/export.png',T('export button'),T('export rows to csv for excel')), 
					"fnClick": function (nButton, oConfig, oFlash) {

					}},
				{"sExtends": "text", 'fnInit': fn_init('images/import.png',T('import button'),T('import csv or xml file to table')), 
					"fnClick": function (nButton, oConfig, oFlash) {

					}},
				{"sExtends": "text", 'fnInit': fn_init('images/save.png',T('save button'),T('save table as backup file')), 
					"fnClick": function (nButton, oConfig, oFlash) {
						var data = {};
						data.cmd = 'backup_database';
						data.db_name = get_db_name();
						data.table_name = get_table_name(),
						submit_schema(data, function(d){
							env.popup(T('SUCCEED'), T('Backup database successfully.'));
						});
					}},
				{"sExtends": "select_all", 'fnInit': fn_init('images/check.png',T('select all button'),T('select all rows, even if beening paged'))},
				{"sExtends": "select_none", 'fnInit': fn_init('images/uncheck.png',T('select none button'),T('unselect all highline rows'))},
				{"sExtends": "text", 'fnInit': fn_init('images/refresh.png',T('REFRESH_BTN'),T('REFRESH_BTN_DESC')), 
					"fnClick": function (nButton, oConfig, oFlash) {event.on_refresh();}},
				{"sExtends": "text", 'fnInit': fn_init('images/detail.png',T('view button'),T('view json data details')), 
					"fnClick": function (nButton, oConfig, oFlash) {event.on_view(datatables_selected(listview_id));}},
				{"sExtends": "text", 'fnInit': fn_init('images/add.png',T('add row button'),T('add a json data as a row')), 
					"fnClick": function (nButton, oConfig, oFlash) {event.on_add();}},
				{"sExtends": "text", 'fnInit': fn_init('images/edit.png',T('edit row button'),T('edit row of json data')), 
					"fnClick": function (nButton, oConfig, oFlash) {event.on_edit(datatables_selected(listview_id));}},
				{"sExtends": "text", 'fnInit': fn_init('images/delete.png',T('delete button'),T('delete rows selected')), 
					"fnClick": function (nButton, oConfig, oFlash) {
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
				'<option value="500">500</option>'+
				'<option value="-1">'+T('All')+'</option>'+
				'</select> '+T('records')
		}

	});	

	window.fixedheader = new $.fn.dataTable.FixedHeader(oTable);

	$.contextMenu({
		selector: '#'+table_id,
		items: {
			create: {
				name: '新建',
				callback: function(key, opt) {
					$('#ToolTables_listview_table_7').click();
				}
			},
			modify: {
				name: '修改',
				callback: function(key, opt) {
					$('#ToolTables_listview_table_8').click();
				}
			},
			remove: {
				name: '删除',
				callback: function(key, opt) {
					$('#ToolTables_listview_table_9').click();
				}

			},
			save: {
				name: '备份',
				callback: function(key, opt) {
					$('#ToolTables_listview_table_2').click();
				}

			}
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

	var id_target = parseInt(data_item[id_index]);
	var found_nTr = null;
	for (var index in aoData){
		var aoData_item = aoData[index];
		var data = oTable.fnGetData(aoData_item.nTr);
		var id_cmp = parseInt(data[id_index]);
		if (id_target == id_cmp) {
			found_nTr = aoData_item.nTr;
			break;
		}
	}

	if (found_nTr === null) {
		return false;
	}

	oTable.fnUpdate(data_item, found_nTr, undefined, false); 
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
	span.paginate_button, span.paginate_page {
		padding: 5px !important;
	}
	input.paginate_input {
		width: 50px !important;
	}
	div.DTTT_container.ui-buttonset {
		margin-bottom:0px !important;
		margin-right:18px !important;
	}
	a#ToolTables_listview_table_0 {
		margin-left: 2px !important;
	}
	a#ToolTables_listview_table_2 {
		margin-right: 18px !important;
	}
	a#ToolTables_listview_table_6 {
		margin-right: 18px !important;
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
	.DataTables_sort_icon { 
		display:none !important;
	}
	div.DataTables_sort_wrapper {
		padding-right: 0px !important;
		text-shadow: 2px 1px 3px #FFF;
	}
	div.ui-widget-header {
		text-shadow: 3px 3px 3px #FFF;
	}
	div#db_captions, div#table_captions {
		text-shadow: 3px 3px 18px #56FF63;
	}
	thead.sorting_asc, thead.sorting_desc {
		text-shadow: 3px 3px 18px #56FF63;
	}
	th.ui-state-default {
		padding-top: 8px !important;
		padding-bottom: 8px !important;
		padding-left: 0px !important;
		padding-right: 0px !important;
	}
	table.display td {
		padding-top: 3px !important;
		padding-bottom: 3px !important;
		padding-left: 3px !important;
		padding-right: 3px !important;
	}
</style>
	*/});
	$('body').append(html_str);
}
