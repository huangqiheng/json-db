
function get_listbox_values(listbox_id)
{
	var values = [];
	var items = $('#'+listbox_id).jqxListBox('getItems'); 
	for (var index in items) {
		var item = items[index];
		values.push(item.value);
	}
	return values;
}

function tip_data(title, content, position)
{
	position || (position = 'bottom-left');
	return {content: ['<table style="font-size:14px;max-width:200px;">',
		'<tr><td><b style="color:blue;">'+title+'</b></td><tr>',
		'<tr><td>'+content+'</td></tr>',
		'</table>'].join(''), 
		position: position, 
		opacity: 1,
		name: 'listview'};
}

function hereDoc(f) 
{
	return f.toString().
		replace(/^[^\/]+\/\*!?/, '').
		replace(/\*\/[^\/]+$/, '');
};

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


function check_name_valid(input_str, cb_done)
{
	if (input_str === null) {
		return;
	}

	jsonp('valid.php', {
		db_name: get_db_name(),
		table_name: get_table_name(),
		what: 'mapper',
		is: input_str
	}, function(d){
		if (d.status === 'ok') {
			cb_done(d.url);
			env.popup(T('ERROR'), T('The key name already exists.')+'<a target="blank" href="'+url+'">'+url+'</a>');
		} else {
			cb_done(null);
		}
	}, function(d){
		cb_done(null);
	});
}

function event_listbox_select(e) 
{
	var input_id = e.data[0];

	var args = e.args;
	if (args) {
		var value = args.item.value;
		$('#'+input_id).val(value);
	}
}

function event_listbox_add(e) 
{
	var input_id = e.data[0];
	var listbox_id = e.data[1];
	var render = e.data[2];
	var check_valid = e.data[3];

	var input_str = $('#'+input_id).val();
	if (input_str.length > 0) {
		var insert_to_list = function() {
			var items = $('#'+listbox_id).jqxListBox('getItems'); 
			var found = false;
			for (var index in items) {
				var item = items[index];
				var value = item.value;
				if (typeof value === 'string') {
					if (value === input_str) {
						found = true;
						break;
					}
				}
			}

			if (found) {
				$('#'+input_id).select();
				env.popup(T('ERROR'), T('The list has the same value'));
				return;
			}

			if (render) {
				render([input_str, listbox_id], function (p) {
					var input_str=p[0], listbox_id=p[1], label=p[2], value=p[3];
					$('#'+listbox_id).jqxListBox('insertAt', {label: label, value: value}, 0); 
				});
			} else {
				$('#'+listbox_id).jqxListBox('insertAt', {label: input_str, value: input_str}, 0); 
			}
			$('#'+input_id).val('');
		};
		if (check_valid) {
			check_valid(input_str, function(url) {
				if (url === null) {
					insert_to_list();
				} else {
					$('#'+input_id).select();
				}
			});
		} else {
			insert_to_list();
		}
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

function mk_guid()
{
	return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
			var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
			return v.toString(16);
			});
}

function rank_str()
{
	var guid = mk_guid();
	return guid.replace(/-/g, '');
}

function json_encode (mixed_val) 
{
	// *        example 1: json_encode(['e', {pluribus: 'unum'}]);
	// *        returns 1: '[\n    "e",\n    {\n    "pluribus": "unum"\n}\n]'
	var retVal, json = this.window.JSON;
	try {
		if (typeof json === 'object' && typeof json.stringify === 'function') {
			retVal = json.stringify(mixed_val); // Errors will not be caught here if our own equivalent to resource
			//  (an instance of PHPJS_Resource) is used
			if (retVal === undefined) {
				throw new SyntaxError('json_encode');
			}
			return retVal;
		}

		var value = mixed_val;
		var quote = function (string) {
			var escapable = /[\\\"\u0000-\u001f\u007f-\u009f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;
			var meta = { // table of character substitutions
				'\b': '\\b',
				'\t': '\\t',
				'\n': '\\n',
				'\f': '\\f',
				'\r': '\\r',
				'"': '\\"',
				'\\': '\\\\'
			};

			escapable.lastIndex = 0;
			return escapable.test(string) ? '"' + string.replace(escapable, function (a) {
					var c = meta[a];
					return typeof c === 'string' ? c : '\\u' + ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
			}) + '"' : '"' + string + '"';
		};

		var str = function (key, holder) {
			var gap = '';
			var indent = '    ';
			var i = 0; // The loop counter.
			var k = ''; // The member key.
			var v = ''; // The member value.
			var length = 0;
			var mind = gap;
			var partial = [];
			var value = holder[key];

			// If the value has a toJSON method, call it to obtain a replacement value.
			if (value && typeof value === 'object' && typeof value.toJSON === 'function') {
				value = value.toJSON(key);
			}

			// What happens next depends on the value's type.
			switch (typeof value) {
				case 'string':
					return quote(value);

				case 'number':
					return isFinite(value) ? String(value) : 'null';

				case 'boolean':
				case 'null':
					return String(value);

				case 'object':
					if (!value) {
						return 'null';
					}
					if ((this.PHPJS_Resource && value instanceof this.PHPJS_Resource) || (window.PHPJS_Resource && value instanceof window.PHPJS_Resource)) {
						throw new SyntaxError('json_encode');
					}
					gap += indent;
					partial = [];
					if (Object.prototype.toString.apply(value) === '[object Array]') {
						length = value.length;
						for (i = 0; i < length; i += 1) {
							partial[i] = str(i, value) || 'null';
						}
						v = partial.length === 0 ? '[]' : gap ? '[\n' + gap + partial.join(',\n'+gap)+'\n'+mind+']' : '['+partial.join(',')+']';
						gap = mind;
						return v;
					}

					for (k in value) {
						if (Object.hasOwnProperty.call(value, k)) {
							v = str(k, value);
							if (v) {
								partial.push(quote(k) + (gap ? ': ' : ':') + v);
							}
						}
					}
					v = partial.length === 0 ? '{}' : gap ? '{\n' + gap + partial.join(',\n' + gap) + '\n' + mind + '}' : '{' + partial.join(',') + '}';
					gap = mind;
					return v;
				case 'undefined': // Fall-through
				case 'function': // Fall-through
				default: throw new SyntaxError('json_encode');
			}
		};
		return str('', {'': value});

	} catch (err) { // Todo: ensure error handling above throws a SyntaxError in all cases where it could
		if (!(err instanceof SyntaxError)) {
			throw new Error('Unexpected error type in json_encode()');
		}
		this.php_js = this.php_js || {};
		this.php_js.last_error_json = 4; // usable by json_last_error()
		return null;
	}
}

function json_decode (str_json) 
{
	// *        example 1: json_decode('[\n    "e",\n    {\n    "pluribus": "unum"\n}\n]');
	// *        returns 1: ['e', {pluribus: 'unum'}]
	var json = this.window.JSON;
	if (typeof json === 'object' && typeof json.parse === 'function') {
		try {
			return json.parse(str_json);
		} catch (err) {
			if (!(err instanceof SyntaxError)) {
				throw new Error('Unexpected error type in json_decode()');
			}
			this.php_js = this.php_js || {};
			this.php_js.last_error_json = 4; // usable by json_last_error()
			return null;
		}
	}

	var cx = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g;
	var j;
	var text = str_json;

	cx.lastIndex = 0;
	if (cx.test(text)) {
		text = text.replace(cx, function (a) {
				return '\\u' + ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
				});
	}

	if ((/^[\],:{}\s]*$/).
			test(text.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g, '@').
			replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
			replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {
		j = eval('(' + text + ')');
		return j;
	}

	this.php_js = this.php_js || {};
	this.php_js.last_error_json = 4; // usable by json_last_error()
	return null;
}

function array_remove(array, items)
{
	for(var i in array){
		for(var j in items) {
			var item = items[j];
			if(array[i]==item){
				array.splice(i,1);
			}
		}
	}
	return array;
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

function get_url(db_name, table_name, filename)
{
	var matchs = window.location.href.match(/^http:\/\/([^\/]+)\/(([^\/]+\/)+)admin\/index\.php/);

	if (matchs === null) {
		return '/databases/'+db_name+'/'+table_name+'/'+filename;
	} else {
		var host = matchs[1];
		var prefix = matchs[2];
		return '/'+prefix +'databases/'+host+'/'+db_name+'/'+table_name+'/'+filename;
	}
}

function empty(mixed_var) {
	//  discuss at: http://phpjs.org/functions/empty/
	// original by: Philippe Baumann
	//    input by: Onno Marsman
	//    input by: LH
	//    input by: Stoyan Kyosev (http://www.svest.org/)
	// bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// improved by: Onno Marsman
	// improved by: Francesco
	// improved by: Marc Jansen
	// improved by: Rafal Kukawski
	//   example 1: empty(null);
	//   returns 1: true
	//   example 2: empty(undefined);
	//   returns 2: true
	//   example 3: empty([]);
	//   returns 3: true
	//   example 4: empty({});
	//   returns 4: true
	//   example 5: empty({'aFunc' : function () { alert('humpty'); } });
	//   returns 5: false

	var undef, key, i, len;
	var emptyValues = [undef, null, false, 0, '', '0'];

	for (i = 0, len = emptyValues.length; i < len; i++) {
		if (mixed_var === emptyValues[i]) {
			return true;
		}
	}

	if (typeof mixed_var === 'object') {
		for (key in mixed_var) {
			// TODO: should we check for own properties only?
			//if (mixed_var.hasOwnProperty(key)) {
			return false;
			//}
		}
		return true;
	}

	return false;
}

function is_url(url_str)
{
	return url_str.match(/(http|ftp|https):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?/g);
}
