# Weather App by Albert Jonathan

## Installation
1. composer install
2. bun install
3. copy .env.example to .env 
4. ./vendor/bin/sail up -d
5. ./vendor/bin/sail artisan key:generate
6. ./vendor/bin/sail artisan migrate
7. ./vendor/bin/sail artisan queue:work
8. ./vendor/bin/sail artisan queue:listen
9. ./vendor/bin/bun run dev

Database schema is in Database ERD.svg
API Documentation wasn't being made due to time constraints
