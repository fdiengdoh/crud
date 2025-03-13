
      <!-- Start Footer -->
      <footer class="footer mt-auto py-3">
         <div class="container">
            <div class="row text-light text-center justify-content-center">
               <div class="col-sm-10 col-md-8 col-lg-6">
                  <a href="/" class="logo">
                     <svg height="30" style="fill: #ccc">
                        <use href="#logo-white"></use>
                     </svg>
                  </a>
                  <p>I'm a hobbyist photographer, videographer, vlogger. I build webapps too using PHP - MySQL. I'm a Chemistry Teacher. </p>                  
                  <ul class="social pt-3">
                     <li><a href="https://whatsapp.com/channel/0029Va9VuhPI1rcehvL1h91F"><i class="bi bi-whatsapp"></i></a></li>
                     <li><a href="https://facebook.com/fdphy"><i class="bi bi-facebook"></i></a></li>
                     <li><a href="https://www.twitter.com/fdphy_in"><i class="bi bi-twitter-x"></i></a></li>
                     <li><a href="https://www.instagram.com/fdphy"><i class="bi bi-instagram"></i></a></li>
                     <li><a href="https://www.youtube.com/fdphy"><i class="bi bi-youtube"></i></a></li>
                  </ul>
               </div>
            </div>
         </div>
      </footer>
      
      <!-- End Footer -->
      <!-- Start Socket -->
      <div class="socket text-light text-center py-3">
         <p> &copy; 2010 - <?= date('Y') ?> Designed By <a href="http://fdiengdoh.com" target="_blank">fdiengdoh.com</a></p>
         <div class="container my-5"></div>
      </div>
      <!-- End Socket -->
   </div>
   <!-- Wrapper Class Ends -->
      <script rel="preload" src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous" as="script"></script>
      <?= $BScripts ?>
      <script>
         //Get the button
         let mybutton = document.getElementById("btn-back-to-top");

         // When the user scrolls down 20px from the top of the document, show the button
         window.onscroll = function () {
         scrollFunction();
         };

         function scrollFunction() {
         if (
            document.body.scrollTop > 20 ||
            document.documentElement.scrollTop > 20
         ) {
            mybutton.style.display = "block";
         } else {
            mybutton.style.display = "none";
         }
         }
         // When the user clicks on the button, scroll to the top of the document
         mybutton.addEventListener("click", backToTop);

         function backToTop() {
         document.body.scrollTop = 0;
         document.documentElement.scrollTop = 0;
         }
      </script>
   </body>
</html>
