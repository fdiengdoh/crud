# Blog App
This is a PHP-based Blog App that uses PSR-4 autoloading, [delight-im/auth](https://github.com/delight-im/PHP-Auth) for authentication, [PHP Mailer](https://github.com/PHPMailer/PHPMailer) for sending email, and a custom architecture to manage posts, categories, profiles, comments, and more.

# Theme
This app is styled using [Bootstrap 5.3](https://getbootstrap.com) and modified some colour scheme where theme files are kept in [public_html](public_html) directory. 

## File Structure

```
project-root/
├── app/
│   ├── admin/
│   │   ├── admin-categories.php
│   │   ├── admin-flag-comment.php
│   │   ├── admin-panel.php
│   │   ├── footer-auth.php
│   │   ├── header-auth.php
│   │   ├── upload-image.php
│   │   └── view-logs.php
│   ├── include/
│   │   ├── footer.php
│   │   ├── header.php
│   │   ├── nav.php
│   │   └── sidebar.php
│   ├── users/
│   │   ├── ajax_handler.php
│   │   ├── edit-profile.php
│   │   ├── forgot-password.php
│   │   ├── increment-view.php
│   │   ├── login.php
│   │   ├── my-posts.php
│   │   ├── post-create.php
│   │   ├── post-delete.php
│   │   ├── post-edit.php
│   │   ├── profile.php
│   │   ├── register.php
│   │   ├── reset-password.php
│   │   └── verify.php
│   ├── 404.php
│   ├── category.php
│   ├── home.php
│   ├── report-comment.php
│   └── single-post.php
├── cache_config/
│   └── config.php
├── logs/
│   └── error.log
├── public_html/
│   ├── assets/
│   │   ├── image/
│   │   │   ├── default-feature.jpg
│   │   │   └── default-feature.webp
│   │   └── profile/
│   │       └── profile.png
│   ├── css/
│   │   ├── carousel.css
│   │   ├── carousel.min.css
│   │   ├── style.css
│   │   └── style.min.css
│   ├── image/
│   ├── js/
│   ├── blog-theme.html
│   ├── category-theme.html
│   ├── favicon.ico
│   ├── index.php
│   ├── install.php
│   └── theme.html
├── src/
│   ├── Controllers/
│   │   ├── AuthController.php
│   │   ├── CategoryController.php
│   │   ├── CommentController.php
│   │   ├── MediaController.php
│   │   ├── PostController.php
│   │   └── ProfileController.php
│   ├── Helpers/
│   │   ├── AuthHelper.php
│   │   └── Link.php
│   ├── Utils/
│   │   └── Cache.php
│   ├── AuthConstants.php
│   ├── Database.php
│   ├── Mailer.php
│   └── ProfileManager.php
├── composer.json
├── composer.lock
├── init.php
└── MySQL.sql
```
## Getting Started

### Follow these steps to get the project up and running:

#### 1. Clone the Repository
   
  ```bash
  git clone https://github.com/fdiengdoh/crud.git
  cd crud
  ```
#### 2. Install Dependencies

Make sure you have Composer installed. Then run:

```bash
composer install
```
#### 3. Configure Environment Variables
  - Copy the example file that is provided or create a new .env file in the project root.
  - Update the values in .env with your settings. Example:

```dotenv
# Database Settings
DB_HOST=localhost
DB_NAME=crud_fdh
DB_USER=db_user
DB_PASS=password

# Email Settings
SMTP_HOST=your.smtp.host
SMTP_USER=email@domain.com
SMTP_PASS=smtp_password
SMTP_PORT=465
MAIL_FROM=email@domain.com
MAIL_FROM_NAME="App Name"

# Website specific settings that you can change
BASE_URL=your-domain.com
FEATURED_POST=featured-post
POSTS_PER_PAGE=5
POPULAR_POST=3
RECENT_POST=5
HOME_POST=blog,technology,web-design

# Define Environment either development or live
ENVIRONMENT=development
```
#### 4. Create a Database
This app uses an extended database of `delight-im/auth` and was included as [MySQL.sql](MySQL.sql) file that add `posts`, `user_profile`, `categories` etc to use for the `CRUD` application so you'll need to create a *database* for this app (eg. crud_fdh) and the MySQL file will be installed automatically as described below.

#### 5. Install the Database and Default Admin User
Assumming your webserver is at `http://localhost` then open your browser and navigate to:

```
http://localhost/install.php
```
- Fill in the default admin credentials in the form and submit.
- This script will:

  -  Create necessary database tables.
  -  Create a default admin user.
  -  Display a verification link to confirm email.

> [!CAUTION]
> Important: For security, remove or secure the install.php script after installation.

#### 6. Use your App
Ensure your document root is set to the `public_html/` directory, then visit [http://localhost/](http://localhost) in your browser.

### Additional Notes
#### - Environment Variables:
  All environment variables from `.env` are auto-loaded in `init.php` and defined as constants, making them available throughout your application.

#### - Admin & Auth:
  The app uses `delight-im/auth` for user authentication and role management. Here I've used only three roles (Admin, Author and Subscriber). Adjust settings as needed. You can access admin area by visiting `http:localhost/admin` in your browser. A non admin user can login to the app by visiting `http://localhost/login`

#### - Routing:
  Using `.htaccess` file to write pretty  url and the `public_html/index.php` acts as a central router to dispatch requests to the appropriate pages in the `app/` directory.
#### - Assets:
  Ensure the `public_html/image/` `public_html/assets` and `public_html/js/vendor/tinymce/` directories exist and have the correct permissions.

### Next Steps
Once the installation and initial setup are complete, you can further customize the application or review security and performance enhancements before moving to production.
