# Testing Parvula

 1. Install [codeception](http://codeception.com/) with `composer require codeception/codeception flow/jsonpath`
 2. Install [phpcs](https://github.com/squizlabs/PHP_CodeSniffer) with `composer global require "squizlabs/php_codesniffer=*"`
 3. Run tests with `composer test` to run the unit test and the code sniffer

*PS*: Be sure to serve Parvula from localhost:8000 (eg. with `composer serve`) 
or edit `api.suite.yml` to change the address and/or port.


## Tests

 - API: `composer test api`
