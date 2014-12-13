<!DOCTYPE html>
<html>
<head>
    <meta charset=utf-8>
    <title>Errors | {info}</title>
    <style> * {
            margin: 0;
            padding: 0;
            border: 0;
            outline: 0;
        }

        html, body {
            min-width: 100%;
            min-height: 100%;
            width: 100%;
            height: 100%;
            background: #efefef;
            z-index: 0;
        }

        #error_page {
            position: relative;
            padding: 15px 0;
        }

        .error_box {
            position: relative;
            min-width: 90%;
            margin: 0 5%;
        }

        .error_box .error_box_header, .error_box .error_box_body, .error_box .error_box_footer {
            position: relative;
            background: #fff;
            color: #333;
        }

        .error_box .error_box_header {
            position: relative;
            height: 10px;
            border-left: #ccc solid 1px;
            border-right: #ccc solid 1px;
            border-top: #ccc solid 1px;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }

        .error_box .error_panel .header {
            position: relative;
            display: block;
            top: -10px;
            font-family: Arial;
            font-size: 12pt;
            font-weight: bold;
            margin: 10px 0 8px 30px;
        }

        .error_panel {
            border-bottom: #ccc solid 1px;
        }

        .error_box .error_panel .notice {
            top: -10px;
            position: relative;
            display: block;
            margin: 5px 15px 0 15px;
            padding: 5px 10px;
            font-family: Arial;
            font-weight: bold;
            font-size: 10pt;
            background: #F7F7F7;
            border: #ccc solid 1px;
            border-radius: 10px;
        }

        .error_box .error_box_body {
            position: relative;
            padding: 5px 0;
            border-left: #ccc solid 1px;
            border-right: #ccc solid 1px;
        }

        .error_box .error_box_body span {
            position: relative;
            display: block;
            font-family: Consolas;
            font-style: normal;
            font-size: 0.81em;
            padding: 5px 5px;
            margin: 0 5px 0 30px;
        }

        .error_box .error_box_footer {
            position: relative;
            height: 23px;
            border-left: #ccc solid 1px;
            border-right: #ccc solid 1px;
            border-bottom: #ccc solid 1px;
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
        }

        .error_box .error_box_footer .copy {
            position: absolute;
            font-size: 8pt;
            bottom: 5px;
            right: 20px;
        }

        .error_box .error_box_footer .complete_time {
            position: absolute;
            font-size: 8pt;
            bottom: 5px;
            left: 20px;
        } </style>
</head>
<body>
<div id=error_page>
    <div class=error_box>
        <div class=error_box_header></div>
        <?php $count = 1 ?>
        <?php if ($this->errors): ?>
            <?php foreach ($this->errors as $error): ?>
                <div class=error_panel>
                    <div class=error_box_body>
                        <div>
                            <span class=header><?= $count++ ?>. [<?= $error[0] ?>]</span>
                        </div>
                        <div>
                            <span class=notice><?= $error[1] ?> in <?= $error[2] ?> on line <?= $error[3] ?></span>
                        </div>
                    <span>
                    <?php $countStack = 0 ?>
                        <?php foreach ($error[4] as $value): ?>
                            <?= $countStack++ ?> in function
                            <strong> <?= $value['class'] . $value['type'] . $value['function'] ?>()</strong>
                            <?php if ($value['file']): ?> in <strong><?= $value['file'] ?></strong><?php endif ?>
                            <?php if ($value['line']): ?> on line <strong><?= $value['line'] ?></strong><?php endif ?>
                            <br/>
                        <?php endforeach ?>
                    </span>
                    </div>
                </div>
            <?php endforeach ?>
        <?php endif ?>
        <div class=error_box_footer>
            <span class=complete_time>Generated in {time} sec.</span>
            <span class=copy>{info}</span>
        </div>
    </div>
</div>
</body>
</html>