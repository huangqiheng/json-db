var langdata = {
    'ERROR': 					{cn:'错误'},
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
    'Don\'t leave it blank please.':		{cn:'请不要留空任意一栏。'},
    'Edit current database':			{cn:'编辑当前数据库'},
    'Create new database':			{cn:'创建新数据库'},
    'Create new table':				{cn:'创建新的数据表格'},
    'Edit current table':			{cn:'编辑当前数据表格'},
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

