@title First_Instalation
@cd ../
@php -r "readfile('https://getcomposer.org/installer');" | php
@php composer.phar update --prefer-dist --dev