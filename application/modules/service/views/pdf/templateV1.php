<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="google-site-verification" content="MUgD96uDtxuU0XZbEgDEzdoDxW0snX5tyveEoodDUWc"/>
    <meta property="fb:app_id" content="3306922722959128"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"/>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?= _lang("Menu") ?></title>
    <style>
        * {
            font-family: "Helvetica Neue", Helvetica, "Droid Sans", sans-serif
        }

        .content{
            padding: 50px;
        }

        .table-responsive {
            display: block;
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch
        }

        .table-responsive table{
            width: 100%;
        }

        .table-responsive > .table-bordered {
            border: 0
        }

        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529
        }

        .table tr .header {
            background-color: #3f92ff;
        }

        .table td, .table th {
            padding: .75rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6
        }

        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6
        }

        .table tbody + tbody {
            border-top: 2px solid #dee2e6
        }

        .table-sm td, .table-sm th {
            padding: .3rem
        }

        .table td h4 {
            padding-top: 0;
            margin-top: 0;
        }

        .table td, .table th {
            padding: 0.75rem;
            vertical-align: top;
            border-top: 1px solid #dee2e6;
        }

        .logo{
            text-align: center;
        }

        .menu-table{
            width: 100%;
        }

        td:empty {
            background: url('<?=AdminTemplateManager::assets("service","images/dot.png")?>') repeat; /* Replace 'path/to/dots.png' with the actual path to your image */
        }

        .image-container-50{
            width: 150px;
            height: 150px;
            border-radius: 50%;
            display: inline-block;
            background-size: cover;
            background-repeat: no-repeat;
        }

        .page_break { page-break-before: always; }
    </style>
</head>
<body>
<div class="container list-pdf mt-5" id="">
    <div class="row">
        <div class="col-md-12">
            <div class="content">
                <div class="logo">
                    <?php

                        $storeLogo = "";
                        if($store['logo']!="" && is_string($store['logo'])){
                            $images = ImageManagerUtils::check(json_decode($store['logo'],JSON_OBJECT_AS_ARRAY));
                            $images = ImageManagerUtils::parseFirstImages($images,ImageManagerUtils::IMAGE_SIZE_200);
                            $storeLogo = $images;
                        }else{
                            $logo = ImageManagerUtils::getValidImages(ConfigManager::getValue('APP_LOGO'));
                            $imageUrl = adminAssets("images/logo.png");
                            if(!empty($logo)){
                                $storeLogo = $logo[0]["200_200"]["url"];
                            }
                        }
                    ?>
                    <img style="width: 100px"
                         src="<?=$storeLogo ?>"/>
                </div>
                <div class="menu-table table-responsive">
                    <table>
                        <tr>
                            <td colspan="3"><h1>Start</h1></td>
                        </tr>
                        <tr>
                            <td style="width: 12%">
                                <img style="width: 100px" class="direct-chat-img" src="<?=$storeLogo?>" alt="Image">
                            </td>
                            <td>
                                <h2 style="margin-bottom: 0">########</h2>
                                <span>####</span>
                            </td>
                            <td style="text-align: right">
                                <h1>#.#$</h1>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <div class="padding-bottom30px"></div>
        </div>
    </div>
</div>
</body>
</html>


