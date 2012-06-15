# Silex Providers

Some small [Service Providers](http://silex.sensiolabs.org/doc/providers.html) for the [Silex framework](silex.sensiolabs.org).
For the moment, we only have 3 Providers there - but more will come ! :-)


## ZendDbProvider

This Provider creates a shared instance of ```Zend_Db_Adapter_Abstract``` (Zend Framework v1.x), usable via ```$app['zend.db']```.

Yes, we already have a [Doctrine-DBAL Provider](http://silex.sensiolabs.org/doc/providers/doctrine.html) provided with Silex,
but for various reasons I prefer the [Zend_Db API](http://framework.zend.com/manual/fr/zend.db.html).
- for instance, I haven't been able to use any 'NOW()' clause with Doctrine-DBAL ```insert``` method, because of the
lack of a [Zend_Db_Expr](http://framework.zend.com/manual/en/zend.db.select.html#zend.db.select.building.columns-expr) mecanism ; this is really frustrating...

The base idea for this Provider comes from [this](http://www.syndicatetheory.com/labs/using-zend_db-with-silex) blog post.

To include the full Zend Framework in your project, you can use this [ZendFramework-1.x](https://github.com/DrBenton/ZendFramework-1.x) repository with Composer.


#### Parameters

- **zend.db.adapter** : see [Zend_Db_Adapter](http://framework.zend.com/manual/fr/zend.db.adapter.html) documentation. Example: "pdo_mysql".
- **zend.class_path** (optional) : if your ZF environment has not been already set, you will have to provide this parameter fo its initialization.
- **zend.db.host** (optional) : your DB host
- **zend.db.dbname** (optional) : your DB name
- **zend.db.username** (optional) : your DB username
- **zend.db.password** (optional) : your DB password
- **zend.db.profiler.enabled** (optional) : set this to ```true``` to enable the [Zend_Db_Profiler](http://framework.zend.com/manual/en/zend.db.profiler.html)
- **zend.db.profiler.log_file_path** (optional) : set this with the previous parameter to enable an automatic queries log

#### Services

- **zend.db** : The ```Zend_Db_Adapter_Abstract``` instance.
- **zend.db.profiler** : If you have set 'zend.db.profiler.enabled' to ```true```, you will have access to this instance of ```Zend_Db_Profiler```.

Example usage:

```
// Let's add a user email in a newsletter table...
$app['zend.db']->insert('newsletter_subscriptions', array(
        'email' =>          $newEmail,
        'creation_date' =>  new \Zend_Db_Expr('NOW()'),
    )
);
// ...and retrieve it
$select = $app['zend.db']->select()
            ->from('newsletter_subscriptions', 'email')
            ->where('email=?', newEmail);
$insertedEmail = $select->query()->fetchColumn();
// Zend_Db API is very powerful, and yet easy to use ! @see http://framework.zend.com/manual/fr/zend.db.adapter.html 
```

#### Registering

```
// Zend_Db API init
$app->register(new DrBenton\Silex\Provider\ZendDbProvider(), array(
    'zend.class_path'                   => APP_ROOT.'/vendor/zend-framework/lib',
    'zend.db.profiler.enabled'          => $app['debug'],
    'zend.db.profiler.log_file_path'    => APP_ROOT . '/app/logs/db.log',
    'zend.db.adapter'                   => 'pdo_mysql',
    'zend.db.host'                      => 'localhost',
    'zend.db.dbname'                    => 'test_db',
    'zend.db.username'                  => 'test_user',
    'zend.db.password'                  => 'text_password',
) );
```


## LessProvider

This Provider is outdated, and will be rewritten soon with @leafo's LessPHP library.
