# AbyssMMO

## Run the project locally

### Requirements

- docker
- docker-compose

### Installing the project

1. Clone the project `git clone https://github.com/yeraycat/abyssmmo.git`
1. Change to the project directory `cd abyssmo`
1. Run docker compose `docker-compose up`
1. In your browser, go to http://localhost:8080/installer.php
1. Set `db` as mysql hostname
1. Set `exampleuser` as mysql username
1. Set `examplepass` as mysql password
1. Set `exampledb` as mysql database
1. Fill the rest of the form and create the admin account

**The installation will remove the installer files and the installation database dump. If you work on the project remember not to commit these deletions**

### Configuring cron jobs

Just after installing, a message with the crons config will appear. To configure the crons:

1. Open bash in the webserver container `docker-compose exec webserver bash`
1. Install cron and nano `apt-get install -y cron nano`
1. Start cron `cron`
1. Edit the cron tabs `crontabs -e`
1. Copy and paste the cron config from the installer page and save
1. Restart the cron service `service cron restart`
