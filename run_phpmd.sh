#!/usr/bin/env bash

php ../phpmd.phar guard.php,classes/. html unusedcode,cleancode,codesize,controversial,design,naming --suffixes php --reportfile phpmd.report.html