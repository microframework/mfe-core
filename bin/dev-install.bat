@title First_Installation
@cd ../
@php -r "readfile('https://getcomposer.org/installer');" | php
@php composer.phar update --prefer-source --no-interaction
