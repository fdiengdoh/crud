<!doctype html>
<html lang="en">
   <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <meta name="description" content="<?= isset($description) ? $description: 'Farlando Diengdoh Blogging randomly about Chemistry, Web Apps, Technology, Culture etc.' ?>">
      <meta name="keywords" content="<?= isset($keywords) ? $keywords: 'Chemistry, Web Apps, Technology, Culture, etc.' ?>">
      <meta name="author" content="Farlando Diengdoh">
      <title><?= $title ?></title>
      <link rel="preconnect" href="https://fonts.googleapis.com">
      <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
      <link rel="preload" href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400..800;1,400..800&family=Imperial+Script&display=swap" as="style" onload="this.onload=null;this.rel='stylesheet'">
      <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous" as="style" onload="this.onload=null;this.rel='stylesheet'">
      <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
      <!-- Custom styles for this template -->
      <link rel="preload" href="/css/style.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
      <link rel="preload" href="/css/carousel.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
   </head>
   <body class="p-0 bg-secondary">
      <!-- SVG File for LOGO -->
      <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
         <symbol id="logo" viewBox="0 0 209.41 30">
            <style type="text/css">.st0{fill:#BB4D27;} .st1{fill:#2B7FC3;}.st2{fill:#395547;}</style>
            <path class="st0" d="m31.67 13.92c2.82 0 5.14 2.32 5.14 5.14s-2.32 5.14-5.14 5.14-5.14-2.32-5.14-5.14 2.32-5.14 5.14-5.14m0-2.37c-4.14 0-7.46 3.37-7.46 7.46s3.37 7.51 7.46 7.51 7.46-3.37 7.46-7.46-3.32-7.51-7.46-7.51z"/>
            <path class="st0" d="m33.45 2.59c1.59 0 2.87 1.27 2.87 2.87s-1.27 2.87-2.87 2.87c-1.59 0-2.87-1.27-2.87-2.87s1.32-2.87 2.87-2.87m0-2.32c-2.87 0-5.19 2.32-5.19 5.19s2.32 5.19 5.19 5.19 5.19-2.32 5.19-5.19-2.33-5.19-5.19-5.19z"/>
            <path class="st0" d="m18.98 5.23c0.86 0 1.55 0.68 1.55 1.55s-0.73 1.55-1.55 1.55-1.55-0.68-1.55-1.55 0.68-1.55 1.55-1.55m0-2.32c-2.14 0-3.87 1.73-3.87 3.87s1.73 3.87 3.87 3.87 3.87-1.73 3.87-3.87-1.74-3.87-3.87-3.87z"/>
            <path class="st0" d="m43.05 10.73c0.86 0 1.55 0.68 1.55 1.55 0 0.86-0.68 1.55-1.55 1.55-0.86 0-1.55-0.68-1.55-1.55s0.68-1.55 1.55-1.55m0-2.36c-2.14 0-3.87 1.73-3.87 3.87s1.73 3.87 3.87 3.87 3.87-1.73 3.87-3.87-1.73-3.87-3.87-3.87z"/>
            <path class="st0" d="m16.61 20.29c2.05 0 3.69 1.64 3.69 3.69s-1.64 3.69-3.69 3.69-3.69-1.64-3.69-3.69 1.64-3.69 3.69-3.69m0-2.32c-3.32 0-6.01 2.68-6.01 6.01s2.68 6.01 6.01 6.01 6.01-2.68 6.01-6.01-2.69-6.01-6.01-6.01z"/>
            <path class="st0" d="m6.23 5.18c2.14 0 3.91 1.73 3.91 3.91s-1.73 3.91-3.91 3.91-3.91-1.73-3.91-3.91 1.73-3.91 3.91-3.91m0-2.32c-3.45 0-6.23 2.78-6.23 6.23s2.78 6.23 6.23 6.23 6.23-2.78 6.23-6.23-2.81-6.23-6.23-6.23z"/>
            <polygon class="st0" points="17.2 12.23 15.11 12.23 15.11 14.37 17.2 14.37"/>
            <polygon class="st0" points="22.12 14.1 20.02 14.1 20.02 16.24 22.12 16.24"/>
            <polygon class="st0" points="26.3 4.68 24.21 4.68 24.21 6.77 26.3 6.77"/>
            <polygon class="st0" points="23.89 27.43 21.8 27.43 21.8 29.53 23.89 29.53"/>
            <path class="st1" d="m52.01 7.46h1.91v-0.87c0-2 0.27-3.41 0.86-4.32 0.59-0.86 1.55-1.32 2.91-1.32 0.55 0 1.05 0.05 1.5 0.09 0.46 0.05 0.91 0.18 1.37 0.41l-0.59 1.96c-0.36-0.18-0.73-0.27-1.05-0.32-0.31-0.05-0.63-0.09-0.95-0.09-0.41 0-0.73 0.09-1 0.27-0.23 0.18-0.41 0.46-0.5 0.77-0.14 0.36-0.18 0.82-0.23 1.37-0.04 0.54-0.04 1.23-0.04 2.05h3.28v2.04h-3.28v13.79h-2.28v-13.74h-1.91v-2.09z"/>
            <path class="st1" d="m70.99 17.88c0 1.09 0 2.05 0.05 2.91 0 0.86 0.09 1.73 0.23 2.59h-1.55l-0.5-1.91h-0.14c-0.32 0.64-0.77 1.18-1.41 1.59s-1.41 0.64-2.28 0.64c-1.73 0-2.96-0.68-3.82-2-0.82-1.32-1.23-3.41-1.23-6.28 0-2.68 0.5-4.73 1.55-6.14 1-1.41 2.41-2.09 4.23-2.09 0.59 0 1.09 0.05 1.46 0.09 0.36 0.09 0.73 0.18 1.18 0.36v-6.51h2.28l-0.05 16.75zm-2.28-7.92c-0.32-0.27-0.64-0.46-1-0.55-0.36-0.14-0.86-0.18-1.46-0.18-1.14 0-2 0.5-2.59 1.5-0.64 1-0.96 2.59-0.96 4.69 0 0.91 0.05 1.77 0.18 2.5 0.14 0.73 0.27 1.41 0.55 1.96 0.23 0.55 0.55 0.96 0.96 1.27 0.41 0.32 0.86 0.46 1.46 0.46 1.5 0 2.5-0.91 2.91-2.68-0.05-0.01-0.05-8.97-0.05-8.97z"/>
            <path class="st1" d="m74.22 2.68c0-0.5 0.14-0.91 0.41-1.23s0.64-0.46 1.14-0.46 0.86 0.14 1.14 0.46c0.27 0.32 0.46 0.73 0.46 1.23s-0.14 0.91-0.46 1.18-0.68 0.41-1.14 0.41-0.82-0.14-1.14-0.46c-0.27-0.27-0.41-0.68-0.41-1.13zm0.41 4.78h2.28v15.84h-2.28v-15.84z"/>
            <path class="st1" d="m90.1 22.24c-0.5 0.46-1.14 0.82-1.91 1.09s-1.59 0.36-2.46 0.36c-1 0-1.87-0.18-2.59-0.59-0.73-0.41-1.32-0.96-1.82-1.68-0.46-0.73-0.82-1.59-1.05-2.59s-0.32-2.14-0.32-3.41c0-2.68 0.5-4.78 1.5-6.19s2.41-2.14 4.19-2.14c0.59 0 1.18 0.09 1.77 0.23s1.09 0.46 1.55 0.86c0.46 0.46 0.82 1.05 1.14 1.87 0.27 0.82 0.41 1.87 0.41 3.14 0 0.36 0 0.73-0.05 1.14s-0.09 0.82-0.09 1.27h-8.05c0 0.91 0.09 1.73 0.23 2.46s0.36 1.37 0.68 1.87 0.73 0.91 1.23 1.18 1.09 0.41 1.87 0.41c0.59 0 1.14-0.09 1.68-0.32 0.55-0.23 1-0.46 1.27-0.77l0.82 1.81zm-1.77-8.5c0.05-1.59-0.18-2.73-0.68-3.5-0.5-0.73-1.14-1.09-2-1.09-0.96 0-1.73 0.36-2.32 1.09s-0.91 1.91-1 3.5h6z"/>
            <path class="st1" d="m101.34 23.29v-9.65c0-1.59-0.18-2.73-0.55-3.41-0.36-0.68-1.05-1.05-1.96-1.05-0.86 0-1.55 0.27-2.09 0.77-0.55 0.5-0.96 1.14-1.18 1.87v11.51h-2.28v-15.87h1.64l0.41 1.68h0.09c0.41-0.59 0.96-1.05 1.64-1.46s1.5-0.59 2.46-0.59c0.68 0 1.27 0.09 1.77 0.27s0.96 0.5 1.32 0.96 0.59 1.05 0.77 1.82 0.27 1.73 0.27 2.87v10.24l-2.31 0.04z"/>
            <path class="st1" d="m116.63 24.02c0 2.05-0.46 3.55-1.37 4.51s-2.23 1.46-3.96 1.46c-1.05 0-1.91-0.09-2.59-0.27s-1.23-0.36-1.64-0.64l0.68-1.96c0.41 0.18 0.86 0.36 1.41 0.55 0.5 0.18 1.14 0.27 1.87 0.27 1.27 0 2.18-0.36 2.64-1.09s0.73-1.91 0.73-3.59v-1.18h-0.09c-0.32 0.5-0.77 0.86-1.32 1.14-0.55 0.27-1.18 0.41-2.05 0.41-1.73 0-2.96-0.64-3.78-1.96s-1.18-3.41-1.18-6.23 0.5-4.78 1.55-6.19 2.59-2.09 4.64-2.09c1 0 1.87 0.09 2.55 0.27 0.73 0.18 1.37 0.41 1.91 0.68v15.91zm-2.28-14.29c-0.64-0.32-1.46-0.5-2.46-0.5-1.09 0-1.96 0.5-2.59 1.46-0.64 1-1 2.55-1 4.69 0 0.86 0.05 1.68 0.14 2.46 0.09 0.73 0.27 1.41 0.55 1.96 0.27 0.55 0.59 1 0.96 1.32 0.41 0.32 0.86 0.46 1.46 0.46 0.82 0 1.46-0.23 1.91-0.64 0.46-0.41 0.82-1.05 1-1.91l0.03-9.3z"/>
            <path class="st1" d="m129.83 17.88c0 1.09 0 2.05 0.05 2.91 0 0.86 0.09 1.73 0.23 2.59h-1.55l-0.5-1.91h-0.14c-0.32 0.64-0.77 1.18-1.41 1.59s-1.41 0.64-2.28 0.64c-1.73 0-2.96-0.68-3.82-2-0.82-1.32-1.23-3.41-1.23-6.28 0-2.68 0.5-4.73 1.55-6.14 1-1.41 2.41-2.09 4.23-2.09 0.59 0 1.09 0.05 1.46 0.09 0.36 0.09 0.73 0.18 1.18 0.36v-6.51h2.28v16.75h-0.05zm-2.28-7.92c-0.32-0.27-0.64-0.46-1-0.55-0.36-0.14-0.86-0.18-1.46-0.18-1.14 0-2 0.5-2.59 1.5-0.64 1-0.96 2.59-0.96 4.69 0 0.91 0.05 1.77 0.18 2.5s0.27 1.41 0.55 1.96c0.23 0.55 0.55 0.96 0.96 1.27s0.86 0.46 1.46 0.46c1.5 0 2.5-0.91 2.91-2.68-0.05-0.01-0.05-8.97-0.05-8.97z"/>
            <path class="st1" d="m132.37 15.37c0-2.87 0.5-4.96 1.46-6.28s2.37-2 4.19-2c1.96 0 3.37 0.68 4.28 2.05s1.37 3.46 1.37 6.23-0.5 4.96-1.5 6.28-2.37 2-4.19 2c-1.96 0-3.37-0.68-4.28-2.05-0.87-1.36-1.33-3.45-1.33-6.23zm2.42 0c0 0.91 0.05 1.77 0.18 2.55 0.14 0.77 0.32 1.41 0.59 1.96s0.59 0.96 1.05 1.27c0.41 0.32 0.91 0.46 1.5 0.46 1.09 0 1.91-0.5 2.46-1.46 0.55-1 0.82-2.55 0.82-4.78 0-0.91-0.05-1.73-0.18-2.5-0.14-0.77-0.32-1.41-0.59-1.96s-0.59-0.96-1.05-1.27-0.91-0.46-1.5-0.46c-1.09 0-1.91 0.5-2.46 1.5-0.55 0.96-0.82 2.51-0.82 4.69z"/>
            <path class="st1" d="m154.4 23.29v-9.6c0-1.46-0.18-2.59-0.5-3.37-0.36-0.77-1.05-1.14-2.09-1.14-0.73 0-1.41 0.27-2 0.77-0.59 0.55-1 1.18-1.23 2v11.38h-2.28v-22.2h2.28v7.83h0.09c0.41-0.55 0.96-1 1.55-1.37 0.64-0.36 1.41-0.5 2.32-0.5 0.68 0 1.32 0.09 1.82 0.27s0.96 0.5 1.27 1c0.32 0.46 0.59 1.09 0.77 1.87 0.18 0.77 0.27 1.73 0.27 2.87v10.24h-2.28v-0.05z"/>
            <path class="st2" d="m161.54 22.2c0-0.59 0.14-1.05 0.46-1.37 0.27-0.32 0.68-0.46 1.18-0.46s0.91 0.14 1.18 0.46 0.46 0.77 0.46 1.37-0.14 1.09-0.46 1.41-0.68 0.46-1.18 0.46-0.91-0.14-1.18-0.46c-0.32-0.32-0.46-0.77-0.46-1.41z"/>
            <path class="st2" d="m175.65 22.88c-0.55 0.41-1.14 0.73-1.87 0.91-0.68 0.18-1.41 0.27-2.18 0.27-1.05 0-1.91-0.18-2.64-0.59s-1.27-0.96-1.73-1.73c-0.46-0.73-0.77-1.64-0.96-2.68-0.18-1.05-0.32-2.23-0.32-3.5 0-2.78 0.5-4.87 1.46-6.33 1-1.46 2.41-2.18 4.23-2.18 0.82 0 1.55 0.09 2.18 0.23 0.59 0.14 1.14 0.36 1.55 0.59l-0.64 2.05c-0.86-0.5-1.82-0.73-2.82-0.73-1.18 0-2.05 0.5-2.64 1.55s-0.91 2.64-0.91 4.82c0 0.86 0.05 1.73 0.18 2.5 0.14 0.77 0.36 1.46 0.64 2 0.32 0.59 0.68 1.05 1.18 1.37 0.46 0.32 1.09 0.5 1.77 0.5 0.55 0 1.09-0.09 1.59-0.27s0.86-0.41 1.18-0.68l0.75 1.9z"/>
            <path class="st2" d="m176.52 15.6c0-2.91 0.5-5.05 1.5-6.42s2.41-2.05 4.28-2.05c2 0 3.46 0.68 4.37 2.09 0.96 1.41 1.41 3.55 1.41 6.37s-0.5 5.1-1.5 6.46c-1 1.37-2.46 2.05-4.28 2.05-2 0-3.46-0.68-4.37-2.09-0.91-1.4-1.41-3.59-1.41-6.41zm2.41 0c0 0.96 0.05 1.82 0.18 2.59 0.14 0.77 0.32 1.46 0.59 2 0.27 0.55 0.64 1 1.05 1.32s0.96 0.46 1.55 0.46c1.14 0 1.96-0.5 2.55-1.5 0.55-1 0.82-2.64 0.82-4.87 0-0.91-0.05-1.77-0.18-2.59-0.14-0.77-0.32-1.46-0.59-2-0.27-0.55-0.64-1-1.05-1.32-0.46-0.32-0.96-0.46-1.55-0.46-1.09 0-1.96 0.5-2.5 1.5-0.6 1-0.87 2.64-0.87 4.87z"/>
            <path class="st2" d="m198.31 23.66v-9.6c0-0.86-0.05-1.59-0.09-2.23-0.05-0.64-0.18-1.14-0.32-1.5-0.18-0.41-0.41-0.68-0.73-0.86s-0.68-0.27-1.18-0.27c-0.73 0-1.37 0.27-1.87 0.86s-0.86 1.23-1.05 1.96v11.65h-2.32v-16.17h1.64l0.41 1.73h0.09c0.46-0.64 1-1.14 1.64-1.5 0.64-0.41 1.41-0.59 2.41-0.59 0.82 0 1.5 0.18 2 0.55 0.55 0.36 0.96 1 1.23 1.91 0.41-0.77 0.96-1.37 1.68-1.77 0.73-0.46 1.5-0.64 2.37-0.64 0.73 0 1.32 0.09 1.82 0.27s0.91 0.5 1.23 0.96 0.55 1.09 0.68 1.87c0.14 0.77 0.23 1.73 0.23 2.91v10.6h-2.32v-10.43c0-1.41-0.14-2.46-0.41-3.14s-0.91-1.05-1.87-1.05c-0.82 0-1.46 0.27-1.96 0.77s-0.82 1.18-1 2.05v11.65l-2.31 0.01z"/>
         </symbol>
         <symbol id="logo-white" viewBox="0 0 209.41 30">
            <path  d="m31.67 13.92c2.82 0 5.14 2.32 5.14 5.14s-2.32 5.14-5.14 5.14-5.14-2.32-5.14-5.14 2.32-5.14 5.14-5.14m0-2.37c-4.14 0-7.46 3.37-7.46 7.46s3.37 7.51 7.46 7.51 7.46-3.37 7.46-7.46-3.32-7.51-7.46-7.51z"/>
            <path  d="m33.45 2.59c1.59 0 2.87 1.27 2.87 2.87s-1.27 2.87-2.87 2.87c-1.59 0-2.87-1.27-2.87-2.87s1.32-2.87 2.87-2.87m0-2.32c-2.87 0-5.19 2.32-5.19 5.19s2.32 5.19 5.19 5.19 5.19-2.32 5.19-5.19-2.33-5.19-5.19-5.19z"/>
            <path  d="m18.98 5.23c0.86 0 1.55 0.68 1.55 1.55s-0.73 1.55-1.55 1.55-1.55-0.68-1.55-1.55 0.68-1.55 1.55-1.55m0-2.32c-2.14 0-3.87 1.73-3.87 3.87s1.73 3.87 3.87 3.87 3.87-1.73 3.87-3.87-1.74-3.87-3.87-3.87z"/>
            <path  d="m43.05 10.73c0.86 0 1.55 0.68 1.55 1.55 0 0.86-0.68 1.55-1.55 1.55-0.86 0-1.55-0.68-1.55-1.55s0.68-1.55 1.55-1.55m0-2.36c-2.14 0-3.87 1.73-3.87 3.87s1.73 3.87 3.87 3.87 3.87-1.73 3.87-3.87-1.73-3.87-3.87-3.87z"/>
            <path  d="m16.61 20.29c2.05 0 3.69 1.64 3.69 3.69s-1.64 3.69-3.69 3.69-3.69-1.64-3.69-3.69 1.64-3.69 3.69-3.69m0-2.32c-3.32 0-6.01 2.68-6.01 6.01s2.68 6.01 6.01 6.01 6.01-2.68 6.01-6.01-2.69-6.01-6.01-6.01z"/>
            <path  d="m6.23 5.18c2.14 0 3.91 1.73 3.91 3.91s-1.73 3.91-3.91 3.91-3.91-1.73-3.91-3.91 1.73-3.91 3.91-3.91m0-2.32c-3.45 0-6.23 2.78-6.23 6.23s2.78 6.23 6.23 6.23 6.23-2.78 6.23-6.23-2.81-6.23-6.23-6.23z"/>
            <polygon  points="17.2 12.23 15.11 12.23 15.11 14.37 17.2 14.37"/>
            <polygon  points="22.12 14.1 20.02 14.1 20.02 16.24 22.12 16.24"/>
            <polygon  points="26.3 4.68 24.21 4.68 24.21 6.77 26.3 6.77"/>
            <polygon  points="23.89 27.43 21.8 27.43 21.8 29.53 23.89 29.53"/>
            <path  d="m52.01 7.46h1.91v-0.87c0-2 0.27-3.41 0.86-4.32 0.59-0.86 1.55-1.32 2.91-1.32 0.55 0 1.05 0.05 1.5 0.09 0.46 0.05 0.91 0.18 1.37 0.41l-0.59 1.96c-0.36-0.18-0.73-0.27-1.05-0.32-0.31-0.05-0.63-0.09-0.95-0.09-0.41 0-0.73 0.09-1 0.27-0.23 0.18-0.41 0.46-0.5 0.77-0.14 0.36-0.18 0.82-0.23 1.37-0.04 0.54-0.04 1.23-0.04 2.05h3.28v2.04h-3.28v13.79h-2.28v-13.74h-1.91v-2.09z"/>
            <path  d="m70.99 17.88c0 1.09 0 2.05 0.05 2.91 0 0.86 0.09 1.73 0.23 2.59h-1.55l-0.5-1.91h-0.14c-0.32 0.64-0.77 1.18-1.41 1.59s-1.41 0.64-2.28 0.64c-1.73 0-2.96-0.68-3.82-2-0.82-1.32-1.23-3.41-1.23-6.28 0-2.68 0.5-4.73 1.55-6.14 1-1.41 2.41-2.09 4.23-2.09 0.59 0 1.09 0.05 1.46 0.09 0.36 0.09 0.73 0.18 1.18 0.36v-6.51h2.28l-0.05 16.75zm-2.28-7.92c-0.32-0.27-0.64-0.46-1-0.55-0.36-0.14-0.86-0.18-1.46-0.18-1.14 0-2 0.5-2.59 1.5-0.64 1-0.96 2.59-0.96 4.69 0 0.91 0.05 1.77 0.18 2.5 0.14 0.73 0.27 1.41 0.55 1.96 0.23 0.55 0.55 0.96 0.96 1.27 0.41 0.32 0.86 0.46 1.46 0.46 1.5 0 2.5-0.91 2.91-2.68-0.05-0.01-0.05-8.97-0.05-8.97z"/>
            <path  d="m74.22 2.68c0-0.5 0.14-0.91 0.41-1.23s0.64-0.46 1.14-0.46 0.86 0.14 1.14 0.46c0.27 0.32 0.46 0.73 0.46 1.23s-0.14 0.91-0.46 1.18-0.68 0.41-1.14 0.41-0.82-0.14-1.14-0.46c-0.27-0.27-0.41-0.68-0.41-1.13zm0.41 4.78h2.28v15.84h-2.28v-15.84z"/>
            <path  d="m90.1 22.24c-0.5 0.46-1.14 0.82-1.91 1.09s-1.59 0.36-2.46 0.36c-1 0-1.87-0.18-2.59-0.59-0.73-0.41-1.32-0.96-1.82-1.68-0.46-0.73-0.82-1.59-1.05-2.59s-0.32-2.14-0.32-3.41c0-2.68 0.5-4.78 1.5-6.19s2.41-2.14 4.19-2.14c0.59 0 1.18 0.09 1.77 0.23s1.09 0.46 1.55 0.86c0.46 0.46 0.82 1.05 1.14 1.87 0.27 0.82 0.41 1.87 0.41 3.14 0 0.36 0 0.73-0.05 1.14s-0.09 0.82-0.09 1.27h-8.05c0 0.91 0.09 1.73 0.23 2.46s0.36 1.37 0.68 1.87 0.73 0.91 1.23 1.18 1.09 0.41 1.87 0.41c0.59 0 1.14-0.09 1.68-0.32 0.55-0.23 1-0.46 1.27-0.77l0.82 1.81zm-1.77-8.5c0.05-1.59-0.18-2.73-0.68-3.5-0.5-0.73-1.14-1.09-2-1.09-0.96 0-1.73 0.36-2.32 1.09s-0.91 1.91-1 3.5h6z"/>
            <path  d="m101.34 23.29v-9.65c0-1.59-0.18-2.73-0.55-3.41-0.36-0.68-1.05-1.05-1.96-1.05-0.86 0-1.55 0.27-2.09 0.77-0.55 0.5-0.96 1.14-1.18 1.87v11.51h-2.28v-15.87h1.64l0.41 1.68h0.09c0.41-0.59 0.96-1.05 1.64-1.46s1.5-0.59 2.46-0.59c0.68 0 1.27 0.09 1.77 0.27s0.96 0.5 1.32 0.96 0.59 1.05 0.77 1.82 0.27 1.73 0.27 2.87v10.24l-2.31 0.04z"/>
            <path  d="m116.63 24.02c0 2.05-0.46 3.55-1.37 4.51s-2.23 1.46-3.96 1.46c-1.05 0-1.91-0.09-2.59-0.27s-1.23-0.36-1.64-0.64l0.68-1.96c0.41 0.18 0.86 0.36 1.41 0.55 0.5 0.18 1.14 0.27 1.87 0.27 1.27 0 2.18-0.36 2.64-1.09s0.73-1.91 0.73-3.59v-1.18h-0.09c-0.32 0.5-0.77 0.86-1.32 1.14-0.55 0.27-1.18 0.41-2.05 0.41-1.73 0-2.96-0.64-3.78-1.96s-1.18-3.41-1.18-6.23 0.5-4.78 1.55-6.19 2.59-2.09 4.64-2.09c1 0 1.87 0.09 2.55 0.27 0.73 0.18 1.37 0.41 1.91 0.68v15.91zm-2.28-14.29c-0.64-0.32-1.46-0.5-2.46-0.5-1.09 0-1.96 0.5-2.59 1.46-0.64 1-1 2.55-1 4.69 0 0.86 0.05 1.68 0.14 2.46 0.09 0.73 0.27 1.41 0.55 1.96 0.27 0.55 0.59 1 0.96 1.32 0.41 0.32 0.86 0.46 1.46 0.46 0.82 0 1.46-0.23 1.91-0.64 0.46-0.41 0.82-1.05 1-1.91l0.03-9.3z"/>
            <path  d="m129.83 17.88c0 1.09 0 2.05 0.05 2.91 0 0.86 0.09 1.73 0.23 2.59h-1.55l-0.5-1.91h-0.14c-0.32 0.64-0.77 1.18-1.41 1.59s-1.41 0.64-2.28 0.64c-1.73 0-2.96-0.68-3.82-2-0.82-1.32-1.23-3.41-1.23-6.28 0-2.68 0.5-4.73 1.55-6.14 1-1.41 2.41-2.09 4.23-2.09 0.59 0 1.09 0.05 1.46 0.09 0.36 0.09 0.73 0.18 1.18 0.36v-6.51h2.28v16.75h-0.05zm-2.28-7.92c-0.32-0.27-0.64-0.46-1-0.55-0.36-0.14-0.86-0.18-1.46-0.18-1.14 0-2 0.5-2.59 1.5-0.64 1-0.96 2.59-0.96 4.69 0 0.91 0.05 1.77 0.18 2.5s0.27 1.41 0.55 1.96c0.23 0.55 0.55 0.96 0.96 1.27s0.86 0.46 1.46 0.46c1.5 0 2.5-0.91 2.91-2.68-0.05-0.01-0.05-8.97-0.05-8.97z"/>
            <path  d="m132.37 15.37c0-2.87 0.5-4.96 1.46-6.28s2.37-2 4.19-2c1.96 0 3.37 0.68 4.28 2.05s1.37 3.46 1.37 6.23-0.5 4.96-1.5 6.28-2.37 2-4.19 2c-1.96 0-3.37-0.68-4.28-2.05-0.87-1.36-1.33-3.45-1.33-6.23zm2.42 0c0 0.91 0.05 1.77 0.18 2.55 0.14 0.77 0.32 1.41 0.59 1.96s0.59 0.96 1.05 1.27c0.41 0.32 0.91 0.46 1.5 0.46 1.09 0 1.91-0.5 2.46-1.46 0.55-1 0.82-2.55 0.82-4.78 0-0.91-0.05-1.73-0.18-2.5-0.14-0.77-0.32-1.41-0.59-1.96s-0.59-0.96-1.05-1.27-0.91-0.46-1.5-0.46c-1.09 0-1.91 0.5-2.46 1.5-0.55 0.96-0.82 2.51-0.82 4.69z"/>
            <path  d="m154.4 23.29v-9.6c0-1.46-0.18-2.59-0.5-3.37-0.36-0.77-1.05-1.14-2.09-1.14-0.73 0-1.41 0.27-2 0.77-0.59 0.55-1 1.18-1.23 2v11.38h-2.28v-22.2h2.28v7.83h0.09c0.41-0.55 0.96-1 1.55-1.37 0.64-0.36 1.41-0.5 2.32-0.5 0.68 0 1.32 0.09 1.82 0.27s0.96 0.5 1.27 1c0.32 0.46 0.59 1.09 0.77 1.87 0.18 0.77 0.27 1.73 0.27 2.87v10.24h-2.28v-0.05z"/>
            <path  d="m161.54 22.2c0-0.59 0.14-1.05 0.46-1.37 0.27-0.32 0.68-0.46 1.18-0.46s0.91 0.14 1.18 0.46 0.46 0.77 0.46 1.37-0.14 1.09-0.46 1.41-0.68 0.46-1.18 0.46-0.91-0.14-1.18-0.46c-0.32-0.32-0.46-0.77-0.46-1.41z"/>
            <path  d="m175.65 22.88c-0.55 0.41-1.14 0.73-1.87 0.91-0.68 0.18-1.41 0.27-2.18 0.27-1.05 0-1.91-0.18-2.64-0.59s-1.27-0.96-1.73-1.73c-0.46-0.73-0.77-1.64-0.96-2.68-0.18-1.05-0.32-2.23-0.32-3.5 0-2.78 0.5-4.87 1.46-6.33 1-1.46 2.41-2.18 4.23-2.18 0.82 0 1.55 0.09 2.18 0.23 0.59 0.14 1.14 0.36 1.55 0.59l-0.64 2.05c-0.86-0.5-1.82-0.73-2.82-0.73-1.18 0-2.05 0.5-2.64 1.55s-0.91 2.64-0.91 4.82c0 0.86 0.05 1.73 0.18 2.5 0.14 0.77 0.36 1.46 0.64 2 0.32 0.59 0.68 1.05 1.18 1.37 0.46 0.32 1.09 0.5 1.77 0.5 0.55 0 1.09-0.09 1.59-0.27s0.86-0.41 1.18-0.68l0.75 1.9z"/>
            <path  d="m176.52 15.6c0-2.91 0.5-5.05 1.5-6.42s2.41-2.05 4.28-2.05c2 0 3.46 0.68 4.37 2.09 0.96 1.41 1.41 3.55 1.41 6.37s-0.5 5.1-1.5 6.46c-1 1.37-2.46 2.05-4.28 2.05-2 0-3.46-0.68-4.37-2.09-0.91-1.4-1.41-3.59-1.41-6.41zm2.41 0c0 0.96 0.05 1.82 0.18 2.59 0.14 0.77 0.32 1.46 0.59 2 0.27 0.55 0.64 1 1.05 1.32s0.96 0.46 1.55 0.46c1.14 0 1.96-0.5 2.55-1.5 0.55-1 0.82-2.64 0.82-4.87 0-0.91-0.05-1.77-0.18-2.59-0.14-0.77-0.32-1.46-0.59-2-0.27-0.55-0.64-1-1.05-1.32-0.46-0.32-0.96-0.46-1.55-0.46-1.09 0-1.96 0.5-2.5 1.5-0.6 1-0.87 2.64-0.87 4.87z"/>
            <path  d="m198.31 23.66v-9.6c0-0.86-0.05-1.59-0.09-2.23-0.05-0.64-0.18-1.14-0.32-1.5-0.18-0.41-0.41-0.68-0.73-0.86s-0.68-0.27-1.18-0.27c-0.73 0-1.37 0.27-1.87 0.86s-0.86 1.23-1.05 1.96v11.65h-2.32v-16.17h1.64l0.41 1.73h0.09c0.46-0.64 1-1.14 1.64-1.5 0.64-0.41 1.41-0.59 2.41-0.59 0.82 0 1.5 0.18 2 0.55 0.55 0.36 0.96 1 1.23 1.91 0.41-0.77 0.96-1.37 1.68-1.77 0.73-0.46 1.5-0.64 2.37-0.64 0.73 0 1.32 0.09 1.82 0.27s0.91 0.5 1.23 0.96 0.55 1.09 0.68 1.87c0.14 0.77 0.23 1.73 0.23 2.91v10.6h-2.32v-10.43c0-1.41-0.14-2.46-0.41-3.14s-0.91-1.05-1.87-1.05c-0.82 0-1.46 0.27-1.96 0.77s-0.82 1.18-1 2.05v11.65l-2.31 0.01z"/>
         </symbol>
      </svg>
      <!-- SVG File for LOGO Ends -->
       <!-- Wrapper Class Start -->
      <div class="container bg-light wrapper p-0">
         <!-- Top Bar -->
         <div class="top-bar">
            <div class="px-3">
               <div class="col-12 py-2 text-center text-md-end social-top sm-h1">
                  <a href="https://whatsapp.com/channel/0029Va9VuhPI1rcehvL1h91F"><i class="bi bi-whatsapp"></i></a>
                  <a href="https://facebook.com/fdphy"><i class="bi bi-facebook"></i></a>
                  <a href="https://www.twitter.com/fdphy_in"><i class="bi bi-twitter-x"></i></a>
                  <a href="https://www.instagram.com/fdphy"><i class="bi bi-instagram"></i></a>
                  <a href="https://www.youtube.com/fdphy"><i class="bi bi-youtube"></i></a>
               </div>
            </div>
         </div>
         <!-- End Top Bar -->
         <div class="p-3">
            <a href="/" class="logo">
               <svg height="40">
                  <use href="#logo"></use>
               </svg>
            </a>
         </div>
         <header>
			<?php require_once APP_DIR . '/include/nav.php' ?>
		</header>
