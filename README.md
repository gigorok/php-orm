# PHP ORM library
=============================

The open source ORM library on PHP.

## Introduction ##

The PHP-ORM follows ActiveRecord architectural pattern.

More details can be found [here](http://en.wikipedia.org/wiki/Active_record_pattern).

## Minimum Requirements

- PHP 5.4+
- PDO driver for your respective database

## Supported Databases

- MySQL
- PostgreSQL

## Features

- Finder methods
- Writer methods
- Relationships
- Validations
- Callbacks
- Transactions
- Support for multiple adapters
- Table's schema
- Mass assignment protection

## Installation

Use [composer](http://getcomposer.org) to install PHP ORM library.
Just add to your `composer.json` a text below and run the `php composer.phar update` command to install it:

    {
        "require": {
            "gigorok/php-orm": "0.2.*"
        }
    }

## Basic CRUD

### Retrieve ###
These are your basic methods to find and retrieve records from your database.

    $post = Post::find(1);
    echo $post->title; # 'Test title!'
    echo $post->author_id; # 5

    # also the same since it is the first record in the db
    $post = Post::first();

    # finding using a conditions array
    $posts = Post::where('name=? or id > ?', array('The Bridge Builder', 100));

### Create ###
Here we create a new post by instantiating a new object and then invoking the save() method.

    $post = new Post();
    $post->title = 'My first blog post!!';
    $post->author_id = 5;
    $post->save();
    # INSERT INTO `posts` (title,author_id) VALUES('My first blog post!!', 5)

### Update ###
To update you would just need to find a record first and then change one of its attributes.

    $post = Post::find(1);
    echo $post->title; # 'My first blog post!!'
    $post->title = 'Some real title';
    $post->save();
    # UPDATE `posts` SET title='Some real title' WHERE id=1

    $post->title = 'New real title';
    $post->author_id = 1;
    $post->save();
    # UPDATE `posts` SET title='New real title', author_id=1 WHERE id=1

### Destroy ###
Deleting a record will not *destroy* the object. This means that it will call sql to delete
the record in your database but you can still use the object if you need to.

    $post = Post::find(1);
    $post->destroy();
    # DELETE FROM `posts` WHERE id=1
    echo $post->title; # 'New real title'

## License

Licensed under the MIT license.