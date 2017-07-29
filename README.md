

Steps taken in order to get this project to where it is:

```
composer create-project symfony/skeleton flex
cd flex
composer require templating doctrine migrations
```

* Template created: `/templates/home/welcome.html.twig`
* Controller created: `/src/Controller/HomeController.php`
* Routing added in `/config/routes.yaml`

From there, the application is ready to be deployed with Dokku in the talk.
