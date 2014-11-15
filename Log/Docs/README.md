
## Logger Class

------

The Logger class assists you to <kbd>write messages</kbd> to your log handlers. The logger class use php SplPriorityQueue class to manage your handler proirities.

**Note:** This class defined as service in your app/clases/Service folder. The <b>logger</b> package uses <kbd>Disabled</kbd> handler as default.

### Available Log Hanlers

* FileHandler
* MongoHandler
* SyslogHandler
* EmailHandler

### Constants

```php
/*
|--------------------------------------------------------------------------
| Log Constants
|--------------------------------------------------------------------------
| @see Syslog Protocol http://tools.ietf.org/html/rfc5424
|
| Constants:
|
| 0  LOG_EMERG: System is unusable
| 1  LOG_ALERT: Action must be taken immediately
| 2  LOG_CRIT: Critical conditions
| 3  LOG_ERR: Error conditions
| 4  LOG_WARNING: Warning conditions
| 5  LOG_NOTICE: Normal but significant condition
| 6  LOG_INFO: Informational messages
| 7  LOG_DEBUG: Debug-level messages
*/
```

### Log Severities:

<table class="span9">
<thead>
<tr>
<th>Severity</th>
<th>Level</th>
<th>Constant</th>
<th>Desciription</th>
</tr>
</thead>
<tbody>
<tr>
<td>emergency</td>
<td>0</td>
<td>LOG_EMERG</td>
<td>Emergency: System is unusable.</td>
</tr>

<tr>
<td>alert</td>
<td>1</td>
<td>LOG_ALERT</td>
<td>Action must be taken immediately. Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.</td>
</tr>

<tr>
<td>critical</td>
<td>2</td>
<td>LOG_CRIT</td>
<td>Critical conditions. Example: Application component unavailable, unexpected exception.</td>
</tr>

<tr>
<td>error</td>
<td>3</td>
<td>LOG_ERR</td>
<td>Runtime errors that do not require immediate action but should typically be logged and monitored.</td>
</tr>

<tr>
<td>warning</td>
<td>4</td>
<td>LOG_WARNING</td>
<td>Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.</td>
</tr>

<tr>
<td>notice</td>
<td>4</td>
<td>LOG_NOTICE</td>
<td>Normal but significant events.</td>
</tr>

<tr>
<td>info</td>
<td>6</td>
<td>LOG_INFO</td>
<td>Interesting events. Examples: User logs in, SQL logs, Application Benchmarks.</td>
</tr>

<tr>
<td>debug</td>
<td>7</td>
<td>LOG_DEBUG</td>
<td>Detailed debug information.</td>
</tr>
</tbody>
</table>

### Creating Log Messages

#### $this->logger->channel(string $channel);

Sets channel of your log messages.

#### $this->logger->severity(string $message, $context = array(), $priority = 0);

First choose your channel and set log severity then you can send your additinonal context data using second optional parameter. Also third parameter is optinal, it is priority of the message.

```php
<?php
$this->logger->channel('security');
$this->logger->alert('Possible hacking attempt !', array('username' => $username));
```


### Enable / Disable Logger

As default framework comes with logging disabled <kbd>false</kbd>. You can enable logger setting <kbd>enabled to true.</kbd>

```php
<?php
    'log' =>   array(
        'enabled' => true,      // On / Off logging.
        'output' => false,      // On / Off debug html output. All handlers disabled in this mode.
        'service' => array(
            'filters' => 'Log\Filters',  // Class paths
        ),
        'default' => array(
            'channel' => 'system',       // Default channel name should be general.
        ),
        'file' => array(
            'path' => array(
                'http'  => 'data/logs/http.log',  // Http requests log path  ( Only for File Handler )
                'cli'   => 'data/logs/cli.log',   // Cli log path  
                'ajax'  => 'data/logs/ajax.log',  // Ajax log path
            )
        ),
        'format' => array(
            'line' => '[%datetime%] %channel%.%level%: --> %message% %context% %extra%\n',  // This format just for line based log drivers.
            'date' =>  'Y-m-d H:i:s',
        ),
        'extra'     => array(
            'queries'   => true,       // If true "all" SQL Queries gets logged.
            'benchmark' => true,       // If true "all" Application Benchmarks gets logged.
        ),
        'queue' => array(
            'workers' => array(
                'logging' => false     // On / Off Queue workers logging functionality.
            ), 
        )
    ),
```
#### Explanation of Settings:

* <b>enabled</b> - On / Off logging
* <b>debug</b> - On / Off html output, logger gives html output bottom of the current page.
* <b>channel</b> - Default channel name should be general.
* <b>line</b> - Logging line format for line based handlers.
* <b>path</b> - File handler paths
* <b>format</b> - Date format for each records.
* <b>extra/queries</b> - If true all Database SQL Queries gets logged.
* <b>extra/benchmark</b> - If true all framework benchmarks gets logged.
* <b>queue/workers/logging</b> - If true all queue worker jobs gets logged. Enable it if you want to install framework as a worker application, otherwise your log data fill up very fast.


### Priorities

```php
<?php
$this->logger->alert('Alert', array('username' => $username), 3);
$this->logger->notice('Notice', array('username' => $username), 2);
$this->logger->notice('Another Notice', array('username' => $username), 1);
```

### Push

Below the example load shows only pushing LOG_ALERT levels.

```php
<?php
$this->logger->load('mongo')->filter('priority', array(LOG_ALERT));
$this->logger->channel('security');               
$this->logger->alert('Possible hacking attempt !', array('username' => $username));
$this->logger->push();
```
or you can use multiple push handlers.

```php
<?php
$this->logger->load('email')->filter('priority.notIn', array(LOG_DEBUG, LOG_INFO, LOG_NOTICE));
$this->logger->load('mongo');  

$this->logger->channel('security');
$this->logger->alert('Something went wrong !', array('username' => $username));

$this->logger->channel('test');
$this->logger->info('User login attempt', array('username' => $username));  // Continue push to another handler

$this->logger->push();

```

<b>IMPORTANT:</b> For a live site you will need priority filter for LOG_EMERG,LOG_ALERT,LOG_CRIT,LOG_ERR,LOG_WARNING,LOG_NOTICE levels to be logged otherwise your log files will fill up very fast.

## Service Configuration

### Example for Mongo Writer

Open your <b>app/classes/Log/Env/$EnvLogger.php</b> then switch mongo database as a primary handler this will replace your "file" handler as "mongo".

```php
<?php

namespace Log\Env;

use Service\ServiceInterface,
    Obullo\Log\LogService,
    Obullo\Log\Handler\NullHandler;

/**
 * Log Service
 *
 * @category  Service
 * @package   Logger
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs/services
 */
Class Local implements ServiceInterface
{
    /**
     * Registry
     *
     * @param object $c container
     * 
     * @return void
     */
    public function register($c)
    {
        // ..
    }
}

// END Local class

/* End of file Local.php */
/* Location: .classes/Log/Env/QueueLogger/Local.php */
```

* TIP: If you have a high traffic web site change your handler path as QueueLogger for best performance.

```php
<?php
$service->logger->registerHandlerPath('Log\QueueLogger');
```

### Adding Multiple Writers

```php
<?php
$service->logger->addWriter('mongo')->priority(5)->filter('priority.notIn', array(LOG_INFO, LOG_DEBUG));
$service->logger->addWriter('file')->priority(4)->filter('priority.notIn', array(LOG_DEBUG));
```

Higher numbers means your handler important than others.

### Handler Priorities

Priority method sets your handler priority.

```php
<?php
$service->logger->registerHandler('file', 'FileHandler', $priority = 5);
$service->logger->registerHandler('email', 'EmailHandler', $priority = 4);
```

### Writer Priorities


```php
<?php
$logger->addWriter('file')->priority(2);
```

### Global Filters

If you we want to use a filter first you need to register it to a class with registerFilter(); method.

An example prototype:

```php
$logger->registerFilter('class.method', 'Namespace\Class');
```

```php
<?php
$logger->registerFilter('priority', 'Log\Filters\Priority');
```
or you can define our own 

```php
<?php
$logger->registerFilter('filtername', 'Log\Filters\MyFilterClass');
```

Then you can use your filters like below.

```php
<?php
$logger->addWriter('email')->priority(2)->filter('priority', array(LOG_NOTICE, LOG_ALERT))->filter('input.filter');
```

or you can run custom methods using "."

```php
<?php
$logger->addWriter('email')->priority(2)->filter('priority.notIn', array(LOG_DEBUG));
```
Above the example executes <b>Priority</b> class <b>notIn</b> method. If you not provide a method name it will run the <b>__construct</b> method.

### Page Filters

Below the example we create a log filter for <b>hello_world</b> page then we push log data to mongo. And the 'mongo' page filter <b>remove the debug level</b> logs.

```php
<?php

/**
 * $app hello_world
 * 
 * @var Controller
 */
$app = new Controller(
    function ($c) {
        $c->load('view');

        $this->logger->load('mongo')->filter('priority.notIn', array(LOG_DEBUG)); // Do not write debugs.

        $this->logger->info('Hello World !');
        $this->logger->notice('Hello Notice !');
        $this->logger->alert('Hello alert !');

        $this->logger->push();
    }
);
```

**Note:** You can create your own filters in your <kbd>app/classes/</kbd> folder then you need set it with <b>$logger->registerFilter('myPriority', 'Filters\MyfilterClass');</b> method in your logger service.

#### Displaying Logs

Open your console and enter the project path

```php
cd /var/www/myproject
```

then run task file

```php
php task log
```
You can follow the all log messages using log command.

```php
php task log ajax
```
This command display only ajax log messages.

```php
php task log cli
```
This command display only cli log messages.


#### Clear All Log Data

```php
php task clear
```

Completely remove log files from <b>data/logs</b> folder or queue if QueueLogger is enabled.



## Queue Logger Configuration

Some times application need to send some logging data to background for heavy <b>async</b> operations. Forexample to sending an email with smtp is very long process. So when we use <b>Email Handler</b> in application first we need to setup a <b>Queue Writer</b> for it.

### Terms

------

<table>
<thead>
<tr>
<th>Term</th>
<th>Description</th>
</thead>
<tbody>
<tr>
<td>Handler</td>
<td>Allows clone of application log data and send them to another handler in the current page.</td>
</tr>
<tr>
<td>Queue Job handler</td>
<td>Listen queued logging jobs and consume them using <b>QueueLogger</b> class which is located in <b>app/Classes</b> folder.</td>
</tr>
</tbody>
</table>

### Setup

------

Open your <kbd>app/Classes/Log/Env/QueueLogger/Local.php</kbd> then update which handler you want to send log data onto the queue.
Please look at following example.

```php
<?php

namespace Log\Env;

use Service\ServiceInterface,
    Obullo\Log\LogService,
    Obullo\Log\Handler\NullHandler;

/**
 * Log Service
 *
 * @category  Service
 * @package   Logger
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs/services
 */
Class LocalLogger implements ServiceInterface
{
    /**
     * Registry
     *
     * @param object $c container
     * 
     * @return void
     */
    public function register($c)
    {
        // .. 
    }
}

// END LocalLogger class

/* End of file LocalLogger.php */
/* Location: .classes/Log/Env/LocalLogger.php */
```

### Handler Setup

------

Below the example setup file handler and priority.

```php
<?php
$log->registerHandler(4, 'file');
```

### Workers ( Job Handler ) Setup

------

QueueLogger class listen your <b>logger queue</b> data then consume them using <b>Job Handlers</b>.

### Available Job Process Handlers

* File
* Mongo
* Email


```php
<?php

namespace Workers;

use Obullo\Queue\Job,
    Obullo\Queue\JobInterface;

/**
 * Queue Logger
 *
 * @category  Queue
 * @package   QueueLogger
 * @author    Obullo Framework <obulloframework@gmail.com>
 * @copyright 2009-2014 Obullo
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GPL Licence
 * @link      http://obullo.com/docs/queue
 */
Class QueueLogger implements JobInterface
{
    /**
     * Container
     * 
     * @var object
     */
    public $c;

    /**
     * Environment
     * 
     * @var string
     */
    public $env;

    /**
     * Constructor
     * 
     * @param object $c   container
     * @param string $env environments
     */
    public function __construct($c, $env)
    {
        $this->c = $c;
        $this->env = $env;
    }

    /**
     * Fire the job
     * 
     * @param Job   $job  object
     * @param array $data data array
     * 
     * @return void
     */
    public function fire(Job $job, $data)
    {
        // ...
    }

}

/* End of file QueueLogger.php */
/* Location: .app/classes/Workers/QueueLogger.php */
```

### Listing Queues

```php
php task queue list --route=myHostname.Logger
```

```php
            ______  _            _  _
           |  __  || |__  _   _ | || | ____
           | |  | ||  _ || | | || || ||  _ |
           | |__| || |_||| |_| || || || |_||
           |______||____||_____||_||_||____|

            Welcome to Task Manager (c) 2014
    You are running $php task queue command which is located in app / tasks folder.

Following queue data ...

Channel : Logs
Route   : MyHostname.Logger.Email
------------------------------------------------------------------------------------------
 Job ID | Job Name             | Data 
------------------------------------------------------------------------------------------
 1      | Workers\QueueLogger          |  {"type":"http","record":"[2014-08-15 19:11:50] system.notice: --> test email notice !   \n[2014-08-15 19:11:50] system.alert: --> test email alert  array (  'test' => 'example data 123',) \n"}

```


### Listening Queues from Command Line

Open your console and write below the command

```php
php task queue listen --channel=Logs --route=myHostname.Logger --delay=0 --memory=128 --timeout=0 --sleep=0 --maxTries=0
```

Above the command listen <b>Logs</b> channel and <b>Email</b> queue.

### Changing Route

You can listen a <b>different route</b> by changing the route name like below.

```php
php task queue listen --channel=Logs --route=myHostname.Logger --delay=0 --memory=128 --timeout=0 --sleep=0 --maxTries=0
```

### Enabling Debbuger

Put <b>--debug</b> end of your command with debug variable you can enable console debug to see errors and queues.

```php
php task queue listen --channel=Logs --route=myHostname.Logger --delay=0 --memory=128 --debug=1
```

### Console Parameters

<table>
<thead>
<tr>
<th>Parameter</th>
<th>Description</th>
</thead>
<tbody>
<tr>
<td>--channel</td>
<td>Sets queue exchange ( Channel ).</td>
</tr>
<tr>
<td>--route</td>
<td>Sets queue name.</td>
</tr>
<tr>
<td>--delay</td>
<td>Sets delay time for uncompleted jobs.</td>
</tr>
<tr>
<td>--memory</td>
<td>Sets maximum allowed memory for current job.</td>
</tr>
<tr>
<td>--timeout</td>
<td>Sets time limit execution of the current job.</td>
</tr>
<tr>
<td>--sleep</td>
<td>If we have not job on the queue sleep the script for a given number of seconds.</td>
</tr>
<tr>
<td>--maxTries</td>
<td>If we have not job on the queue sleep the script for a given number of seconds.</td>
</tr>
<tr>
<td>--debug</td>
<td>Debug queue output and any possible exceptions. ( Designed for local environment  )</td>
</tr>
<tr>
<td>--project</td>
<td>You can set your project name using this variable if you  need to change your configurations for each projects.</td>
</tr>
<tr>
<td>--var</td>
<td>Custom variable for extra arguments.</td>
</tr>
</tbody>
</table>


### Installing Framework as a Completely Worker Application

If you want to create a distributed logging structure setting up a worker application is good idea. Otherwise if you have too many requests your http servers can get tired.

To setup a completely worker application open your config file and set "log => queue => workers => logging => true" otherwise "AbstractHandler" class will 
not allow your data send to writers.

```php
'queue' => array(
    'workers' => array(
        'logging' => true  // On / Off Queue workers logging functionality.
    ), 
)
```

This enables logging process of your workers ( background logging ) and disables http logging. It is normally disabled for normal applications which they work with http requests.


### Handlers Reference

------

#### $this->logger->load(string $handler = 'email');

Load a log handler for push method. Handler constants are defined in your root constants file.

#### $this->logger->addWriter(string $name);

Add a log writer.

#### $this->logger->removeWriter(string $name);

Remove a log writer.

#### $this->logger->registerFilter(string $name, string $namespace);

Register your filters using namespace.

#### $this->logger->registerHandler(string $name, string $namespace, integer $priority);

Register your handlers using namespace and sets priority of handler.

#### $this->logger->filter(string $name, array $params = array());

Execute your filter before releated method.

#### $this->logger->push($handlername = null);

Push current page log data to your loaded log handler. Handlername variable is optional.

#### $this->logger->printDebugger(string $handler = 'file');

On / Off debug html output. When it is enabled all push handlers will be disabled.


### Logger Reference

------

#### $this->logger->channel(string $channel);

Sets log channel.

#### $this->logger->emergency(string $message = '', $context = array(), integer $priority = 0);

Create <b>LOG_EMERG</b> level log message.

#### $this->logger->alert(string $message = '', $context = array(), integer $priority = 0);

Create <b>LOG_ALERT</b> level log message.

#### $this->logger->critical(string $message = '', $context = array(), integer $priority = 0);

Create <b>LOG_CRIT</b> level log message.

#### $this->logger->error(string $message = '', $context = array(), integer $priority = 0);

Create <b>LOG_ERROR</b> level log message.

#### $this->logger->warning(string $message = '', $context = array(), integer $priority = 0);

Create <b>LOG_WARNING</b> level log message.

#### $this->logger->notice(string $message = '', $context = array(), integer $priority = 0);

Create <b>LOG_NOTICE</b> level log message.

#### $this->logger->info(string $message = '', $context = array(), integer $priority = 0);

Create <b>LOG_INFO</b> level log message.
    
#### $this->logger->debug(string $message = '', $context = array(), integer $priority = 0);

Create <b>LOG_DEBUG</b> level log message.
