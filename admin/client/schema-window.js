function new_schema_window(title, cb_done, init_data)
{
	var time = new Date().getTime();
	var window_id = 'win_'+time.toString(); 
	var ok_id = window_id+'_ok';
	var no_id = window_id+'_no';
	var name_id = window_id+'_name';
	var title_id = window_id+'_title';
	var content_id = window_id+'_content';
	var key_id = window_id+'_key';
	var image_id = window_id+'_image';
	var image_btn_id = window_id+'_imgbtn';
	var image_grid_id = window_id+'_imggrid';

	var wk_list_id = window_id+'_wk_list';
	var wk_input_id = window_id+'_wk_input';
	var wk_addbtn_id = window_id+'_wk_addbtn';
	var wk_delbtn_id = window_id+'_wk_delbtn';

	var notify_id = window_id+'_notify';
	var logos_winid = window_id+'_logos';
	var this_windows_class = 'new_schema_windows';

	var btn_width = 22;
	var btn_height = 22;

	$('body').append([
            '<div id="'+window_id+'" class="'+this_windows_class+'">',
                '<div>',
                    '<img width="20" height="20" src="images/new-table.png" alt="" style="float:left;" /><strong style="font-size:16px;">'+title+'</strong></div>',
                '<div>',
                    '<div align="center"><p><table>',
			'<tr><td align="right">'+T('name')+':</td><td><input type="text" id="'+name_id+'" /></td></tr>',
			'<tr><td align="right">'+T('title')+':</td><td><input type="text" id="'+title_id+'" /></td></tr>',
			'<tr><td align="right" style="vertical-align:top;">'+T('desc')+':</td><td><textarea id="'+content_id+'"></textarea></td></tr>',
			'<tr><td align="right">'+T('seckey')+':</td><td><input type="text" id="'+key_id+'" /></td></tr>',
			'<tr><td align="right">'+T('image')+':</td><td><input type="text" id="'+image_id+'" style="float:left;"/>',
				'<div id="'+image_btn_id+'" style="float:left;"><div id="'+image_grid_id+'"></div></div></td></tr>',
			'<tr><td align="right" style="vertical-align:top;">'+T('webhook')+':</td><td>',
				'<div id="'+wk_list_id+'"></div>',
				'<input id="'+wk_input_id+'" style="float:left" />',
				'<div id="'+wk_addbtn_id+'" style="padding:0px;float:left;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/add.png"/></div>',
				'<div id="'+wk_delbtn_id+'" style="padding:0px;float:left;"><img height="'+btn_height+'" width="'+btn_width+'" src="images/delete.png"/></div>',
			    '</td></tr>',
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

	var win_count = $('.'+this_windows_class).length;

	$('#'+window_id).jqxWindow({height: 450, width: 450,
		position: {x:win_count*30+200, y:50+win_count*41},
		resizable: false, isModal: true, modalOpacity: 0.3,
		cancelButton: $('#'+no_id),
		initContent: function () {
			var right_width = 300;

			var wk_source = [];

			if (init_data) {
			if (init_data.hooks) {
				for(var i in init_data.hooks) {
					var url = init_data.hooks[i];
					wk_source.push({label: url, value: url});
				}
			}
			}

			$('#'+name_id).jqxInput({width:right_width, height: 25});
			$('#'+title_id).jqxInput({width:right_width, height: 25});
			$('#'+content_id).jqxInput({width:right_width, height: 50});
			$('#'+key_id).jqxInput({width:right_width, height: 25});
			$('#'+image_id).jqxInput({width:273, height: 25});
			$('#'+image_btn_id).jqxDropDownButton({width: 25, height: 25, dropDownHorizontalAlignment: 'right'});
			$('#'+image_btn_id).css('background-image', 'url(images/add.png)');
			$('#'+image_btn_id).css('background-size', '100%');

			$('#'+wk_list_id).jqxListBox({source: wk_source, width: right_width, height: 100});
			$('#'+wk_input_id).jqxInput({width: (right_width-(btn_width+2)*2), height: btn_height,placeHolder: T('Please input webhook url')});
			$('#'+wk_addbtn_id).jqxButton({width: btn_width, height:btn_height});
			$('#'+wk_delbtn_id).jqxButton({width: btn_width, height:btn_height});
			$('#'+wk_list_id).on('select', [wk_input_id], event_listbox_select);
			$('#'+wk_delbtn_id).on('click', [wk_list_id], event_listbox_delete);
			$('#'+wk_addbtn_id).on('click',[wk_input_id,wk_list_id,null,function(input_str,cb){
				if (is_url(input_str)) {return cb(null);} 
				cb(input_str);
				env.popup(T('ERROR'), T('Please input url correctly'));
			}], event_listbox_add);

			jsonp('options.php', {what: 'logos'}, function(d){
				if (d.status === 'ok') {
					init_logos_grid(d.items);
				}
			});

			if (init_data) {
				if (!init_data.hasOwnProperty('key') || (init_data.key == '') || (!init_data.key)) {
					init_data.key = rank_str();
				}
				$('#'+name_id).val(init_data.name);
				$('#'+title_id).val(init_data.title);
				$('#'+content_id).val(init_data.content);
				$('#'+key_id).val(init_data.key);
				$('#'+image_id).val(init_data.image);
				$('#'+notify_id).text(init_data.notify);
			} else {
				$('#'+key_id).val(rank_str());
			}

			$('#'+ok_id).jqxButton({width: 65, height:35});
			$('#'+no_id).jqxButton({width: 65, height:35});
			focus_on_blank([name_id,title_id,content_id,image_id]);

			$('#'+ok_id).on('click', function(e){
				var d_name = $('#'+name_id).jqxInput('val');
				var d_title = $('#'+title_id).jqxInput('val');
				var d_content = $('#'+content_id).jqxInput('val');
				var d_key = $('#'+key_id).jqxInput('val');
				var d_image = $('#'+image_id).jqxInput('val');
				var d_hooks = get_listbox_values(wk_list_id);

				if ((d_title==='')||(d_content==='')||(d_image==='')) {
					var d_init = {
						'name': d_name,
						'title': d_title,
						'content': d_content,
						'key': d_key,
						'image': d_image,
						'hooks': d_hooks,
						'notify': T('Don\'t leave it blank please.')
					};
					new_schema_window(title, cb_done, d_init);
					return;
				}
				cb_done({
					'name': d_name,
					'title': d_title,
					'content': d_content,
					'key': d_key,
					'image': d_image,
					'hooks': d_hooks
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

	function init_logos_grid(urls) 
	{
		$('#'+image_btn_id).bind('open', function () { 
			var url2img = function(url){
				return '<img height="64" width="64" src="'+url+'">';
			};

			var make_array_data = function(urls) 
			{
				var items = [];
				for(var i in urls) {
					items.push(url2img(urls[i]));
				}

				var data = Array();
				while (items.length > 0) {
					var line = {};
					line['1'] = items.shift();
					line['2'] = items.shift();
					line['3'] = items.shift();
					line['4'] = items.shift();
					line['5'] = items.shift();
					line['6'] = items.shift();
					line['7'] = items.shift();
					line['8'] = items.shift();
					data.push(line);
				};
				return data;
			};

			var source = $('#'+image_grid_id).jqxGrid('source');
			var localdatas = make_array_data(urls);
			source._source.localdata = localdatas;
			source.dataBind();
		});
		$('#'+image_btn_id).bind('close', function () { 
			$('#'+image_grid_id).jqxGrid('clearselection');
		});

		$('#'+image_grid_id).jqxGrid({
			width: 560,
			columnsresize: true,
			showemptyrow: false,
			autoheight: true,
			autorowheight: true,
			showheader: false,
			selectionmode: 'singlecell',
			columns: [
				{text: '1', columntype: 'textbox', datafield: '1', width: 70 },
				{text: '2', columntype: 'textbox', datafield: '2', width: 70 },
				{text: '3', columntype: 'textbox', datafield: '3', width: 70 },
				{text: '4', columntype: 'textbox', datafield: '4', width: 70 },
				{text: '5', columntype: 'textbox', datafield: '5', width: 70 },
				{text: '6', columntype: 'textbox', datafield: '6', width: 70 },
				{text: '7', columntype: 'textbox', datafield: '7', width: 70 },
				{text: '8', columntype: 'textbox', datafield: '8', width: 70 }
			],
			source : new $.jqx.dataAdapter({
				localdata: [],
				datafields:
				[
					{name: '1', type: 'string'},
					{name: '2', type: 'string'},
					{name: '3', type: 'string'},
					{name: '4', type: 'string'},
					{name: '5', type: 'string'},
					{name: '6', type: 'string'},
					{name: '7', type: 'string'},
					{name: '8', type: 'string'}
				],
				datatype: "array"
			})
		});

		$('#'+image_grid_id).on('cellselect', function (event) {
			var value = $('#'+image_grid_id).jqxGrid('getcellvalue', event.args.rowindex, event.args.datafield);
			if (value == '') {return;} 

			var patt = new RegExp(/src="(.+)"/);
			var result = patt.exec(value);
			var img_url = $.trim(result[1]);

			$('#'+image_id).val(img_url);
			$('#'+image_btn_id).jqxDropDownButton('close'); 
		});
	}


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
