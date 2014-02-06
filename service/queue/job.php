<?php
require_once '../../admin/functions.php';
require_once 'Eventio/BBQ.php';

use Eventio\BBQ;
use Eventio\BBQ\Queue\DirectoryQueue;
use Eventio\BBQ\Job\Payload\StringPayload;

define('CACHE_PATH', 'cache/bbq');

$req = get_param();
list($name,$key,$data) = null_exit($req,'name','table_name','data','apikey');
items_exit($data,'ident','facility','priority','message');

$bbq   = new BBQ();
$queue = new DirectoryQueue('tasks', dirname(__FILE__).'/'.CACHE_PATH);
$bbq->registerQueue($queue);

$bbq->pushJob('tasks', new StringPayload('New task payload'));

$job = $bbq->fetchJob('tasks');
$payload = $job->getPayload();
echo $payload;

$bbq->finalizeJob($job);

