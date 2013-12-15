var langdata = {
    'ERROR': 						{cn:'错误'},
    'Create new data': 					{cn:'生成新数据'},
    'Edit data':					{cn:'编辑数据'},
    'No entries to show':				{cn:'没有数据可以显示'},
    'Showing _START_ to _END_ of _TOTAL_ entries': 	{cn:'共_TOTAL_个记录，显示从第_START_个到第_END_个'},
    ' - filtering from _MAX_ records':			{cn:'，过滤自_MAX_条记录'},
    'Display _MENU_ records':				{cn:'显示 _MENU_ 条记录'},
    'No data available in table':		{cn:'此数据表中没有数据'},
    'Search:':					{cn:'搜索：'},
    'delete': 					{cn:'删除'},
    'add': 					{cn:'添加'},
    'edit': 					{cn:'编辑'},
    'select all':				{cn:'全选'},
    'select none':				{cn:'不选'},
    'refresh':					{cn:'刷新'},
    'expands the item, please click again.':	{cn:'已经先展开了字段，请再次点击该按钮。'},
    'Please choose field type':			{cn:'请选择字段类型'},
    'please fill in "field name"':		{cn:'请填入字段名称'},
    'please fill in "tab name"':		{cn:'请填入标签名称'},
    'please select field type':			{cn:'请选择字段类型'},
    'type': 					{cn:'类型'},
    'view': 					{cn:'显示'},
    'category': 				{cn:'分类'},
    'Add/Modify': 				{cn:'添加/修改'},
    'no data found, please add new.':		{cn:'没有发现数据，请添加'},
    'no data field is set, please add new.':	{cn:'没有发现数据表字段，请添加'},
    'no data is found, item id error.':		{cn:'没有发现数据，可能是id错误'},
    'Please select or create table': 		{cn:'请选择表格或者创建新表'},
    'Please Choose DB:': 			{cn:'请选择数据库：'},
    'Please Choose Table:': 			{cn:'请选择表格：'},
    'unknow command.':				{cn:'未知命令'},
    'The request database already exists.':	{cn:'请求的数据库已经存在了'},
    'The request database not found.':		{cn:'请求的数据库不存在'},
    'The request table already exists.':	{cn:'请求的数据表格已经存在了'},
    'The request table not found.':		{cn:'请求的数据表格不存在'},
    'New Database':				{cn:'创建新数据库'},
    'Edit DB Description':			{cn:'编辑数据库的描述'},
    'Create New Table':				{cn:'创建新数据表格'},
    'Edit Table Description':			{cn:'编辑数据表格的描述'},
    'name':					{cn:'名称'},
    'title':					{cn:'标题'},
    'desc':					{cn:'描述'},
    'image':					{cn:'图片'},
    'OK':					{cn:'确认'},
    'Cancel':					{cn:'取消'},
    'Expand':					{cn:'展开'},
    'Collapse':					{cn:'收起'},
    'Don\'t leave it blank please.':		{cn:'请不要留空任意一栏。'},
    'Edit current database':			{cn:'编辑当前数据库'},
    'Create new database':			{cn:'创建新数据库'},
    'Create new table':				{cn:'创建新的数据表格'},
    'Edit current table':			{cn:'编辑当前数据表格'},
    'Edit fields':				{cn:'编辑数据表格字段'},
    'network request failure.':			{cn:'网络请求失败了。'},
    'Delete Database':				{cn:'删除数据库'},
    'Delete Table':				{cn:'删除数据表格'}
};

var lang_selected = null;

function T(en)
{
	var lang = langdata[en];
	if (lang === undefined) {return en;}
	var res = lang[lang_selected];
	if (res === undefined) {return en;}
	return res;
}

function set_language(code)
{
	lang_selected = code;
}

