# README

- tracker of student mental health, providing schools and the UK government with data, analysis and evidence-based
- many school connect with system and There are multiple schools associated with system in which they can log in with different-different credentials and can get separate permission.
- The system is divided into 2 parts. Students and staff. student only give assessment and staff manage the score and action

The feature of the system are as follows
- assessment management
- track the progress of the pupils
- multilevel role management 
- course management
- Train staff online

## Technology and Versions

- Laravel Version: 10
- php : 8.1
- MySql: 8.0
- redis: 6.2.0
- Gotenberg PDF Render engine: 7.10.2 
- react: 17.0.2


## Installation

1. Install or update laravel composer:
    - composer update

2. Redis install - for Ubuntu
 - sudo apt install redis-server
 - sudo nano /etc/redis/redis.conf
	Inside the file, find the supervised directive. This directive allows you to declare an init system to manage Redis as a service, providing you with more control over its operation. The supervised directive is set to no by default. Since you are running Ubuntu, which uses the systemd init system, change this to systemd:
 - sudo systemctl restart redis.service

3. yarn install and yarn watch

4. set env and database connection

## Accessing GraphQL Tool

    
