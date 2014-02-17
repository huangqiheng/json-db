<?php
require_once 'functions.php';

$user_info = denies_with_redirect();
$user_email = $user_info['user_email'];

if (!preg_match('|@appgame\.com$|i', $user_email)) {
	header('Location: /index.php');
	exit();
}

header('Content-Type: text/html; charset=utf-8');

$db_captions = get_db_captions();
$table_captions = get_table_captions(); 
$web_root = "http://{$_SERVER['SERVER_NAME']}";
$init_db = get_param('db', 'default');
$init_table = get_param('table', 'default');

?>
<html><head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<link rel="shortcut icon" type="image/ico" href="images/favicon.ico" />
<title>JsonDB</title>


<!--加载jquery-->
<script type="text/javascript" language="javascript" src="client/jquery/jquery-2.0.3.min.js"></script>
<script type="text/javascript" language="javascript" src="client/jquery/md5.min.js"></script>

<!--加载gritter-->
<link rel="stylesheet" href="client/gritter/jquery.gritter.css" type="text/css" />
<script type="text/javascript" language="javascript" src="client/gritter/jquery.gritter.min.js"></script>

<!--加载jqwidgets-->
<link rel="stylesheet" href="client/jqwidgets/styles/jqx.base.css" type="text/css" />
<script type="text/javascript" language="javascript" src="client/jqwidgets/jqx-all.js"></script> 
<script type="text/javascript" language="javascript" src="client/jqwidgets/globalization/globalize.js"></script>
<script type="text/javascript" language="javascript" src="client/jqwidgets/globalization/globalize.culture.zh-CN.js"></script> 

<!--加载datatables-->
<style type="text/css" title="currentStyle">
	@import "client/datatables/demo_table_jui.css";
	@import "client/datatables/jquery-ui-1.8.4.custom.css";
	@import "client/datatables/demo_page.css";
	@import "client/datatables/header.css";
	@import "client/datatables/demo_table.css";
	@import "client/datatables/TableTools.css";
</style>
<script type="text/javascript" language="javascript" src="client/datatables/jquery.dataTables.min.js"></script>
<script type="text/javascript" language="javascript" src="client/datatables/TableTools.js"></script>
<script type="text/javascript" language="javascript" src="client/datatables/ZeroClipboard.js"></script>

<!--加载自己的js-->
<script type="text/javascript" language="javascript" src="client/json-db-lib.js"></script>
<script type="text/javascript" language="javascript" src="client/language.js"></script>

<!--动态生成的js变量-->
<script type="text/javascript" charset="utf-8">
window.env = {};
set_language('cn');
env.web_root = "<?php echo $web_root; ?>";
env.init_db = "<?php echo $init_db; ?>";
env.init_table = "<?php echo $init_table; ?>";

if ((env.init_db === 'default') && (env.init_table === 'default')) {
	env.init_db = get_cookie('init_db');
	env.init_table = get_cookie('init_table');
}

env.username = "<?php echo $user_info['user_name']; ?>";
env.logout = "<?php echo login_wrap_referer($user_info['logout'],'/index.php'); ?>";

env.field_types = <?php echo json_encode($field_types); ?>;

env.db_captions = <?php echo json_encode($db_captions); ?>;
env.table_captions = <?php echo json_encode($table_captions); ?>;
init_db_captions();
for (var db_name in env.table_captions) {init_table_captions(db_name);}

env.db_index = get_index(env.db_captions, env.init_db);
env.table_index = get_index(env.table_captions[env.init_db], env.init_table);
env.db_last_unselect = env.db_index;
env.table_last_unselect = env.table_index;
env.popup = function (title, content){$.gritter.add({title: title, text: content});};
env.db_cmd_count = 4;
env.table_cmd_count = 4;
env.last_refresh_time = 0;

$(document).ready(jsondb_main);


function jsondb_main() 
{
	$.ajaxSetup({ cache: false });

	var db_dataAdapter = new $.jqx.dataAdapter({
		localdata: env.db_captions,
		datatype: "array"
	});
	var table_dataAdapter = new $.jqx.dataAdapter({
		localdata: env.table_captions[env.init_db],
		datatype: "array"
	});
	var dropdown_opt = {
		autoDropDownHeight: true,
		displayMember: "title", 
		valueMember: "name", 
		itemHeight: -1, height: 25, width: 280,
		renderer: render_captions
	};

	$('#db_captions').jqxDropDownList($.extend({
		selectedIndex:env.db_index, 
		selectionRenderer: render_db_selection,
		placeHolder: T('Please Choose DB:'),
		dropDownWidth:280, 
		source:db_dataAdapter}, dropdown_opt));
	$('#table_captions').jqxDropDownList($.extend({
		selectedIndex:env.table_index, 
		selectionRenderer: render_table_selection,
		placeHolder: T('Please Choose Table:'),
		dropDownWidth:343, 
		source:table_dataAdapter}, dropdown_opt));

	if (env.table_captions.length === 0) {
		$('#table_captions').jqxDropDownList({disabled: true});
	}

	$('#refresh_btn').jqxButton({width: 32, height: 27});
	$('#config_btn').jqxButton({width: 32, height: 27});
	$('#user_info').html('<span>'+T('Hello')+' "'+env.username+'", '+T('you can')+' </span><a href="'+env.logout+'" style="color:red;">'+T('Logout')+'</a>');

	$('#refresh_btn').on('click', function(e){
		var db_name = get_db_name();
		var table_name = get_table_name();
		if (table_name === '') {
			env.popup(T('ERROR'), T('Please select or create table'));
			return;
		}
		refresh_listview(db_name, table_name);
	});

	$('#config_btn').on('click', function(e){
		var db_name = get_db_name();
		var table_name = get_table_name();
		if (table_name === '') {
			env.popup(T('ERROR'), T('Please select or create table'));
			return;
		}
		edit_fields(db_name, table_name);
	});

	
	function trigger_refresh() {
		if (env.dont_refresh) {
			env.dont_refresh = false;
			return;
		}
		env.dont_refresh = false;

		var now_time = new Date().getTime();
		if ((now_time - env.last_refresh_time) < 1000) {
			return;
		}

		if ((env.db_index!==-1) && (env.table_index!==-1)) {
			$('#refresh_btn').trigger('click');
			env.last_refresh_time = now_time;
		}
	}

	function edit_fields(db_name, table_name)
	{
		var schema_url = get_url(db_name, table_name, 'schema.json');

		json(schema_url, schema_done, function(e) {
			env.popup(T('ERROR'), T('no data filed is set, please add new.'));
		});

		function schema_done(schema_data) {
			var input = [schema_data.listview, schema_data.fields, schema_data.onebox, env.field_types];
			edit_fields_windows(T('Edit fields'), input, function(p){
				var data = {
					cmd:'update_fields', 
					db_name: db_name, 
					table_name: table_name,
					listview:p[0], 
					fields:p[1],
					onebox:p[2]
				};
				post('field.php', data, function(d){
					if (d.hasOwnProperty('status')) {
						if (d.status === 'ok') {
							trigger_refresh();
						} else {
							env.popup(T('ERROR'), T(d.error));
						}
					}
				},function(){
					env.popup(T('ERROR'), T('network request failure.'));
				});
			});
		};
	}

	function render_db_selection(d,index,label,value) 
	{
		if (!d) {return d;}
		var image = 'images/database.png';
		var counter = env.db_captions.length-env.db_cmd_count;
		return '<table style="border:none; border-spacing:0px; font-size:12;"><tr>'+
			'<td><img height="16" width="16" src="'+image+'"/></td>'+
			'<td>x'+ counter +'&nbsp;</td>'+
			'<td>'+ label + '</td>'+
			'</tr></table>';
	}

	function render_table_selection(d,index,label,value) 
	{
		if (!d) {return d;}
		var image = 'images/table.png';
		var db_name = get_db_name();
		var captions = env.table_captions;
		var counter = captions[db_name].length - env.table_cmd_count;
		return '<table style="border:none; border-spacing:0px; font-size:12;"><tr>'+
			'<td><img height="16" width="16" src="'+image+'"/></td>'+
			'<td>x'+ counter +'&nbsp;</td>'+
			'<td>'+ label+ '</td>'+
			'</tr></table>';
	}

	function render_captions(index, label, value) {
		var data = this.records;
		var datarecord = data[index];
		if (is_operate_item(value)) {
			var img = '<img height="25" width="25" src="' + datarecord.image+ '"/>';
			var table = '<table style="max-width: '+(this.width-10)+'px; font-size:12; border-spacing:0px;"><tr><td>' + img + 
				'</td><td><strong>' + datarecord.title +'</strong></td></tr></table>';

		} else {
			var img = '<img height="55" width="55" src="' + datarecord.image+ '"/>';
			var table = '<table style="max-width: '+(this.width-10)+'px; font-size:12; border-spacing:0px;"><tr><td style="width: 70px;" rowspan="2">' + img + 
				'</td><td><strong>' + datarecord.title +'</strong></td></tr><tr><td>' + datarecord.content+ '</td></tr></table>';
		}
		return table;
	}

	function delete_caption_item(is_table, cb_done)
	{
		confirm_dialog(T('Delete database or row'), T('Are you sure???'), function(e){
			var db_name = get_db_name();
			var table_name = get_table_name();
			var data = {};
			data.db_name = db_name;
			if (is_table) {
				data.cmd = 'del_table';
				data.table_name = table_name;
			} else {
				data.cmd = 'del_database';
			}

			submit_schema(data, function(d){
				if (table_name) {
					cb_done(db_name, table_name);
				} else {
					cb_done(db_name);
				}
			});
		});
	}

	function on_create_db(mode)
	{
		if ((mode==='EDITITEM') && (env.db_last_unselect===-1)) {
			return;
		}

		if (mode === 'DELITEM') {
			delete_caption_item(false, function(){
				var index = $('#db_captions').jqxDropDownList('getSelectedIndex'); 
				var db_name = $('#db_captions').jqxDropDownList('val'); 
				$('#db_captions').jqxDropDownList('clearSelection'); 
				$('#db_captions').jqxDropDownList('removeAt', index); 

				del_db_captions(db_name);
				del_table_captions(db_name);
				table_dataAdapter = new $.jqx.dataAdapter({
					localdata: env.table_captions[db_name],
					datatype: "array"
				});
				$('#table_captions').jqxDropDownList('clearSelection'); 
				$('#table_captions').jqxDropDownList('clear'); 
				$('#table_captions').jqxDropDownList({source:table_dataAdapter});
			});
			return;
		}

		var opt_cmd = (mode==='EDITITEM')? 'edit_database' : 'new_database';
		var init_data = undefined;
		if (opt_cmd === 'edit_database') {
			var caption = env.db_captions[env.db_index];
			init_data = {};
			init_data.name = caption.name;
			init_data.title = caption.title;
			init_data.content = caption.content;
			init_data.key = caption.key;
			init_data.image = caption.image;
			init_data.notify = '';
		}

		var title_str = (mode==='EDITITEM')? T('Edit current database') : T('Create new database');

		new_schema_window(title_str, function(data){
			data.cmd = opt_cmd;
			data.ori_name = get_db_name();
			submit_schema(data, function(d){
				if (opt_cmd === 'edit_database') {
					var caption= env.db_captions[env.db_index];
					caption.title = data.title;
					caption.content = data.content;
					caption.image = data.image;
					caption.key = data.key;
					caption.name = data.name;
				} else {
					var caption= {};
					caption.title = data.title;
					caption.content = data.content;
					caption.image = data.image;
					caption.key = data.key;
					caption.name = data.name;
					var captions = env.db_captions;
					captions.splice(captions.length - env.db_cmd_count,0, caption);
					init_table_captions(data.name);
				}
				db_dataAdapter.dataBind();
				table_dataAdapter.dataBind();
			});
		}, init_data);
	}

	function on_create_table(mode, db_name)
	{
		var table_name = get_table_name();
		if ((mode==='EDITITEM') && (env.table_last_unselect === -1)) {
			return;
		}

		if (mode === 'DELITEM') {
			delete_caption_item(true, function(){
				var index = $('#table_captions').jqxDropDownList('getSelectedIndex'); 
				$('#table_captions').jqxDropDownList('clearSelection'); 
				$('#table_captions').jqxDropDownList('removeAt', env.table_index); 
			});
			return;
		}

		var title = db_name;
		for(var index in env.db_captions) {
			var caption = env.db_captions[index];
			if (caption.name === db_name) {
				title = caption.title;
				break;
			}
		}

		var init_data = undefined;
		var opt_cmd = (mode==='EDITITEM')? 'edit_table' : 'new_table';
		if (env.table_index === -1) {
			opt_cmd = 'new_table';
		}

		if (opt_cmd === 'edit_table') {
			var caption = env.table_captions[db_name][env.table_index];
			init_data = {};
			init_data.name = caption.name;
			init_data.title = caption.title;
			init_data.content = caption.content;
			init_data.key = caption.key;
			init_data.image = caption.image;
			init_data.notify = '';
		}

		var title_str = (opt_cmd==='edid_table')? T('Edit current table') : T('Create new table');
		title_str += '('+title+')';

		new_schema_window(title_str, function(data){
			data.cmd = opt_cmd;
			data.db_name = db_name;
			data.ori_name = table_name;
			submit_schema(data, function(d){
				if (opt_cmd === 'edit_table') {
					var caption = env.table_captions[db_name][env.table_index];
					caption.title = data.title;
					caption.content = data.content;
					caption.image = data.image;
					caption.key = data.key;
					caption.name = data.name;
				} else {
					var caption= {};
					caption.title = data.title;
					caption.content = data.content;
					caption.image = data.image;
					caption.key = data.key;
					caption.name = data.name;
					var captions = env.table_captions[db_name];
					captions.splice(captions.length-env.table_cmd_count,0, caption);
				}
				table_dataAdapter.dataBind();
			});
		}, init_data);
	}

	$('#db_captions').on('select', function(event){
		var args = event.args;
		if (args && args.item) {
			var item = args.item;
			var value = item.value;

			if (is_operate_item(value)) {
				env.dont_refresh = true;
				$("#db_captions").jqxDropDownList('selectIndex', env.db_last_unselect); 
				if (value === 'BACKUP') {
					var data = {};
					data.cmd = 'backup_database';
					data.db_name = get_db_name();
					submit_schema(data, function(d){
						env.popup(T('SUCCEED'), T('Backup database successfully.'));
					});
				} else {
					on_create_db(value);
				}
			} else {
				var db_name = value;
				var default_index = where_default(db_name);
				env.table_index = default_index;
				env.db_index = args.index;

				$('#table_captions').jqxDropDownList({disabled: false}); 

				table_dataAdapter = new $.jqx.dataAdapter({
					localdata: env.table_captions[db_name],
					datatype: "array"
				});

				$('#table_captions').jqxDropDownList('clearSelection'); 
				$('#table_captions').jqxDropDownList('clear'); 
				$('#table_captions').jqxDropDownList({source:table_dataAdapter, selectedIndex: default_index});
			}
		}                        
	});

	$('#db_captions').on('unselect', function (event)
	{
		var args = event.args;
		if (args) {
			env.db_last_unselect = args.index;

			var counter = env.db_captions.length - env.db_cmd_count;
			if (env.db_last_unselect >= counter) {
				env.dont_refresh = true;
			}
		}        
	});

	$('#table_captions').on('select', function(event){
		var args = event.args;
		if (args && args.item) {
			var item = args.item;
			var value = item.value;

			if (is_operate_item(value)) {
				$("#table_captions").jqxDropDownList('selectIndex', env.table_last_unselect); 

				if (value === 'CLEANUP') {
					var data = {};
					data.cmd = 'refresh_data';
					data.db_name = get_db_name();
					data.table_name = get_table_name();
					submit_data(data, function(d){
						trigger_refresh();
					});
				} else {
					on_create_table(value, $('#db_captions').jqxDropDownList('val'));
				}
				env.dont_refresh = true;
			} else {
				var table_name = value;
				env.table_index = args.index;
				trigger_refresh();
			}
		}                        
	});

	$('#table_captions').on('unselect', function (event)
	{
		var args = event.args;
		if (args) {
			env.table_last_unselect = args.index;
		}        
	});

	trigger_refresh();
}

function get_index(captions, target_name) 
{
	if (captions instanceof Array) {
		return captions.map(function(e) { return e.name; }).indexOf(target_name);
	}
	return -1;
}

function get_db_name()
{
	if (env.db_index === -1) {
		return '';
	}
	var caption = env.db_captions[env.db_index];
	return caption.name;
}

function get_db_desc(db_name)
{
	for (var index in env.db_captions) {
		var item = env.db_captions[index];
		if (db_name === item.name) {
			return item.title;
		}
	}
	return '';
}

function get_table_name()
{
	if (env.table_index === -1) {
		return '';
	}

	var db_name = get_db_name();
	var caption = env.table_captions[db_name];
	return caption[env.table_index].name;
}

function get_table_desc(db_name, table_name)
{
	var captions = env.table_captions[db_name];
	for (var index in captions) {
		var item = captions[index];
		if (table_name === item.name) {
			return item.title;
		}
	}
	return '';
}

function where_default(db_name)
{
	var find_default = function(captions) {
		for (var index in captions) {
			var caption = captions[index];
			if (/default/.test(caption.name.toLowerCase())) {
				return index;
			}
		}
		return (captions.length>0)? 0: -1;
	}

	if (db_name === undefined) {
		return find_default(env.db_captions);
	} else {
		return find_default(env.table_captions[db_name]);

	}
}

function is_operate_item(name) 
{
	return (
	(name === 'NEWITEM') || 
	(name === 'EDITITEM') || 
	(name === 'DELITEM') ||
	(name === 'BACKUP') ||
	(name === 'CLEANUP')
	);
}

function submit_data(data, cb_done){return submit_post('data.php', data, cb_done);}
function submit_field(data, cb_done){return submit_post('field.php', data, cb_done);}
function submit_schema(data, cb_done){return submit_post('schema.php', data, cb_done);}
function submit_post(url, data, cb_done)
{
	post(url, data, function(d){
		if (d.hasOwnProperty('status')) {
			if (d.status === 'ok') {
				cb_done(d);
			} else {
				env.popup(T('ERROR'), T(d.error));
			}
		}
	},function(){
		env.popup(T('ERROR'), T('network request failure.'));

	});
}



function init_db_captions()
{
	env.db_captions.push(
	    {'name':'NEWITEM', 'image':'images/add.png', 'title':T('New Database')},
	    {'name':'EDITITEM','image':'images/edit.png','title':T('Edit DB Description')},
	    {'name':'DELITEM','image':'images/delete.png','title':T('Delete Database')},
	    {'name':'BACKUP','image':'images/backup.png','title':T('Backup Database')}
	);
}

function init_table_captions(db_name)
{
	var captions = env.table_captions[db_name];
	if (captions === undefined) {
		env.table_captions[db_name] = [];
		captions = env.table_captions[db_name];
	}

	captions.push(
	    {'name':'NEWITEM', 'image':'images/add.png','title':T('Create New Table')},
	    {'name':'EDITITEM','image':'images/edit.png','title':T('Edit Table Description')},
	    {'name':'DELITEM','image':'images/delete.png','title':T('Delete Table')},
	    {'name':'CLEANUP','image':'images/registry.png','title':T('Cleanup Table')}
	);
}

function del_db_captions(db_name)
{
	var found_index = -1;
	for (var index in env.db_captions) {
		var caption = env.db_captions[index];
		if (caption.name === db_name) {
			found_index = index;
			break;
		}
	}
	if (found_index !== -1) {
		env.db_captions.splice(found_index, 1);
	}
}

function del_table_captions(db_name)
{
	env.table_captions[db_name] = [];
}

function get_url(db_name, table_name, filename)
{
	return '/databases/'+db_name+'/'+table_name+'/'+filename;
}

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
				var input = [schema_data.fields, null, options];
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
				var input = [schema_data.fields, old_data ,options];
				var title = T('Edit data')+'('+table_desc+')';
				edit_data_window(title, input, function(new_data){
					var req_data = {};
					req_data['cmd'] = 'update';
					req_data['data'] = new_data;
					req_data['db_name'] = db_name;
					req_data['table_name'] = table_name;
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
			}

			var opt_url = get_url(db_name, table_name, 'options.json');
			json(opt_url, get_items_data, function(d){
				get_items_data({});
			});

		};

		json(data_url, function(data){
			datatables_new(listview_id, data, aoColumns, event);
		}, function() {
			datatables_new(listview_id, [], aoColumns, event);
		});

		set_cookie('init_db', db_name, 30);
		set_cookie('init_table', table_name, 30);
	};
}


</script>
</head>
<body background="images/bg_tile.jpg">
<table style="width:100%; font-size:12px;">
<tr><td>
	<div id="db_captions" style="float:left;"></div>
	<div id="table_captions" style="float:left;"></div>
	<div id="refresh_btn" style="float:left; padding:0px"><img height="25" width="25" src="images/refresh.png"/></div>
	<div id="config_btn" style="float:left; padding:0px"><img height="25" width="25" src="images/setting.png"/></div>
	<div id="user_info" style="float:right; padding:4px; color: white;"></div>
</td></tr><tr><td>
	<div id="listview" style="width:100%;"></div>
</td></tr>
</table>
</body></html>
