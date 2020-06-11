Nette Web Project
=================
Make sure to install before runing.
To run, switch to the project folder then use command:

	php -S localhost:8000 -t www

change "localhost" and 8000 to server IP and port you wish to use (see Web Server Startup for more info).


Installation
------------

The best way to install Web Project is using Composer. If you don't have Composer yet,
download it following [the instructions](https://doc.nette.org/composer). Then use command:

	composer create-project nette/web-project path/to/install
	cd path/to/install


Make directories `temp/` and `log/` writable.


Web Server Setup
----------------

The simplest way to get started is to start the built-in PHP server in the root directory of your project:

	php -S localhost:8000 -t www

Then visit `http://localhost:8000` in your browser to see the welcome page.

For Apache or Nginx, setup a virtual host to point to the `www/` directory of the project and you
should be ready to go.

**It is CRITICAL that whole `app/`, `log/` and `temp/` directories are not accessible directly
via a web browser. See [security warning](https://nette.org/security-warning).**


Piskvorky (tic-tac-toe) app
---------------------------

Application is located in /app/Presenters
where

`HomepagePresenter.php` - Main page of the website
`PiskvorkyPresenter.php` - Main presenter of the application
`PiskvorkyGameLogic.php` - Tic-tac-toe game logic class
`Piskvorky/default.latte` - default page to be rendered when open
`Piskvorky/newgame.latte` - page containing information about newly created game
`Piskvorky/load.latte` - page containing list of all saved games. Games are saved automatically when created.
`Piskvorky/settings.latte` - page containing settings of currently selected game
`Piskvorky/game.latte` - page containing game itself

