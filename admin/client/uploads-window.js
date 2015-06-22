
function get_uploaded_file(type, title, cb_done)
{
	type = type || 'all';
	var ranid = rand_str();
	var window_id = 'win_'+ranid; 
	var vars_id = 'var_'+ranid; 
	var jqxTabs_id = window_id+'_jqxTabs'; 
	var this_windows_class = 'uploads_window';
	var ret_id = window_id+'_ret';
	var ok_id = window_id+'_ok';
	var no_id = window_id+'_no';
	var uploader_id = window_id+'_uploader';


	$('body').append([
            '<div id="'+window_id+'" class="'+this_windows_class+'">',
                '<div>',
                    '<img width="20" height="20" src="images/new-table.png" alt="" style="float:left;" /><strong style="font-size:16px;">'+title+'</strong></div>',
                '<div style="height:auto !important;">',
		    '<div>',
                       '<div id="'+uploader_id+'">',
                         '<div>',
                           '<input type="button" id="'+no_id+'" value="'+T('Cancel')+'" style="float:right;" />',
                           '<input type="button" id="'+ok_id+'" value="'+T('OK')+'" style="float:right; margin-right:2px" />',
                           '<input type="text" id="'+ret_id+'" style="float:right; margin-right:2px" />',
                         '</div>',
                       '</div>',
                    '</div>',
                    '<div id="'+jqxTabs_id+'">',
			'<ul style="margin-left: 25px;">',
			    '<li><div class="tab-header">',
			        '<img src="images/ico.png" />',
			        '<div>' + T('Icon') + '</div></div></li>',
			    '<li><div class="tab-header">',
			        '<img src="images/album.png" />',
			        '<div>' + T('Logo') + '</div></div></li>',
			    '<li><div class="tab-header">',
			        '<img src="images/photo.png" />',
			        '<div>' + T('Image') + '</div></div></li>',
			    '<li><div class="tab-header">',
			        '<img src="images/video.png" />',
			        '<div>' + T('Video') + '</div></div></li>',
			    '<li><div class="tab-header">',
			        '<img src="images/music.png" />',
			        '<div>' + T('Music') + '</div></div></li>',
			    '<li><div class="tab-header">',
			        '<img src="images/binary.png" />',
			        '<div>' + T('File') + '</div></div></li>',
			'</ul>',
			'<div class="tab-zone"><div id="'+window_id+'_icon" class="content-container"></div></div>',
			'<div class="tab-zone"><div id="'+window_id+'_logo" class="content-container"></div></div>',
			'<div class="tab-zone"><div id="'+window_id+'_image" class="content-container"></div></div>',
			'<div class="tab-zone"><div id="'+window_id+'_video" class="content-container"></div></div>',
			'<div class="tab-zone"><div id="'+window_id+'_music" class="content-container"></div></div>',
			'<div class="tab-zone"><div id="'+window_id+'_file" class="content-container"></div></div>',
                    '</div>',
		    '<div id="'+vars_id+'" style="display:none;"></div>',
                '</div>',
		'<style type="text/css">',
			'.tab-header {height: 30px;}',
			'.tab-header img {float: left; width: 32px; height: 32px;}',
			'.tab-header div {float: left; margin-left: 6px; text-align: center; margin-top: 5px; font-size: 13px;}',
			'.tab-zone {overflow:hidden;}',
			'.content-container {margin-top: 8px; margin-left: auto !important; margin-right: auto !important;}',
			'.jqx-file-upload-button-browse {width: 100px;}',
			'#'+window_id+'{height: auto !important;}',
			'.image-incell {width:100%; position:absolute; top:0px; bottom:0px; margin:auto;}',
			'#img-tips {max-width: 300px; height: auto;}',
                '</style>',
            '</div>'].join(''));

	$('#'+window_id).jqxWindow({
		height: 'auto', 
		minHeight: 572,
		maxHeight: '100%',
		width: 800,
		position: {x:120, y:50},
		resizable: false, 
		isModal: true, modalOpacity: 0.3,
		cancelButton: $('#'+no_id),
		initContent: function () {
	                $('#'+uploader_id).jqxFileUpload({
				browseTemplate: 'success', 
				uploadTemplate: 'primary',  
				cancelTemplate: 'danger', 
				uploadUrl: 'upload.php', 
				fileInputName: 'fileToUpload',
				width: '100%',
				localization: { 
					browseButton: T('Browse'), 
					uploadButton: T('Upload All'), 
					cancelButton: T('Cancel All'), 
					uploadFileTooltip: T('Upload File'), 
					cancelFileTooltip: T('Cancel File') 
				},
				multipleFilesUpload: true, 
				autoUpload: false 
			});

			$('#'+uploader_id).on('uploadStart', function (event) {
				var fileName = event.args.file;
			});

			$('#'+uploader_id).on('uploadEnd', function (event) {
				var args = event.args;
				var fileName = args.file;
				var res = args.response.split(' ');
				var err_code = parseInt(res[0]);

				if (err_code > 2000) {
					env.popup(T('ERROR'), T('Upload file ERROR'));
				}

				set_result(res[1]);
				init_tab();
			});

			function set_result(value) 
			{
				if (typeof value === 'undefined') {
					value = $('#'+ret_id).jqxInput('val');
				} else {
					$('#'+ret_id).jqxInput('val', value);
				}

				if (value == '') {
					return;
				}

				var imgurl = format_url(value);

				$('#'+ok_id).jqxTooltip({ content: [
					'<b>'+T('The Selected Item:')+'</b><br>',
					'<img id="img-tips" src="'+imgurl+'">'
				].join(''), disabled: false});
			}

			function init_grid_cells(type, colum_count, row_count, ratio) 
			{
			    set_uploadurl(type);
			    jsonp('upload.php',{cmd:'count',type:type}, function(total_count){
				if (total_count === 0) {
					//console.log('init_grid_'+type+' count: 0');
				}

				var cell_width = get_grid_width(colum_count);
				var grid_id = window_id+'_'+type;
				var cell_height = cell_width * ratio;

				$('#'+grid_id).jqxGrid('clear');

				$('#'+grid_id).jqxGrid({
					width: cell_width * colum_count,
					height: 86*6,
					rowsheight: cell_height,
					showheader: false,
					showemptyrow: false,
					virtualmode: true,
					selectionmode: 'singlecell',
					scrollmode: 'logical',
					pageable: true,
					pagermode: 'simple',
					pagesize: row_count,
					source:  new $.jqx.dataAdapter({
						datatype: "array",
						localdata: {},
						totalrecords: parseInt(total_count / colum_count) + 1
					}),
					columns: define_columns(colum_count, function (row, column, cellvalue) {
						return '<div class="cellvalue">'+cellvalue+'</div>';
					}),
					rendergridrows: render_empty_cells,					
					rendered: render_real_cells
				});

				$('#'+grid_id).on('pagesizechanged pagechanged', function (event) {
					$('#'+grid_id).jqxGrid('refreshdata');
				});

				$('#'+grid_id).on('cellselect', function (event) {
					var value = $('#'+grid_id).jqxGrid('getcellvalue', event.args.rowindex, event.args.datafield);
					if (value !== '') {
						var value = value.match(/ori="(.+)?"/)[1];
					}
					set_result(value);
				});

				function render_real_cells(d) {
					if (d !== 'full') return;
					var source = $('#'+grid_id).jqxGrid('source');
					var params = source.params;
					if (!params) return;

					var records = source._source.records;
					jsonp('upload.php',{
						cmd:'list',
						startindex: params.startindex,
						endindex: params.endindex,
						pagesize: colum_count,
						type:type
					}, function(datas){
						for (var i in records) { for (var j in records[i]) {
							records[i][j] = '';
						}}

						var store_data = {};
						for (var i in datas) { for (var j in datas[i]) {
						    var detail = datas[i][j];
						    var imgurl = env.jsondb_root + detail.thumb;
						    records[i][j] = '<img class="image-incell" src="'+imgurl+'" ori="'+detail.ori+'">';
						}}

						$('#'+grid_id).jqxGrid('refreshdata');
					});
					source.params = null;
				}

				function render_empty_cells (params) 
				{
					var source = $('#'+grid_id).jqxGrid('source');
					source.params = params;

					var data = {};
					for (var i = params.startindex; i < params.endindex; i++) {
						var row = {};
						for (var j=0; j<colum_count; j++) {
							var numstr = (j+1).toString();
							var cell_id = grid_id+'_'+i+'_'+j;
							var empt_class = grid_id+'_cell';
							row[numstr] = 'loading...';
						}
						data[i] = row;
					}

					return data;
				}
			    });
			}


			function get_grid_width(colum_count)
			{
				var tab_width = $('#'+jqxTabs_id).width() - 2;
				return parseInt(tab_width / colum_count);
			}

			function define_columns (colum_count, cell_render) 
			{
				var cell_width = get_grid_width(colum_count);
			
				var colum = [];
				for (var j=0; j<colum_count; j++) {
					var numstr = (j).toString();
					var item = {
						text: numstr, 
						datafield:numstr, 
						width: cell_width,
						cellsrenderer:cell_render
					};
					colum.push(item);
				}
				return colum;
			}

			function init_tab(tab) 
			{
				if (typeof tab === 'undefined') {
					var tab = $('#'+jqxTabs_id).jqxTabs('selectedItem');
				}

				var type = id2type(tab);
				switch(type) {
					case 'icon':  return init_grid_cells(type, 18, 16, 1);
					case 'logo':  return init_grid_cells(type, 10, 6, 1);
					case 'image': return init_grid_cells(type, 6, 6, 0.618);
					case 'video': return init_grid_cells(type, 6, 10, 0.618);
					case 'music': return init_grid_cells(type, 6, 10, 0.618);
					case 'file':  return init_grid_cells(type, 5, 20, 0.2);
				}
			}


			$('#'+jqxTabs_id).jqxTabs({ 
				width: '100%', 
				height: 'auto',
				selectionTracker: true, 
				animationType: 'fade',
				selectedItem: select_index(type),
				initTabContent: init_tab
			});

			var disable_lst = disable_arr(type);
			for (var id in disable_lst) {
				$('#'+jqxTabs_id).jqxTabs('disableAt', disable_lst[id]); 
			}

			$('#'+jqxTabs_id).on('selected', function (event) { 
				var index = event.args.item;
				set_uploadurl(id2type(index));
			}); 

			$('#'+ret_id).jqxInput({disabled:true, placeHolder: T('Please browser and upload local file'), height: 22, width: 540});
			$('#'+no_id).jqxButton({width: 65, height:24});
			$('#'+ok_id).jqxButton({width: 65, height:24});
			$('#'+ok_id).jqxTooltip({position:'bottom-left', autoHideDelay:30000, opacity:1, disabled:true});

			$('#'+ok_id).mouseover(function(e){
				if ($('#'+ok_id).jqxTooltip('disabled')) {
					set_result();
					$('#'+ok_id).jqxTooltip('open');
					return;
				}

				var imgurl = format_url($('#'+ret_id).jqxInput('val'));
				var content = $('#'+ok_id).jqxTooltip('content');
				var tipurl = content.match(/src="(.+)?"/)[1];

				if (imgurl !== tipurl) {
					set_result();
					return;
				}
			});

			$('#'+ok_id).on('click', function(e){
				var url = $('#'+ret_id).jqxInput('val');

				if (is_uploaded(url)) {
					cb_done(format_url(url));
				}

				$('#'+ok_id).jqxTooltip('destroy');
				$('#'+window_id).jqxWindow('close');
			});


			$('#'+window_id).on('close', function (event) {  
				if (event.target.id === window_id) {
					$('#'+window_id).jqxWindow('destroy'); 
				}
			}); 


		}
	});

	function is_uploaded(url) 
	{
		return /\/databases\/.+\/ori\/\w{32}\./.test(url);
	}

	function format_url(input_val) 
	{
		if (/https?:\/\//.test(input_val)) {
			return input_val;
		}

		if (is_uploaded(input_val)) {
			return env.jsondb_root + input_val;
		} else {
			return input_val;
		}
	}

	function index_setting(tab_index, data)
	{
		if (typeof data === 'undefined') {
			var store_str = loc_variable(vars_id, 'settings', tab_index);
			if (store_str) {
				return basejson_decode(store_str);
			} else {
				return null;
			}
		} else {
			var store_str = basejson_encode(data);
			return loc_variable(vars_id, 'settings', tab_index, store_str);
		}
	}

	function set_uploadurl(typestr)
	{
		$('#'+uploader_id).jqxFileUpload({ uploadUrl: 'upload.php?cmd=write&type=' + typestr});
	}

	function id2type(tab_id)
	{
		switch(tab_id) {
		  case 0:  return 'icon';
		  case 1:  return 'logo';
		  case 2:  return 'image';
		  case 3:  return 'video';
		  case 4:  return 'music';
		  case 5:  return 'file';
		}
		return null;
	}

	function select_index (type)
	{
		switch(type) {
		  case 'icon':  return 0;
		  case 'logo':  return 1;
		  case 'image': return 2;
		  case 'video': return 3;
		  case 'music': return 4;
		  case 'file':  return 5;
		}
		return 2;
	}

	function disable_arr (type)
	{
		switch(type) {
		  case 'icon':  return [1,2,3,4,5];
		  case 'logo':  return [0,2,3,4,5];
		  case 'image': return [0,1,3,4,5];
		  case 'video': return [0,1,2,4,5];
		  case 'music': return [0,1,2,3,5];
		  case 'file':  return [0,1,2,3,4];
		}
		return [];
	}

}
