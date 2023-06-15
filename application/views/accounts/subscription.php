<!DOCTYPE html>
<html lang="en">

<!-- Mirrored from html.truelysell.com/template3/provider-subscription.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 05 Jun 2023 21:58:44 GMT -->

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Truelysell | Template</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?= base_url('assets/img/favicon.png'); ?>">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css'); ?>">

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/fontawesome/css/fontawesome.min.css'); ?>">
    <link rel="stylesheet" href="<?= base_url('assets/plugins/fontawesome/css/all.min.css'); ?>">

    <!-- Fearther CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/feather.css'); ?>">

    <!-- select CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/select2/css/select2.min.css'); ?>">

    <!-- Datetimepicker CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-datetimepicker.min.css'); ?>">

    <!-- Datatables CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/plugins/datatables/datatables.min.css'); ?>">

    <!-- Main CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css?v=' . uniqid()); ?>">

</head>

<body class="provider-body">
    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <div class="widget-title">
                            <h4>Subscription</h4>
                        </div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <ul class="subs-list d-flex" style="overflow:auto;">
                            <li>
                                <a href="?renewal=monthly" class="<?= $renewal === 'monthly' || $renewal === null ? 'active' : '' ?>">Every Month</a>
                            </li>
                            <li>
                                <a href="?renewal=quarterly" class="<?= $renewal === 'quarterly' ? 'active' : '' ?>">Every 3 Months</a>
                            </li>
                            <li>
                                <a href="?renewal=semi-annually" class="<?= $renewal === 'semi-annually' ? 'active' : '' ?>">Every 6 Months</a>
                            </li>
                            <li>
                                <a href="?renewal=annually" class="<?= $renewal === 'annually' ? 'active' : '' ?>">Every Year</a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Subscription -->
                <div class="row provider-price">
                    <div class="col-md-12">
                        <div class="choose-title text-center">
                            <h6>Choose A Plan</h6>
                        </div>
                    </div>
                    <?php

                    $priority = [0 => 'Low', 1 => 'Medium', 2 => 'Hight', 3 => "Highest"];
                    $status = ['inactive', 'active'];
                    foreach ($subscriptions as $key => $sub) {
                        $sub->offers = json_decode($sub->offers, false);
                        $active = false;
                    ?>
                        <!-- Price List -->
                        <div class="col-md-3 d-flex">
                            <div class="price-card <?= $active ? 'active' : '' ?> flex-fill">
                                <div class="price-head">
                                    <div class="price-level">
                                        <h6><?= $sub->package ?></h6>
                                        <?php if ($active) { ?>
                                            <span class="badge-success">Active</span>
                                        <?php } ?>
                                    </div>
                                    <h1><?= $this->setting->get('currency', 'GHS') ?><?= $sub->price ?> <span>/ <?= $sub->renewal ?></span></h1>
                                </div>
                                <div class="price-body">
                                    <ul>
                                        <li class="<?= $sub->offers->badge ?>-badge"><?= ucfirst($sub->offers->badge) ?> Badge</li>
                                        <li class="active"><?= $sub->offers->number_of_daily_posts ?> posts per day</li>
                                        <li class="active"><?= $sub->offers->images_per_post ?> images max per each post</li>
                                        <li class="active"><?= $priority[$sub->offers->profile_priority]; ?> profile discovery</li>
                                        <li class="<?= $status[intval($sub->offers->show_all_contacts)] ?>">Show all contacts</li>
                                        <li class="<?= $status[intval($sub->offers->show_all_social_links)] ?>">Show all social links</li>
                                        <li class="<?= $status[intval($sub->offers->show_website_link)] ?>">Show website link</li>
                                    </ul>
                                    <div class="text-center">
                                        <a href="#" class="btn btn-choose">Choose Plan <i class="feather-arrow-right-circle"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /Price List -->
                    <?php } ?>
                </div>
                <!-- /Subscription -->

            </div>
        </div>
        <!-- Delete Account -->
        <div class="modal fade custom-modal" id="del-account">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-bottom-0 justify-content-between">
                        <h5 class="modal-title">Delete Account</h5>
                        <button type="button" class="close-btn" data-bs-dismiss="modal" aria-label="Close"><i class="feather-x"></i></button>
                    </div>
                    <div class="modal-body pt-0">
                        <div class="write-review">
                            <form action="https://html.truelysell.com/template3/login.html">
                                <p>Are you sureyou want to delete This Account? To delete your account, Type your password.</p>
                                <div class="form-group">
                                    <label class="col-form-label">Password</label>
                                    <div class="pass-group">
                                        <input type="password" class="form-control pass-input" placeholder="*************">
                                        <span class="toggle-password feather-eye"></span>
                                    </div>
                                </div>
                                <div class="modal-submit text-end">
                                    <a href="#" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</a>
                                    <button type="submit" class="btn btn-danger">Delete Account</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Delete Account -->

        <!-- Cursor -->
        <div class="mouse-cursor cursor-outer"></div>
        <div class="mouse-cursor cursor-inner"></div>
        <!-- /Cursor -->

    </div>

    <!-- jQuery -->
    <script data-cfasync="false" src="https://html.truelysell.com/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <script src="<?= base_url('assets/js/jquery-3.6.1.min.js') ?>"></script>

    <!-- Bootstrap Core JS -->
    <script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>

    <!-- Fearther JS -->
    <script src="<?= base_url('assets/js/feather.min.js') ?>"></script>

    <!-- select JS -->
    <script src="<?= base_url('assets/plugins/select2/js/select2.min.js') ?>"></script>

    <!-- Datetimepicker JS -->
    <script src="<?= base_url('assets/js/moment.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/bootstrap-datetimepicker.min.js') ?>"></script>

    <!-- Slimscroll JS -->
    <script src="<?= base_url('assets/plugins/slimscroll/jquery.slimscroll.min.js') ?>"></script>

    <!-- Datatables JS -->
    <script src="<?= base_url('assets/plugins/datatables/jquery.dataTables.min.js') ?>"></script>
    <script src="<?= base_url('assets/plugins/datatables/datatables.min.js') ?>"></script>

    <!-- Custom JS -->
    <script src="<?= base_url('assets/js/script.js') ?>"></script>

</body>

<!-- Mirrored from html.truelysell.com/template3/provider-subscription.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 05 Jun 2023 22:01:13 GMT -->

</html>