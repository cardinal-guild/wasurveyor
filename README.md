```bash
dokku apps:create php-symfony-flex
dokku config:set APP_ENV=prod
git push dokku master
dokku letsencrypt
```
