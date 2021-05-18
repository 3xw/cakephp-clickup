# cakephp-clickup plugin for CakePHP
This plugin allows you handle dealwith clickup as driver/webservice/endpoint

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

	composer require 3xw/cakephp-clickup

Load it in your src/Application.php

	$this->addPlugin(\Trois\Clickup\Plugin::class);

## Configure
in app.php

```php
'Datasources' => [
	'click_up' => [
		'className' => 'Muffin\Webservice\Connection',
		'service' => 'Trois/Clickup.ClickUp',
		'token' => 'pk_***'
	],
	//..
]
```

## Use
```php
$this->loadModel('Trois/Clickup.Tasks', 'Endpoint');
debug($this->Tasks->find()->where(['listId' => 'xxx'])->toArray());
debug($this->Tasks->find()->where(['taskId' => 'xxx'])->toArray());

```

