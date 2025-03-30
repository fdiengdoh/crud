# CRUD Blog App

This is a PHP-based CRUD Blog App that uses PSR-4 autoloading that features pretty URLs, a custom caching system, authentication via [delight-im/auth](https://github.com/delight-im/PHP-Auth), [PHP Mailer](https://github.com/PHPMailer/PHPMailer) for sending email and flexible routing system. The CRUD system would allow admin/authors for creating posts and admins to manage categories. A public viewer can comment on a post. 

# Theme

This app is styled using [Bootstrap 5.3](https://getbootstrap.com) and I've made minor changes to the colour scheme. You can take a look at the theme files that are kept in [public_html](public_html) directory. 

## Table of Contents

- [Features](#features)
- [Core Functions](#core-function)
- [Installation](#installation)
- [Configuration](#configuration)
- [File Structure](#file-structure)
- [Usage](#usage)
- [Routing](#routing)
- [Caching](#caching)
- [Security](#security)
- [Additional Notes](#additional-notes)

## Features

- **User Authentication:** Registration, login, password reset, and email verification using delight‑im/auth.
- **Post Management:** Create, read, update, and delete posts with support for pretty URLs.
- **Category Filtering:** Browse posts by category.
- **Dynamic Routing:** Only allow valid post and category slugs.
- **Caching System:** File‑based caching that serves static pages for guest visitors and bypasses cache for dynamic content.
- **Social Previews:** Support for Open Graph and Twitter Cards (configurable in header files).
- **Commenting:** Comment system that has moderation/flag system 

## Core Function

The core functionality of this app is to use as a blogging application. All the requirements for the app to function is in the `/src` directory (The full [file structure](#file-structure) is given below). A sample `/app` dir and sample `/public_html` dir is added if you want to use this app as it is. If you want to have your own theme and system, you can use this app as follows:

```php
<?php
   require_once __DIR__ . '../vendor/autoload.php';
   use App\Database;         //Database connection
   use Delight\Auth\Auth;    //Authentication with delight-im/auth
   use App\Controllers\PostController;       // To perform CRUD on posts
   use App\Controllers\CategoryController;   // To Manage categories

   // Instantiate classes
   $pdo = Database::getConnection();
   $auth = new Auth($pdo);
   // Instantiate controllers for retrieving allowed slugs
   $postController = new PostController();
   $categoryController = new CategoryController();
```
Each class is well commented on their uses, so feel free to explore the [src](src) directory. If you want a quick set up and just start posting then you can follow the steps below: 

## Installation

1. **Clone the Repository**

   ```bash
   git clone https://github.com/fdiengdoh/crud.git
   cd crud
   ```

2. **Install Composer Dependencies**

   Make sure you have [Composer](https://getcomposer.org/) installed, then run:

   ```bash
   composer install
   ```

3. **Set Up the Environment**

   - Create a copy of the `.env.example` file (if provided) as `.env` in the project root.
   - Edit the `.env` file to include your database credentials, email settings, and any other configuration:
     
   ```dotenv
   # Database Settings
   DB_HOST=localhost
   DB_NAME=db_name
   DB_USER=db_user
   DB_PASS=password
   
   #Email Settings
   SMTP_HOST=smtp.email
   SMTP_USER=smtp.user
   SMTP_PASS=smtp.password
   SMTP_PORT=465
   MAIL_FROM=email@address
   MAIL_FROM_NAME="CRUD Blog App"
   
   # Website specific settings
   BASE_URL=https://yourdomain.com
   LOGIN_URL=https://your-domain.com
   FEATURED_POST=featured-post
   POSTS_PER_PAGE=5
   POPULAR_POST=3
   RECENT_POST=5
   HOME_POST=list,of,blog,for,home,page
   
   # Define Environment ad development or live
   ENVIRONMENT=development
   ```

4. **Run the Installation Script**

   - Navigate to your site in a browser (e.g., `https://yourdomain.com/install.php`).
   - Fill in the required fields (default admin email, username, password).
   - The installer will create the necessary database tables and a default admin user.
   - **Important:** Once installation is complete, remove or secure the `install.php` file.

## Configuration

- **init.php**  
  Loads environment variables, starts sessions, initializes the database and authentication systems.

- **Cache Configuration**  
  Cache settings are stored in a configuration file (e.g., `cache_config/config.php`). Adjust the TTL (time-to-live) as needed.

## File Structure
```
project-root/
├── app/
│   ├── admin/
│   │   ├── admin-categories.php      # Admin categories management
│   │   ├── admin-flag-comment.php    # Admin flag comments
│   │   ├── admin-panel.php           # Admin panel for various functions
│   │   ├── footer-auth.php           # Authenticated pages footer
│   │   ├── header-auth.php           # Authenticated pages header
│   │   ├── upload-image.php          # Upload images functionality
│   │   └── view-logs.php             # Admin view error logs for live environment
│   ├── include/
│   │   ├── footer.php                # Common footer for public pages
│   │   ├── header.php                # Common header for public pages
│   │   ├── nav.php                   # Common navigation for public pages
│   │   └── sidebar.php               # Common sidebar for public pages
│   ├── users/
│   │   ├── ajax_handler.php          # Ajax handler file
│   │   ├── edit-profile.php          # User edit profile
│   │   ├── forgot-password.php       # User forgot password function
│   │   ├── increment-view.php        # Increment views of a page
│   │   ├── login.php                 # Login function
│   │   ├── my-posts.php              # List users post
│   │   ├── post-create.php           # Create a new post
│   │   ├── post-delete.php           # Delete post
│   │   ├── post-edit.php             # Edit existing post
│   │   ├── profile.php               # View User's Profile
│   │   ├── register.php              # Register a new user
│   │   ├── reset-password.php        # Reset password of an existing user
│   │   └── verify.php                # Email based verification for new user
│   ├── 404.php                       # Public 404 Error page
│   ├── category.php                  # Public list of categories
│   ├── home.php                      # Public Home page
│   ├── report-comment.php            # Public report comments
│   └── single-post.php               # Public show a single post
├── cache_config/
│   └── config.php                    # Cache configuration file
├── logs/
│   └── error.log                     # Error logs for live environment
├── public_html/                      # Publicly accesible files
│   ├── assets/
│   │   ├── image/
│   │   │   ├── default-feature.jpg   # Default feature image in jpg
│   │   │   └── default-feature.webp  # Default feature image in webp
│   │   └── profile/
│   │       └── profile.png           # Default profile image
│   ├── css/
│   │   ├── carousel.css              # Carousel css
│   │   ├── carousel.min.css
│   │   ├── style.css                 # public pages style css
│   │   └── style.min.css
│   ├── image/
│   ├── js/                           # vendor js
│   ├── blog-theme.html               # sample blog-theme in bootstrap 5
│   ├── category-theme.html           # sample category theme in bootstrap 5
│   ├── favicon.ico                   # sample favicon
│   ├── .htaccess                     # Sample .htaccess for routing
│   ├── index.php                     # index.ph file for routing pretty url
│   ├── install.php                   # install file for use at the start
│   └── theme.html                    # public home page theme
├── src/
│   ├── Controllers/                  # Various controller class
│   │   ├── AuthController.php
│   │   ├── CategoryController.php
│   │   ├── CommentController.php
│   │   ├── MediaController.php
│   │   ├── PostController.php
│   │   └── ProfileController.php
│   ├── Helpers/                      # Helper Class
│   │   ├── AuthHelper.php
│   │   └── Link.php
│   ├── Utils/
│   │   └── Cache.php                 # Cache class
│   ├── AuthConstants.php             # Role constants (ROLE_ADMIN, ROLE_AUTHOR, ROLE_SUBSCRIBER)
│   ├── Database.php                  # Database connection handler
│   ├── Mailer.php                    # Email-sending class using PHPMailer
│   └── ProfileManager.php            # Profile manager class
├── vendor/                           # Composer packages
├── composer.json                     # Composer dependencies configuration
├── composer.lock
├── .env                              # Environment configuration file
├── init.php                          # Global initialization file
└── MySQL.sql                         # Modified MySQL file from delight-im for installation
```

## Usage

- **Front-End Routing:**  
  The front controller in `public/index.php` handles all requests. It:
  - Checks for cached pages and serves them for GET requests (for guest users).
  - Uses allowed post and category slugs (retrieved from the database) to determine if the URL is valid.
  - Redirects invalid URLs to a 404 page.
  - Normalizes category URLs to include page numbers (e.g., `/search/label/blog/1`).

- **Admin and User Pages:**  
  Authentication is handled by `delight‑im/auth`. Users can log in, manage posts, update profiles, etc.

- **Caching:**  
  Pages are cached as static HTML files based on the full request URI (e.g., `/search/label/blog/1`).  
  Admin can clear all cache if needed, or you can just use `?refresh=1` to force a refresh if needed.

## Routing

The router uses a `routeFile` variable to determine which page to include based on the URL path. For example:

- `/` loads `home.php`
- `/profile/username` loads `users/profile.php`
- `/search/label/blog/1` loads `category.php` (with page number normalized)
- Otherwise, the system assumes the URL is a post slug and loads `single-post.php`
- Invalid URLs redirect to `/404.html`

## Caching

- **Cache Key Generation:**  
  The cache key is based on the normalized request URI. For category pages, the page number is appended (e.g., `/search/label/blog/1`).

- **Clearing Cache:**  
  You can clear specific pages or all cache using the methods in `App\Utils\Cache`.

## Security

- **Authentication:**  
  delight‑im/auth is used for secure login and session management.
- **HTTPS Enforcement:**  
  `.htaccess` that enforces HTTPS.
- **CSP and Other Headers:**  
  Additional security headers are set to protect against clickjacking and content sniffing pre-build in `delight-im/auth`.
- **Cache Bypass for Authenticated Users:**  
  Only guest visitors receive cached pages, ensuring authenticated users always see dynamic content.

## Additional Notes

- **Development vs. Production:**  
  Make sure to update error reporting and caching settings in your `.env` file for production.
- **Further Improvements:**  
  Future improvements might include autosave, social meta tags, and more.
