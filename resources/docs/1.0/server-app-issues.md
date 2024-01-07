# Common Issues Of Server App

---

- [Uploaded Images Not Showing](#showing-image-issue)
- [Unauthorized request kreit laravel](#kreit-laravel)
- [Data Array Serializer Issue](#serializer-issue)
- [Personal Access Client Not Found](#access-client-issue)
- [Push Notifications Not Received](#push-notification-issue)

<a name="showing-image-issue"></a>
## Uploaded Images Not Showing

* Please make sure the below things
	* We need to link the storage by the below command
		```php
		php artisan storage:link
		```
	* We need to update the base url in .env in the below param like this
		```php
			APP_URL=http://tagxi-server.ondemandappz.com
		```


	
<a name="kreit-laravel"></a>
## Unauthorized request kreit laravel

* This issue comes at end of the registeration process because of the misconfigured firebase setup for the backend app, Please make sure that you have done the below steps properly.

	* We need to update the firebase service-accounts.json in the below path
		Path: server-app-folder/public/push-configurations/firebase.json

		* Reference For downloading service-accounts.json - https://tagxi-docs.ondemandappz.com/user-manual/1.0/backend-setup#section-5

	* Make sure that you have updated the correct path & firebase database url in .env file like below
		```php
		FIREBASE_CREDENTIALS=/var/www/html/server-app/public/push-configurations/firebase.json

		FIREBASE_DATABASE_URL=https://your-project-rtdb.firebaseio.com
		```



<a name="serializer-issue"></a>
## Data Array Serializer Issue

* This issue has reported because the php version. Please make sure that you are using php7.2



<a name="access-client-issue"></a>
## Personal Access Client Not Found

* This issue generally comes on authentication part due to passport installation not done.

* Please make sure that you have run the below command in terminal of your project path
	
	```php
	php artisan passport:install
	```


<a name="push-notification-issue"></a>
## Push Notifications Not Received

* This issue comes because of firebase or supervisor misconfigurations.

* Please Make sure that you have done the firebase setup & the laravel supervisor setup.

* reference link for supervisor-setup - https://tagxi-docs.ondemandappz.com/user-manual/1.0/backend-setup#section-6

