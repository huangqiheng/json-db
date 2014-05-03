function edit_fields(db_name, table_name)
{
	var schema_url = get_url(db_name, table_name, 'schema.json');

	json(schema_url, schema_done, function(e) {
		env.popup(T('ERROR'), T('no data filed is set, please add new.'));
	});

	function schema_done(schema_data) {
		var input = [schema_data, env.field_types];
		edit_fields_windows(T('Edit fields'), input, function(p){
			var data = {
				cmd:'update_fields', 
				db_name: db_name, 
				table_name: table_name,
				listview :p[0], 
				fields   :p[1],
				initials :p[2],
				options  :p[3],
				buttons  :p[4],
				onebox   :p[5],
				mapper	 :p[6],
				timers   :p[7]
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

function edit_fields_windows(title, input, cb_done)
{
	var schema_data = input[0];
	var fields_types = input[1];

	var listview = schema_data.listview;
	var fields = schema_data.fields;
	var initials = schema_data.initials;
	var options = schema_data.options;
	var buttons = schema_data.buttons;
	var onebox = schema_data.onebox;
	var mapper = schema_data.mapper;
	var timers = schema_data.timers;

	if (mapper === undefined) {
		mapper = [];
	}

	if (timers === undefined) {
		timers = [];
	}

	if (options === undefined) {
		options = [];
	}

	if (initials === undefined) {
		initials = [];
	}

	if (onebox === undefined) {
		onebox = {};
		onebox.title = '';
		onebox.desc = '';
		onebox.image = '';
	}

	if (buttons === undefined) {
		buttons = [];
	}

	var time = new Date().getTime();
	var window_id = 'win_'+time.toString(); 
	var tabs_id = window_id+'_tabs';
	var ok_id = window_id+'_ok';
	var no_id = window_id+'_no';
	var list_id = window_id+'_list';
	var name_id = window_id+'_name';
	var view_id = window_id+'_view';
	var category_id = window_id+'_cate';
	var type_id = window_id+'_type';
	var image_id = window_id+'_image';
	var add_id = window_id+'_add';
	var ob_title_id = window_id+'_obtitle';
	var ob_desc_id = window_id+'_obdesc';
	var ob_image_id = window_id+'_obimage';

	var delbtn_id = window_id+'_del';
	var upbtn_id = window_id+'_up';
	var downbtn_id = window_id+'_down';
	var this_windows_class = 'edit_data_windows';

	var init_list_id = window_id+'_i_list';
	var init_field_id = window_id+'_i_field';
	var init_value_id = window_id+'_i_value';
	var init_suffix_id = window_id+'_i_suffix';
	var init_share_id = window_id+'_i_share';
	var init_readonly_id = window_id+'_i_readonly';
	var init_vital_id = window_id+'_i_vital';
	var init_rank_id = window_id+'_i_rank';
	var init_addbtn_id = window_id+'_i_addbtn';
	var init_delbtn_id = window_id+'_i_delbtn';
	var init_tips_id = window_id+'_i_tips';

	var opt_list_id = window_id+'_o_list';
	var opt_field_id = window_id+'_o_field';
	var opt_value_id = window_id+'_o_value';
	var opt_fixed_id = window_id+'_o_fixed';
	var opt_addbtn_id = window_id+'_o_addbtn';
	var opt_delbtn_id = window_id+'_o_delbtn';

	var map_list_id = window_id+'_m_list';
	var map_field_id = window_id+'_m_field';
	var map_addbtn_id = window_id+'_m_addbtn';
	var map_delbtn_id = window_id+'_m_delbtn';
	var map_upbtn_id = window_id+'_m_up';
	var map_downbtn_id = window_id+'_m_down';

	var timer_list_id = window_id+'_t_list';
	var timer_url_id = window_id+'_t_url';
	var timer_interval_id = window_id+'_t_val';
	var timer_addbtn_id = window_id+'_t_addbtn';
	var timer_delbtn_id = window_id+'_t_delbtn';

	var c_list_id = window_id+'_c_list';
	var c_img_id = window_id+'_c_img';
	var c_url_id = window_id+'_c_url';
	var c_addbtn_id = window_id+'_c_add';
	var c_delbtn_id = window_id+'_c_del';
	var c_upbtn_id = window_id+'_c_up';
	var c_downbtn_id = window_id+'_c_down';
	var c_tips_id = window_id+'_c_tips';
	var btn_width = 22;
	var btn_height = 22;

	$('body').append([
            '<div id="'+window_id+'" class="'+this_windows_class+'">',
                '<div>',
		    '<table id="header_table"><tr><td>',
                    '<img width="20" height="20" src="images/new-table.png" alt="" style="float:left;" />',
		    '</td><td>',
		    	'<strong style="font-size:13px;line-height:23px;">'+title+'</strong>',
		    '</td><td align="right">',
			    '<div style="width:480px; float:right;">',
				'<input type="button" id="'+ok_id+'" value="'+T('OK')+'" style="margin-right: 1px" />',
				'<input type="button" id="'+no_id+'" value="'+T('Cancel')+'" />',
			    '</div>',
		    '</td></tr></table>',
		'</div>',
                '<div>',
                    '<div id="'+tabs_id+'" align="center">',
			    //页签
			    '<ul style="margin-left: 20px;">',
				'<li>'+T('field definer')+'</li>',
				'<li>'+T('field propertys')+'</li>',
				'<li>'+T('option values')+'</li>',
				'<li>'+T('custom buttons')+'</li>',
				'<li>'+T('other settings')+'</li>',
				'</ul>',
			    //定义字段
			    '<div><table style="height:460;width:100%; margin-top:5px;">',
				'<tr><td>',
				    '<div id="'+list_id+'"></div>',
				    '<div style="width:230px;">',
					'<div id="'+delbtn_id+'" style="padding:0px;float:left;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/delete.png"/></div>',
					'<div id="'+upbtn_id+'" style="padding:0px;float:left;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/up.png"/></div>',
					'<div id="'+downbtn_id+'" style="padding:0px;float:left;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/down.png"/></div>',
				    '</div>',
				    '</td>',
				'<td><table style="font-size:13;">',
				    '<tr><td align="right">'+T('category')+':</td><td><input type="text" id="'+category_id+'" /></td><td rowspan="4"><input type="button" id="'+add_id+'" value="'+T('Add/Modify')+'" /></td></tr>',
				    '<tr><td align="right">'+T('name')+':</td><td><input type="text" id="'+name_id+'" /></td></tr>',
				    '<tr><td align="right">'+T('view')+':</td><td><div style="position:relative;top:5px;" id="'+view_id+'"><img height="15" width="30" src="images/eye.png"/></div></td></tr>',
				    '<tr><td align="right">'+T('type')+':</td><td><div id="'+type_id+'"></div></td></tr>',
				    '<tr><td></td><td colspan="2"><div style="width:300px; height:300px; border: 1px solid rgb(224, 224, 224);" id="'+image_id+'" /></td></tr>',
				'</table></td>',
			    '</table></div>',
			    //定义字段属性
			    '<div><p>',
				'<div align="center">',
				    '<div id="'+init_list_id+'"></div>',
				    '<div>',
					'<div id="'+init_field_id+'" style="float:left; margin-left:9px;"></div>',
					'<input type="text" id="'+init_value_id+'" style="float:left;" />',
					'<input type="text" id="'+init_suffix_id+'" style="float:left;" />',
					'<div id="'+init_share_id+'" style="float:left;margin:4px;">'+T('share')+'</div>',
					'<div id="'+init_rank_id+'" style="float:left;margin:4px;">'+T('rank')+'</div>',
					'<div id="'+init_addbtn_id+'" style="padding:0px;float:left;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/add.png"/></div>',
					'<div id="'+init_delbtn_id+'" style="padding:0px;float:left;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/delete.png"/></div>',
					'<textarea id="'+init_tips_id+'" style="float: left;margin-left: 9px;"></textarea>',
					'<div id="'+init_vital_id+'" style="float:left;margin:4px;">'+T('vital')+'</div>',
					'<div id="'+init_readonly_id+'" style="float:left;margin:4px;">'+T('readonly')+'</div>',
				    '</div>',
				'</div>',
			    '</div>',
			    //选项值编辑框
			    '<div><p>',
				'<div align="center">',
				    '<div id="'+opt_list_id+'"></div>',
				    '<div>',
					'<div id="'+opt_field_id+'" style="float:left; margin-left:8px;"></div>',
					'<input type="text" id="'+opt_value_id+'" style="float:left;" />',
					'<div id="'+opt_fixed_id+'" style="float:left;margin:4px;">'+T('fixed')+'</div>',
					'<div id="'+opt_addbtn_id+'" style="padding:0px;float:left;"><img height="'+(btn_height)+'" width="'+btn_width+'" src="images/add.png"/></div>',
					'<div id="'+opt_delbtn_id+'" style="padding:0px;float:left;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/delete.png"/></div>',
				    '</div>',
				'</div>',
			    '</div>',
			    //自定义按钮
			    '<div><p>',
				'<div align="center">',
				    '<div id="'+c_list_id+'"></div>',
				    '<div>',
					'<input type="text" id="'+c_img_id+'" style="float:left; margin-left:8px;" />',
					'<input type="text" id="'+c_url_id+'" style="float:left;" />',
					'<div id="'+c_addbtn_id+'" style="padding:0px;float:left;"><img height="'+(btn_height)+'" width="'+btn_width+'" src="images/add.png"/></div>',
					'<div id="'+c_delbtn_id+'" style="padding:0px;float:left;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/delete.png"/></div>',
					'<div id="'+c_upbtn_id+'" style="padding:0px;float:left;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/up.png"/></div>',
					'<div id="'+c_downbtn_id+'" style="padding:0px;float:left;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/down.png"/></div>',
					'<textarea id="'+c_tips_id+'"></textarea>',
				    '</div>',
				'</div>',
			    '</div>',
			    //onebox编辑框
			    '<div>',
			    	'<div style="width:100px; text-align: center; border: 1px solid rgb(224, 224, 224);top: 15px;left: 20px;padding: 5px;position: relative;background: white;">',
				    T('onebox editor'),
				'</div>',
			        '<div style="width:560px; margin-left:40px; padding-top:15px; border: 1px solid rgb(224, 224, 224);">',
				  '<table style="font-size:13; margin-left:120px;">',
				    '<tr><td align="right">'+T('title')+':</td><td><div id="'+ob_title_id+'"></div></td></tr>',
				    '<tr><td align="right">'+T('desc')+':</td><td><div id="'+ob_desc_id+'"></div></td></tr>',
				    '<tr><td align="right">'+T('thumbnail')+':</td><td><div id="'+ob_image_id+'"></div></td></tr>',
				  '</table>',
				'</div>',

				//自定义映射名称
			    	'<div style="width:100px; text-align: center; border: 1px solid rgb(224, 224, 224);top: 15px;left: 20px;padding: 5px;position: relative;background: white;">',
				    T('custom mapper key'),
				'</div>',
			        '<div style="width:560px; margin-left:40px; padding-top:15px; padding-bottom:35px; border: 1px solid rgb(224, 224, 224);">',
				    '<div id="'+map_list_id+'" style="margin-left:120px;"></div>',
				    '<div>',
					'<div id="'+map_field_id+'" style="float:left; margin-left:120px;"></div>',
					'<div id="'+map_addbtn_id+'" style="padding:0px;float:left;"><img height="'+(btn_height)+'" width="'+btn_width+'" src="images/add.png"/></div>',
					'<div id="'+map_delbtn_id+'" style="padding:0px;float:left;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/delete.png"/></div>',
					'<div id="'+map_upbtn_id+'" style="padding:0px;float:left;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/up.png"/></div>',
					'<div id="'+map_downbtn_id+'" style="padding:0px;float:left;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/down.png"/></div>',
				    '</div>',
				'</div>',
				//设置定时触发器
			    	'<div style="width:100px; text-align: center; border: 1px solid rgb(224, 224, 224);top: 15px;left: 20px;padding: 5px;position: relative;background: white;">',
				    T('define time trigger'),
				'</div>',
			        '<div style="width:560px; margin-left:40px; padding-top:15px; padding-bottom:35px; border: 1px solid rgb(224, 224, 224);">',
				    '<div id="'+timer_list_id+'" style="margin-left:90px;"></div>',
				    '<div>',
					'<input type="text" id="'+timer_url_id+'" style="float:left; margin-left:90px;" />',
					'<input type="text" id="'+timer_interval_id+'" style="float:left;" />',
					'<div id="'+timer_addbtn_id+'" style="padding:0px;float:left;"><img height="'+(btn_height)+'" width="'+btn_width+'" src="images/add.png"/></div>',
					'<div id="'+timer_delbtn_id+'" style="padding:0px;float:left;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/delete.png"/></div>',
				    '</div>',
				'</div>',
			    '</div>',
		    '</div>',
                '</div>',
            '</div>'].join(''));

	$('#'+tabs_id).jqxTabs({ 
		position: 'top', 
		width: 'auto', 
		height: 500, 
		scrollable:false
	});

	var show_notify = function(msg){
		env.popup(T('ERROR'), T(msg));
	};

	var win_count = $('.'+this_windows_class).length;

	$('#'+window_id).jqxWindow({height: 555, width: 660,
			position: {x:win_count*30, y:12+win_count*41},
			resizable: false, isModal: true, modalOpacity: 0.3,
			cancelButton: $('#'+no_id),
			initContent: tree_render
	});

	function tree_render() {
		$('#'+delbtn_id).jqxButton({width:btn_width, height:btn_height});
		$('#'+upbtn_id).jqxButton({width:btn_width, height:btn_height});
		$('#'+downbtn_id).jqxButton({width:btn_width, height:btn_height});
		$('#'+delbtn_id).on('click', event_tree_delete);
		$('#'+upbtn_id).on('click', event_tree_up);
		$('#'+downbtn_id).on('click', event_tree_down);


		$('#'+category_id).jqxInput({width:215, height: 25});
		$('#'+name_id).jqxInput({width:215, height: 25});
		$('#'+view_id).jqxCheckBox({width: 215, height: 25});
		$('#'+type_id).jqxDropDownList({source:fields_types, autoDropDownHeight:true,  
					placeHolder: T('Please choose field type'),
					width:215, height: 25});
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

		$('#'+ok_id).jqxButton({width: 65, height:23});
		$('#'+no_id).jqxButton({width: 65, height:23});
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

			var initials = get_listbox_values(init_list_id);
			var options = get_listbox_values(opt_list_id);
			var custom_btns = get_listbox_values(c_list_id);
			var custom_mapper = get_listbox_values(map_list_id);
			var timers = get_listbox_values(timer_list_id);

			cb_done([listview, fields, 
				 initials, options, custom_btns, 
				 res_onebox, custom_mapper, timers]);
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
		$('#'+list_id).jqxTree({source: records, width:'230px', height:'430px', 
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
				if (item.label==='CREATE') {
					$('#'+name_id).jqxInput({disabled:true});
					$('#'+type_id).jqxDropDownList({disabled:true});
				}
			}
		});


		$('#'+add_id).jqxButton({width: 81, height:'100%'});
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
			return;
		});

		function event_tree_delete(){
			var item = $('#'+list_id).jqxTree('getSelectedItem');
			if (item === null){return;}
			if (item.label === 'ID'){return;}
			if (item.label === 'TIME'){return;}
			if (item.label === 'CREATE'){return;}
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

		//摘要框
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

		//公共属性
		var width = 630;
		var height = 386;
		var source_fields = array_remove(get_list_items(), ['ID','CREATE','TIME']);

		//设置字段属性
		var initval_item = function(field, init_val, suffix, is_share, is_rank, readonly, vital, tips){
			var init_str = (init_val==='')? '--' : init_val;
			var suffix_str = (suffix==='')? '--' : suffix;
			var readonly_str = (readonly) ? T('readonly') : '--'; 
			var vital_str = (vital) ? T('vital') : '--'; 
			var share_str = (is_share) ? T('share') : '--'; 
			var rank_str = (is_rank) ? T('rank') : '--'; 
			var tips_str = (tips===undefined)? '--' : ((tips==='')? '--' : tips);
			var html = '<table style="width:100%; border:none; border-spacing:0px; font-size:12;"><tr>'+
				'<td style="width:90px;">'+ field +'</td>'+
				'<td>'+ init_str + '</td>'+
				'<td style="width:40px;">'+ suffix_str+ '</td>'+
				'<td style="width:40px;">'+ share_str + '</td>'+
				'<td style="width:40px;">'+ rank_str + '</td>'+
				'<td style="width:40px;">'+ readonly_str +'</td>'+
				'<td style="width:40px;">'+ vital_str+'</td>'+
				'<td style="width:200px;">'+ tips_str +'</td>'+
				'</tr></table>';
			var value = {
				field: field,
				value: init_val,
				suffix: suffix,
				share: is_share,
				rank: is_rank,
				readonly: readonly,
				vital: vital,
				tips: tips
			};
			return [html, value];
		};

		var initval_render = function(data, finish) {
			finish(data.concat(initval_item(data[0],data[1],data[2],data[3],data[4],data[5],data[6],data[7])));
		};

		var initval_source = function(init) {
			var source = [];
			if (init instanceof Array) {
				for(var i in init) {
					var item = init[i];
					var format_item = initval_item(item['field'], item['value'], item['suffix'],item['share']==='true', 
							item['rank']==='true', item['readonly']==='true', item['vital']==='true', item['tips']);
					source.push({html:format_item[0], label:format_item[1], value:format_item[1]});
				}
			}
			return source;
		};

		$('#'+init_list_id).jqxListBox({source: initval_source(initials), width: width, height: height});
		$('#'+init_field_id).jqxDropDownList({source:source_fields, autoDropDownHeight:true,  
						placeHolder: T('Please choose field name'), width:150, height: btn_height});
		$('#'+init_value_id).jqxInput({width: (width-(btn_width+2)*2-150-80-50*2-6*3), height: btn_height, placeHolder: T('input initial value, seperated with comma if array')});
		$('#'+init_suffix_id).jqxInput({width: 48, height: btn_height, placeHolder: T('input suffix string')});

		$('#'+init_share_id).jqxCheckBox({width: 50, height: btn_height-8});
		$('#'+init_rank_id).jqxCheckBox({width: 80, height: btn_height-8});
		$('#'+init_readonly_id).jqxCheckBox({width: 50, height: btn_height-8});
		$('#'+init_vital_id).jqxCheckBox({width: 50, height: btn_height-8});
		$('#'+init_addbtn_id).jqxButton({width: btn_width, height:btn_height});
		$('#'+init_delbtn_id).jqxButton({width: btn_width, height:btn_height});
		$('#'+init_tips_id).jqxInput({width: 435, height: 35, placeHolder:T('Please input the help tips of the field')});
		$('#'+init_delbtn_id).on('click', [init_list_id], event_listbox_delete);
		$('#'+init_addbtn_id).on('click',function(e){
			var field_name = $('#'+init_field_id).val();
			var field_value = $('#'+init_value_id).val();
			var field_suffix = $('#'+init_suffix_id).val();
			var is_share = $('#'+init_share_id).jqxCheckBox('checked');
			var is_readonly = $('#'+init_readonly_id).jqxCheckBox('checked');
			var is_vital = $('#'+init_vital_id).jqxCheckBox('checked');
			var is_rank = $('#'+init_rank_id).jqxCheckBox('checked');
			var tips_str = $('#'+init_tips_id).val();
			var rand_arr = [field_name, field_value, field_suffix, is_share, is_rank, is_readonly, is_vital, tips_str, init_list_id];
			if (field_name.length == 0) {return;}

			var update_to_list = function(item){
				initval_render(rand_arr, function (p) {
					var value=p.pop(), label=p.pop();
					$('#'+init_list_id).jqxListBox('updateAt', { label: label, value: value}, item.index);
				});
			};

			var insert_to_list = function() {
				initval_render(rand_arr, function (p) {
					var value=p.pop(), label=p.pop();
					$('#'+init_list_id).jqxListBox('insertAt', {label: label, value: value}, 0); 
				});
			};

			var items = $('#'+init_list_id).jqxListBox('getItems');
			for(var index in items) {
				var item = items[index];
				//证明了这是修改
				if (field_name === item.value.field) {
					update_to_list(item);
					return;
				}
			}

			insert_to_list();
		});
		$('#'+init_list_id).on('select', function(e) {
			var args = e.args;
			if (args) {
				var item = args.item.value;
				$('#'+init_field_id).val(item.field);
				$('#'+init_value_id).val((item.value)?item.value:'');
				$('#'+init_suffix_id).val((item.suffix)?item.suffix:'');
				$('#'+init_share_id).jqxCheckBox({checked:(item.share)});
				$('#'+init_rank_id).jqxCheckBox({checked:(item.rank)});
				$('#'+init_readonly_id).jqxCheckBox({checked:(item.readonly)});
				$('#'+init_vital_id).jqxCheckBox({checked:(item.vital)});
				$('#'+init_tips_id).val((item.tips)?item.tips:'');
			}
		});

		//自定义映射名
		$('#'+map_list_id).jqxListBox({source: mapper, width: 150+btn_width*4+7, height: btn_height*3});
		$('#'+map_field_id).jqxDropDownList({source:source_fields, autoDropDownHeight:true,  
						placeHolder: T('Please choose field name'), width:150, height: btn_height});
		$('#'+map_addbtn_id).jqxButton({width: btn_width, height:btn_height});
		$('#'+map_delbtn_id).jqxButton({width: btn_width, height:btn_height});
		$('#'+map_upbtn_id).jqxButton({width: btn_width, height:btn_height});
		$('#'+map_downbtn_id).jqxButton({width: btn_width, height:btn_height});
		$('#'+map_delbtn_id).on('click', [map_list_id], event_listbox_delete);
		$('#'+map_upbtn_id).on('click', [map_list_id], event_listbox_up);
		$('#'+map_downbtn_id).on('click', [map_list_id], event_listbox_down);
		$('#'+map_addbtn_id).on('click',function(e){
			var field_name = $('#'+map_field_id).val();
			if (field_name.length == 0) {return;}
			$('#'+map_list_id).jqxListBox('addItem', { label: field_name, value: field_name});
		});

		//选项值配置
		var option_item = function(field, opt_arr, fixed){
			var fixed_str = (fixed)? T('fixed') : '--';
			var html = '<table style="width:100%; border:none; border-spacing:0px; font-size:12;"><tr>'+
				'<td style="width:120px;"><div>'+ field +'</div></td>'+
				'<td style="max-width:400px; word-wrap:break-word;">'+ opt_arr.join(', ') +'</td>'+
				'<td style="width:80px;">'+ fixed_str +'</td>'+
				'</tr></table>';
			var value = {
				field: field,
				value: opt_arr,
				fixed: fixed
			};
			return [html, value];
		};

		var option_render = function(data, finish) {
			finish(data.concat(option_item(data[0],data[1],data[2])));
		};

		var option_source = function(init) {
			var source = [];
			if (init instanceof Array) {
				for(var i in init) {
					var item = init[i];
					var format_item = option_item(item['field'], item['value'], item['fixed']==='true');
					source.push({html:format_item[0], label:format_item[1], value:format_item[1]});
				}
			}
			return source;
		};

		$('#'+opt_list_id).jqxListBox({source: option_source(options), width: width, height: height});
		$('#'+opt_field_id).jqxDropDownList({source:source_fields, autoDropDownHeight:true,  
						placeHolder: T('Please choose field name'), width:150, height: btn_height});
		$('#'+opt_value_id).jqxInput({width: (width-(btn_width+2)*2-150-80-5*2), height: btn_height, placeHolder: T('Please input options, seperated with comma')});

		$('#'+opt_fixed_id).jqxCheckBox({width: 80, height: btn_height-8});
		$('#'+opt_addbtn_id).jqxButton({width: btn_width, height:btn_height});
		$('#'+opt_delbtn_id).jqxButton({width: btn_width, height:btn_height});
		$('#'+opt_delbtn_id).on('click', [opt_list_id], event_listbox_delete);
		$('#'+opt_addbtn_id).on('click',function(e){
			var field_name = $('#'+opt_field_id).val();
			var field_value = $('#'+opt_value_id).val();
			if (field_name.length == 0) {return;}
			if (field_value.length == 0) {return;}
			field_value = field_value.replace(/，/g, ',');
			var field_values = field_value.split(',');
			field_values = field_values.filter(function(e){return e.trim()!=='';});
			var is_fixed = $('#'+opt_fixed_id).jqxCheckBox('checked');

			var update_to_list = function(item){
				option_render([field_name, field_values, is_fixed, opt_list_id], function (p) {
					var label=p[4], value=p[5];
					$('#'+opt_list_id).jqxListBox('updateAt', { label: label, value: value}, item.index);
				});
			};

			var insert_to_list = function() {
				option_render([field_name, field_values, is_fixed, opt_list_id], function (p) {
					var label=p[4], value=p[5];
					$('#'+opt_list_id).jqxListBox('insertAt', {label: label, value: value}, 0); 
				});
			};

			var items = $('#'+opt_list_id).jqxListBox('getItems');
			for(var index in items) {
				var item = items[index];
				//证明了这是修改
				if (field_name === item.value.field) {
					update_to_list(item);
					return;
				}
			}

			insert_to_list();
		});
		$('#'+opt_list_id).on('select', function(e) {
			var args = e.args;
			if (args) {
				var item = args.item.value;
				$('#'+opt_field_id).val(item.field);
				$('#'+opt_value_id).val(item.value.join(','));
				$('#'+opt_fixed_id).jqxCheckBox({checked:item.fixed});
			}
		});

		//自定义按钮
		var imgurl_item = function(img, url, tips) {
			var html = '<table style="width:100%; border:none; border-spacing:0px; font-size:12;"><tr>'+
				'<td><img height="25" width="25" src="'+img+'"/></td>'+
				'<td>'+ url +'</td>'+
				'<td style="width:40%;">'+ tips + '</td></tr></table>';
			var value = {
				image: img,
				url: url,
				tips: tips
			};
			return [html, value];
		};

		var imgurl_render = function(data, finish) {
			finish(data.concat(imgurl_item(data[0],data[1],data[2])));
		};

		var imgurl_source = function(init) {
			var source = [];
			if (init instanceof Array) {
				for(var i in init) {
					var item = init[i];
					var format_item = imgurl_item(item['image'], item['url'], item['tips']);
					source.push({html:format_item[0], label:item, value:item});
				}
			}
			return source;
		};

		$('#'+c_list_id).jqxListBox({source: imgurl_source(buttons), width: width, height: height});
		$('#'+c_img_id).jqxInput({width: (width-(btn_width+2)*4-2)/2, height: btn_height, placeHolder: T('Please input image url')});
		$('#'+c_url_id).jqxInput({width: (width-(btn_width+2)*4-2)/2, height: btn_height, placeHolder: T('Please input execute command url')});
		$('#'+c_addbtn_id).jqxButton({width: btn_width, height:btn_height});
		$('#'+c_delbtn_id).jqxButton({width: btn_width, height:btn_height});
		$('#'+c_upbtn_id).jqxButton({width: btn_width, height:btn_height});
		$('#'+c_downbtn_id).jqxButton({width: btn_width, height:btn_height});
		$('#'+c_tips_id).jqxInput({width: width, height: 35, placeHolder:T('Please input the help tips of the button')});

		$('#'+c_delbtn_id).on('click', [c_list_id], event_listbox_delete);
		$('#'+c_upbtn_id).on('click', [c_list_id], event_listbox_up);
		$('#'+c_downbtn_id).on('click', [c_list_id], event_listbox_down);
		$('#'+c_addbtn_id).on('click', function(e) {
			var check_valid = null;
			var img_str = $('#'+c_img_id).val();
			var url_str = $('#'+c_url_id).val();
			var tips_str = $('#'+c_tips_id).val();
			if (img_str.length == 0) {return;}
			if (url_str.length == 0) {return;}
			if (tips_str.length == 0) {return;}

			var update_to_list = function(item){
				imgurl_render([img_str, url_str, tips_str, c_list_id], function (p) {
					var label=p[4], value=p[5];
					$('#'+c_list_id).jqxListBox('updateAt', { label: label, value: value}, item.index);
				});
			};

			var insert_to_list = function() {
				imgurl_render([img_str, url_str, tips_str, c_list_id], function (p) {
					var label=p[4], value=p[5];
					$('#'+c_list_id).jqxListBox('insertAt', {label: label, value: value}, 0); 
				});
			};

			var update_list = function(){
				var items = $('#'+c_list_id).jqxListBox('getItems');
				for(var index in items) {
					var item = items[index];
					//证明了这是修改
					if (url_str === item.value.url) {
						update_to_list(item);
						return true;
					}
				}
				return false;
			};

			if (check_valid) {
				check_valid(c_url_id, function(url) {
					if (url === null) {
						if (!update_list()) {
							insert_to_list();
						}
					}
				});
			} else {
				if (!update_list()) {
					insert_to_list();
				}
			}
		});
		$('#'+c_list_id).on('select', function(e) {
			var args = e.args;
			if (args) {
				var item = args.item.value;
				$('#'+c_img_id).val(item.image);
				$('#'+c_url_id).val(item.url);
				$('#'+c_tips_id).val(item.tips);
			}
		});

		//设置“时间触发器”
		var timers_item = function(url, minutes) {
			var html = '<table style="width:100%; border:none; border-spacing:0px; font-size:12;"><tr>'+
				'<td>'+url+'</td>'+
				'<td>'+ minutes+'</td>'+
				'</tr></table>';
			var value = {
				url: url,
				minutes: minutes
			};
			return [html, value];
		};

		var timers_render = function(data, finish) {
			finish(data.concat(timers_item(data[0],data[1])));
		};

		var timers_source = function(init) {
			var source = [];
			if (init instanceof Array) {
				for(var i in init) {
					var item = init[i];
					var format_item = timers_item(item['url'], item['minutes']);
					source.push({html:format_item[0], label:item, value:item});
				}
			}
			return source;
		};
		$('#'+timer_list_id).jqxListBox({source: timers_source(timers), width: 400, height: btn_height*3});
		$('#'+timer_url_id).jqxInput({width: 400-(btn_width+2)*2-50-1, height: btn_height, placeHolder: T('Please input trigger url')});
		$('#'+timer_interval_id).jqxInput({width: 50, height: btn_height, placeHolder: T('minutes interval')});
		$('#'+timer_addbtn_id).jqxButton({width: btn_width, height:btn_height});
		$('#'+timer_delbtn_id).jqxButton({width: btn_width, height:btn_height});
		$('#'+timer_delbtn_id).on('click', [timer_list_id], event_listbox_delete);
		$('#'+timer_addbtn_id).on('click',function(e){
			var url = $('#'+timer_url_id).val();
			var minutes = $('#'+timer_interval_id).val();
			if (url.length == 0) {return;}
			if (!url.match(/^https?:\/\//i)) {return;}
			if (minutes.length == 0) {return;}
			minutes = parseInt(minutes);
			join_item(timer_list_id, 'url', timers_item(url, minutes));

			function join_item(list_id, key_name, rended) {
				var items = $('#'+timer_list_id).jqxListBox('getItems');
				var new_key = rended[1][key_name];
				for(var index in items) {
					var item = items[index];
					if (new_key === item.value[key_name]) {
						$('#'+list_id).jqxListBox('updateAt', {label: rended[0], value: rended[1]}, item.index);
						return;
					}
				}
				$('#'+list_id).jqxListBox('insertAt', {label: rended[0], value: rended[1]}, 0); 
			}
		});
		$('#'+timer_list_id).on('select', function(e) {
			var args = e.args;
			if (args) {
				var item = args.item.value;
				$('#'+timer_url_id).val(item.url);
				$('#'+timer_interval_id).val(item.minutes);
			}
		});

	}
}

