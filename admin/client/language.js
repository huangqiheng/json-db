var langdata = {
    'TIP_LENGTH':					{en:'lines per page', cn:'每页显示行数'},
    'TIP_LENGTH_DESC':					{en:'When too much data is displayed in pages, set how many rows of data per page', 
    							 cn:'数据太多时会分页显示，这里设定每页显示多少行数据'},
    'TIP_FILTER':					{en:'filter input box', cn:'关键字过滤框'},
    'TIP_FILTER_DESC':					{en:'support multiple keywords, seperated by space.', cn:'可以输入多个关键字，每个用空格隔开。'},
    'CREATE_DB':					{en:'comand: create new database.', cn:'命令：创建数据库'},
    'CREATE_DB_DESC':					{en:'create a new database.', cn:'创建一个新数据库'},
    'EDIT_DB':						{en:'comand: edit the database.', cn:'命令：编辑数据库'},
    'EDIT_DB_DESC':					{en:'edit the database.', cn:'编辑数据库的属性，如描述和需要经常更改的密钥'},
    'DELETE_DB':					{en:'comand: delete the database.', cn:'命令：删除数据库'},
    'DELETE_DB_DESC':					{en:'delete the database.', cn:'警告！此命令不可撤销，最好先备份整个数据库，才执行删除'},
    'BACKUP_DB':					{en:'comand: backup the database.', cn:'命令：备份数据库'},
    'BACKUP_DB_DESC':					{en:'backup the database.', cn:'备份数据库，请经常执行此命令'},
    'CREATE_TABLE':					{en:'comand: create new table.', cn:'命令：创建数数据表格'},
    'CREATE_TABLE_DESC':				{en:'create a new table.', cn:'创建一个新数据表格'},
    'EDIT_TABLE':					{en:'comand: edit the table.', cn:'命令：编辑数据表格'},
    'EDIT_TABLE_DESC':					{en:'edit the table.', cn:'编辑数据表格的属性，如描述和需要经常更改的密钥'},
    'DELETE_TABLE':					{en:'comand: delete the table.', cn:'命令：删除数据表格'},
    'DELETE_TABLE_DESC':				{en:'delete the table.', cn:'警告！此命令不可撤销，最好先备份整个数据库，才执行删除'},
    'CLEANUP_TABLE':					{en:'comand: backup the table.', cn:'命令：清理数据表格'},
    'CLEANUP_TABLE_DESC':				{en:'backup the table.', 
    							 cn:'有时候数据表显示错误，例如重复行，或者其他异常，执行此可重新生产显示效果。'+
							    '执行此命令，并不会影响实际的json数据结构。可能时间会比较长，超过1万条记录时，会长达2分钟'},
    'Icon':						{cn:'图标'},
    'Logo':						{cn:'简徽'},
    'Image':						{cn:'图片'},
    'Video':						{cn:'视频'},
    'Music':						{cn:'音乐'},
    'File':						{cn:'文件'},
    'Browse':						{cn:'浏览文件'},
    'Upload All':					{cn:'全部上传'},
    'Cancel All':					{cn:'全部取消'},
    'Upload File':					{cn:'上传文件'},
    'Cancel File':					{cn:'取消文件'},
    'Upload file to server':				{cn:'上传文件到服务器保存。'},
    'Upload file ERROR':				{cn:'上传文件发生错误'},
    'Please browser and upload local file':		{cn:'请浏览和选择上传本地文件'},
    'The Selected Item:': 				{cn:'选中的上传文件：'},
    'Too much datas, faint!':				{cn:'太多数据了，惨，等我有空再扩容！'},
    'Please input image url':				{cn:'请输入按钮图片URL'},
    'Please input url correctly':			{cn:'请正确输入URL网址'},
    'Please input webhook url':				{cn:'请输入webhook的网址'},
    'Please input trigger url':				{cn:'请输入需要触发的URL'},
    'minutes interval':					{cn:'分钟数'},
    'The list has the same value':			{cn:'列表中已经有相同的值了'},
    'Please input execute command url':			{cn:'请输入执行命令的URL'},
    'Input the title of tips'		:		{cn:'输入按钮提示标题'},
    'Please input the help tips of the button':		{cn:'请输入该按钮的提示帮助信息'},
    'Please input the help tips of the field':		{cn:'请输入该字段的提示帮助信息'},
    'input initial value, seperated with comma if array':{cn:'请输入初始化值，如数组则逗号隔开'},
    'input suffix string'				:{cn:'后缀'},
    'Please input options, seperated with comma':	{cn:'请输入选项值，逗号隔开'},
    'The key name already exists.':			{cn:'该关键字名已经存在。'},
    'The value already exists.':			{cn:'该值已经存在。'},
    'seckey':						{cn:'密钥'},
    'webhook':						{cn:'网钩'},
    'Maybe you input name is invalid.':			{cn:'可能你输入的名称不正确，不在映射范围'},
    'Maybe you input url is invalid.': 			{cn:'可能你输入的网址不正确，不在Onebox服务范围'},
    'Delete database or row':				{cn:'删除数据库或表'},
    'readonly':						{cn:'只读'},
    'share':						{cn:'共享'},
    'vital':						{cn:'必要'},
    'rank':						{cn:'排行'},
    'fixed':						{cn:'固定值'},
    'field propertys':					{cn:'设置字段属性'},
    'option values':					{cn:'选项值'},
    'other settings':					{cn:'其他设置'},
    'onebox editor':					{cn:'设置摘要框'},
    'custom mapper key':				{cn:'定义映射关键字'},
    'define time trigger':				{cn:'设置定时触发器'},
    'field definer':					{cn:'定义字段类型'},
    'custom buttons':					{cn:'自定义按钮'},
    'Delete rows':					{cn:'删除记录'},
    'Are you sure???':					{cn:'您真的确定要删除吗？？？'},
    'SUCCEED': 						{cn:'成功'},
    'ERROR': 						{cn:'错误'},
    'Logout':						{cn:'登出'},
    'Hello':						{cn:'您好'},
    'you can':						{cn:'您可以'},
    'Create new data': 					{cn:'生成新数据'},
    'Edit data':					{cn:'编辑数据'},
    'No entries to show':				{cn:'没有数据可以显示'},
    'Showing _START_ to _END_ of _TOTAL_ entries': 	{cn:'共_TOTAL_个记录，显示从第_START_个到第_END_个'},
    ' - filtering from _MAX_ records':			{cn:'，过滤自_MAX_条记录'},
    'All':						{cn:'全部'},
    'Display':						{cn:'显示'},
    'records':					{cn:'条记录'},
    'No data available in table':		{cn:'此数据表中没有数据'},
    'Search:':					{cn:'搜索：'},
    'CONFIG_BTN':				{en:'configuration button', cn:'配置按钮'},
    'CONFIG_BTN_DESC':				{en:'config everything about the tables.', 
    						 cn:'配置表格字段属性，摘要框和自定义按钮等，所有表格的配置都在这里，请慎重修改'},
    'REFRESH_BTN':				{en:'refresh table button', cn:'刷新表格按钮'},
    'REFRESH_BTN_DESC':				{en:'refresh the whole table, selection will be unselect', 
    						 cn:'刷新表格数据，所有选择将会被取消，如果表格数据错误，请点击导航菜单的“整理表格”'},

    'hide button': 				{cn:'隐藏导航栏按钮'},
    'hide navigation bar': 			{cn:'隐藏上方的导航栏，减少误操作，也更简洁美观些，推荐。'},
    'import button': 				{cn:'导入按钮'},
    'import csv or xml file to table': 		{cn:'导入csv或者xml格式到数据表中'},
    'export button': 				{cn:'导出按钮'},
    'export rows to csv for excel':		{cn:'将选定的行，导出成csv格式给excel等编辑'},
    'save button': 				{cn:'保存按钮'},
    'save table as backup file': 		{cn:'保存当前表格数据为单独备份'},
    'delete button': 				{cn:'删除按钮'},
    'delete rows selected': 			{cn:'永久删除选择了的所有行，警惕使用，不可恢复'},
    'add row button': 				{cn:'添加行按钮'},
    'add a json data as a row': 		{cn:'添加一个json结构成为一行'},
    'edit row button': 				{cn:'编辑行按钮'},
    'edit row of json data': 			{cn:'编辑行，每行其实是一个单独的json结构，可以多选并编辑多行'},
    'select all button':			{cn:'全选按钮'},
    'select all rows, even if beening paged':	{cn:'选择全部的行，即使已经被分页显示'},
    'select none button':			{cn:'取消选择按钮'},
    'unselect all highline rows':		{cn:'取消所有选择的行，即使是其他分页显示的'},
    'expands the item, please click again.':	{cn:'已经先展开了字段，请再次点击该按钮。'},
    'Please choose field type':			{cn:'请选择字段类型'},
    'Please choose field name':			{cn:'请选择字段名称'},
    'please fill in "field name"':		{cn:'请填入字段名称'},
    'please fill in "tab name"':		{cn:'请填入标签名称'},
    'please select field type':			{cn:'请选择字段类型'},
    'type': 					{cn:'类型'},
    'view': 					{cn:'显示'},
    'view button': 				{cn:'显示数据按钮'},
    'view json data details': 			{cn:'显示完整的json结构，查看数据的细节'},
    'category': 				{cn:'分类'},
    'Add/Modify': 				{cn:'添加/修改'},
    'no data found, please add new.':		{cn:'没有发现数据，请添加'},
    'no data field is set, please add new.':	{cn:'没有发现数据表字段，请添加'},
    'no data is found, item id error.':		{cn:'没有发现数据，可能是id错误'},
    'Please select or create table': 		{cn:'请选择表格或者创建新表'},
    'Please Choose DB:': 			{cn:'请选择数据库：'},
    'Please Choose Table:': 			{cn:'请选择表格：'},
    'unknow command.':				{cn:'未知命令'},
    'execute error':				{cn:'执行错误'},
    'execute succefully':			{cn:'执行成功'},
    'execute failure':				{cn:'执行失败'},
    'The request database already exists.':	{cn:'请求的数据库已经存在了'},
    'The request database not found.':		{cn:'请求的数据库不存在'},
    'The request table already exists.':	{cn:'请求的数据表格已经存在了'},
    'The request table not found.':		{cn:'请求的数据表格不存在'},
    'New Database':				{cn:'创建新数据库'},
    'Edit DB Description':			{cn:'编辑数据库的描述'},
    'Create New Table':				{cn:'创建新数据表格'},
    'Edit Table Description':			{cn:'编辑数据表格的描述'},
    'Copy Table':				{cn:'复制数据表格'},
    'Copy to':					{cn:'复制到'},
    'Copy or move table':			{cn:'复制或者移动数据表'},
    'Delete src':				{cn:'删除原数据'},
    'Yes':					{cn:'是'},
    'No':					{cn:'否'},
    'name':					{cn:'名称'},
    'title':					{cn:'标题'},
    'desc':					{cn:'描述'},
    'image':					{cn:'图片'},
    'thumbnail':				{cn:'缩略图'},
    'OK':					{cn:'确认'},
    'Cancel':					{cn:'取消'},
    'Expand':					{cn:'展开'},
    'Collapse':					{cn:'收起'},
    'Don\'t leave it blank please.':		{cn:'请不要留空任意一栏。'},
    'Edit current database':			{cn:'编辑当前数据库'},
    'Create new database':			{cn:'创建新数据库'},
    'Create new table':				{cn:'创建新的数据表格'},
    'Copy current table':			{cn:'复制当前数据表格'},
    'Edit current table':			{cn:'编辑当前数据表格'},
    'Edit fields':				{cn:'编辑数据表格字段'},
    'network request failure.':			{cn:'网络请求失败了。'},
    'Delete Database':				{cn:'删除数据库'},
    'Backup Database':				{cn:'备份数据库'},
    'Cleanup Table':				{cn:'整理数据表格，删除冗余信息'},
    'Delete Table':				{cn:'删除数据表格'},
    'Backup database successfully.':		{cn:'已经成功备份数据库，恢复请找管理员。'}
};

var lang_selected = 'en';

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

