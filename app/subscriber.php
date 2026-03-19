<?php
use \App\Controllers\SubscribersController;

$subController = new SubscribersController();

$response = ['alert' => 'danger', 'message' => 'Unknown Error'];
if(isset($_GET['unsubscribe']) && ($_GET['unsubscribe'] === 'true')){
    //Get Email
    $email = $_GET['email'];

    $title = "Unsubscribe from Mailing List &raquo; fdiengdoh.com";
    //Unsubscribe email address
    $result = $subController->unsubscribe($email);
    $response['message'] = '<i class="bi bi-exclamation-circle-fill"></i> ' . $result;
}elseif(isset($_GET['verify']) && ($_GET['verify'] === 'true')){
    //Get Email address
    $email = $_GET['email'];
    $token = $_GET['token'];

    $title = "Verify subscriber for Mailing List &raquo; fdiengdoh.com";

    //verify a subscriber
    $result = $subController->verify($email,$token);
    $response = ['alert' => 'success', 'message' => '<i class="bi bi-info-circle-fill"></i> ' . $result ];
}else{
    header("Location: /404.html");
    exit;
}

// Set the page title for header
$description = "Mailing List settings for subscribers";

include APP_DIR . '/include/header.php';
?>

<main>
    <div class="row m-0">
        <div class="col-md-8">
            <div class="container p-5">
                <div class="alert alert-<?= $response['alert'] ?> d-flex align-items-center" role="alert">
                <div>
                    <?= $response['message'] ?>
                </div>
                </div>
            </div>
        </div>
        <?php require_once(APP_DIR . '/include/sidebar.php'); ?>
    <!-- Row Ends -->
    </div>
</main>

<?php include APP_DIR . '/include/footer.php'; ?>