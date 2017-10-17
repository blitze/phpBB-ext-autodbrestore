# Auto Database Restore

Auto Database Restore is an Extension for [phpBB 3.2](https://www.phpbb.com/)

## Description

Automatically restore database to a specified backup.

[![Travis branch](https://img.shields.io/travis/blitze/phpBB-ext-autodbrestore/master.svg?style=flat)](https://travis-ci.org/blitze/phpBB-ext-autodbrestore) [![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/blitze/phpBB-ext-autodbrestore/master.svg?style=flat)](https://scrutinizer-ci.com/g/blitze/phpBB-ext-autodbrestore/?branch=master) [![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/blitze/phpBB-ext-autodbrestore/master.svg?style=flat)](https://scrutinizer-ci.com/g/blitze/phpBB-ext-autodbrestore/?branch=master)

## Features

* Select a backup file that you want to automatically restore your site to
* Select how often you want to reset the site to the specified backup (15, 30, 60 minutes) or
* Optionally enter a custom restore frequency in minutes

## Installation

* Clone into phpBB/ext/blitze/autodbrestore:

    ```git clone https://github.com/blitze/phpBB-ext-autodbrestore.git phpBB/ext/blitze/autodbrestore```

* Go to "ACP" > "Customise" > "Extensions" and enable the "Auto Database Restore" extension.
* Go to "ACP" > "Maintenance" > "Backup" and create a local backup of your data
* Go to "ACP" > "Extensions" > "Auto Database Restore" > "Settings" and select the desired backup file and restore frequency

That's it your done!!!

## Collaborate

* Create a issue in the [tracker](https://github.com/blitze/phpBB-ext-autodbrestore/issues)
* Note the restrictions for [branch names](https://wiki.phpbb.com/Git#Branch_Names) and [commit messages](https://wiki.phpbb.com/Git#Commit_Messages) are similar to phpBB3
* Submit a [pull-request](https://github.com/blitze/phpBB-ext-autodbrestore/pulls)

## Testing

We use Travis-CI as a continuous integration server and phpunit for our unit testing. See more information on the [phpBB development wiki](https://wiki.phpbb.com/Unit_Tests).

## License

[GPLv2](license.txt)
