# gcrud
This package creates a layer structure to organize your laravel projects

### How to Install
From the root of a laravel project, type the composer command:

```{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@gitlab.com:davidson.ferreira/gcrud.git"
        }
    ],
    "require": {
        "djferreira/gcrud": "dev-master"
    }
}
```


Once installed, the following artisan commands will be available:
```
gcrud:makebo
gcrud:makecontroller
gcrud:makecrud
gcrud:makemodel
gcrud:makerepository
gcrud:makerequest
gcrud:makeresource
gcrud:maketrait
```
You can call each one separately or choose the command that does the entire structure at once:

```php artisan gcrud:makecrud --all```

The makecrud command with **--all** option will read the configured database (.env) and create all the files of the proposed structure for each table found
