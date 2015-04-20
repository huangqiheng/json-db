function build_navi_bar()
{
	window.db_dataAdapter = new $.jqx.dataAdapter({
		localdata: env.db_captions,
		datatype: "array"
	});
	window.table_dataAdapter = new $.jqx.dataAdapter({
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
		//autoOpen: true,
		source:db_dataAdapter}, dropdown_opt));
	$('#table_captions').jqxDropDownList($.extend({
		selectedIndex:env.table_index, 
		selectionRenderer: render_table_selection,
		placeHolder: T('Please Choose Table:'),
		dropDownWidth:343, 
		//autoOpen: true,
		source:table_dataAdapter}, dropdown_opt));

	if (env.table_captions.length === 0) {
		$('#table_captions').jqxDropDownList({disabled: true});
	}

	$('#refresh_btn').jqxButton({width: nav_btn_width, height: nav_btn_height});
	$('#config_btn').jqxButton({width: nav_btn_width, height: nav_btn_height});
	$('#user_info').html('<span>'+T('Hello')+' "'+env.username+'", '+T('you can')+' </span><a href="'+env.logout+'" style="color:red;">'+T('Logout')+'</a>');

	$('#refresh_btn').jqxTooltip(tip_data(T('REFRESH_BTN'),T('REFRESH_BTN_DESC')));
	$('#config_btn').jqxTooltip(tip_data(T('CONFIG_BTN'),T('CONFIG_BTN_DESC')));

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

}

function render_db_selection(d,index,label,value) 
{
	if (!d) {return d;}
	var image = 'images/database.png';
	var counter = env.db_captions.length-env.db_cmd_count;

	if (counter === 0) {
		return d;
	}

	return '<table style="border:none; font-size:12;"><tr>'+
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

	if (db_name === '') {
		return d;
	}

	var counter = captions[db_name].length - env.table_cmd_count;
	return '<table style="border:none; font-size:12;"><tr>'+
		'<td><img height="16" width="16" src="'+image+'"/></td>'+
		'<td>x'+ counter +'&nbsp;</td>'+
		'<td>'+ label+ '</td><td id="counter_table_datas"></td>'+
		'</tr></table>';
}

function render_captions(index, label, value) {
	var data = this.records;
	var datarecord = data[index];
	if (is_operate_item(value)) {
		var img = '<img height="25" width="25" src="' + datarecord.image+ '"/>';
		var table = '<table id="'+datarecord.id+'" style="max-width: '+(this.width-10)+'px;font-size:12; border-spacing:0px;"><tr><td>' + img + 
			'</td><td><strong>' + datarecord.title +'</strong></td></tr></table>';

		setTimeout(function(){
			switch (datarecord.id) {
			case 'tips_db_new_item': var mark='CREATE_DB'; break;
			case 'tips_db_edt_item': var mark='EDIT_DB'; break;
			case 'tips_db_del_item': var mark='DELETE_DB'; break;
			case 'tips_db_bak_item': var mark='BACKUP_DB'; break;
			case 'tips_tbl_new_item': var mark='CREATE_TABLE'; break;
			case 'tips_tbl_edt_item': var mark='EDIT_TABLE'; break;
			case 'tips_tbl_del_item': var mark='DELETE_TABLE'; break;
			case 'tips_tbl_bak_item': var mark='CLEANUP_TABLE'; break;
			}
			var title = mark;
			var content = mark + '_DESC';
			$('#'+datarecord.id).parent('.jqx-item').jqxTooltip(tip_data(T(title), T(content), 'mouse'));
			$(".jqx-tooltip").css("z-index", '99999999999999');
		},100);

	} else {
		var img = '<img height="55" width="55" src="' + datarecord.image+ '"/>';
		var table = '<table style="max-width: '+(this.width-10)+'px; width:100%; font-size:12; border-spacing:0px; background-image: url(images/gradient_grey.png);background-position-y: -75px;"><tr><td style="width: 70px;" rowspan="2">' + img + 
			'</td><td><strong>' + datarecord.title+'('+datarecord.name+')'+'</strong></td></tr><tr><td style="color:gray;">' + datarecord.content+ '</td></tr></table>';
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
		init_data = caption;
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
				caption.hooks= data.hooks;
			} else {
				var caption= {};
				caption.title = data.title;
				caption.content = data.content;
				caption.image = data.image;
				caption.key = data.key;
				caption.name = data.name;
				caption.hooks = data.hooks;
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
		init_data = caption;
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
				caption.hooks= data.hooks;
			} else {
				var caption= {};
				caption.title = data.title;
				caption.content = data.content;
				caption.image = data.image;
				caption.key = data.key;
				caption.name = data.name;
				caption.hooks = data.hooks;
				var captions = env.table_captions[db_name];
				captions.splice(captions.length-env.table_cmd_count,0, caption);
			}
			table_dataAdapter.dataBind();
		});
	}, init_data);
}

function get_index(captions, target_name) 
{
	var ret_index = -1;
	if (captions instanceof Array) {
		ret_index = captions.map(function(e) { return e.name; }).indexOf(target_name);
	}
	return ret_index;
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


function init_db_captions()
{
	env.db_captions.push(
	    {'name':'NEWITEM', 'id':'tips_db_new_item', 'image':'images/add.png', 'title':T('New Database')},
	    {'name':'EDITITEM','id':'tips_db_edt_item', 'image':'images/edit.png','title':T('Edit DB Description')},
	    {'name':'DELITEM', 'id':'tips_db_del_item', 'image':'images/delete.png','title':T('Delete Database')},
	    {'name':'BACKUP',  'id':'tips_db_bak_item', 'image':'images/backup.png','title':T('Backup Database')}
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
	    {'name':'NEWITEM', 'id':'tips_tbl_new_item', 'image':'images/add.png','title':T('Create New Table')},
	    {'name':'EDITITEM','id':'tips_tbl_edt_item', 'image':'images/edit.png','title':T('Edit Table Description')},
	    {'name':'DELITEM', 'id':'tips_tbl_del_item', 'image':'images/delete.png','title':T('Delete Table')},
	    {'name':'CLEANUP', 'id':'tips_tbl_bak_item', 'image':'images/registry.png','title':T('Cleanup Table')}
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

