./vendor/bin/sail composer install
./vendor/bin/sail bun install
./vendor/bin/sail cp .env.example .env 
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
./vendor/bin/sail psql \i ./database/functions.sql
./vendor/bin/sail artisan queue:work
./vendor/bin/sail artisan queue:listen
./vendor/bin/bun run dev