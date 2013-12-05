<?php
require_once 'functions.php';
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

<!--加载css-->
<link rel="stylesheet" href="jqwidgets/styles/jqx.base.css" type="text/css" />
<link rel="stylesheet" href="js/jquery.gritter.css" type="text/css" />

<!--加载js-->
<script type="text/javascript" language="javascript" src="js/jquery-2.0.3.min.js"></script>
<script type="text/javascript" language="javascript" src="js/jquery.gritter.min.js"></script>
<script type="text/javascript" language="javascript" src="js/md5.min.js"></script>

<script type="text/javascript" language="javascript" src="jqwidgets/jqx-all.js"></script> 
<script type="text/javascript" language="javascript" src="jqwidgets/globalization/globalize.js"></script>
<script type="text/javascript" language="javascript" src="jqwidgets/globalization/globalize.culture.zh-CN.js"></script> 
<script type="text/javascript" language="javascript" src="js/json-db-lib.js"></script>
<script type="text/javascript" language="javascript" src="js/language.js"></script>

<!--动态生成的js变量-->
<script type="text/javascript" charset="utf-8">
window.env = {};
set_language('cn');
env.web_root = "<?php echo $web_root; ?>";
env.init_db = "<?php echo $init_db; ?>";
env.init_table = "<?php echo $init_table; ?>";

env.db_captions = <?php echo json_encode($db_captions); ?>;
env.table_captions = <?php echo json_encode($table_captions); ?>;
init_db_captions();
for (var db_name in env.table_captions) {init_table_captions(db_name);}

env.db_index = get_index(env.db_captions, env.init_db);
env.table_index = get_index(env.table_captions[env.init_db], env.init_table);
env.db_last_unselect = env.db_index;
env.table_last_unselect = env.table_index;
env.popup = function (title, content){$.gritter.add({title: title, text: content});};

$(document).ready(jsondb_main);

function init_db_captions()
{
	env.db_captions.push(
	    {'name':'NEWITEM', 'image':'images/new-database.ico', 'title':T('New Database')},
	    {'name':'EDITITEM','image':'images/new-database.ico','title':T('Edit DB Description')},
	    {'name':'DELITEM','image':'images/new-database.ico','title':T('Delete Database')}
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
	    {'name':'NEWITEM', 'image':'images/new-table.png','title':T('Create New Table')},
	    {'name':'EDITITEM','image':'images/new-table.png','title':T('Edit Table Description')},
	    {'name':'DELITEM','image':'images/new-table.png','title':T('Delete Table')}
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

function jsondb_main() 
{
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
		itemHeight: -1, height: 25, width: 200,
		renderer: render_captions
	};

	$('#db_captions').jqxDropDownList($.extend({
		selectedIndex:env.db_index, 
		selectionRenderer: render_db_selection,
		placeHolder: T('Please Choose DB:'),
		dropDownWidth:200, 
		source:db_dataAdapter}, dropdown_opt));
	$('#table_captions').jqxDropDownList($.extend({
		selectedIndex:env.table_index, 
		selectionRenderer: render_table_selection,
		placeHolder: T('Please Choose Table:'),
		dropDownWidth:295, 
		source:table_dataAdapter}, dropdown_opt));
	$('#reflash_btn').jqxButton({width: 46, height: 25});
	$('#new_btn').jqxButton({width: 46, height: 25});
	$('#config_btn').jqxButton({width: 32, height: 25});

	$('#reflash_btn').on('click', function(e){
		var db_name = get_db_name();
		var table_name = get_table_name();
		if (table_name === '') {
			env.popup(T('ERROR'), T('Please select or create table'));
			return;
		}
		var listview_url = 'databases/'+db_name+'/'+table_name+'/listview.json';

		console.log(listview_url);

	});


	function render_db_selection(d,index,label,value) 
	{
		if (!d) {return d;}
		var image = 'images/database.png';
		var counter = env.db_captions.length-3;
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
		var counter = captions[db_name].length-3;
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
			var img = '<img height="55" width="65" src="' + datarecord.image+ '"/>';
			var table = '<table style="max-width: '+(this.width-10)+'px; font-size:12; border-spacing:0px;"><tr><td style="width: 70px;" rowspan="2">' + img + 
				'</td><td><strong>' + datarecord.title +'</strong></td></tr><tr><td>' + datarecord.content+ '</td></tr></table>';
		}
		return table;
	}

	function delete_caption_item(cb_done)
	{
		db_name = get_db_name();
		table_name = get_table_name();
		var data = {};
		data.db_name = db_name;
		if (table_name) {
			data.cmd = 'del_table';
			data.table_name = table_name;
		} else {
			data.cmd = 'del_database';
		}

		$.ajax({
			type : "GET",
			dataType : "jsonp",
			data: data,
			url : 'caption.php', 
		}).done(function(d){
			if (d.hasOwnProperty('status')) {
				if (d.status === 'ok') {
					if (table_name) {
						cb_done(db_name, table_name);
					} else {
						cb_done(db_name);
					}
				} else {
					env.popup(T('ERROR'), T(d.error));
				}
			}
		}).fail(function(e){
			env.popup(T('ERROR'), T('network request failure.'));
		});
	}

	function on_create_db(mode)
	{
		if ((mode==='EDITITEM') && (env.db_last_unselect===-1)) {
			return;
		}

		if (mode === 'DELITEM') {
			delete_caption_item(function(){
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
			init_data.image = caption.image;
			init_data.notify = '';
		}

		var title_str = (mode==='EDITITEM')? T('Edit current database') : T('Create new database');

		new_schema_window(title_str, function(data){
			data.cmd = opt_cmd;
			data.ori_name = get_db_name();
			submit_caption(data, function(){
				if (opt_cmd === 'edit_database') {
					var caption= env.db_captions[env.db_index];
					caption.title = data.title;
					caption.content = data.content;
					caption.image = data.image;
					caption.name = data.name;
				} else {
					var caption= {};
					caption.title = data.title;
					caption.content = data.content;
					caption.image = data.image;
					caption.name = data.name;
					var captions = env.db_captions;
					captions.splice(captions.length-3,0, caption);
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
			delete_caption_item(function(){
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
			init_data.image = caption.image;
			init_data.notify = '';
		}

		var title_str = (opt_cmd==='edid_table')? T('Edit current table') : T('Create new table');
		title_str += '('+title+')';

		new_schema_window(title_str, function(data){
			data.cmd = opt_cmd;
			data.db_name = db_name;
			data.ori_name = table_name;
			submit_caption(data, function(){
				if (opt_cmd === 'edit_table') {
					var caption = env.table_captions[db_name][env.table_index];
					caption.title = data.title;
					caption.content = data.content;
					caption.image = data.image;
					caption.name = data.name;
				} else {
					var caption= {};
					caption.title = data.title;
					caption.content = data.content;
					caption.image = data.image;
					caption.name = data.name;
					var captions = env.table_captions[db_name];
					captions.splice(captions.length-3,0, caption);
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
				$("#db_captions").jqxDropDownList('selectIndex', env.db_last_unselect); 
				on_create_db(value);
			} else {
				var db_name = value;
				var default_index = where_default(db_name);
				env.table_index = default_index;
				env.db_index = args.index;

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
		}        
	});

	$('#table_captions').on('select', function(event){
		var args = event.args;
		if (args && args.item) {
			var item = args.item;
			var value = item.value;

			if (is_operate_item(value)) {
				$("#table_captions").jqxDropDownList('selectIndex', env.table_last_unselect); 
				on_create_table(value, $('#db_captions').jqxDropDownList('val'));
			} else {
				var table_name = value;
				env.table_index = args.index;
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

function get_table_name()
{
	if (env.table_index === -1) {
		return '';
	}

	var db_name = get_db_name();
	var caption = env.table_captions[db_name];
	return caption[env.table_index].name;
}

function where_default(db_name)
{
	var find_default = function(captions) {
		for (var index in captions) {
			var caption = captions[index];
			if (caption.name === 'default') {
				return index;
			}
		}
		return -1;
	}

	if (db_name === undefined) {
		return find_default(env.db_captions);
	} else {
		return find_default(env.table_captions[db_name]);

	}
}

function is_operate_item(name) 
{
	return ((name === 'NEWITEM') || (name === 'EDITITEM') || (name === 'DELITEM'));
}

function submit_caption(data, cb_done)
{
	jsonp('caption.php', data, function(d){
		if (d.hasOwnProperty('status')) {
			if (d.status === 'ok') {
				cb_done();
			} else {
				env.popup(T('ERROR'), T(d.error));
			}
		}
	},function(){
		env.popup(T('ERROR'), T('network request failure.'));

	});
}

</script>
</head>
<body background="images/bg_tile.jpg">
<table style="width:100%;">
<tr><td>
	<div id="db_captions" style="float:left;"></div>
	<div id="table_captions" style="float:left;"></div>
	<div id="reflash_btn" style="float:left; padding:0px"><img height="25" width="25" src="images/refresh.png"/></div>
	<div id="new_btn" style="float:left; padding:0px"><img height="25" width="25" src="images/new_record.png"/></div>
	<div id="config_btn" style="float:right; padding:0px"><img height="25" width="25" src="images/setting.png"/></div>
</td></tr><tr><td>

</td></tr>
</table>
</body></html>
