:: Run easy-coding-standard (ecs) via this batch file inside your IDE e.g. PhpStorm (Windows only)
:: Install inside PhpStorm the  "Batch Script Support" plugin
cd..
cd..
cd..
cd..
cd..
cd..
php vendor\bin\ecs check vendor/markocupic/contao-github-login/src --fix --config vendor/markocupic/contao-github-login/tools/ecs/config.php
php vendor\bin\ecs check vendor/markocupic/contao-github-login/contao --fix --config vendor/markocupic/contao-github-login/tools/ecs/config.php
php vendor\bin\ecs check vendor/markocupic/contao-github-login/config --fix --config vendor/markocupic/contao-github-login/tools/ecs/config.php
php vendor\bin\ecs check vendor/markocupic/contao-github-login/templates --fix --config vendor/markocupic/contao-github-login/tools/ecs/config.php
php vendor\bin\ecs check vendor/markocupic/contao-github-login/tests --fix --config vendor/markocupic/contao-github-login/tools/ecs/config.php
