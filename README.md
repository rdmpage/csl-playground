# csl-playground

Citation Style Language (CSL) database implemented in CouchDB, using JATS-XML to display articles.

## Getting started

```
composer require php:^5.6

echo "vendor/" > .gitignore
```

Push code to GitHub, then deploy on Heroku. Need to add config variables CLOUDANT_USERNAME and CLOUDANT_PASSWORD to Heroku settings.

We need XSL so add this to composer.json
```
{
    "require": {
        "php": "^5.6",
        "ext-xsl": "*"
    }
}
```
and then run 
```
composer update
```




