# Backend Setup

---

- [Introduction](#section-1)
- [Setup Instructions](#section-2)
- [<!-- Node -->.js File Setup](#section-3)
- [Map Setup](#section-4)
- [Firebase Config](#section-5)
- [Queue Setup](#section-6)
- [Translation Setup](#section-7)


<a name="section-1"></a>
## Introduction
You are almost finished and ready to setup your back end part. once you setup jenkins and taken a build or just uploaded the laravel project in your project path. Please follow the below steps for running the server app.

* Admin App & Dispatcher App Link

    Admin App: http://your-base-url/login

    Default Access For Admin

     email: admin@admin.com <br>

     password: 123456789 <br>


 Dispatcher App :  http://your-base-url/dispatch-login






<a name="section-2"></a>
## Setup Instructions

* rename the ".env-example" file to ".env"
* Create a database using phpmyadmin
* Setup DB config in .env file
    ```php
    APP_URL=http://tagxi.com/future/public
    ```
   ```php
   DB_PORT=3306
DB_DATABASE=tyt-backend
DB_USERNAME=tyt_user
DB_PASSWORD=tyt_user@2020

   ```

   * Sample .env file
   ```php
APP_NAME=tagxi
APP_ENV=local
APP_KEY=base64:8zIYjbZv9brVUOy0XdixW2Oxpkg7S7DCe20ptOMbaRU=
APP_DEBUG=true
APP_URL=http://localhost/tyt-truck/public
NODE_APP_URL=http://localhost
LOG_CHANNEL=daily
SYSTEM_DEFAULT_TIMEZONE=Asia/Kolkata
SYSTEM_DEFAULT_CURRENCY = 'INR'
APP_FOR=demos
COORDINATES_PATH=/var/www/html/coords/india.kml
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rovon
DB_USERNAME=tyt_user
DB_PASSWORD=tyt_user@2020
BROADCAST_DRIVER=log
CACHE_DRIVER=array
QUEUE_CONNECTION=database
SESSION_DRIVER=file
SESSION_LIFETIME=120
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=SG.TFEnuSBS65fg.qAmjKNd4LUxxowWdtklHgamytGu_mIBGMQhHVINFZiY
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=suport@tagxi.com
MAIL_FROM_NAME="tagxi"
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
FILESYSTEM_DRIVER=local
SMS_PROVIDER=log
DEFAULT_ALERT_EMAIL='dhilipkumar.kgcas@gmail.com'
NODE_APP_PORT=3000
FIREBASE_CREDENTIALS=/var/www/html/tyt-backend/public/push-configurations/firebase.json
FIREBASE_DATABASE_URL=https://cabeezie.firebaseio.com/
PAYMENT_GATEWAY=braintree
GOOGLE_MAP_KEY=AIzaSyBeVRuuicwooRpk7ErjCEQCwu0OQowVt9I
GOOGLE_SHEET_KEY = AIzaSyggE-WE-lwXhxWFHJthZ6FleF1WQ3NmGAU
DEFAULT_LAT=11.21215
DEFAULT_LNG=76.54545
```

* Need to configure database config in .env file mentioned above.

* run the below commands to run the project.

    * composer install
    * php artisan migrate
    * php artisan db:seed
    * php artisan passport:install
    * php artisan storage:link

<!-- <a name="section-3"></a>
## Node.js File Setup

* By run the node.js server file we will be fetch the nearest drivers using geofire in firebase realtime database. so we need to run the node file, please follow the below instructions.

Steps

* Find the node file by below path
    "project-file/node/geofire.js"

* run the server file by using pm2. "pm2 start geofire.js"
 -->
<a name="section-4"></a>
## Map Configuration

* To create zone & see map view  & other map functionalities we need to add google map key in admin app settings section.


<a name="section-5"></a>
## Firebase Configuration

* After created the account in firebase, you need to create realtime database by following the explanation in android setup document section. 

* After created the realtime database you need to copy the database url and paste it to the below .env variable
```php
FIREBASE_DATABASE_URL=https://your-app.firebaseio.com/
```

* To get realtime drivers from fiebase we need to config web app in firebase. so that we need to create web app.
![image](../../images/user-manual-docs/firebase-create-web-app.png)
![image](../../images/user-manual-docs/firebase-web-config.png)


```php
GOOGLE_MAP_KEY=AIzaSyBeTRs1icwooRpk7ErjCEQCwu0OQowVt9I
```

* Generate firebase.json content and replace it in the below paths. Please find the image for how to generate firebase.json file.

    <!-- * node/firebase.json -->
    * public/push-configurations/firebase.json

![image](../../images/user-manual-docs/project-settings–firebase-console.png)


<a name="section-6"></a>
## Queue Setup

* for sending notifications & other stuffs we need to configure the supervisor setup to run the queue jobs in the server by following the document https://laravel.com/docs/8.x/queues#supervisor-configuration

* sample laraver-worker file

```php
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/project-name/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=ubuntu
numprocs=8
redirect_stderr=true
stdout_logfile=/var/www/html/project-name/worker.log
stopwaitsecs=3600

```
* We need to run cron jobs so that please open the cronjob file and enter the below line with your projrvt name.
* to open cronjob file please use the following command "crontab -e"

```php
* * * * * cd /var/www/html/taxi && php artisan schedule:run >> /dev/null 2>&1
```

<a name="section-7"></a>
## Translation

* We have used barryvdh/laravel-translation-manager for the admin app translations

* For Mobile Translation keywords you need to enable the translation sheet api in google cloud console & get the api key from there & paste in to our .environment value below like this.

```php
GOOGLE_SHEET_KEY = AIzaGyBVE-WE-lwXhxWFHJthZ6FleF1WQ3NmGAV
```