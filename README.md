# Laravel E-commerce Website
E-commerce application built with Laravel, Vue.js, Tailwind.css and Alpine.js. <br>

## Installation 
Make sure you have environment setup properly. You will need Docker, PHP8.1, Node.js and composer.

### Install Laravel Website + API
1. Download the project (or clone using GIT)
2. Copy `.env.example` into `.env` and configure database credentials
3. Navigate to the project's root directory using terminal
4. Run `composer install`
5. Set the encryption key by executing `sail artisan key:generate --ansi`
6. Run the containers `./vendor/bin/sail up -d`
7. Run migrations `sail artisan migrate --seed`
8. Run storage link `sail artisan storage:link`
9. Create your own stripe test credentials and add in the .env
10. Open new terminal and navigate to the project root directory
11. Run `sail npm install`
12. Run `sail npm run dev` to start vite server for Laravel frontend
13. To run the tests `sail artisan test`

### Install Vue.js Admin Panel
1. Navigate to `backend` folder
2. Run `npm install`
3. Copy `backend/.env.example` into `backend/.env`
4. Make sure `VITE_API_BASE_URL` key in `backend/.env` is set to your Laravel API host (Default: http://localhost:8000)
5. Run `npm run dev`
6. Open Vue.js Admin Panel in browser and login with
    ```
    admin@example.com
    admin123
    ```
