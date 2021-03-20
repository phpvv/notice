# Notice

VV Notice Service

*(todo: complete)*

## Installation

Package is available on [Packagist](https://packagist.org/packages/phpvv/notice), you can install it
using [Composer](https://getcomposer.org).

```shell
composer require phpvv/notice
```

## Configuring

```php
// bootstrap.php
require_once __DIR__ . '/vendor/autoload.php';

use VV\Notice;

\VV\Notice::factory()->config()
    ->addAllNoticer(new class implements \VV\Notice\AllNoticer {
        public function log(\VV\Notice $notice){
         // TODO: Implement log() method.
        }
        public function sendMail(\VV\Notice $notice,string $subject,string $message) : void{
         // TODO: Implement sendMail() method.
        }
        public function sendSms(\VV\Notice $notice) : void{
         // TODO: Implement sendSms() method.
        }
        public function syslog(\VV\Notice $notice) : void{
         // TODO: Implement syslog() method.
        }
    })
    //->setSmsRepeatTimeout(3600)
    //->setCache(new \VV\Cache\Local\FileCache(\VV\Utils\Fs::tmpDir('notice-timeout-lock')))
    ;
```

## Usage

```php
use VV\Notice;

Notice::info('Some information'/*, code: 300*/)->mail();
Notice::warning('Some warning'/*, code: 200*/)
    //->log()->mail()  // same as below
    ->logMail();
Notice::error('Some error'/*, code: 100*/)
    //->log()->mail()->syslog()->sms() // same as below
    ->all();
```
